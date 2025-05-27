// Clase principal para gestionar las rutas
class RutasManager {
    constructor() {
        this.xmlData = null;
        this.kmlData = null;
        this.svgData = null;
        this.rutasArray = [];
        this.mapaInstance = null;
        this.elementoMapa = null;
        this.init();
    }

    init() {
        $(document).ready(() => {
            this.configurarEventosArchivos();
        });
    }

    configurarEventosArchivos() {
        // Configurar eventos para cada tipo de archivo usando File API HTML5
        $('input[type="file"]').on('change', (event) => {
            const archivo = event.target.files[0];
            if (!archivo) return;
            
            const nombreArchivo = archivo.name.toLowerCase();
            
            // Determinar el tipo de archivo por extensión o tipo MIME
            if (nombreArchivo.endsWith('.xml')) {
                this.procesarArchivoXML(archivo);
            } else if (nombreArchivo.endsWith('.kml')) {
                this.procesarArchivoKML(archivo);
            } else if (nombreArchivo.endsWith('.svg')) {
                this.procesarArchivoSVG(archivo);
            } else {
                this.mostrarMensaje('❌ Tipo de archivo no compatible. Use XML, KML o SVG.', 'error');
            }
        });
    }

    procesarArchivoXML(archivo) {
        if (!archivo) return;

        const lector = new FileReader();
        lector.onload = (evento) => {
            try {
                const contenido = evento.target.result;
                this.xmlData = $.parseXML(contenido);
                this.parsearRutasXML();
                this.mostrarRutas();
                this.mostrarMensaje('✅ Archivo XML cargado correctamente', 'success');
            } catch (error) {
                this.mostrarMensaje('❌ Error al procesar el archivo XML', 'error');
                console.error('Error procesando XML:', error);
            }
        };
        lector.onerror = () => {
            this.mostrarMensaje('❌ Error al leer el archivo XML', 'error');
        };
        lector.readAsText(archivo);
    }

    procesarArchivoKML(archivo) {
        if (!archivo) return;

        const lector = new FileReader();
        lector.onload = (evento) => {
            try {
                const contenido = evento.target.result;
                this.kmlData = $.parseXML(contenido);
                this.mostrarMapaConKML();
                this.mostrarMensaje('✅ Archivo KML cargado correctamente', 'success');
            } catch (error) {
                this.mostrarMensaje('❌ Error al procesar el archivo KML', 'error');
                console.error('Error procesando KML:', error);
            }
        };
        lector.onerror = () => {
            this.mostrarMensaje('❌ Error al leer el archivo KML', 'error');
        };
        lector.readAsText(archivo);
    }

    procesarArchivoSVG(archivo) {
        if (!archivo) return;

        const lector = new FileReader();
        lector.onload = (evento) => {
            try {
                const contenido = evento.target.result;
                this.svgData = contenido;
                this.mostrarAltimetria();
                this.mostrarMensaje('✅ Archivo SVG cargado correctamente', 'success');
            } catch (error) {
                this.mostrarMensaje('❌ Error al procesar el archivo SVG', 'error');
                console.error('Error procesando SVG:', error);
            }
        };
        lector.onerror = () => {
            this.mostrarMensaje('❌ Error al leer el archivo SVG', 'error');
        };
        lector.readAsText(archivo);
    }

    parsearRutasXML() {
        this.rutasArray = [];
        const rutas = $(this.xmlData).find('ruta');
        
        rutas.each((_, elemento) => {
            const ruta = new RutaInfo($, elemento);
            this.rutasArray.push(ruta);
        });
    }

    mostrarRutas() {
        // Buscar contenedor de rutas existente
        let contenedor = $('main').find('section[data-content="routes"]');
        
        if (contenedor.length === 0) {
            contenedor = $('<section>').attr('data-content', 'routes');
            $('main').append(contenedor);
        }
        
        // Limpiar solo el contenedor de rutas
        contenedor.empty();

        const titulo = $('<h2>').text('Rutas Disponibles');
        contenedor.append(titulo);
        
        this.rutasArray.forEach((ruta, indice) => {
            const elementoRuta = this.crearElementoRuta(ruta, indice);
            contenedor.append(elementoRuta);
        });
    }

    crearElementoRuta(ruta, _) {
        const articulo = $('<article>');
        
        // Título principal
        const titulo = $('<h3>').text(ruta.nombre);
        articulo.append(titulo);

        // Información básica
        const elementosBasicos = this.crearInformacionBasicaCompleta(ruta);
        articulo.append(elementosBasicos);

        // Información del punto de inicio
        const elementosInicio = this.crearInformacionInicioCompleta(ruta);
        articulo.append(elementosInicio);

        // Hitos de la ruta
        const elementosHitos = this.crearSeccionHitosCompleta(ruta);
        articulo.append(elementosHitos);

        // Referencias
        const elementosReferencias = this.crearSeccionReferenciasCompleta(ruta);
        articulo.append(elementosReferencias);
        
        return articulo;
    }

    crearInformacionBasicaCompleta(ruta) {
        const titulo = $('<h4>').text('Información General');
        const lista = $('<ul>');
        const campos = [
            { etiqueta: 'Tipo', valor: ruta.tipo },
            { etiqueta: 'Descripción', valor: ruta.descripcion },
            { etiqueta: 'Duración', valor: `${ruta.duracion} horas` },
            { etiqueta: 'Medio de transporte', valor: ruta.medioTransporte },
            { etiqueta: 'Personas adecuadas', valor: ruta.personasAdecuadas },
            { etiqueta: 'Agencia', valor: ruta.agencia },
            { etiqueta: 'Fecha de inicio', valor: ruta.fechaInicio },
            { etiqueta: 'Hora de inicio', valor: ruta.horaInicio },
            { etiqueta: 'Recomendación', valor: `${ruta.recomendacion}/10` }
        ];

        campos.forEach(campo => {
            const elemento = $('<li>');
            elemento.html(`<strong>${campo.etiqueta}:</strong> ${campo.valor}`);
            lista.append(elemento);
        });

        return [titulo, lista];
    }

    crearInformacionInicioCompleta(ruta) {
        const titulo = $('<h4>').text('Punto de Inicio');
        const lugar = $('<p>').html(`<strong>Lugar:</strong> ${ruta.inicio.lugar}`);
        const direccion = $('<p>').html(`<strong>Dirección:</strong> ${ruta.inicio.direccion}`);
        const coordenadas = $('<p>').html(
            `<strong>Coordenadas:</strong> ${ruta.inicio.latitud}, ${ruta.inicio.longitud} (${ruta.inicio.altitud}m)`
        );

        return [titulo, lugar, direccion, coordenadas];
    }

    crearSeccionHitosCompleta(ruta) {
        const tituloHitos = $('<h4>').text('Hitos de la Ruta');

        if (ruta.hitos && ruta.hitos.length > 0) {
            const listaHitos = $('<ul>');
            
            ruta.hitos.forEach((hito, indice) => {
                const elementoHito = $('<li>');
                
                // Información principal del hito
                const nombreHito = $('<strong>').text(`${indice + 1}. ${hito.nombre}`);
                const descripcionHito = $('<p>').text(hito.descripcion);
                const coordenadas = $('<p>').html(
                    `<strong>Coordenadas:</strong> ${hito.latitud}, ${hito.longitud} (${hito.altitud}m)`
                );
                const distancia = $('<p>').html(
                    `<strong>Distancia desde inicio:</strong> ${hito.distancia}m`
                );
                
                elementoHito.append(nombreHito, descripcionHito, coordenadas, distancia);
                
                // Galería de fotos si existe
                if (hito.fotos && hito.fotos.length > 0) {
                    const tituloFotos = $('<strong>').text('Galería de fotos:');
                    const listaFotos = $('<ul>');
                    
                    hito.fotos.forEach(foto => {
                        const elementoFoto = $('<li>');
                        const enlaceFoto = $('<a>')
                            .attr('href', foto)
                            .attr('target', '_blank')
                            .text(foto);
                        elementoFoto.append(enlaceFoto);
                        listaFotos.append(elementoFoto);
                    });
                    
                    elementoHito.append(tituloFotos, listaFotos);
                }
                
                listaHitos.append(elementoHito);
            });
            
            return [tituloHitos, listaHitos];
        } else {
            const mensaje = $('<p>').text('No hay hitos definidos para esta ruta.');
            return [tituloHitos, mensaje];
        }
    }

    crearSeccionReferenciasCompleta(ruta) {
        const titulo = $('<h4>').text('Referencias y Enlaces');
        
        if (ruta.referencias && ruta.referencias.length > 0) {
            const lista = $('<ul>');
            
            ruta.referencias.forEach(referencia => {
                const elemento = $('<li>');
                const enlace = $('<a>')
                    .attr('href', referencia)
                    .attr('target', '_blank')
                    .text(referencia);
                elemento.append(enlace);
                lista.append(elemento);
            });
            
            return [titulo, lista];
        } else {
            const mensaje = $('<p>').text('No hay referencias disponibles para esta ruta.');
            return [titulo, mensaje];
        }
    }



    mostrarMapaConKML() {
        if (!this.kmlData) return;
        
        // Buscar contenedor de mapa existente
        let contenedorMapa = $('main').find('section[data-content="map"]');
        
        if (contenedorMapa.length === 0) {
            contenedorMapa = $('<section>').attr('data-content', 'map');
            $('main').append(contenedorMapa);
        }
        
        // Limpiar solo el contenedor de mapa
        contenedorMapa.empty();

        const titulo = $('<h2>').text('Planimetría de las Rutas');
        contenedorMapa.append(titulo);
        
        // Crear elemento para el mapa
        this.elementoMapa = $('<section>')
            .attr('data-map', 'container');
        
        contenedorMapa.append(this.elementoMapa);

        // Inicializar mapa con Google Maps
        this.esperarGoogleMapsYInicializar();
    }

    inicializarMapa() {
        // Coordenadas centrales de Pesoz
        const latitudCentral = 43.2578;
        const longitudCentral = -6.8756;

        const opciones = {
            zoom: 13,
            center: { lat: latitudCentral, lng: longitudCentral },
            mapTypeId: google.maps.MapTypeId.TERRAIN
        };

        this.mapaInstance = new google.maps.Map(this.elementoMapa[0], opciones);
    }

    procesarDatosKML() {
        const carpetas = $(this.kmlData).find('Folder');
        
        carpetas.each((indice, carpeta) => {
            const nombreRuta = $(carpeta).find('name').first().text();
            const procesadorKML = new ProcesadorKML($, carpeta, this.mapaInstance);
            procesadorKML.agregarRutaAlMapa(nombreRuta);
        });
    }

    mostrarAltimetria() {
        if (!this.svgData) {
            console.error('No hay datos SVG para mostrar');
            return;
        }
        
        // Buscar contenedor de altimetría existente
        let contenedorAltimetria = $('main').find('section[data-content="altimetry"]');
        
        if (contenedorAltimetria.length === 0) {
            contenedorAltimetria = $('<section>').attr('data-content', 'altimetry');
            $('main').append(contenedorAltimetria);
        }
        
        // Limpiar solo el contenedor de altimetría
        contenedorAltimetria.empty();

        const titulo = $('<h2>').text('Altimetría de la Ruta');
        contenedorAltimetria.append(titulo);
 
        try {
            const procesadorSVG = new ProcesadorSVG($, this.svgData);
            const elementosSVG = procesadorSVG.crearVisualizacionMejorada();
            
            // elementosSVG puede ser un array [svgContenedor, informacionHitos] o un elemento único
            if (Array.isArray(elementosSVG)) {
                elementosSVG.forEach(elemento => {
                    if (elemento) {
                        contenedorAltimetria.append(elemento);
                    }
                });
            } else {
                contenedorAltimetria.append(elementosSVG);
            }
        } catch (error) {
            console.error('Error procesando SVG:', error);
            const mensajeError = $('<p>').text('Error al procesar el archivo SVG: ' + error.message);
            contenedorAltimetria.append(mensajeError);
        }
    }

    esperarGoogleMapsYInicializar() {
        const verificarGoogleMaps = () => {
            if (typeof google !== 'undefined' && google.maps) {
                try {
                    this.inicializarMapa();
                    this.procesarDatosKML();
                } catch (error) {
                    console.error('Error inicializando Google Maps:', error);
                    this.mostrarMensaje('❌ Error al inicializar Google Maps', 'error');
                }
            } else {
                // Reintentar después de 500ms
                setTimeout(verificarGoogleMaps, 500);
            }
        };
        
        verificarGoogleMaps();
    }

    mostrarMensaje(mensaje, tipo) {
        // Eliminar mensajes anteriores
        $('section[data-message]').remove();
        
        // Crear nuevo mensaje
        const elementoMensaje = $('<section>')
            .attr('data-message', tipo)
            .text(mensaje);
        
        // Insertar al principio del main
        $('main').prepend(elementoMensaje);
        
        // Auto-eliminar después de 3 segundos
        setTimeout(() => {
            elementoMensaje.fadeOut(500, () => {
                elementoMensaje.remove();
            });
        }, 3000);
    }

}

// Clase para representar información de una ruta
class RutaInfo {
    constructor(jquery, elementoXML) {
        $ = jquery;
        this.elemento = $(elementoXML);
        this.parsearDatos();
    }

    parsearDatos() {
        this.nombre = this.elemento.children('nombre').text();
        this.tipo = this.elemento.children('tipo').text();
        this.medioTransporte = this.elemento.children('medio_transporte').text();
        this.fechaInicio = this.elemento.children('fecha_inicio').text();
        this.horaInicio = this.elemento.children('hora_inicio').text();
        this.duracion = this.elemento.children('duracion').text();
        this.agencia = this.elemento.children('agencia').text();
        this.descripcion = this.elemento.children('descripcion').text();
        this.personasAdecuadas = this.elemento.children('personas_adecuadas').text();
        this.recomendacion = this.elemento.children('recomendacion').text();
        
        this.inicio = this.parsearInicio();
        this.referencias = this.parsearReferencias();
        this.hitos = this.parsearHitos();
    }

    parsearInicio() {
        const inicioElemento = this.elemento.find('inicio');
        return {
            lugar: inicioElemento.find('lugar').text(),
            direccion: inicioElemento.find('direccion').text(),
            longitud: inicioElemento.find('longitud').text(),
            latitud: inicioElemento.find('latitud').text(),
            altitud: inicioElemento.find('altitud').text()
        };
    }

    parsearReferencias() {
        const referencias = [];
        this.elemento.find('referencias referencia').each((indice, ref) => {
            referencias.push($(ref).text());
        });
        return referencias;
    }

    parsearHitos() {
        const hitos = [];
        this.elemento.find('hitos hito').each((indice, hitoElemento) => {
            const hito = new HitoInfo($, hitoElemento);
            hitos.push(hito);
        });
        return hitos;
    }
}

// Clase para representar información de un hito
class HitoInfo {
    constructor(jquery, elementoXML) {
        $ = jquery;
        this.elemento = $(elementoXML);
        this.parsearDatos();
    }

    parsearDatos() {
        this.nombre = this.elemento.find('nombre').text();
        this.descripcion = this.elemento.find('descripcion').text();
        this.longitud = this.elemento.find('coordenadas longitud').text();
        this.latitud = this.elemento.find('coordenadas latitud').text();
        this.altitud = this.elemento.find('coordenadas altitud').text();
        this.distancia = this.elemento.find('distancia').text();
        this.fotos = this.parsearFotos();
    }

    parsearFotos() {
        const fotos = [];
        this.elemento.find('galeria_fotos foto').each((indice, foto) => {
            fotos.push($(foto).text());
        });
        return fotos;
    }
}

// Clase para mostrar detalles completos de una ruta
class DetallesRuta {
    constructor(jquery, ruta) {
        $ = jquery;
        this.ruta = ruta;
    }

    crearElementoCompleto() {
        const contenedor = $('<article>');
        
        const titulo = $('<h2>').text(this.ruta.nombre);
        const informacionBasica = this.crearInformacionBasica();
        const informacionInicio = this.crearInformacionInicio();
        const seccionReferencias = this.crearSeccionReferencias();
        
        contenedor.append(titulo, informacionBasica, informacionInicio, seccionReferencias);
        
        return contenedor;
    }

    crearInformacionBasica() {
        const seccion = $('<section>');
        const titulo = $('<h3>').text('Información General');
        
        const lista = $('<ul>');
        const campos = [
            { etiqueta: 'Tipo', valor: this.ruta.tipo },
            { etiqueta: 'Descripción', valor: this.ruta.descripcion },
            { etiqueta: 'Duración', valor: `${this.ruta.duracion} horas` },
            { etiqueta: 'Medio de transporte', valor: this.ruta.medioTransporte },
            { etiqueta: 'Personas adecuadas', valor: this.ruta.personasAdecuadas },
            { etiqueta: 'Agencia', valor: this.ruta.agencia },
            { etiqueta: 'Fecha de inicio', valor: this.ruta.fechaInicio },
            { etiqueta: 'Hora de inicio', valor: this.ruta.horaInicio },
            { etiqueta: 'Recomendación', valor: `${this.ruta.recomendacion}/10` }
        ];

        campos.forEach(campo => {
            const elemento = $('<li>');
            elemento.html(`<strong>${campo.etiqueta}:</strong> ${campo.valor}`);
            lista.append(elemento);
        });

        seccion.append(titulo, lista);
        return seccion;
    }

    crearInformacionInicio() {
        const seccion = $('<section>');
        const titulo = $('<h3>').text('Punto de Inicio');
        
        const lugar = $('<p>').html(`<strong>Lugar:</strong> ${this.ruta.inicio.lugar}`);
        const direccion = $('<p>').html(`<strong>Dirección:</strong> ${this.ruta.inicio.direccion}`);
        const coordenadas = $('<p>').html(
            `<strong>Coordenadas:</strong> ${this.ruta.inicio.latitud}, ${this.ruta.inicio.longitud} (${this.ruta.inicio.altitud}m)`
        );

        seccion.append(titulo, lugar, direccion, coordenadas);
        return seccion;
    }

    crearSeccionReferencias() {
        const seccion = $('<section>');
        const titulo = $('<h3>').text('Referencias');
        
        if (this.ruta.referencias && this.ruta.referencias.length > 0) {
            const lista = $('<ul>');
            
            this.ruta.referencias.forEach(referencia => {
                const elemento = $('<li>');
                const enlace = $('<a>')
                    .attr('href', referencia)
                    .attr('target', '_blank')
                    .text(referencia);
                elemento.append(enlace);
                lista.append(elemento);
            });
            
            seccion.append(titulo, lista);
        }

        return seccion;
    }
}

// Clase para procesar archivos KML y mostrarlos en el mapa
class ProcesadorKML {
    constructor(jquery, carpetaKML, mapaInstance) {
        $ = jquery;
        this.carpeta = $(carpetaKML);
        this.mapa = mapaInstance;
    }

    agregarRutaAlMapa(nombreRuta) {
        this.agregarLineaRuta();
        this.agregarMarcadoresHitos();
    }

    agregarLineaRuta() {
        const lineString = this.carpeta.find('LineString coordinates').text().trim();
        if (lineString) {
            const coordenadas = this.parsearCoordenadas(lineString);
            
            const polylineOptions = {
                path: coordenadas,
                geodesic: true,
                strokeColor: '#FF0000',
                strokeOpacity: 1.0,
                strokeWeight: 4
            };

            const polyline = new google.maps.Polyline(polylineOptions);
            polyline.setMap(this.mapa);
            
            // Ajustar vista del mapa a la ruta
            this.ajustarVistaARuta(coordenadas);
        }
    }

    agregarMarcadoresHitos() {
        const puntos = this.carpeta.find('Point');
        
        puntos.each((indice, punto) => {
            const coordenadas = $(punto).find('coordinates').text().trim();
            const placemark = $(punto).closest('Placemark');
            const nombre = placemark.find('name').text();
            const descripcion = placemark.find('description').text();
            
            if (coordenadas) {
                const [longitud, latitud] = coordenadas.split(',').map(Number);
                
                const marcador = new google.maps.Marker({
                    position: { lat: latitud, lng: longitud },
                    map: this.mapa,
                    title: nombre
                });

                const infoWindow = new google.maps.InfoWindow({
                    content: `<strong>${nombre}</strong><br>${descripcion}`
                });

                marcador.addListener('click', () => {
                    infoWindow.open(this.mapa, marcador);
                });
            }
        });
    }

    parsearCoordenadas(coordenadasTexto) {
        return coordenadasTexto.split(/\s+/)
            .filter(linea => linea.trim())
            .map(linea => {
                const [longitud, latitud] = linea.split(',').map(Number);
                return { lat: latitud, lng: longitud };
            });
    }

    ajustarVistaARuta(coordenadas) {
        if (coordenadas.length === 0) return;

        const bounds = new google.maps.LatLngBounds();
        coordenadas.forEach(coord => {
            bounds.extend(coord);
        });
        
        this.mapa.fitBounds(bounds);
    }
}

// Clase para procesar archivos SVG de altimetría
class ProcesadorSVG {
    constructor(jquery, contenidoSVG) {
        $ = jquery;
        this.contenidoSVG = contenidoSVG;
    }

    crearVisualizacionMejorada() {
        try {
            // Crear contenedor simple para el SVG
            const svgContenedor = $('<article>').html(this.contenidoSVG);
            
            // Verificar que el SVG se insertó correctamente
            const svgElement = svgContenedor.find('svg');
            if (svgElement.length === 0) {
                console.warn('No se encontró elemento SVG en el contenido');
                return $('<p>').text('El archivo SVG no contiene un elemento SVG válido');
            }
            
            // Agregar línea de cota cero
            this.agregarLineaCotaCero(svgContenedor);
            
            // Extraer y mostrar información de hitos
            const elementosHitos = this.extraerInformacionHitos();
            
            // Crear un array plano con todos los elementos
            const todosLosElementos = [svgContenedor];
            if (Array.isArray(elementosHitos)) {
                todosLosElementos.push(...elementosHitos);
            } else {
                todosLosElementos.push(elementosHitos);
            }
            
            return todosLosElementos;
        } catch (error) {
            console.error('Error en crearVisualizacionMejorada:', error);
            return $('<p>').text('Error al procesar el contenido SVG');
        }
    }

    agregarLineaCotaCero(contenedorSVG) {
        const svg = contenedorSVG.find('svg');
        if (svg.length > 0) {
            // Agregar línea horizontal en la cota cero (nivel del mar)
            const lineaCero = $('<line>')
                .attr({
                    'x1': '50',
                    'y1': '350',
                    'x2': '750',
                    'y2': '350',
                    'stroke': 'blue',
                    'stroke-width': '2',
                    'stroke-dasharray': '5,5'
                });
            
            const textoCero = $('<text>')
                .attr({
                    'x': '760',
                    'y': '355',
                    'font-size': '12',
                    'fill': 'blue'
                })
                .text('Nivel del mar (0m)');
            
            svg.append(lineaCero);
            svg.append(textoCero);
        }
    }

    extraerInformacionHitos() {
        try {
            const titulo = $('<h3>').text('Información de Hitos en la Altimetría');
            
            // Parsear el SVG para extraer información de hitos
            const svgParseado = $.parseXML(this.contenidoSVG);
            const circulos = $(svgParseado).find('circle');
            const textos = $(svgParseado).find('text');
            
            if (circulos.length > 0) {
                const lista = $('<ul>');
                
                circulos.each((indice, circulo) => {
                    const cx = $(circulo).attr('cx');
                    const cy = $(circulo).attr('cy');
                    
                    // Buscar el texto correspondiente
                    const textoHito = textos.filter((i, texto) => {
                        const x = $(texto).attr('x');
                        const y = $(texto).attr('y');
                        return Math.abs(parseFloat(x) - parseFloat(cx)) < 20;
                    }).first().text();
                    
                    if (textoHito) {
                        const elemento = $('<li>');
                        elemento.html(`<strong>${textoHito}</strong> - Posición: (${cx}, ${cy})`);
                        lista.append(elemento);
                    } else {
                        // Mostrar círculo sin texto asociado
                        const elemento = $('<li>');
                        elemento.html(`<strong>Hito ${indice + 1}</strong> - Posición: (${cx}, ${cy})`);
                        lista.append(elemento);
                    }
                });
                
                return [titulo, lista];
            } else {
                const mensaje = $('<p>').text('No se encontraron hitos marcados en el SVG');
                return [titulo, mensaje];
            }
        } catch (error) {
            console.error('Error extrayendo información de hitos:', error);
            const titulo = $('<h3>').text('Información de Hitos en la Altimetría');
            const mensaje = $('<p>').text('Error al procesar los hitos del SVG');
            return [titulo, mensaje];
        }
    }
}

// Variable global para el manager de rutas
let rutasManagerGlobal;

// Función de callback para Google Maps
window.initMap = function() {
};

// Inicialización cuando el documento esté listo
$(() => {
    rutasManagerGlobal = new RutasManager();
}); 