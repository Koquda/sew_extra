/**
 * Clase principal para manejar la información meteorológica
 */
class Meteorologia {
    constructor() {
        // Key de OpenWeather API - en producción esto debería estar en un servidor
        this.apiKey = "bec0832374cee309477e3a6d5e5bef94"; // Necesitarás reemplazar esto con tu API key de OpenWeather
        this.ciudad = "Pesoz"; // Capital del concejo
        this.urlBase = "https://api.openweathermap.org/data/2.5/";
        this.unidades = "metric"; // Celsius
        this.idioma = "es"; // Español
        
        // Inicializar toda la funcionalidad al cargar la página
        $(document).ready(() => {
            this.iniciar();
        });
    }
    
    /**
     * Inicia la funcionalidad de meteorología
     */
    iniciar() {
        // Crear estructura HTML para la información meteorológica
        this.crearEstructuraHTML();
        
        // Inicializar las clases para clima actual y pronóstico después de crear la estructura
        this.climaActual = new ClimaActual(this);
        this.pronostico = new Pronostico(this);
        
        // Cargar datos meteorológicos
        this.climaActual.cargarDatos();
        this.pronostico.cargarDatos();
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
            <section>
                <section>
                    <p>Cargando información del clima actual...</p>
                </section>
            </section>
        `);
        
        // Sección para el pronóstico de 7 días
        const $seccionPronostico = $("<section>");
        $seccionPronostico.html(`
            <h2>Pronóstico para los próximos 7 días</h2>
            <section>
                <section>
                    <p>Cargando pronóstico...</p>
                </section>
            </section>
        `);
        
        // Añadir secciones al main
        $main.append($seccionActual);
        $main.append($seccionPronostico);
    }
}

/**
 * Clase para manejar la información del clima actual
 */
class ClimaActual {
    constructor(meteorologia) {
        this.meteorologia = meteorologia;
        // Usar selector posicional: la primera sección después de la introducción, dentro su primera subsección
        this.$contenedor = $("main section:nth-of-type(2) > section > section");
    }
    
    /**
     * Carga los datos del clima actual
     */
    cargarDatos() {
        const url = `${this.meteorologia.urlBase}weather?q=${this.meteorologia.ciudad}&appid=${this.meteorologia.apiKey}&units=${this.meteorologia.unidades}&lang=${this.meteorologia.idioma}`;
        
        $.ajax({
            url: url,
            method: "GET",
            dataType: "json",
            success: (data) => {
                this.mostrarDatos(data);
            },
            error: (error) => {
                this.mostrarError("No se pudo cargar la información del clima actual.");
                console.error("Error al cargar el clima actual:", error);
            }
        });
    }
    
    /**
     * Muestra los datos del clima actual en la interfaz
     * @param {Object} data - Datos del clima actual
     */
    mostrarDatos(data) {
        // Si no hay contenedor, salir
        if (this.$contenedor.length === 0) return;
        
        // Formatear datos
        const temperatura = Math.round(data.main.temp);
        const sensacionTermica = Math.round(data.main.feels_like);
        const descripcion = data.weather[0].description;
        const humedad = data.main.humidity;
        const viento = Math.round(data.wind.speed * 3.6); // Convertir de m/s a km/h
        const icono = data.weather[0].icon;
        const iconoUrl = `https://openweathermap.org/img/wn/${icono}@2x.png`;
        
        // Crear HTML para mostrar datos sin usar IDs ni clases
        const html = `
            <section>
                <section>
                    <section>
                        <img src="${iconoUrl}" alt="${descripcion}">
                        <h3>${temperatura}°C</h3>
                        <p>${this.capitalizarPrimeraLetra(descripcion)}</p>
                    </section>
                    <section>
                        <p><strong>Sensación térmica:</strong> ${sensacionTermica}°C</p>
                        <p><strong>Humedad:</strong> ${humedad}%</p>
                        <p><strong>Viento:</strong> ${viento} km/h</p>
                    </section>
                </section>
            </section>
        `;
        
        // Actualizar contenido
        this.$contenedor.html(html);
    }
    
    /**
     * Muestra un mensaje de error
     * @param {string} mensaje - Mensaje de error a mostrar
     */
    mostrarError(mensaje) {
        this.$contenedor.html(`<p>${mensaje}</p>`);
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

/**
 * Clase para manejar el pronóstico de los próximos 7 días
 */
class Pronostico {
    constructor(meteorologia) {
        this.meteorologia = meteorologia;
        // Usar selector posicional: la tercera sección del main (después de la introducción y el clima actual),
        // dentro su primera subsección y luego la primera subsección de ésta
        this.$contenedor = $("main section:nth-of-type(3) > section > section");
    }
    
    /**
     * Carga los datos del pronóstico
     */
    cargarDatos() {
        // Para el pronóstico de 7 días usamos la API oneCall
        const url = `${this.meteorologia.urlBase}forecast?q=${this.meteorologia.ciudad}&appid=${this.meteorologia.apiKey}&units=${this.meteorologia.unidades}&lang=${this.meteorologia.idioma}`;
        
        $.ajax({
            url: url,
            method: "GET",
            dataType: "json",
            success: (data) => {
                this.mostrarDatos(data);
            },
            error: (error) => {
                this.mostrarError("No se pudo cargar el pronóstico del tiempo.");
                console.error("Error al cargar el pronóstico:", error);
            }
        });
    }
    
    /**
     * Muestra los datos del pronóstico en la interfaz
     * @param {Object} data - Datos del pronóstico
     */
    mostrarDatos(data) {
        // Si no hay contenedor, salir
        if (this.$contenedor.length === 0) return;
        
        // Procesar los datos para obtener pronóstico diario (cada 24h)
        const pronosticoDiario = this.procesarPronosticoDiario(data);
        
        // Actualizar contenido - creamos directamente la estructura HTML
        let htmlContenido = '';
        
        // Crear una tarjeta para cada día del pronóstico
        pronosticoDiario.forEach((dia) => {
            htmlContenido += this.crearTarjetaPronosticoHTML(dia);
        });
        
        this.$contenedor.html(htmlContenido);
    }
    
    /**
     * Procesa los datos para obtener el pronóstico diario
     * @param {Object} data - Datos del pronóstico
     * @returns {Array} Array de objetos con el pronóstico para cada día
     */
    procesarPronosticoDiario(data) {
        // La API forecast devuelve datos cada 3 horas, necesitamos agruparlos por día
        const dias = [];
        const diasProcesados = new Set();
        
        // Limitamos a 7 días
        let contadorDias = 0;
        
        data.list.forEach(item => {
            // Obtener fecha del pronóstico
            const fecha = new Date(item.dt * 1000);
            const dia = fecha.getDate();
            
            // Si ya hemos procesado 7 días, salimos
            if (contadorDias >= 7) return;
            
            // Si no hemos procesado este día, lo añadimos
            if (!diasProcesados.has(dia)) {
                diasProcesados.add(dia);
                contadorDias++;
                
                // Crear objeto con información del día
                dias.push({
                    fecha: fecha,
                    temperatura: Math.round(item.main.temp),
                    tempMin: Math.round(item.main.temp_min),
                    tempMax: Math.round(item.main.temp_max),
                    descripcion: item.weather[0].description,
                    icono: item.weather[0].icon,
                    humedad: item.main.humidity,
                    viento: Math.round(item.wind.speed * 3.6) // m/s a km/h
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
        // Nombres de días de la semana en español
        const nombresDias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
        const nombreDia = nombresDias[dia.fecha.getDay()];
        
        // Formatear fecha
        const fechaFormateada = `${dia.fecha.getDate()}/${dia.fecha.getMonth() + 1}`;
        
        // Crear HTML para la tarjeta
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
     * @param {string} mensaje - Mensaje de error a mostrar
     */
    mostrarError(mensaje) {
        this.$contenedor.html(`<p>${mensaje}</p>`);
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
