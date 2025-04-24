class Noticias {
    constructor(ulElement, urlRSS) {
        this.ulElement = ulElement;
        this.urlRSS = urlRSS;
    }

    cargarNoticias() {
        const self = this;
        $.ajax({
            url: 'https://api.rss2json.com/v1/api.json?rss_url=' + encodeURIComponent(this.urlRSS),
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                self.mostrarNoticias(data.items);
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
        $(this.ulElement).append('<li><h2>Noticias</h2></li>');
        // Insertar noticias como <li>
        noticias.slice(0,5).forEach(function(noticia) {
            $(this.ulElement).append(`<li><a href="${noticia.link}" target="_blank">${noticia.title}</a></li>`);
        }, this);
    }
}

$(document).ready(function() {
    // Segundo <ul> de <main> para noticias
    const ulNoticias = $('main ul').eq(1);
    const noticias = new Noticias(ulNoticias, 'https://www.lne.es/rss/asturias-occidente');
    noticias.cargarNoticias();
});
