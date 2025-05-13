class Route {
    constructor(name, description, difficulty, distance, duration, elevation) {
        this.name = name;
        this.description = description;
        this.difficulty = difficulty;
        this.distance = distance;
        this.duration = duration;
        this.elevation = elevation;
    }

    static fromXML(xmlNode) {
        return new Route(
            $(xmlNode).find('nombre').text(),
            $(xmlNode).find('descripcion').text(),
            $(xmlNode).find('dificultad').text(),
            $(xmlNode).find('distancia').text(),
            $(xmlNode).find('duracion').text(),
            $(xmlNode).find('desnivel').text()
        );
    }
}

class RouteManager {
    constructor() {
        this.routes = [];
        this.currentRoute = null;
        this.map = null;
    }

    async loadRoutes() {
        try {
            const response = await $.ajax({
                url: 'xml/rutas.xml',
                method: 'GET',
                dataType: 'xml'
            });

            $(response).find('ruta').each((_, routeNode) => {
                this.routes.push(Route.fromXML(routeNode));
            });

            this.displayRoutes();
        } catch (error) {
            console.error('Error loading routes:', error);
        }
    }

    displayRoutes() {
        const routesContainer = $('#routes-container');
        routesContainer.empty();

        this.routes.forEach((route, index) => {
            const routeElement = $(`
                <div class="route-card" data-route-index="${index}">
                    <h3>${route.name}</h3>
                    <p><strong>Dificultad:</strong> ${route.difficulty}</p>
                    <p><strong>Distancia:</strong> ${route.distance} km</p>
                    <p><strong>Duración:</strong> ${route.duration}</p>
                    <button class="view-route-btn">Ver ruta</button>
                </div>
            `);

            routeElement.find('.view-route-btn').on('click', () => this.selectRoute(index));
            routesContainer.append(routeElement);
        });
    }

    async selectRoute(index) {
        this.currentRoute = this.routes[index];
        await this.loadRouteDetails();
    }

    async loadRouteDetails() {
        if (!this.currentRoute) return;

        // Load route information
        $('#route-details').html(`
            <h2>${this.currentRoute.name}</h2>
            <p>${this.currentRoute.description}</p>
            <p><strong>Dificultad:</strong> ${this.currentRoute.difficulty}</p>
            <p><strong>Distancia:</strong> ${this.currentRoute.distance} km</p>
            <p><strong>Duración:</strong> ${this.currentRoute.duration}</p>
            <p><strong>Desnivel:</strong> ${this.currentRoute.elevation} m</p>
        `);

        // Initialize map
        this.initializeMap();
        
        // Load KML
        await this.loadKML();
        
        // Load SVG altimetry
        this.loadAltimetry();
    }

    initializeMap() {
        if (this.map) {
            this.map.remove();
        }

        this.map = L.map('map').setView([43.2571, -6.8757], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);
    }

    async loadKML() {
        try {
            const kmlUrl = 'xml/rutas_asturias.kml';
            const kmlLayer = new L.KML(kmlUrl);
            kmlLayer.addTo(this.map);
        } catch (error) {
            console.error('Error loading KML:', error);
        }
    }

    loadAltimetry() {
        const svgFileName = `ruta_${this.currentRoute.name.toLowerCase().replace(/\s+/g, '_')}.svg`;
        $('#altimetry-container').html(`
            <object data="xml/${svgFileName}" type="image/svg+xml" width="100%" height="400">
                Tu navegador no soporta SVG
            </object>
        `);
    }
}

// Initialize when document is ready
$(document).ready(() => {
    const routeManager = new RouteManager();
    routeManager.loadRoutes();
}); 