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

// --- REGISTRATION FORM HANDLING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $usuario = clean($_POST['usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $contrasena2 = $_POST['contrasena2'] ?? '';

    if (empty($usuario) || empty($contrasena) || empty($contrasena2)) {
        $errors[] = "Por favor completa todos los campos.";
    } elseif ($contrasena !== $contrasena2) {
        $errors[] = "Las contraseñas no coinciden.";
    } else {
        // Verificar si usuario ya existe
        $stmt = $mysqli->prepare("SELECT id_usuario FROM usuario WHERE nombre_usuario = ?");
        if ($stmt) {
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = "El nombre de usuario ya está en uso.";
            } else {
                // Insertar nuevo usuario con password_hash
                $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                $stmt2 = $mysqli->prepare("INSERT INTO usuario (nombre_usuario, contrasena) VALUES (?, ?)");
                if ($stmt2) {
                    $stmt2->bind_param('ss', $usuario, $hash);
                    if ($stmt2->execute()) {
                        // Redirect to login page with a success message
                        header("Location: login.php?registration_success=true");
                        exit();
                    } else {
                        $errors[] = "Error al crear la cuenta. Intenta de nuevo.";
                    }
                    $stmt2->close();
                } else {
                    $errors[] = "Error al preparar la consulta de registro.";
                }
            }
            $stmt->close();
        } else {
            $errors[] = "Error al preparar la consulta de verificación de usuario.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Registrarse - chefsito's game</title>
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
    ?>

    <h2>Registrarse</h2>
    <form method="POST" action="">
        <input type="hidden" name="action" value="register" />
        <input type="text" name="usuario" placeholder="Usuario" required />
        <input type="password" name="contrasena" placeholder="Contraseña" required />
        <input type="password" name="contrasena2" placeholder="Repetir contraseña" required />
        <button type="submit" class="btn-primary">Crear cuenta</button>
    </form>
    <div style="text-align:center;">
        <p>¿Ya tienes una cuenta? <a href="login.php" class="btn-toggle">Iniciar sesión</a></p>
    </div>
</div>

</body>
</html>