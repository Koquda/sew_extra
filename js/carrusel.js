class Carrusel {
    constructor(containerSelector, imagenes) {
        this.containerSelector = containerSelector;
        this.imagenes = imagenes;
        this.indice = 0;
    }

    iniciar() {
        // Obtenemos la sección contenedora
        const section = $(this.containerSelector);
        
        // Limpiamos la sección y añadimos la imagen y los botones
        section.empty();
        section.append(`
            <img src="${this.imagenes[0]}" alt="Foto carrusel">
            <button type="button">&#10094;</button>
            <button type="button">&#10095;</button>
        `);
        
        this.registrarEventos();
    }

    mostrarImagen(indice) {
        $(this.containerSelector).find('img').attr('src', this.imagenes[indice]);
    }

    registrarEventos() {
        const self = this;
        const section = $(this.containerSelector);
        
        // Botón anterior (primer botón)
        section.find('button').eq(0).on('click', function() {
            self.indice = (self.indice - 1 + self.imagenes.length) % self.imagenes.length;
            self.mostrarImagen(self.indice);
        });
        
        // Botón siguiente (segundo botón)
        section.find('button').eq(1).on('click', function() {
            self.indice = (self.indice + 1) % self.imagenes.length;
            self.mostrarImagen(self.indice);
        });
    }
}

$(document).ready(function() {
    const imagenes = [
        'multimedia/pesoz1.jpg',
        'multimedia/pesoz2.jpg',
        'multimedia/pesoz3.jpg',
        'multimedia/pesoz4.jpg',
        'multimedia/mapa_pesoz.jpg'
    ];
    // Seleccionamos directamente la primera sección de main
    const carrusel = new Carrusel('main section:first-of-type', imagenes);
    carrusel.iniciar();
});
