import constantes
from items import Item
from personaje import Personaje
obstaculos = [19,29,21,43,44,45,67,68,69,91,92,93,94,95,115,116,117,118,119,135, 136,137,138,139,142,143,144,145,146,147,148,149,157,158,163,164,165,166,167,168,169,170,171,172,173,181,182,187,188,189,190,191,192,193,194,195,196,197,205,206,211,212,213,214,216,217,218,219,220,221,229,230,231,235,236,237,238,240,241,242,243,244,245,253,254,264,265,266,267,268,269,270,277,278,279,280]




class Mundo():
    def __init__(self):
        self.map_tiles = []
        self.obstaculos_tiles = []
        self.exit_tile = None
        self.lista_item = []
        self.lista_enemigo = []

    def process_data(self, data, tile_list, item_imagenes, animaciones_enemigos):
        self.level_length = len(data)
        for y, row in enumerate(data):
            for x, tile in enumerate(row):
                image = tile_list[tile]
                image_rect = image.get_rect()
                image_x = x * constantes.TILE_SIZE
                image_y = y * constantes.TILE_SIZE
                image_rect.center = (image_x, image_y)
                tile_data = [image, image_rect, image_x, image_y]

                if tile in obstaculos:
                    self.obstaculos_tiles.append(tile_data)
                #tile de salida
                elif tile == 140:
                    self.exit_tile = tile_data
                elif tile == 41:
                    moneda = Item(image_x, image_y, 0, item_imagenes[0])
                    self.lista_item.append(moneda)
                    tile_data[0] = tile_list[100]
                elif tile == 65:
                    posion = Item(image_x, image_y, 1, item_imagenes[1])
                    self.lista_item.append(posion)
                    tile_data[0] = tile_list[100]
                #ratas
                elif tile == 287:
                    rata = Personaje(image_x, image_y, animaciones_enemigos[0], 250, 2)
                    self.lista_enemigo.append(rata)
                    tile_data[0] = tile_list[100]

                self.map_tiles.append(tile_data)

    def update(self, posicion_pantalla):
        for tile in self.map_tiles:
            tile[2] += posicion_pantalla[0]
            tile[3] += posicion_pantalla[1]
            tile[1].center = (tile[2], tile[3])

    def draw(self, surface):
        for tile in self.map_tiles:
            surface.blit(tile[0], tile[1])