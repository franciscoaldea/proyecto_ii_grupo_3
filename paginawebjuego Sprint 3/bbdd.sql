-- Active: 1742060195588@@127.0.0.1@3306
CREATE DATABASE IF NOT EXISTS cinee;
USE cinee;

CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    mail VARCHAR(255) NOT NULL,
    contraseña VARCHAR(255) NOT NULL,
    nombre_usuario VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS items (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    detalle_item VARCHAR(255),
    tipo_item VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS inventario (
    id_inventario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario_fk INT,
    id_item_fk INT,
    FOREIGN KEY (nombre_usuario_fk) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_item_fk) REFERENCES items(id_item)
);

CREATE TABLE IF NOT EXISTS resenas (
    id_resena INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    texto_resenas TEXT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);
CREATE TABLE reacciones_resena (
    id_reaccion INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_resena INT NOT NULL,
    emoji VARCHAR(10) NOT NULL,
    fecha_reaccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_resena) REFERENCES resenas(id_resena),
    UNIQUE (id_usuario, id_resena)
);

INSERT INTO usuario (nombre_usuario, contraseña, mail) VALUES (
    'fran',
    '$2y$10$HGNZJOP7t2jjVeLIJx9nF.V0s4n4x8oa7g9.TXBmLFK/RhMk63NWm',
    'fran@example.com'
);
