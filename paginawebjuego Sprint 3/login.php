<?php
session_start();
include("conexion.php"); // Ensure this file exists and contains your database connection logic

$errors = [];
$success = '';

// Auxiliary function for cleaning input
function clean($str) {
    return htmlspecialchars(trim($str));
}

// Redirect to index if user is already logged in
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// --- LOGIN FORM HANDLING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $usuario = clean($_POST['usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if (empty($usuario) || empty($contrasena)) {
        $errors[] = "Por favor completa todos los campos.";
    } else {
        $stmt = $mysqli->prepare("SELECT id_usuario, contrasena FROM usuario WHERE nombre_usuario = ?");
        if ($stmt) {
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id_usuario, $hash);
                $stmt->fetch();
                if (password_verify($contrasena, $hash)) {
                    // Login successful
                    $_SESSION['usuario_id'] = $id_usuario;
                    $_SESSION['usuario_nombre'] = $usuario;
                    header("Location: index.php");
                    exit();
                } else {
                    $errors[] = "Contraseña incorrecta.";
                }
            } else {
                $errors[] = "El usuario no existe.";
            }
            $stmt->close();
        } else {
            $errors[] = "Error al preparar la consulta de login.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Iniciar sesión - chefsito's game</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background:rgb(128, 20, 20);
        color: #eee;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }
    .auth-container {
        max-width: 400px;
        width: 100%;
        background: #1e1e1e;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    }
    .auth-container h2 {
        text-align: center;
        margin-bottom: 20px;
    }
    .auth-container form {
        display: flex;
        flex-direction: column;
    }
    .auth-container input[type="text"],
    .auth-container input[type="password"] {
        padding: 10px;
        margin-bottom: 15px;
        font-size: 1em;
        border-radius: 5px;
        border: none;
    }
    .auth-container button {
        padding: 12px;
        font-size: 1.1em;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }
    .btn-primary {
        background: #0a84ff;
        color: white;
        margin-bottom: 10px;
    }
    .btn-primary:hover {
        background: #006fdd;
    }
    .btn-toggle {
        background: transparent;
        color: #0a84ff;
        border: none;
        text-decoration: underline;
        cursor: pointer;
        font-size: 0.9em;
        margin-top: 10px;
    }
    .error-msg {
        background: #ff4c4c;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
        color: white;
    }
    .success-msg {
        background: #28a745;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
        color: white;
    }
</style>
</head>
<body>

<div class="auth-container">
    <?php
    // Mostrar errores
    if ($errors) {
        foreach ($errors as $error) {
            echo '<div class="error-msg">' . $error . '</div>';
        }
    }
    // Mostrar éxito (if redirected from registration with success)
    if (isset($_GET['registration_success']) && $_GET['registration_success'] == 'true') {
        echo '<div class="success-msg">Registro exitoso. Ya puedes iniciar sesión.</div>';
    }
    ?>

    <h2>Iniciar sesión</h2>
    <form method="POST" action="">
        <input type="hidden" name="action" value="login" />
        <input type="text" name="usuario" placeholder="Usuario" required />
        <input type="password" name="contrasena" placeholder="Contraseña" required />
        <button type="submit" class="btn-primary">Entrar</button>
    </form>
    <div style="text-align:center;">
        <p>¿No tienes cuenta? <a href="register.php" class="btn-toggle">Registrarse</a></p>
    </div>
</div>

</body>
</html>