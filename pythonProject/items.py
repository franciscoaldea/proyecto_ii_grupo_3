import pygame.sprite


class Item(pygame.sprite.Sprite):
    def __init__(self, x, y, item_type, animacion_list):
        pygame.sprite.Sprite.__init__(self)
        self.item_type = item_type  # 0 = monedas, 1 = posiones
        self.animacion_list = animacion_list
        self.frame_index = 0
        self.update_time = pygame.time.get_ticks()
        self.image = self.animacion_list[self.frame_index]
        # Inicializar la hitbox basada en la imagen
        self.rect = self.image.get_rect()
        self.rect.center = (x, y)
        # Reducir el tamaño de la hitbox (por ejemplo, la mitad del tamaño original)
        self.rect.width - 20  # Reducir el ancho
        self.rect.height - 90  # Reducir la altura
        self.rect = self.image.get_rect()
        self.rect.center = (x, y)


    def update(self, posicion_pantalla, personaje):
        from main import posicion_pantalla
        #reposicionar basado en el lugar de la camara
        self.rect.x += posicion_pantalla[0]
        self.rect.y += posicion_pantalla[1]
        #comprobar la colision entre personajes e items
        if self.rect.colliderect(personaje.forma):
            import main
            #monedas
            if self.item_type == 0:
                personaje.score += 1
                main.sonido_loot.play()
            #posiones
            elif self.item_type == 1:
                main.sonido_vida.play()

                personaje.energia += 50
                if personaje.energia > 100:
                    personaje.energia = 100
            self.kill()
        cooldown_animacion = 150
        self.image = self.animacion_list[self.frame_index]

        if pygame.time.get_ticks() - self.update_time > cooldown_animacion:
            self.frame_index += 1
            self.update_time = pygame.time.get_ticks()

        if self.frame_index >= len(self.animacion_list):
            self.frame_index = 0

