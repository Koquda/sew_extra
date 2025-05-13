class Question {
    constructor(question, options, correctAnswer) {
        this.question = question;
        this.options = options;
        this.correctAnswer = correctAnswer;
    }
}

class Quiz {
    constructor(questions) {
        this.questions = questions;
        this.currentQuestionIndex = 0;
        this.score = 0;
        this.answered = new Array(questions.length).fill(false);
    }

    getCurrentQuestion() {
        return this.questions[this.currentQuestionIndex];
    }

    checkAnswer(selectedAnswer) {
        const currentQuestion = this.getCurrentQuestion();
        if (selectedAnswer === currentQuestion.correctAnswer) {
            this.score++;
        }
        this.answered[this.currentQuestionIndex] = true;
    }

    isComplete() {
        return this.answered.every(answer => answer);
    }

    getScore() {
        return this.score;
    }
}

class Game {
    constructor() {
        this.questions = [
            new Question(
                "¿Qué tipo de plato es el 'Pote Asturiano'?",
                ["Un postre", "Una sopa", "Un guiso", "Una ensalada", "Un aperitivo"],
                2
            ),
            new Question(
                "¿Cuál es el plato más famoso de la gastronomía de Pesoz?",
                ["Fabada Asturiana", "Pote Asturiano", "Cachopo", "Arroz con leche", "Sidra"],
                1
            ),
            new Question(
                "¿Qué ruta turística pasa por Pesoz?",
                ["Ruta del Cares", "Ruta del Vino", "Ruta de los Pueblos", "Ruta del Agua", "Ruta de la Sidra"],
                1
            ),
            new Question(
                "¿Qué tipo de clima predomina en Pesoz?",
                ["Mediterráneo", "Atlántico", "Continental", "Subtropical", "Alpino"],
                1
            ),
            new Question(
                "¿Qué actividad turística es más popular en Pesoz?",
                ["Esquí", "Senderismo", "Surf", "Ciclismo", "Parapente"],
                1
            ),
            new Question(
                "¿Qué tipo de alojamiento se puede reservar en Pesoz?",
                ["Hotel 5 estrellas", "Casa rural", "Camping", "Hostal", "Apartamento"],
                1
            ),
            new Question(
                "¿Qué monumento histórico destaca en Pesoz?",
                ["Castillo", "Iglesia", "Palacio", "Torre", "Muralla"],
                1
            ),
            new Question(
                "¿Qué producto local es más conocido de Pesoz?",
                ["Vino", "Queso", "Miel", "Sidra", "Chorizo"],
                3
            ),
            new Question(
                "¿Qué estación del año es la más recomendada para visitar Pesoz?",
                ["Invierno", "Primavera", "Verano", "Otoño", "Todas son buenas"],
                4
            ),
            new Question(
                "¿Qué tipo de paisaje predomina en Pesoz?",
                ["Desierto", "Montaña", "Playa", "Llanura", "Selva"],
                1
            )
        ];
        
        this.quiz = new Quiz(this.questions);
        this.initializeGame();
    }

    initializeGame() {
        $(document).ready(() => {
            $('input[type="button"]').on('click', () => this.startGame());
        });
    }

    startGame() {
        $('section').empty();
        this.displayQuestion();
    }

    displayQuestion() {
        const question = this.quiz.getCurrentQuestion();
        const questionHtml = `
            <article>
                <h3>Pregunta ${this.quiz.currentQuestionIndex + 1} de ${this.questions.length}</h3>
                <p>${question.question}</p>
                <form>
                    <fieldset>
                        <legend>Opciones</legend>
                        ${question.options.map((option, index) => `
                            <p data-option="${index}">
                                <input type="radio" name="answer" value="${index}">
                                <label>${option}</label>
                            </p>
                        `).join('')}
                    </fieldset>
                    <input type="button" value="${this.quiz.currentQuestionIndex === this.questions.length - 1 ? 'Finalizar' : 'Siguiente'}">
                </form>
            </article>
        `;

        $('section').html(questionHtml);
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Hacer que el párrafo completo sea clickeable
        $('fieldset p').on('click', function() {
            const radioInput = $(this).find('input[type="radio"]');
            radioInput.prop('checked', true);
        });

        $('input[type="button"]').on('click', () => {
            const selectedAnswer = $('input[name="answer"]:checked').val();
            
            if (selectedAnswer === undefined) {
                alert('Por favor, selecciona una respuesta');
                return;
            }

            this.quiz.checkAnswer(parseInt(selectedAnswer));

            if (this.quiz.currentQuestionIndex < this.questions.length - 1) {
                this.quiz.currentQuestionIndex++;
                this.displayQuestion();
            } else {
                this.showResults();
            }
        });
    }

    showResults() {
        const score = this.quiz.getScore();
        const resultHtml = `
            <article>
                <h3>¡Juego terminado!</h3>
                <p>Tu puntuación final es: ${score} de ${this.questions.length}</p>
                <input type="button" value="Jugar de nuevo">
            </article>
        `;

        $('section').html(resultHtml);
        $('input[type="button"]').on('click', () => {
            this.quiz = new Quiz(this.questions);
            this.startGame();
        });
    }
}

// Inicializar el juego cuando se carga la página
$(document).ready(() => {
    new Game();
}); 