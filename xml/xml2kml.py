import xml.etree.ElementTree as ET
from typing import List, Dict, Tuple

class RutaKML:
    def __init__(self, nombre: str, coordenadas: List[Tuple[float, float, float]], hitos: List[Dict]):
        self.nombre = nombre
        self.coordenadas = coordenadas
        self.hitos = hitos

    def generar_kml(self) -> str:
        kml_template = f'''
    <Folder>
        <name>{self.nombre}</name>
        <description>Ruta turística en Asturias</description>
        <Style id="linea_{self.nombre.lower().replace(' ', '_')}">
            <LineStyle>
                <color>ff0000ff</color>
                <width>4</width>
            </LineStyle>
        </Style>
        <Style id="marcador_{self.nombre.lower().replace(' ', '_')}">
            <IconStyle>
                <Icon>
                    <href>http://maps.google.com/mapfiles/kml/shapes/placemark_circle.png</href>
                </Icon>
            </IconStyle>
        </Style>
        <Placemark>
            <name>Ruta</name>
            <styleUrl>#linea_{self.nombre.lower().replace(' ', '_')}</styleUrl>
            <LineString>
                <coordinates>
                    {self._generar_coordenadas()}
                </coordinates>
            </LineString>
        </Placemark>
        {self._generar_hitos()}
    </Folder>'''
        return kml_template

    def _generar_coordenadas(self) -> str:
        return '\n'.join([f"{lon},{lat},{alt}" for lon, lat, alt in self.coordenadas])

    def _generar_hitos(self) -> str:
        hitos_kml = []
        for i, hito in enumerate(self.hitos, 1):
            hitos_kml.append(f'''
        <Placemark>
            <name>Hito {i}: {hito['nombre']}</name>
            <description>{hito['descripcion']}</description>
            <styleUrl>#marcador_{self.nombre.lower().replace(' ', '_')}</styleUrl>
            <Point>
                <coordinates>{hito['coordenadas'][0]},{hito['coordenadas'][1]},{hito['coordenadas'][2]}</coordinates>
            </Point>
        </Placemark>''')
        return ''.join(hitos_kml)

class GeneradorKML:
    def __init__(self, archivo_xml: str):
        self.archivo_xml = archivo_xml
        self.rutas = []

    def cargar_rutas(self):
        tree = ET.parse(self.archivo_xml)
        root = tree.getroot()
        
        for ruta in root.findall('ruta'):
            nombre = ruta.find('nombre').text
            coordenadas = []
            hitos = []
            
            # Obtener coordenadas de inicio
            inicio = ruta.find('inicio/coordenadas')
            if inicio is not None:
                lon = float(inicio.find('longitud').text)
                lat = float(inicio.find('latitud').text)
                alt = float(inicio.find('altitud').text)
                coordenadas.append((lon, lat, alt))
            
            # Obtener coordenadas de los hitos
            for hito in ruta.findall('hitos/hito'):
                coords = hito.find('coordenadas')
                if coords is not None:
                    lon = float(coords.find('longitud').text)
                    lat = float(coords.find('latitud').text)
                    alt = float(coords.find('altitud').text)
                    coordenadas.append((lon, lat, alt))
                    
                    hitos.append({
                        'nombre': hito.find('nombre').text,
                        'descripcion': hito.find('descripcion').text,
                        'coordenadas': (lon, lat, alt)
                    })
            
            self.rutas.append(RutaKML(nombre, coordenadas, hitos))

    def generar_archivo_kml(self):
        kml_template = '''<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
    <Document>
        <name>Rutas Turísticas de Asturias</name>
        <description>Colección de rutas turísticas por Asturias</description>
        {rutas}
    </Document>
</kml>'''

        rutas_kml = ''.join([ruta.generar_kml() for ruta in self.rutas])
        kml_completo = kml_template.format(rutas=rutas_kml)

        with open('rutas_asturias.kml', 'w', encoding='utf-8') as f:
            f.write(kml_completo)
        print("Archivo KML generado: rutas_asturias.kml")

def main():
    generador = GeneradorKML('rutas.xml')
    generador.cargar_rutas()
    generador.generar_archivo_kml()

if __name__ == "__main__":
    main() 