<?php
# Inicia o reanuda la sesión PHP. Es crucial para mantener el estado del usuario entre diferentes páginas.
session_start();
# Incluye el archivo de conexión a la base de datos.
include("conexion.php"); 

# Cuando el usuario hace click en "Cerrar Sesión" (detectado por el parámetro 'logout' en la URL),
# elimina su sesión actual y lo redirige a la página de inicio de sesión (login.php).
if (isset($_GET['logout'])) {
    # Destruye todos los datos de la sesión actual del usuario.
    session_destroy(); 
    # Redirige el navegador del usuario a la página de login.
    header("Location: login.php"); 
    # Termina la ejecución del script para asegurar la redirección inmediata.
    exit(); 
}

# Si un usuario intenta acceder a esta página sin haber iniciado sesión previamente (es decir,
# si la variable de sesión 'usuario_id' no está establecida), se lo redirige a la página de login.
if (!isset($_SESSION['usuario_id'])) {
    # Redirige el navegador del usuario a la página de login.
    header("Location: login.php");
    # Termina la ejecución del script para asegurar la redirección inmediata.
    exit();
}

# Identifica una solicitud para eliminar una reseña que fue enviada a través de un método POST.
# Comprueba que la solicitud HTTP sea POST, que el campo 'action' esté presente y que su valor sea 'delete_resena'.
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "delete_resena") {
    # Se hace un control de seguridad adicional para asegurar que solo un usuario autenticado
    # pueda intentar eliminar una reseña, incluso si la solicitud ya es POST y tiene la acción correcta.
    if (!isset($_SESSION['usuario_id'])) {
        # Si no está autenticado, redirige a la página de login.
        header("location: login.php");
        # Termina la ejecución del script.
        exit();
    }
# Obtiene el ID del usuario logueado de la sesión, que se usará para verificar la propiedad de la reseña.
    $id_usuario = $_SESSION["usuario_id"];
# Obtiene el ID de la reseña a eliminar del formulario POST y lo convierte en un entero
# para prevenir inyecciones SQL y asegurar que es un valor numérico.
    $id_resena_a_eliminar = intval($_POST["id_resena_a_eliminar"]); 

    # Prepara una consulta SQL para eliminar una reseña de la tabla 'resenas'.
    # La eliminación solo se realizará si el 'id_resena' coincide Y el 'id_usuario' coincide,
    # lo que asegura que un usuario solo pueda eliminar sus propias reseñas.
    $stmt = $mysqli->prepare("DELETE FROM resenas WHERE id_resena = ? AND id_usuario = ?");
    # Verifica si la preparación de la consulta fue exitosa.
    if ($stmt) {
        # Vincula los parámetros a la consulta preparada. "ii" indica que ambos son enteros.
        $stmt->bind_param("ii", $id_resena_a_eliminar, $id_usuario);
        # Ejecuta la consulta preparada.
        if ($stmt->execute()) {
            // Comentario: Aquí se podría añadir lógica para mostrar un mensaje de éxito al usuario.
            // reseña eliminada con éxito
        } else {
            // Comentario: Aquí se podría añadir lógica para mostrar un mensaje de error si la eliminación falló.
            // error al eliminar la reseña
        }
        # Cierra la sentencia preparada para liberar los recursos de la base de datos.
        $stmt->close();
    } else {
        // Comentario: Aquí se podría añadir lógica para mostrar un mensaje de error si la preparación de la consulta falló.
        // error al preparar la consulta
    }
    # Después de intentar eliminar la reseña, redirige al usuario a la página principal.
    header("location: index.php");
    # Termina la ejecución del script para asegurar la redirección inmediata.
    exit();
}


# Este bloque maneja las interacciones de los usuarios con las reacciones a las reseñas (ej. likes, corazones).
# Se activa si la solicitud es POST y la acción es 'react_resena'.
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "react_resena") {
    # Vuelve a verificar que el usuario esté autenticado antes de permitir la reacción.
    if (!isset($_SESSION['usuario_id'])) {
        # Redirige a la página de login si no está autenticado.
        header("Location: login.php");
        # Termina la ejecución.
        exit();
    }

    # Obtiene el ID del usuario actual de la sesión.
    $id_usuario = $_SESSION["usuario_id"];
    # Obtiene el ID de la reseña a la que se está reaccionando y lo convierte a entero.
    $id_resena = intval($_POST["id_resena"]); 
    # Obtiene el emoji seleccionado por el usuario y elimina espacios en blanco al inicio/final.
    $emoji = trim($_POST["emoji"]);

    # Define un array con los emojis permitidos para las reacciones.
    $emojis_permitidos = ['👍', '❤️', '😂', '🔥', '🤔']; 
    # Si el emoji recibido no está en la lista de permitidos, se establece un valor predeterminado (👍).
    if (!in_array($emoji, $emojis_permitidos)) {
        $emoji = '👍'; 
    }

    # Prepara una sentencia SQL 'INSERT ... ON DUPLICATE KEY UPDATE'.
    # Esto permite que los usuarios añadan su reacción si aún no han reaccionado a esa reseña,
    # o que cambien su reacción si ya habían reaccionado (actualizando el emoji y la fecha).
    $stmt = $mysqli->prepare("INSERT INTO reacciones_resena (id_usuario, id_resena, emoji) VALUES (?, ?, ?)
                             ON DUPLICATE KEY UPDATE emoji = VALUES(emoji), fecha_reaccion = CURRENT_TIMESTAMP");
    # Verifica si la preparación de la consulta fue exitosa.
    if ($stmt) {
        # Vincula los parámetros a la consulta. "iis" indica entero, entero, string.
        $stmt->bind_param("iis", $id_usuario, $id_resena, $emoji);
        # Ejecuta la consulta.
        if ($stmt->execute()) {
            // Comentario: Lógica para manejar el éxito de la reacción (guardada/actualizada).
            // reacción guardada/actualizada exitosamente.
        } else {
            // Comentario: Lógica para manejar errores al guardar la reacción.
            // error al guardar la reacción.
        }
        # Cierra la sentencia preparada.
        $stmt->close();
    } else {
        // Comentario: Lógica para manejar errores al preparar la consulta.
        // error al preparar la reacción.
    }
    # Después de procesar la reacción, redirige al usuario a la página principal para ver los cambios.
    header("Location: index.php");
    # Termina la ejecución del script.
    exit();
}


# Este bloque procesa el envío de nuevas reseñas por parte de los usuarios.
# Se activa si la solicitud es POST y el campo 'texto_resena' está presente.
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["texto_resena"])) {
    # Obtiene el texto de la reseña del formulario POST y elimina espacios en blanco.
    $texto = trim($_POST["texto_resena"]);
    # Obtiene el ID del usuario actual de la sesión.
    $id_usuario = $_SESSION["usuario_id"]; 

    # Verifica que el texto de la reseña no esté vacío antes de intentar insertarlo.
    if (!empty($texto)) {
        # Prepara una sentencia SQL para insertar una nueva reseña en la tabla 'resenas'.
        $stmt = $mysqli->prepare("INSERT INTO resenas (id_usuario, texto_resenas) VALUES (?, ?)");
        # Verifica si la preparación de la consulta fue exitosa.
        if ($stmt) {
            # Vincula los parámetros a la consulta. "is" indica entero, string.
            $stmt->bind_param("is", $id_usuario, $texto);
            # Ejecuta la consulta.
            $stmt->execute();
            # Cierra la sentencia preparada.
            $stmt->close();
        } else {
            # Muestra un mensaje de error si la preparación de la declaración falló.
            echo "error al preparar la declaración: " . $mysqli->error;
        }
    }
    // Comentario: Aunque no hay una redirección explícita aquí, al recargar la página (común después de un POST),
    // la nueva reseña aparecerá en la lista.
}

# Este bloque es responsable de obtener todas las reseñas y sus respectivas reacciones de la base de datos.
# Inicializa un array vacío para almacenar las reseñas y sus reacciones.
$reseñas_y_reacciones = [];
# Consulta SQL para seleccionar todas las reseñas junto con el nombre de usuario del autor,
# ordenadas por la fecha de creación en orden descendente (las más nuevas primero).
$query_reseñas = "SELECT r.id_resena, u.nombre_usuario, r.texto_resenas, r.fecha_creacion, r.id_usuario 
                  FROM resenas r JOIN usuario u ON r.id_usuario = u.id_usuario
                  ORDER BY r.fecha_creacion DESC";
# Ejecuta la consulta de las reseñas.
$result_reseñas = $mysqli->query($query_reseñas);

# Verifica si la consulta de reseñas fue exitosa.
if ($result_reseñas) {
    # Itera sobre cada fila de resultados y la añade al array '$reseñas_y_reacciones'.
    while($row_reseña = $result_reseñas->fetch_assoc()) {
        $reseñas_y_reacciones[] = $row_reseña;
    }
    # Libera la memoria asociada al resultado de la consulta.
    $result_reseñas->free();
}

# Itera sobre cada reseña obtenida para cargar sus reacciones y la reacción del usuario actual.
# El '&' antes de '$reseña' hace que la variable sea una referencia, permitiendo modificar el array original.
foreach ($reseñas_y_reacciones as &$reseña) { 
    # Obtiene el ID de la reseña actual para las consultas de reacción.
    $id_resena_actual = $reseña['id_resena'];
    # Inicializa un array para almacenar el conteo de cada tipo de emoji para la reseña actual.
    $reacciones_count = []; 
    # Inicializa una variable para almacenar el emoji con el que el usuario actual reaccionó a esta reseña.
    $user_reacted_emoji = ''; 

    # Prepara una consulta para obtener el conteo de cada emoji para la reseña actual.
    $stmt_reacciones = $mysqli->prepare("SELECT emoji, COUNT(*) AS count FROM reacciones_resena WHERE id_resena = ? GROUP BY emoji");
    # Verifica si la preparación de la consulta fue exitosa.
    if ($stmt_reacciones) {
        # Vincula el ID de la reseña a la consulta. "i" indica entero.
        $stmt_reacciones->bind_param("i", $id_resena_actual);
        # Ejecuta la consulta.
        $stmt_reacciones->execute();
        # Obtiene el resultado de la consulta.
        $result_reacciones = $stmt_reacciones->get_result();
        # Itera sobre los resultados y almacena el conteo de cada emoji en el array '$reacciones_count'.
        while($row_reaccion = $result_reacciones->fetch_assoc()) {
            $reacciones_count[$row_reaccion['emoji']] = $row_reaccion['count'];
        }
        # Cierra la sentencia preparada.
        $stmt_reacciones->close();
    }

    # Verifica si el usuario actual está logueado para ver si ha reaccionado a esta reseña.
    if (isset($_SESSION['usuario_id'])) {
        # Prepara una consulta para obtener el emoji con el que el usuario actual reaccionó a esta reseña.
        $stmt_user_reaction = $mysqli->prepare("SELECT emoji FROM reacciones_resena WHERE id_resena = ? AND id_usuario = ?");
        # Verifica si la preparación de la consulta fue exitosa.
        if ($stmt_user_reaction) {
            # Vincula los parámetros (ID de reseña e ID de usuario) a la consulta.
            $stmt_user_reaction->bind_param("ii", $id_resena_actual, $_SESSION['usuario_id']);
            # Ejecuta la consulta.
            $stmt_user_reaction->execute();
            # Obtiene el resultado de la consulta.
            $result_user_reaction = $stmt_user_reaction->get_result();
            # Si se encontró una reacción del usuario, almacena el emoji.
            if ($row_user_reaction = $result_user_reaction->fetch_assoc()) {
                $user_reacted_emoji = $row_user_reaction['emoji'];
            }
            # Cierra la sentencia preparada.
            $stmt_user_reaction->close();
        }
    }

    # Añade el conteo de reacciones y el emoji reaccionado por el usuario a la reseña actual.
    $reseña['reacciones_count'] = $reacciones_count;
    $reseña['user_reacted_emoji'] = $user_reacted_emoji;
}
# Desvincula la referencia de '$reseña' para evitar efectos secundarios inesperados en bucles futuros.
unset($reseña); 

# Este bloque de cierre de sesión es redundante con el que ya está al principio del archivo.
# Se recomienda mantener solo uno para evitar confusiones y mejorar la claridad del código.
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php"); 
    exit();
}

# Nota: los detalles de conexión a la base de datos y funciones auxiliares siguen siendo necesarios.
# Es una buena práctica mantener la conexión en 'conexion.php' y solo incluirla para organizar el código.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tiny5&display=swap" rel="stylesheet">
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>chefsito's page</title>
    <style>
        /* --- estilos globales para el cuerpo de la página --- */
        /* Esta sección define el estilo general del documento, incluyendo el fondo,
        la fuente predeterminada y el color del texto. La imagen de fondo está
        configurada para cubrir toda la ventana y ser fija al desplazarse,
        dando un efecto de paralaje suave si el contenido es desplazable. */
        body {
            background-image: url('img/bannerpro.png'); 
            background-size: 100% 100%; 
            background-position: center center; 
            background-repeat: no-repeat; 
            background-attachment: fixed; 
            font-family: 'tiny5', Arial, sans-serif;
            color: #eee; 
            margin: 0;
            padding: 0; 
        }
        /* --- estilos para el encabezado (header) de la página --- */
        /* Esta sección estiliza la barra superior de la página,
        proporcionando un fondo semitransparente, padding y alineación central para el texto.
        La propiedad 'position: relative' es necesaria para posicionar elementos hijos de forma absoluta dentro de él (como el botón de cerrar sesión). */
        header {
            background-image: none; 
            background: rgba(34, 34, 34, 0.7); 
            padding: 20px;
            text-align: center;
            position: relative;
            height: auto; 
        }

        /* --- estilos para el título principal dentro del header --- */
        /* Define la apariencia del título 'la tierra de las manzanas',
        incluyendo color, una sombra de texto para mejorar la legibilidad y tamaño de fuente. */
        header h1 {
            color: white; 
            text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
            margin: 0;
            font-size: 2.5em;
        }
        /* --- estilos para los formularios de reseña y los ítems de reseña individuales --- */
        /* Esta sección aplica estilos generales a los contenedores de formularios y reseñas,
        como un fondo semitransparente oscuro, bordes redondeados y un margen inferior para separarlos. */
        .review-form, .review-item {
            background: rgba(42, 42, 42, 0.8); 
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #eee; 
        }

        /* --- estilos para el botón de cerrar sesión --- */
        /* Esta sección estiliza el botón de cierre de sesión,
        definiendo su color de fondo. Las otras propiedades se definen en una clase posterior con posicionamiento absoluto. */
        .logout-button {
            background-color: #dc3545; 
            /* ... otros estilos del botón ... */
        }

        /* --- estilos para el contenido principal (main) --- */
        /* Esta sección centra el contenido principal en la página,
        establece un ancho máximo para una mejor legibilidad en pantallas grandes
        y añade padding horizontal para que el contenido no toque los bordes en pantallas pequeñas. */
        main {
            max-width: 900px;
            margin: 20px auto;
            padding: 0 20px;
        }
        /* --- estilos para la sección de descripción del juego --- */
        /* Esta sección aplica un fondo distintivo (verde), padding y bordes redondeados
        a la caja de descripción del juego, haciéndola resaltar. */
        section.description {
            background:rgb(27, 105, 16);
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px; 
        }
        /* --- estilos para los títulos h2 dentro de la descripción --- */
        /* Ajusta el margen superior de los títulos dentro de la descripción para una mejor separación visual. */
        section.description h2 {
            margin-top: 0;
        }
        /* --- estilos para el botón de descarga --- */
        /* Estiliza el botón de descarga, dándole un aspecto prominente con
        color de fondo, texto blanco, padding generoso, tamaño de fuente más grande, bordes redondeados,
        y un efecto de transición suave al pasar el ratón. Se centra en la página. */
        button.download-btn {
            display: block;
            background:rgb(15, 231, 33);
            color: white;
            border: none;
            padding: 15px 25px;
            font-size: 1.2em;
            border-radius: 8px;
            cursor: pointer;
            margin: 20px auto;
            transition: background-color 0.3s ease;
        }
        /* --- efecto hover para el botón de descarga --- */
        /* Define el cambio de estilo (fondo transparente) cuando el cursor se posa sobre el botón de descarga. */
        button.download-btn:hover {
            background:rgba(255, 255, 255, 0);
        }
        /* --- estilos para la sección de reseñas --- */
        /* Esta sección define el aspecto general del contenedor de reseñas,
        incluyendo un margen superior para separarlo, un fondo oscuro, padding interno y bordes redondeados. */
        section.reviews {
            margin-top: 40px;
            background: #1e1e1e; 
            padding: 20px;
            border-radius: 10px;
        }
        /* --- estilos para los títulos h2 dentro de las reseñas --- */
        /* Ajusta el margen inferior de los títulos dentro de la sección de reseñas para una mejor separación. */
        section.reviews h2 {
            margin-bottom: 10px;
        }
        /* --- estilos para el formulario de reseña --- */
        /* Esta sección organiza los elementos del formulario de reseña
        utilizando flexbox para una disposición vertical de los elementos. */
        form.review-form {
            display: flex;
            flex-direction: column;
        }
        /* --- estilos para el área de texto de la reseña --- */
        /* Estiliza el campo de entrada de texto para las reseñas,
        permitiendo redimensionamiento vertical por el usuario, estableciendo una altura mínima,
        añadiendo padding, tamaño de fuente, bordes redondeados y un margen inferior. */
        textarea {
            resize: vertical;
            min-height: 100px;
            padding: 10px;
            font-size: 1em;
            border-radius: 5px;
            border: none;
            margin-bottom: 10px;
        }
        /* --- estilos para el botón de enviar reseña --- */
        /* Define el estilo del botón para enviar reseñas,
        incluyendo color de fondo (verde), texto blanco, sin borde, padding,
        tamaño de fuente, bordes redondeados, cursor de puntero y una transición suave.
        Se alinea a la izquierda dentro del flexbox. */
        button.submit-review {
            align-self: flex-start;
            background:rgb(25, 255, 79);
            color: white;
            border: none;
            padding: 10px 18px;
            font-size: 1em;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        /* --- efecto hover para el botón de enviar reseña --- */
        /* Define el cambio de color (a un verde más oscuro) cuando el cursor se posa sobre el botón de enviar reseña. */
        button.submit-review:hover {
            background: #1e7e34;
        }
        /* --- estilos para el botón de cerrar sesión (posicionamiento absoluto) --- */
        /* Esta sección posiciona el botón de cerrar sesión de forma absoluta en la esquina
        superior izquierda del encabezado, asegurando que siempre sea visible y no afecte el flujo del documento.
        Tiene un z-index alto para que aparezca por encima de otros elementos si hay superposición. */
        .logout-button {
            position: absolute; 
            top: 10px; 
            left: 10px; 
            display: block;
            width: fit-content;
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            z-index: 10; 
        }
        /* --- efecto hover para el botón de cerrar sesión --- */
        /* Define el cambio de color (a un rojo más oscuro) cuando el cursor se posa sobre el botón de cerrar sesión. */
        .logout-button:hover {
            background-color: #c82333;
        }
        /* --- clase para la fuente 'tiny5' --- */
        /* Define la aplicación de la fuente "tiny5" para elementos que tengan esta clase,
        asegurando un peso de fuente normal y estilo normal. */
        .tiny5-regular {
            font-family: "tiny5", sans-serif;
            font-weight: 400;
            font-style: normal;
        }
        /* --- estilos para cada ítem de reseña individual --- */
        /* Esta sección estiliza cada reseña individualmente,
        proporcionando un fondo oscuro, padding, bordes redondeados y un margen inferior para separarlas. */
        .review-item {
            background: #2a2a2a;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        /* --- estilos para los metadatos de la reseña (autor, fecha) --- */
        /* Estiliza la información del autor y la fecha de la reseña,
        incluyendo un tamaño de fuente ligeramente más pequeño y un color de texto más claro. */
        .review-meta {
            font-size: 0.9em;
            color: #bbb;
            margin-bottom: 10px;
        }
        /* --- estilos para el contenido de texto de la reseña --- */
        /* Define el estilo del texto principal de la reseña,
        incluyendo el margen inferior y la altura de línea para mejorar la legibilidad. */
        .review-content {
            margin-bottom: 15px;
            line-height: 1.5;
        }
        /* --- estilos para la sección de reacciones (emojis) --- */
        /* Organiza los botones de reacción de emojis utilizando flexbox
        para una alineación horizontal, alineación vertical de ítems y espaciado entre ellos. */
        .reaction-section {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        /* --- estilos para los botones de reacción de emoji --- */
        /* Estiliza los botones de reacción de emojis, incluyendo
        fondo transparente, borde sutil, bordes redondeados, padding, tamaño de fuente,
        cursor de puntero y transiciones suaves para efectos visuales.
        Utiliza flexbox para alinear el emoji y el conteo. */
        .reaction-button {
            background: none;
            border: 1px solid #444;
            border-radius: 5px;
            padding: 5px 10px;
            font-size: 1.2em;
            cursor: pointer;
            transition: background-color 0.2s, border-color 0.2s;
            color: #eee;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        /* --- efecto hover para los botones de reacción --- */
        /* Define los cambios de estilo (fondo y color de borde más oscuro) cuando el cursor se posa sobre los botones de reacción. */
        .reaction-button:hover {
            background-color: #3a3a3a;
            border-color: #666;
        }
        /* --- estilos para el botón de reacción activo (seleccionado por el usuario) --- */
        /* Esta clase aplica un estilo visual distintivo (fondo azul, borde azul y texto blanco)
        a un botón de reacción cuando el usuario ya ha seleccionado ese emoji para una reseña,
        indicando su estado actual. */
        .reaction-button.active {
            background-color: #007bff; 
            border-color: #007bff;
            color: white;
        }
        /* --- estilos para el contador de reacciones (número junto al emoji) --- */
        /* Estiliza el número que muestra la cantidad de veces que se ha usado un emoji para una reseña. */
        .reaction-count {
            font-size: 0.9em;
            color: #ccc;
        }
        /* --- estilos para el conteo total de reacciones de una reseña --- */
        /* Posiciona el conteo total de reacciones a la derecha de la sección de reacciones
        utilizando 'margin-left: auto' dentro de un flexbox. */
        .total-reactions {
            margin-left: auto; 
            font-size: 0.9em;
            color: #bbb;
        }
        /* --- estilos para el contenedor del video (iframe responsivo) --- */
        /* Esta sección asegura que el video incrustado sea responsivo y
        mantenga una relación de aspecto consistente (16:9, dado por padding-bottom: 56.25%)
        en diferentes tamaños de pantalla. Oculta el desbordamiento y añade un fondo negro. */
        .video-container {
            position: relative;
            padding-bottom: 56.25%; 
            height: 0;
            overflow: hidden;
            max-width: 100%;
            margin: 20px 0; 
            background: #000; 
            border-radius: 8px; 
        }

        /* --- estilos para el iframe dentro del contenedor de video (para hacerlo responsivo) --- */
        /* Asegura que el iframe del video ocupe todo el espacio de su contenedor responsivo,
        cubriendo la posición absoluta y eliminando cualquier borde predeterminado. */
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
        /* --- estilos para el botón de eliminar reseña --- */
        /* Define el estilo visual del botón de eliminar reseña,
        usando un color rojo para indicar una acción de borrado, texto blanco,
        bordes redondeados, padding y un tamaño de fuente pequeño para que sea discreto. */
        .delete-button {
            background-color: #dc3545; 
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            font-size: 0.8em; 
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        /* --- efecto hover para el botón de eliminar reseña --- */
        /* Cambia el color del botón de eliminar reseña a un rojo más oscuro al pasar el ratón. */
        .delete-button:hover {
            background-color: #c82333; 
        }

    </style>
</head>
<body>

<header>
    <h1>la tierra de las manzanas</h1>
</header>

<main>
        <a href="?logout=true" class="logout-button">cerrar sesión</a>
    <section class="description">
        <h2>descripción del juego</h2>
        <p>
            Acompañá a Juan, Adán y Maria en una historia que combina amistad, misterio y tecnología. Todo comienza como un simple paseo para recolectar manzanas, pero el destino da un giro inesperado cuando aparece la Señora Holograma, una enigmática figura digital que roba toda la cosecha… ¡y además secuestra a Maria!

Con solo un fragmento digital de su amiga como pista, Juan y Adán —bajo los apodos de Martincho y Morte— deberán embarcarse en una travesía repleta de desafíos, enemigos y obstáculos que pondrán a prueba su valentía. Cada nivel traerá nuevos peligros y secretos, mientras los protagonistas desbloquean habilidades únicas y se enfrentan a criaturas misteriosas en su camino hacia el enfrentamiento final contra la Señora Holograma.

¿Lograrán salvar a Maria y recuperar las manzanas? Descubrilo en este juego lleno de acción, emoción y compañerismo.
        </p>
        <h3>gameplay del juego</h3>
                <div class="video-container">
                        <iframe width="560" height="315" src="https://www.youtube.com/embed/fjRyYf5TQgk?si=0b4LrTOs4FhdQ8iC" title="youtube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-called-cross-origin" allowfullscreen>
            </iframe>
        </div>
                <button class="download-btn">descargar juego</button>
    </section>

    <section class="reviews">
        <h2>escribe tu reseña</h2>
                <form class="review-form" method="post" action="">
                        <textarea name="texto_resena" placeholder="escribe aquí tu reseña..."></textarea>
                        <button type="submit" class="submit-review">enviar reseña</button>
        </form>

        <h3>reseñas de usuarios:</h3>
        <?php 
        # Comprueba si el array de reseñas está vacío.
        if (empty($reseñas_y_reacciones)): 
        ?>
                <p>aún no hay reseñas. ¡sé el primero en escribir una!</p>
        <?php else: ?>
            <?php 
            # Itera sobre cada reseña obtenida de la base de datos para mostrarla.
            foreach ($reseñas_y_reacciones as $reseña): 
            ?>
                                <div class="review-item">
                                        <div class="review-meta">
                        <strong><?php echo htmlspecialchars($reseña['nombre_usuario']); ?></strong>
                        <span> - <?php echo (new datetime($reseña['fecha_creacion']))->format('d/m/y h:i'); ?></span>
                        
                        <?php 
                        # Este bloque muestra el botón de eliminar reseña.
                        # Solo se renderiza si el usuario actualmente logueado es el autor de la reseña,
                        # lo que asegura que solo los creadores puedan borrar sus propias entradas.
                        if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $reseña['id_usuario']): 
                        ?>
                                                        <form method="post" action="" style="display:inline-block; margin-left: 10px;">
                                <input type="hidden" name="action" value="delete_resena">
                                <input type="hidden" name="id_resena_a_eliminar" value="<?php echo $reseña['id_resena']; ?>">
                                                                <button type="submit" class="delete-button" onclick="return confirm('¿estás seguro de que quieres eliminar esta reseña?');">eliminar</button>
                            </form>
                        <?php endif; ?>

                    </div>
                                        <p class="review-content"><?php echo htmlspecialchars($reseña['texto_resenas']); ?></p>

                                        <div class="reaction-section">
                        <?php
                        // Este bloque itera sobre los emojis disponibles y muestra un botón de reacción para cada uno.
                        // También muestra el conteo de cada reacción y resalta la reacción del usuario actual.
                        $emojis_disponibles = ['👍', '❤️', '😂', '🔥', '🤔']; 
                        $total_reactions_for_review = 0;
                        foreach ($emojis_disponibles as $emoji_option):
                            # Obtiene el conteo de cada emoji para la reseña actual, si no existe, es 0.
                            $count = $reseña['reacciones_count'][$emoji_option] ?? 0;
                            # Suma el conteo de cada emoji al total de reacciones de esta reseña.
                            $total_reactions_for_review += $count;
                            # Determina si el emoji actual es el que el usuario ya ha seleccionado, para aplicar la clase 'active'.
                            $is_active = ($reseña['user_reacted_emoji'] === $emoji_option) ? ' active' : '';
                        ?>
                                                        <form method="post" action="" style="display:inline-block;">
                                <input type="hidden" name="action" value="react_resena">
                                <input type="hidden" name="id_resena" value="<?php echo $reseña['id_resena']; ?>">
                                <input type="hidden" name="emoji" value="<?php echo $emoji_option; ?>">
                                                                <button type="submit" class="reaction-button<?php echo $is_active; ?>" title="reaccionar con <?php echo $emoji_option; ?>">
                                    <?php echo $emoji_option; ?> <span class="reaction-count"><?php echo $count; ?></span>
                                </button>
                            </form>
                        <?php endforeach; ?>
                                                <span class="total-reactions">total: <?php echo $total_reactions_for_review; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
    
        <a href="?logout=true" class="logout-button">cerrar sesión</a>

</main>

</body>
</html>