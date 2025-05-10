class Noticias {
    constructor(ulElement) {
        this.ulElement = ulElement;
        this.apiKey = '4a236f45a50843a5827606a3edb96ed2';
    }

    cargarNoticias() {
        const self = this
        $.ajax({
            url: 'https://newsapi.org/v2/everything',
            method: 'GET',
            data: {
                q: 'Asturias',
                language: 'es',
                sortBy: 'popularity',
                apiKey: this.apiKey
            },
            success: function(data) {
                self.mostrarNoticias(data.articles);
            },
            error: function() {
                $(self.ulElement).html('<li>No se pudieron cargar las noticias.</li>');
            }
        });
    }

    mostrarNoticias(noticias) {
        // Limpiar el ul
        $(this.ulElement).empty();
        // Insertar título como <li>
        $(this.ulElement).append('<li><h2>Noticias sobre Pesoz y los alrededores</h2></li>');
        // Insertar noticias como <li>
        noticias.slice(0,5).forEach(function(noticia) {
            $(this.ulElement).append(`
                <li>
                    <a href="${noticia.url}" target="_blank">${noticia.title}</a>
                    <p>${noticia.description}</p>
                </li>
            `);
        }, this);
    }
}

$(document).ready(function() {
    // Seleccionar el ul dentro de section dentro de main
    const ulNoticias = $('main section ul');
    const noticias = new Noticias(ulNoticias);
    noticias.cargarNoticias();
});
