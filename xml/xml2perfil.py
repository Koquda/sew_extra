import xml.etree.ElementTree as ET
from typing import List, Tuple
import math

class RutaSVG:
    def __init__(self, nombre: str, coordenadas: List[Tuple[float, float, float]], distancias: List[float]):
        self.nombre = nombre
        self.coordenadas = coordenadas
        self.distancias = distancias
        self.altitudes = [coord[2] for coord in coordenadas]
        self._calcular_dimensiones()

    def _calcular_dimensiones(self):
        self.altura_max = max(self.altitudes)
        self.altura_min = min(self.altitudes)
        # Ensure minimum height difference of 1 meter
        if self.altura_max == self.altura_min:
            self.altura_max += 0.5
            self.altura_min -= 0.5
        self.distancia_total = max(sum(self.distancias), 1)  # Ensure minimum distance of 1
        self.margen = 50
        self.ancho = 800
        self.alto = 400

    def _escalar_coordenadas(self) -> List[Tuple[float, float]]:
        puntos = []
        x_acumulado = 0
        
        for i, (_, _, alt) in enumerate(self.coordenadas):
            if i > 0:
                x_acumulado += self.distancias[i-1]
            
            # Escalar X (distancia)
            x = self.margen + (x_acumulado / self.distancia_total) * (self.ancho - 2 * self.margen)
            
            # Escalar Y (altitud)
            y = self.alto - self.margen - ((alt - self.altura_min) / (self.altura_max - self.altura_min)) * (self.alto - 2 * self.margen)
            
            puntos.append((x, y))
        
        return puntos

    def generar_svg(self) -> str:
        puntos = self._escalar_coordenadas()
        path_d = 'M ' + ' L '.join([f"{x},{y}" for x, y in puntos])
        
        svg_template = f'''
    <g transform="translate(0, {self.alto + 50})">
        <text x="{self.ancho/2}" y="-30" 
              text-anchor="middle" font-size="16" font-weight="bold">{self.nombre}</text>
        
        <!-- Fondo -->
        <rect x="0" y="-{self.alto}" width="{self.ancho}" height="{self.alto}" fill="#f5f5f5"/>
        
        <!-- Cuadrícula -->
        {self._generar_cuadricula()}
        
        <!-- Línea de elevación -->
        <path d="{path_d}" 
              stroke="#ff0000" 
              stroke-width="3" 
              fill="none"/>
        
        <!-- Puntos de los hitos -->
        {self._generar_puntos_hitos(puntos)}
        
        <!-- Etiquetas de ejes -->
        {self._generar_etiquetas_ejes()}
    </g>'''
        return svg_template

    def _generar_cuadricula(self) -> str:
        # Líneas horizontales (altitud)
        lineas_h = []
        num_lineas = 5
        for i in range(num_lineas + 1):
            y = -(self.margen + (i * (self.alto - 2 * self.margen) / num_lineas))
            altitud = self.altura_min + (i * (self.altura_max - self.altura_min) / num_lineas)
            lineas_h.append(f'''
        <line x1="{self.margen}" y1="{y}" 
              x2="{self.ancho - self.margen}" y2="{y}" 
              stroke="#cccccc" stroke-width="1"/>
        <text x="{self.margen - 10}" y="{y + 5}" 
              text-anchor="end" font-size="12">{int(altitud)}m</text>''')
        
        # Líneas verticales (distancia)
        lineas_v = []
        num_lineas = 8
        for i in range(num_lineas + 1):
            x = self.margen + (i * (self.ancho - 2 * self.margen) / num_lineas)
            distancia = i * (self.distancia_total / num_lineas)
            lineas_v.append(f'''
        <line x1="{x}" y1="-{self.margen}" 
              x2="{x}" y2="-{self.alto - self.margen}" 
              stroke="#cccccc" stroke-width="1"/>
        <text x="{x}" y="20" 
              text-anchor="middle" font-size="12">{int(distancia)}m</text>''')
        
        return ''.join(lineas_h + lineas_v)

    def _generar_puntos_hitos(self, puntos: List[Tuple[float, float]]) -> str:
        puntos_svg = []
        for i, (x, y) in enumerate(puntos):
            puntos_svg.append(f'''
        <circle cx="{x}" cy="-{y}" r="5" fill="#ff0000"/>
        <text x="{x + 10}" y="-{y - 10}" font-size="12">Hito {i+1}</text>''')
        return ''.join(puntos_svg)

    def _generar_etiquetas_ejes(self) -> str:
        return f'''
        <text x="{self.ancho/2}" y="40" 
              text-anchor="middle" font-size="14">Distancia (metros)</text>
        <text x="20" y="-{self.alto/2}" 
              text-anchor="middle" font-size="14" 
              transform="rotate(-90, 20, -{self.alto/2})">Altitud (metros)</text>'''

class GeneradorSVG:
    def __init__(self, archivo_xml: str):
        self.archivo_xml = archivo_xml
        self.rutas = []

    def cargar_rutas(self):
        tree = ET.parse(self.archivo_xml)
        root = tree.getroot()
        
        for ruta in root.findall('ruta'):
            nombre = ruta.find('nombre').text
            coordenadas = []
            distancias = [0]  # La primera distancia es 0
            
            # Obtener coordenadas de inicio
            inicio = ruta.find('inicio/coordenadas')
            if inicio is not None:
                lon = float(inicio.find('longitud').text)
                lat = float(inicio.find('latitud').text)
                alt = float(inicio.find('altitud').text)
                coordenadas.append((lon, lat, alt))
            
            # Obtener coordenadas y distancias de los hitos
            for hito in ruta.findall('hitos/hito'):
                coords = hito.find('coordenadas')
                if coords is not None:
                    lon = float(coords.find('longitud').text)
                    lat = float(coords.find('latitud').text)
                    alt = float(coords.find('altitud').text)
                    coordenadas.append((lon, lat, alt))
                    
                    # Obtener distancia
                    distancia = hito.find('distancia')
                    if distancia is not None:
                        distancias.append(float(distancia.text))
            
            self.rutas.append(RutaSVG(nombre, coordenadas, distancias))

    def generar_archivo_svg(self):
        # Calcular altura total necesaria
        altura_total = sum(ruta.alto + 100 for ruta in self.rutas)  # 100px de margen entre rutas
        
        svg_template = f'''<?xml version="1.0" encoding="UTF-8"?>
<svg width="800" height="{altura_total}" xmlns="http://www.w3.org/2000/svg">
    <title>Perfiles de elevación - Rutas de Asturias</title>
    {''.join([ruta.generar_svg() for ruta in self.rutas])}
</svg>'''

        with open('perfiles_rutas.svg', 'w', encoding='utf-8') as f:
            f.write(svg_template)
        print("Archivo SVG generado: perfiles_rutas.svg")

def main():
    generador = GeneradorSVG('rutas.xml')
    generador.cargar_rutas()
    generador.generar_archivo_svg()

if __name__ == "__main__":
    main() 