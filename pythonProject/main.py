import os
from typing import List
import pygame
from PIL.ImageChops import constant
from pygame import Surface, SurfaceType
from mundo import Mundo
import constantes
from items import Item
import personaje
from personaje import Personaje
from textos import DamageText
from weapon import Weapon
import csv

# funciones
# escalar imagen
def escalar_img(image, scale):
    w = image.get_width()
    h = image.get_height()
    nueva_imagen = pygame.transform.scale(image, (w * scale, h * scale))
    return nueva_imagen

# funcion para contar elementos
def contar_elementos(directorio):
    return len(os.listdir(directorio))

# funcion listar nombres elementos
def nombres_carpetas(directorio):
    return os.listdir(directorio)

pygame.init()
ventana = pygame.display.set_mode((constantes.ANCHO_VENTANA, constantes.ALTO_VENTANA))
pygame.display.set_caption("chefsito's game")

#variables
posicion_pantalla = [0, 0]
nivel = 1

# fuentes
font = pygame.font.Font("assets//fonts//m5x7.ttf", 25)
font_game_over = pygame.font.Font("assets//fonts//m5x7.ttf", 100)
font_reinicio = pygame.font.Font("assets//fonts//m5x7.ttf", 30)
font_inicio = pygame.font.Font("assets//fonts//m5x7.ttf", 38)
font_titulo = pygame.font.Font("assets//fonts//m5x7.ttf", 75)

#botones de inicio
boton_jugar = pygame.Rect(constantes.ANCHO_VENTANA / 2 - 100,
                          constantes.ALTO_VENTANA / 2 + 200, 200, 50)

boton_salir = pygame.Rect(constantes.ANCHO_VENTANA - 200 - 20,  # 20 es el margen desde la derecha
                          constantes.ALTO_VENTANA - 50 - 20,  # 20 es el margen desde la parte inferior
                          200, 50)

texto_boton_jugar = font_inicio.render("Jugar", True,
                                       constantes.NEGRO)

texto_boton_salir = font_inicio.render("Salir", True,
                                       constantes.NEGRO)


game_over_text = font_game_over.render('Game Over', True, constantes.BLANCO)
texto_boton_reinicio = font_reinicio.render("Reiniciar", True, constantes.NEGRO)
"""ruta_imagenes = os.path.join(os.getcwd(), 'imagenes')

personaje1_imagen = pygame.image.load(ruta_imagenes, 'assets/images/seleccion/carpincho_parado.png')
personaje2_imagen = pygame.image.load(ruta_imagenes, 'assets/images/seleccion/perro_sentado.png')
"""


# Función para mostrar el menú de selección de personajes
def mostrar_menu_seleccion():
    seleccionando = True
    personaje_seleccionado = None

    while seleccionando:
        ventana.fill((255, 255, 255))  # Color de fondo del menú de selección

        # Configuración del menú de selección
        margen = 50
        num_personajes = len(personajes)
        ancho_total_disponible = constantes.ANCHO_VENTANA - (2 * margen)
        ancho_por_personaje = ancho_total_disponible / num_personajes

        # Obtener la posición del mouse
        mouse_x, mouse_y = pygame.mouse.get_pos()

        for i, personaje in enumerate(personajes):
            pos_x = margen + (i * ancho_por_personaje) + (ancho_por_personaje / 2) - (
                        personaje['animaciones'][0].get_width() / 2)
            pos_y = constantes.ALTO_VENTANA // 2 - (personaje['animaciones'][0].get_height() / 2)
            rect_personaje = pygame.Rect(pos_x, pos_y, personaje['animaciones'][0].get_width(),
                                         personaje['animaciones'][0].get_height())

            # Resaltar el personaje si el mouse está sobre él
            if rect_personaje.collidepoint(mouse_x, mouse_y):
                imagen_resaltada = pygame.transform.scale(personaje['animaciones'][0], (
                int(personaje['animaciones'][0].get_width() * 1.1),
                int(personaje['animaciones'][0].get_height() * 1.1)))
                ventana.blit(imagen_resaltada, (
                pos_x - (imagen_resaltada.get_width() - personaje['animaciones'][0].get_width()) / 2,
                pos_y - (imagen_resaltada.get_height() - personaje['animaciones'][0].get_height()) / 2))
            else:
                ventana.blit(personaje['animaciones'][0], (pos_x, pos_y))

        # Dibuja texto de instrucción
        dibujar_texto("Seleccione su personaje", font, (0, 0, 0), constantes.ANCHO_VENTANA / 2 - 100, 100)

        pygame.display.flip()  # Actualiza la pantalla

        # Detectar eventos para seleccionar personaje
        for event in pygame.event.get():
            if event.type == pygame.QUIT:
                pygame.quit()
                exit()
            elif event.type == pygame.MOUSEBUTTONDOWN:
                for i, personaje in enumerate(personajes):
                    pos_x = margen + (i * ancho_por_personaje) + (ancho_por_personaje / 2) - (
                                personaje['animaciones'][0].get_width() / 2)
                    pos_y = constantes.ALTO_VENTANA // 2 - (personaje['animaciones'][0].get_height() / 2)
                    rect_personaje = pygame.Rect(pos_x, pos_y, personaje['animaciones'][0].get_width(),
                                                 personaje['animaciones'][0].get_height())
                    if rect_personaje.collidepoint(mouse_x, mouse_y):
                        personaje_seleccionado = personaje
                        seleccionando = False  # Salimos del menú
                        break
    return personaje_seleccionado



#pantalla de inicio
def pantalla_inicio():
    ventana.fill(constantes.COLOR_PIEL)
    ventana.blit(fondo, (constantes.ANCHO_VENTANA / 2 - fondo.get_width() / 2,
                                  constantes.ALTO_VENTANA / 2 - fondo.get_height() / 2 + 30))  # Ajusta la posición según sea necesario
    #dibujar_texto("chefsito's games", font_titulo, constantes.BLANCO,
    #              constantes.ANCHO_VENTANA / 2 - 200,
    #              constantes.ALTO_VENTANA / 2 - 200)

    # Centrar texto "Jugar" y "Salir" dentro de los botones
    ventana.blit(texto_boton_jugar, (boton_jugar.centerx - texto_boton_jugar.get_width() / 2,
                                     boton_jugar.centery - texto_boton_jugar.get_height() / 2))
    ventana.blit(texto_boton_salir, (boton_salir.centerx - texto_boton_salir.get_width() / 2,
                                     boton_salir.centery - texto_boton_salir.get_height() / 2))



# importar imagenes

#fondo
fondo = pygame.image.load("assets/images/fondo/fondo_inicio.png")

fondo = escalar_img(fondo, 5)

# energia
manzana_vacia = pygame.image.load("assets/images/items/manzana_empty.png").convert_alpha()
manzana_vacia = escalar_img(manzana_vacia, constantes.SCALA_MANZANA)
manzana_mitad = pygame.image.load("assets/images/items/manzana_half.png").convert_alpha()
manzana_mitad = escalar_img(manzana_mitad, constantes.SCALA_MANZANA)
manzana_llena = pygame.image.load("assets/images/items/manzana_full.png").convert_alpha()
manzana_llena = escalar_img(manzana_llena, constantes.SCALA_MANZANA)

# personaje
animaciones = []
for i in range(4):
    img = pygame.image.load(f"assets//images//characters//player//player 1//carpincho_{i}.png")
    img = escalar_img(img, constantes.SCALA_PERSONAJE)
    animaciones.append(img)

animaciones2 = []
for i in range(6):
    img2 = pygame.image.load(f"assets/images/characters/player/player 2/perro ({1}).png")
    img2 = escalar_img(img2, constantes.SCALA_PERSONAJE)
    animaciones2.append(img2)

personajes = [
    {"Adan": "Jugador 1", "animaciones" : animaciones},
{"Juan": "Jugador 2", "animaciones" : animaciones2}
]



# enemigos
directorio_enemigos = "assets//images//characters//enemies"
tipo_enemigos = nombres_carpetas(directorio_enemigos)
animaciones_enemigos = []
for ene in tipo_enemigos:
    lista_temp = []
    ruta_temp = f"assets//images//characters//enemies//{ene}"
    num_animaciones = contar_elementos(ruta_temp)
    for i in range(num_animaciones):
        img_enemigo = pygame.image.load((f"{ruta_temp}//{ene}_{i+1}.png"))
        img_enemigo = escalar_img(img_enemigo, constantes.SCALA_ENEMIGOS)
        lista_temp.append(img_enemigo)
    animaciones_enemigos.append(lista_temp)

# arma
imagen_pistola = pygame.image.load(f"assets/images/weapons/file.png")
imagen_pistola = escalar_img(imagen_pistola, constantes.SCALA_ARMA)

# Balas
imagen_bala = pygame.image.load(f"assets/images/weapons/bala.png").convert_alpha()
imagen_bala = escalar_img(imagen_bala, constantes.SCALA_ARMA)

#cargar imagenes del mundo
tile_list = []
for x in range (constantes.TILE_TYPES):
    tile_image = pygame.image.load(f"assets/images/tiles/tile({x+1}).png")
    tile_image = pygame.transform.scale(tile_image, (constantes.TILE_SIZE, constantes.TILE_SIZE))
    tile_list.append(tile_image)

# Items
posion_mate = pygame.image.load("assets//images//items//posion.png")
posion_mate = escalar_img(posion_mate, 0.7)

# Cargar imágenes de monedas
coin_images = []
ruta_img = "assets//images//items//coins"
num_coin_images = contar_elementos(ruta_img)
for i in range(num_coin_images):
    img = pygame.image.load(f"assets//images//items//coins//coin_{i+1}.png")
    img = escalar_img(img, 0.025)
    coin_images.append(img)

item_imagenes = [coin_images, [posion_mate]]

def dibujar_texto(texto, fuente, color, x, y):
    img = fuente.render(texto, True, color)
    ventana.blit(img, (x, y))


def vida_jugador():
    c_mitad_dibujado = False
    for i in range(4):
        if jugador.energia >= ((i+1) * 25):
            ventana.blit(manzana_llena, (5 + i * 50, 5))
        elif jugador.energia % 25 > 0 and not c_mitad_dibujado:
            ventana.blit(manzana_mitad, (5 + i * 50, 5))
            c_mitad_dibujado = True
        else:
            ventana.blit(manzana_vacia, (5 + i * 50, 5))
def resetear_mundo():
    grupo_damage_text.empty()
    grupo_balas.empty()
    grupo_items.empty()

    #crear lista de tiles vacia
    data = []
    for fila in range (constantes.FILAS):
        filas = [2] * constantes.COLUMNAS
        data.append(filas)
    return data


world_data = []

# cargar el archivo con el nivel
with open("niveles//nivel_1.csv", newline="") as csvfile:
    reader = csv.reader(csvfile, delimiter=",")
    world_data = [[int(columna) for columna in fila] for fila in reader]
world = Mundo()
world.process_data(world_data, tile_list, item_imagenes, animaciones_enemigos)

for fila in range (constantes.FILAS):
    filas = [101] * constantes.COLUMNAS
    world_data.append(filas)



def dibujar_grid():
    for x in range(30):
        pygame.draw.line(ventana, constantes.BLANCO, (x * constantes.TILE_SIZE, 0), (x * constantes.TILE_SIZE, constantes.ALTO_VENTANA))
        pygame.draw.line(ventana, constantes.BLANCO, (0, x * constantes.TILE_SIZE), (constantes.ANCHO_VENTANA, x * constantes.TILE_SIZE))
# crear jugador de la clase personajes
jugador = personaje.Personaje(50, 50, animaciones, 80, 1)



# crear lista enemigos
lista_enemigos = []
for ene in world.lista_enemigo:
    lista_enemigos.append(ene)

# crear un arma de la clase weapons
manzana_arma = Weapon(imagen_pistola, imagen_bala)

# crear un grupo de sprites
grupo_damage_text = pygame.sprite.Group()
grupo_balas = pygame.sprite.Group()
grupo_items = pygame.sprite.Group()
#añadir items desde la data del nivel

for item in world.lista_item:
    grupo_items.add(item)




# Definir las variables de movimiento del jugador
mover_arriba = False
mover_abajo = False
mover_izquierda = False
mover_derecha = False


# Controlar frame del rate
reloj = pygame.time.Clock()

# BOTON REINICIO
boton_reinicio = pygame.Rect(constantes.ANCHO_VENTANA / 2 - 100,
                             constantes.ALTO_VENTANA / 2 + 100, 200, 50)



mostrar_inicio = True
mostrar_seleccion = False
personaje_seleccionado = None
reloj = pygame.time.Clock()
run = True
sonido_loot = pygame.mixer.Sound("assets/sonidos/manzana_loot.mp3")
sonido_vida = pygame.mixer.Sound("assets/sonidos/matecito_loot.mp3")
while run:
    if mostrar_inicio:
        # Dibuja la imagen de fondo
        ventana.blit(fondo, (0, 0))
        pantalla_inicio()
        for event in pygame.event.get():
            if event.type == pygame.QUIT:
                run = False
            if event.type == pygame.MOUSEBUTTONDOWN:
                if boton_jugar.collidepoint(event.pos):
                    mostrar_inicio = False
                    mostrar_seleccion = True
    elif mostrar_seleccion:
        personaje_seleccionado = mostrar_menu_seleccion()
        jugador = Personaje(50, 50, personaje_seleccionado["animaciones"], 100, 1)
        mostrar_seleccion = False
    else:
        ventana.fill(constantes.COLOR_FONDO_JUEGO)

        # que vaya a 60fps
        reloj.tick(constantes.FPS)



        if jugador.vivo:
            # El resto de tu código para el juego...

            # Calcular el movimiento del jugador
            delta_x = 0
            delta_y = 0

            if mover_derecha:
                delta_x = constantes.VELOCIDAD
            if mover_izquierda:
                delta_x = -constantes.VELOCIDAD
            if mover_arriba:
                delta_y = -constantes.VELOCIDAD
            if mover_abajo:
                delta_y = constantes.VELOCIDAD




            # mover al jugador
            posicion_pantalla, nivel_completado = jugador.movimiento(delta_x, delta_y, world.obstaculos_tiles, world.exit_tile)

            #actualiazr el mapa
            world.update(posicion_pantalla)

            # actualiza el estado del jugador
            jugador.update(posicion_pantalla)



            # actualiza estado del arma
            bala = manzana_arma.update(jugador)
            if bala:
                grupo_balas.add(bala)
            for bala in grupo_balas:
                damage, pos_damage = bala.update(lista_enemigos, world.obstaculos_tiles)
                if damage:
                    damage_text = DamageText(pos_damage.centerx, pos_damage.centery, str(damage), font, constantes.ROJO)
                    grupo_damage_text.add(damage_text)
            # actualizar daño
            grupo_damage_text.update(posicion_pantalla)

            # actualizar items
            grupo_items.update(posicion_pantalla, jugador)

        #dibujar mundo
        world.draw(ventana)

        # dibujar jugador
        jugador.dibujar(ventana)

        # dibujar enemigo
        for ene in lista_enemigos:
            if ene.energia < 0:
                lista_enemigos.remove(ene)
            if ene.energia > 0:
                ene.enemigos(jugador, world.obstaculos_tiles, posicion_pantalla, world.exit_tile)
                ene.dibujar(ventana)

        # dibujar arma
        manzana_arma.dibujar(ventana)

        # dibujar balas
        for bala in grupo_balas:
            bala.dibujar(ventana)

        # dibujar manzana de vida
        vida_jugador()

        # dibujar textos

        grupo_damage_text.draw(ventana)
        dibujar_texto(f"Score: {jugador.score}", font, (255,255,0), 700, 5)

        #nivel
        dibujar_texto(f"Nivel : " + str(nivel), font, constantes.BLANCO, constantes.ANCHO_VENTANA / 2, 5)

        # dibujar items
        grupo_items.draw(ventana)

        if nivel_completado == True:
            if nivel < constantes.NIVEL_MAXIMO:
                nivel +=1
                world_data = resetear_mundo()
                # cargar el archivo con el nivel
                with open(f"niveles//nivel_{nivel}.csv", newline="") as csvfile:
                    reader = csv.reader(csvfile, delimiter=",")
                    for x, fila in enumerate(reader):
                        for y, columna in enumerate(fila):
                            world_data[x][y] = int(columna)
                world = Mundo()
                world.process_data(world_data, tile_list, item_imagenes, animaciones_enemigos)
                jugador.actualizar_coordenadas(constantes.COORDENADAS[str(nivel)])

                # crear lista enemigos
                lista_enemigos = []
                for ene in world.lista_enemigo:
                    lista_enemigos.append(ene)

                # añadir items desde la data del nivel
                for item in world.lista_item:
                    grupo_items.add(item)

        if jugador.vivo == False:
            ventana.fill(constantes.ROJO_OSCURO)
            text_rect = game_over_text.get_rect(center=(constantes.ANCHO_VENTANA /2,
                                                        constantes.ALTO_VENTANA /2))

            ventana.blit(game_over_text, text_rect)


            pygame.draw.rect(ventana, constantes.AMARILLO, boton_reinicio)
            ventana.blit(texto_boton_reinicio, (boton_reinicio.x + 50, boton_reinicio.y + 10))
        for event in pygame.event.get():
            if event.type == pygame.QUIT:
                run = False

            if event.type == pygame.KEYDOWN:
                if event.key == pygame.K_a:
                    mover_izquierda = True
                if event.key == pygame.K_d:
                    mover_derecha = True
                if event.key == pygame.K_w:
                    mover_arriba = True
                if event.key == pygame.K_s:
                    mover_abajo = True

            # para cuando se suelta la tecla
            if event.type == pygame.KEYUP:
                if event.key == pygame.K_a:
                    mover_izquierda = False
                if event.key == pygame.K_d:
                    mover_derecha = False
                if event.key == pygame.K_w:
                    mover_arriba = False
                if event.key == pygame.K_s:
                    mover_abajo = False

            if event.type == pygame.MOUSEBUTTONDOWN:
                if boton_reinicio.collidepoint(event.pos) and not jugador.vivo:
                    jugador.vivo = True
                    jugador.energia = 100
                    jugador.score = 0
                    nivel = 1
                    world_data = resetear_mundo()
                    with open(f"niveles//nivel_1.csv", newline="") as csvfile:
                        reader = csv.reader(csvfile, delimiter=",")
                        filas = list(reader)  # Leer todas las filas
                        # Ajustar world_data a las dimensiones del CSV
                        world_data = [[0 for _ in range(len(filas[0]))] for _ in range(len(filas))]

                        for x, fila in enumerate(filas):
                            for y, columna in enumerate(fila):
                                world_data[x][y] = int(columna)

                    world = Mundo()
                    world.process_data(world_data, tile_list, item_imagenes, animaciones_enemigos)
                    jugador.actualizar_coordenadas(constantes.COORDENADAS[str(nivel)])

                    # crear lista enemigos
                    lista_enemigos = []
                    for ene in world.lista_enemigo:
                        lista_enemigos.append(ene)

                    # añadir items desde la data del nivel
                    for item in world.lista_item:
                        grupo_items.add(item)
    pygame.display.update()