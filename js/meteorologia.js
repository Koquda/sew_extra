/**
 * Clase para manejar la información meteorológica
 */
class Meteorologia {
    constructor() {
        // Key de OpenWeather API - en producción esto debería estar en un servidor
        this.apiKey = "bec0832374cee309477e3a6d5e5bef94";
        this.ciudad = "Pesoz";
        this.urlBase = "https://api.openweathermap.org/data/2.5/";
        this.unidades = "metric";
        this.idioma = "es";
        
        // Referencias a los contenedores
        this.$contenedorActual = null;
        this.$contenedorPronostico = null;
        
        // Inicializar toda la funcionalidad al cargar la página
        $(document).ready(() => {
            this.iniciar();
        });
    }
    
    /**
     * Inicia la funcionalidad de meteorología
     */
    iniciar() {
        this.crearEstructuraHTML();
        this.cargarDatosActual();
        this.cargarDatosPronostico();
    }
    
    /**
     * Crea la estructura HTML básica para mostrar la información meteorológica
     */
    crearEstructuraHTML() {
        const $main = $("main");
        
        // Sección para el clima actual
        const $seccionActual = $("<section>");
        $seccionActual.html(`
            <h2>Clima actual en ${this.ciudad}</h2>
            <article>
                <p>Cargando información del clima actual...</p>
            </article>
        `);
        
        // Sección para el pronóstico de 7 días
        const $seccionPronostico = $("<section>");
        $seccionPronostico.html(`
            <h2>Pronóstico para los próximos 7 días</h2>
            <p>Cargando pronóstico...</p>
        `);
        
        // Añadir secciones al main
        $main.append($seccionActual);
        $main.append($seccionPronostico);
        
        // Guardar referencias a los contenedores
        this.$contenedorActual = $("main section:nth-of-type(2) > article");
        this.$contenedorPronostico = $("main section:nth-of-type(3)");
    }
    
    /**
     * Carga los datos del clima actual
     */
    cargarDatosActual() {
        const url = `${this.urlBase}weather?q=${this.ciudad}&appid=${this.apiKey}&units=${this.unidades}&lang=${this.idioma}`;
        
        $.ajax({
            url: url,
            method: "GET",
            dataType: "json",
            success: (data) => {
                this.mostrarDatosActual(data);
            },
            error: (error) => {
                this.mostrarError(this.$contenedorActual, "No se pudo cargar la información del clima actual.");
                console.error("Error al cargar el clima actual:", error);
            }
        });
    }
    
    /**
     * Carga los datos del pronóstico
     */
    cargarDatosPronostico() {
        const url = `${this.urlBase}forecast?q=${this.ciudad}&appid=${this.apiKey}&units=${this.unidades}&lang=${this.idioma}`;
        
        $.ajax({
            url: url,
            method: "GET",
            dataType: "json",
            success: (data) => {
                this.mostrarDatosPronostico(data);
            },
            error: (error) => {
                this.mostrarError(this.$contenedorPronostico, "No se pudo cargar el pronóstico del tiempo.");
                console.error("Error al cargar el pronóstico:", error);
            }
        });
    }
    
    /**
     * Muestra los datos del clima actual en la interfaz
     * @param {Object} data - Datos del clima actual
     */
    mostrarDatosActual(data) {
        if (this.$contenedorActual.length === 0) return;
        
        const temperatura = Math.round(data.main.temp);
        const sensacionTermica = Math.round(data.main.feels_like);
        const descripcion = data.weather[0].description;
        const humedad = data.main.humidity;
        const viento = Math.round(data.wind.speed * 3.6);
        const icono = data.weather[0].icon;
        const iconoUrl = `https://openweathermap.org/img/wn/${icono}@2x.png`;
        
        const html = `
            <img src="${iconoUrl}" alt="${descripcion}">
            <h3>${temperatura}°C</h3>
            <p>${this.capitalizarPrimeraLetra(descripcion)}</p>
            <p><strong>Sensación térmica:</strong> ${sensacionTermica}°C</p>
            <p><strong>Humedad:</strong> ${humedad}%</p>
            <p><strong>Viento:</strong> ${viento} km/h</p>
        `;
        
        this.$contenedorActual.html(html);
    }
    
    /**
     * Muestra los datos del pronóstico en la interfaz
     * @param {Object} data - Datos del pronóstico
     */
    mostrarDatosPronostico(data) {
        if (this.$contenedorPronostico.length === 0) return;
        
        const pronosticoDiario = this.procesarPronosticoDiario(data);
        let htmlContenido = '';
        
        pronosticoDiario.forEach((dia) => {
            htmlContenido += this.crearTarjetaPronosticoHTML(dia);
        });
        
        // Eliminar el párrafo de carga y añadir el contenido después del h2
        this.$contenedorPronostico.find("p").remove();
        this.$contenedorPronostico.find("h2").after(htmlContenido);
    }
    
    /**
     * Procesa los datos para obtener el pronóstico diario
     * @param {Object} data - Datos del pronóstico
     * @returns {Array} Array de objetos con el pronóstico para cada día
     */
    procesarPronosticoDiario(data) {
        const dias = [];
        const diasProcesados = new Set();
        let contadorDias = 0;
        
        data.list.forEach(item => {
            const fecha = new Date(item.dt * 1000);
            const dia = fecha.getDate();
            
            if (contadorDias >= 7) return;
            
            if (!diasProcesados.has(dia)) {
                diasProcesados.add(dia);
                contadorDias++;
                
                dias.push({
                    fecha: fecha,
                    temperatura: Math.round(item.main.temp),
                    tempMin: Math.round(item.main.temp_min),
                    tempMax: Math.round(item.main.temp_max),
                    descripcion: item.weather[0].description,
                    icono: item.weather[0].icon,
                    humedad: item.main.humidity,
                    viento: Math.round(item.wind.speed * 3.6)
                });
            }
        });
        
        return dias;
    }
    
    /**
     * Crea una tarjeta HTML para mostrar el pronóstico de un día
     * @param {Object} dia - Objeto con datos del pronóstico para un día
     * @returns {string} HTML para la tarjeta de pronóstico
     */
    crearTarjetaPronosticoHTML(dia) {
        const nombresDias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
        const nombreDia = nombresDias[dia.fecha.getDay()];
        const fechaFormateada = `${dia.fecha.getDate()}/${dia.fecha.getMonth() + 1}`;
        
        return `
            <article>
                <h4>${nombreDia} ${fechaFormateada}</h4>
                <img src="https://openweathermap.org/img/wn/${dia.icono}@2x.png" alt="${this.capitalizarPrimeraLetra(dia.descripcion)}">
                <p class="temp">${dia.temperatura}°C</p>
                <p class="minmax">${dia.tempMin}°C / ${dia.tempMax}°C</p>
                <p>${this.capitalizarPrimeraLetra(dia.descripcion)}</p>
                <p>Humedad: ${dia.humedad}%</p>
                <p>Viento: ${dia.viento} km/h</p>
            </article>
        `;
    }
    
    /**
     * Muestra un mensaje de error
     * @param {jQuery} $contenedor - Contenedor donde mostrar el error
     * @param {string} mensaje - Mensaje de error a mostrar
     */
    mostrarError($contenedor, mensaje) {
        $contenedor.html(`<p>${mensaje}</p>`);
    }
    
    /**
     * Capitaliza la primera letra de una cadena
     * @param {string} texto - Texto a capitalizar
     * @returns {string} Texto con primera letra en mayúscula
     */
    capitalizarPrimeraLetra(texto) {
        return texto.charAt(0).toUpperCase() + texto.slice(1);
    }
}

// Iniciar la aplicación
var meteo = new Meteorologia();
