/**
 * Clase para gestionar el juego de preguntas sobre Pesoz
 */
class JuegoPesoz {
    constructor() {
        // Inicialización de variables
        this.preguntas = [];
        this.respuestasUsuario = new Array(10).fill(null);
        this.preguntaActual = 0;
        this.puntuacion = 0;
        this.inicializado = false;
        this.juegoIniciado = false;
        
        // Inicialización cuando el documento esté listo
        $(document).ready(this.inicializar.bind(this));
    }
    
    /**
     * Inicializa el juego
     */
    inicializar() {
        if (this.inicializado) return;
        this.inicializado = true;
        
        // Cargar las preguntas del juego
        this.cargarPreguntas();
        
        // Configurar el botón de inicio, select input type button
        $("input[type='button']").on("click", () => {
            this.iniciarJuego();
        });
    }
    
    /**
     * Inicia el juego cuando el usuario hace clic en el botón
     */
    iniciarJuego() {
        if (this.juegoIniciado) return;
        this.juegoIniciado = true;
        
        // Resetear las respuestas del usuario
        this.respuestasUsuario = new Array(10).fill(null);
        this.preguntaActual = 0;
        this.puntuacion = 0;
        
        // Ocultar instrucciones y mostrar el juego
        $("section > h2, section > p, section > ul, section > input[type='button']").hide();
        
        // Configurar la interfaz del juego
        this.configurarInterfaz();
        
        // Mostrar la primera pregunta
        this.mostrarPregunta(0);
    }
    
    /**
     * Carga las preguntas del juego
     */
    cargarPreguntas() {
        this.preguntas = [
            {
                pregunta: "¿Dónde está situado el municipio de Pesoz?",
                opciones: [
                    "En el sureste de Asturias",
                    "En el suroeste de Asturias",
                    "En el noreste de Asturias",
                    "En el noroeste de Asturias",
                    "En el centro de Asturias"
                ],
                respuestaCorrecta: 1
            },
            {
                pregunta: "¿Cuál es uno de los platos típicos de la gastronomía de Pesoz?",
                opciones: [
                    "Fabada asturiana",
                    "Arroz con leche",
                    "Cachopo",
                    "Callos a la asturiana",
                    "Pote asturiano"
                ],
                respuestaCorrecta: 0
            },
            {
                pregunta: "¿Qué monumento destacado se puede visitar en Pesoz?",
                opciones: [
                    "Catedral de Oviedo",
                    "Iglesia de Santa María",
                    "Basílica de Covadonga",
                    "Palacio de los Condes de Pesoz",
                    "Castro de Coaña"
                ],
                respuestaCorrecta: 3
            },
            {
                pregunta: "¿Qué río atraviesa el municipio de Pesoz?",
                opciones: [
                    "Río Nalón",
                    "Río Navia",
                    "Río Sella",
                    "Río Esva",
                    "Río Eo"
                ],
                respuestaCorrecta: 1
            },
            {
                pregunta: "¿Cuál es la principal actividad económica tradicional de Pesoz?",
                opciones: [
                    "Minería",
                    "Pesca",
                    "Ganadería",
                    "Industria siderúrgica",
                    "Turismo de playa"
                ],
                respuestaCorrecta: 2
            },
            {
                pregunta: "¿Qué festividad tradicional se celebra en Pesoz?",
                opciones: [
                    "Descenso del Sella",
                    "Fiestas de San Agustín",
                    "Carnaval de Avilés",
                    "Semana Negra de Gijón",
                    "Festival de la Sidra"
                ],
                respuestaCorrecta: 1
            },
            {
                pregunta: "¿Qué ruta de senderismo es popular en Pesoz?",
                opciones: [
                    "Ruta del Cares",
                    "Senda del Oso",
                    "Ruta de las Xanas",
                    "Ruta del Río Agüeira",
                    "Ruta de los Lagos de Covadonga"
                ],
                respuestaCorrecta: 3
            },
            {
                pregunta: "¿Cuál es la estación del año ideal para visitar Pesoz?",
                opciones: [
                    "Verano",
                    "Invierno",
                    "Primavera",
                    "Otoño",
                    "Cualquier época del año"
                ],
                respuestaCorrecta: 4
            },
            {
                pregunta: "¿Qué servicio turístico ofrece el municipio de Pesoz?",
                opciones: [
                    "Parque acuático",
                    "Casino",
                    "Casas rurales",
                    "Estación de esquí",
                    "Campos de golf"
                ],
                respuestaCorrecta: 2
            },
            {
                pregunta: "¿Qué caracteriza al clima de Pesoz?",
                opciones: [
                    "Clima mediterráneo",
                    "Clima atlántico húmedo",
                    "Clima continental",
                    "Clima desértico",
                    "Clima subtropical"
                ],
                respuestaCorrecta: 1
            }
        ];
    }
    
    /**
     * Configura la interfaz inicial del juego
     */
    configurarInterfaz() {
        // Crear contenedor principal del juego
        const contenedorJuego = $("<article></article>").attr("data-tipo", "contenedor-juego");
        
        // Crear elemento para mostrar la pregunta
        const pregunta = $("<article></article>").attr("data-tipo", "pregunta");
        
        // Crear elemento para las opciones
        const opciones = $("<article></article>").attr("data-tipo", "opciones");
        
        // Crear elementos para navegación
        const navegacion = $("<article></article>").attr("data-tipo", "navegacion");
        const btnAnterior = $("<button></button>")
            .text("Anterior")
            .attr("data-accion", "anterior")
            .on("click", this.preguntaAnterior.bind(this));
        
        const btnSiguiente = $("<button></button>")
            .text("Siguiente")
            .attr("data-accion", "siguiente")
            .on("click", this.preguntaSiguiente.bind(this));
        
        const btnFinalizar = $("<button></button>")
            .text("Finalizar juego")
            .attr("data-accion", "finalizar")
            .on("click", this.finalizarJuego.bind(this));
        
        // Crear elemento para resultados
        const resultados = $("<article></article>")
            .attr("data-tipo", "resultados")
            .hide();
        
        // Añadir elementos al DOM
        navegacion.append(btnAnterior, btnSiguiente, btnFinalizar);
        contenedorJuego.append(pregunta, opciones, navegacion, resultados);
        
        // Añadir el contenedor a la sección existente
        $("main > section").append(contenedorJuego);
        
        // Guardar referencias a los elementos para uso posterior
        this.elementoPregunta = pregunta;
        this.elementoOpciones = opciones;
        this.elementoNavegacion = navegacion;
        this.elementoResultados = resultados;
    }
    
    /**
     * Muestra una pregunta específica
     * @param {number} indice - Índice de la pregunta a mostrar
     */
    mostrarPregunta(indice) {
        if (indice < 0 || indice >= this.preguntas.length) return;
        
        this.preguntaActual = indice;
        const preguntaObj = this.preguntas[indice];
        
        // Actualizar título de la pregunta
        this.elementoPregunta.html(`<h2>Pregunta ${indice + 1} de ${this.preguntas.length}</h2>
                           <p>${preguntaObj.pregunta}</p>`);
        
        // Generar opciones
        this.elementoOpciones.empty();
        
        preguntaObj.opciones.forEach((opcion, i) => {
            const seleccionada = this.respuestasUsuario[indice] === i ? "checked" : "";
            
            const opcionHTML = $("<label></label>");
            const radioBtn = $("<input>")
                .attr("type", "radio")
                .attr("name", `pregunta${indice}`)
                .attr("value", i)
                .prop("checked", seleccionada);
            
            if (seleccionada) {
                radioBtn.prop("checked", true);
            }
            
            radioBtn.on("change", () => {
                this.respuestasUsuario[indice] = i;
            });
            
            opcionHTML.append(radioBtn, opcion);
            this.elementoOpciones.append(opcionHTML);
        });
    }
    
    /**
     * Muestra la pregunta anterior
     */
    preguntaAnterior() {
        if (this.preguntaActual > 0) {
            this.mostrarPregunta(this.preguntaActual - 1);
        }
    }
    
    /**
     * Muestra la pregunta siguiente
     */
    preguntaSiguiente() {
        if (this.preguntaActual < this.preguntas.length - 1) {
            this.mostrarPregunta(this.preguntaActual + 1);
        }
    }
    
    /**
     * Comprueba si todas las preguntas han sido respondidas
     * @returns {boolean} - Verdadero si todas las preguntas tienen respuesta
     */
    todasRespondidas() {
        return !this.respuestasUsuario.includes(null);
    }
    
    /**
     * Calcula la puntuación obtenida
     * @returns {number} - Puntuación final (0-10)
     */
    calcularPuntuacion() {
        let aciertos = 0;
        
        for (let i = 0; i < this.preguntas.length; i++) {
            if (this.respuestasUsuario[i] === this.preguntas[i].respuestaCorrecta) {
                aciertos++;
            }
        }
        
        this.puntuacion = aciertos;
        return this.puntuacion;
    }
    
    /**
     * Finaliza el juego y muestra resultados
     */
    finalizarJuego() {
        if (!this.todasRespondidas()) {
            alert("Debes responder todas las preguntas antes de finalizar el juego.");
            return;
        }
        
        // Calcular puntuación
        const puntuacion = this.calcularPuntuacion();
        
        // Mostrar resultados
        this.elementoResultados.empty();
        
        const titulo = $("<h2></h2>").text("Resultados del juego");
        const puntuacionText = $("<p></p>").text(`Tu puntuación: ${puntuacion} de 10`);
        
        this.elementoResultados.append(titulo, puntuacionText);
        
        // Mostrar respuestas correctas e incorrectas
        const respuestasHTML = $("<ul></ul>");
        
        for (let i = 0; i < this.preguntas.length; i++) {
            const esCorrecta = this.respuestasUsuario[i] === this.preguntas[i].respuestaCorrecta;
            const resultado = esCorrecta ? "correcto" : "incorrecto";
            
            const respuestaLi = $("<li></li>").attr("data-resultado", resultado);
            
            const respuestaUsuario = this.preguntas[i].opciones[this.respuestasUsuario[i]];
            const respuestaCorrecta = this.preguntas[i].opciones[this.preguntas[i].respuestaCorrecta];
            
            respuestaLi.text(`Pregunta ${i + 1}: ${esCorrecta ? "Correcta" : "Incorrecta"}`);
            
            if (!esCorrecta) {
                respuestaLi.append($("<p></p>").text(`Tu respuesta: ${respuestaUsuario}`));
                respuestaLi.append($("<p></p>").text(`Respuesta correcta: ${respuestaCorrecta}`));
            }
            
            respuestasHTML.append(respuestaLi);
        }
        
        this.elementoResultados.append(respuestasHTML);
        
        // Mostrar resultados
        this.elementoPregunta.hide();
        this.elementoOpciones.hide();
        this.elementoNavegacion.hide();
        this.elementoResultados.show();
        
        // Añadir botones para reiniciar el juego o volver al inicio en una columna vertical
        const btnReiniciar = $("<button></button>")
            .text("Jugar de nuevo")
            .attr("data-accion", "reiniciar")
            .css({
                "display": "block",
                "width": "100%",
                "margin-bottom": "1rem",
                "margin-top": "2rem"
            })
            .on("click", this.reiniciarJuego.bind(this));
            
        const btnVolver = $("<button></button>")
            .text("Volver a instrucciones")
            .attr("data-accion", "volver")
            .css({
                "display": "block",
                "width": "100%"
            })
            .on("click", this.volverAInstrucciones.bind(this));
        
        // Añadir botones directamente al contenedor de resultados (sin contenedor adicional)
        this.elementoResultados.append(btnReiniciar, btnVolver);
    }
    
    /**
     * Reinicia el juego
     */
    reiniciarJuego() {
        this.respuestasUsuario = new Array(10).fill(null);
        this.preguntaActual = 0;
        this.puntuacion = 0;
        this.elementoResultados.hide();
        this.elementoPregunta.show();
        this.elementoOpciones.show();
        this.elementoNavegacion.show();
        this.mostrarPregunta(0);
    }
    
    /**
     * Finaliza el juego completamente y vuelve a mostrar las instrucciones
     */
    volverAInstrucciones() {
        $("main > section > article[data-tipo='contenedor-juego']").remove();
        $("section > h2, section > p, section > ul, section > input[type='button']").show();
        this.juegoIniciado = false;
        this.respuestasUsuario = new Array(10).fill(null);
        this.preguntaActual = 0;
        this.puntuacion = 0;
    }
}

// Crear instancia del juego
const juego = new JuegoPesoz();
