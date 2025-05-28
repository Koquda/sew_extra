class Carrusel {
    constructor(containerSelector, imagenes) {
        this.containerSelector = containerSelector;
        this.imagenes = imagenes;
        this.indice = 0;
    }

    iniciar() {
        // Crear y añadir una nueva sección al main
        const main = $('main');
        const section = $('<section></section>');
        section.append(`
            <h2>Carrusel de imágenes</h2>
            <img src="${this.imagenes[0]}" alt="Foto carrusel">
            <button type="button">&#10094;</button>
            <button type="button">&#10095;</button>
        `);
        main.append(section);
        
        // Actualizamos el selector para apuntar a la sección
        this.containerSelector = section;
        this.registrarEventos();
    }

    mostrarImagen(indice) {
        this.containerSelector.find('img').attr('src', this.imagenes[indice]);
    }

    registrarEventos() {
        const self = this;
        const section = this.containerSelector;
        
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
    // El carrusel creará su propia sección dinámicamente
    const carrusel = new Carrusel(null, imagenes);
    carrusel.iniciar();
});
