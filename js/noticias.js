class Noticias {
    constructor() {
        // Crear la sección y el ul dinámicamente
        const main = $('main');
        const section = $('<section></section>');
        section.append('<h2>Noticias sobre Pesoz y los alrededores</h2>');
        const ul = $('<ul></ul>');
        section.append(ul);
        main.append(section);
        
        this.ulElement = ul;
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
    // Las noticias crearán su propia sección dinámicamente
    const noticias = new Noticias();
    noticias.cargarNoticias();
});
