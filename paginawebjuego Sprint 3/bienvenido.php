<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background:rgb(128, 20, 20);
            color: #eee;
            text-align: center;
            padding-top: 50px;
        }
        .container {
            background: #1e1e1e;
            margin: 0 auto;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        h1 {
            color: #0a84ff;
        }
        a {
            color: #0a84ff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>!</h1>
        <p>Gracias por iniciar sesión en chefsito's game.</p>
        <p><a href="index.php">Ir a la página principal del juego</a></p>
        <p><a href="index.php?logout=true">Cerrar sesión</a></p>
    </div>
</body>
</html>