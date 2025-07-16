<?php
session_start();
echo "ID de sesión actual: " . session_id() . "<br>";

if (!isset($_SESSION['test_var'])) {
    $_SESSION['test_var'] = 'Hola Sesion!';
    echo "Variable de sesión 'test_var' establecida.<br>";
} else {
    echo "Variable de sesión 'test_var' ya existe: " . $_SESSION['test_var'] . "<br>";
}

echo "Contenido completo de \$_SESSION:<br>";
var_dump($_SESSION);
?>