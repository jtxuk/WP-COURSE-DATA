<?php
/*
Plugin Name: Datos Cursos
Description: Un plugin para agregar y gestionar los datos de todos los cursos.
Author: Josep TXK
Version: 1.0
*/

/*
 * 
 */
function datos_cursos_menu() {

    add_menu_page(
        'Cursos',
        'Datos Cursos',
        'manage_options',
        'datos-cursos',
        'datos_cursos_pagina_principal',
        'dashicons-welcome-learn-more',
        100
    );
    
    add_submenu_page(
        'datos-cursos',
        'Añadir Curso',
        'Añadir Curso',
        'manage_options',
        'datos-cursos-anadir',
        'datos_cursos_anadir'
    );
    
}
add_action('admin_menu', 'datos_cursos_menu');

/*
 * Función para guardar los cursos cuando se envía el formulario
 */
function datos_cursos_anadir() {

	// Para cargar los estilos CSS
    wp_enqueue_style('datos-cursos-admin', plugins_url('admin.css', __FILE__));

	 //campos    
	 if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $curso = array(
            'nombre' => sanitize_text_field($_POST['nombre']),
            'fecha' => sanitize_text_field($_POST['fecha']),
            'horario' => sanitize_text_field($_POST['horario']),
            'precio' => sanitize_text_field($_POST['precio'])
        );

        // Obtén la opción arr_cursos y asegúrate de que sea un array
        $arr_cursos = get_option('arr_cursos');
        if (!is_array($arr_cursos)) {
            $arr_cursos = array();
        }

        $arr_cursos[] = $curso;
        update_option('arr_cursos', $arr_cursos);
    }
    ?>
    
    <!- He metido todo el CSS del formulario incrustado en cada elemento porque no había manera de que me lo cogiera de la hoja de estilos. ->
    <div class="formulario-anadir-curso" style="background-color: #f9f9f9;padding: 20px;border-radius: 5px;max-width: 400px;margin: 40px 40px;">
    	<h1 style="margin-top: 20px;margin-bottom: 50px;text-align: left;">Formulario Para Añadir Curso</h1>
	    <form method="post">
	        <label style="display: block;margin-bottom: 5px;" for="nombre">Nombre:</label>
	        <input style="width: 100%;padding: 5px 10px;margin-bottom: 15px;border: 1px solid #ccc;border-radius: 3px;" type="text" name="nombre" required><br>
	        <label style="display: block;margin-bottom: 5px;" for="fecha">Fecha:</label>
	        <input style="width: 100%;padding: 5px 10px;margin-bottom: 15px;border: 1px solid #ccc;border-radius: 3px;" type="text" name="fecha" required><br>
	        <label style="display: block;margin-bottom: 5px;" for="horario">Horario:</label>
	        <input style="width: 100%;padding: 5px 10px;margin-bottom: 15px;border: 1px solid #ccc;border-radius: 3px;" type="text" name="horario" required><br>
	        <label style="display: block;margin-bottom: 5px;" for="precio">Precio:</label>
	        <input style="width: 100%;padding: 5px 10px;margin-bottom: 15px;border: 1px solid #ccc;border-radius: 3px;" type="text" name="precio" required><br>
	        <input style="background-color: #0073aa;border: none;color: white;padding: 10px 20px;text-align: center;text-decoration: none;display: inline-block;font-size: 14px;cursor: pointer;border-radius: 4px;letter-spacing: 2;" type="submit" value="Añadir Curso">
	    </form>
    </div>
    <?php
}

/*
 * Función para mostrar los cursos en la página principal del plugin:
 */
function datos_cursos_pagina_principal() {

	// Cargar los estilos CSS
    wp_enqueue_style('datos-cursos-admin', plugins_url('admin.css', __FILE__));

    $arr_cursos = get_option('arr_cursos', array());
    ?>
    <h1>Listado de Cursos</h1>
    <p>Este plugin sirve para manejar de forma fácil y sencilla los datos de todos los cursos, y que automáticamente se visualicen en cualquier punto de la web.</p>
    <h4>Forma de utilizarlo:</h4>
    <p>Para mostrar la fecha del curso en la posición 1: <b>[datos_cursos curso=1 campo=fecha]</b></p>    
    <table border="1">
        <tr>
            <th>Número</th>
            <th>Nombre</th>
            <th>Fecha</th>
            <th>Horario</th>
            <th>Precio</th>
        </tr>
        <?php foreach ($arr_cursos as $key => $curso): ?>
            <tr>
                <td><?php echo $key + 1; ?></td>
                <td contenteditable="true"><?php echo esc_html($curso['nombre']); ?></td>
                <td contenteditable="true"><?php echo esc_html($curso['fecha']); ?></td>
                <td contenteditable="true"><?php echo esc_html($curso['horario']); ?></td>
                <td contenteditable="true"><?php echo esc_html($curso['precio']); ?></td>
                <td class="eliminar-celda"><button class="eliminar-curso" data-curso-id="<?php echo $key; ?>">Eliminar</button></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <button id="guardar-cambios">Actualizar</button>
    <?php
    // Elimina la ventana emergente cada vez que se actualizan los datos
	echo '<div id="mensaje-actualizacion" style=""></div>';
}

/* 
 * Función que llama al código Javascript que detecta cuando el contenido de la tabla se edita y envía los cambios al servidor
 */
function datos_cursos_admin_scripts() {
    wp_enqueue_script('datos-cursos-admin', plugins_url('admin.js', __FILE__), array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'datos_cursos_admin_scripts');

/*
 * Agregar la función de devolución de llamada AJAX
 */
function guardar_cambios_cursos_callback() {

    check_ajax_referer('datos-cursos-security-nonce', 'security');

    if (current_user_can('manage_options')) {
        $cursos = json_decode(stripslashes(json_encode($_POST['cursos'])), true);
        update_option('arr_cursos', $cursos);
        wp_send_json_success('Cambios guardados correctamente.');
    } else {
        wp_send_json_error('No tienes permiso para realizar esta acción.');
    }

    wp_die();
    
}
add_action('wp_ajax_guardar_cambios_cursos', 'guardar_cambios_cursos_callback');


/* 
 * Función para cargar el archivo CSS en la página de administración del plugin
 */
function datos_cursos_admin_styles($hook) {

    if ($hook != 'toplevel_page_datos-cursos' && $hook != 'toplevel_page_datos-cursos_page_datos-cursos-anadir') {
        return;
    }
    wp_enqueue_style('datos-cursos-admin', plugins_url('admin.css', __FILE__));
    
}
add_action('admin_enqueue_scripts', 'datos_cursos_admin_styles', 100);

/* 
 * Pasar variables al archivo JavaScript
 */
function datos_cursos_localize_script($hook) {

    if ($hook != 'toplevel_page_datos-cursos') {
        return;
    }

    $ajax_obj = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('datos-cursos-security-nonce'),
    );

    wp_localize_script('datos-cursos-admin', 'datos_cursos_ajax_obj', $ajax_obj);
    wp_enqueue_script('datos-cursos-admin');
    
}
add_action('admin_enqueue_scripts', 'datos_cursos_localize_script', 10, 1);

/* 
 * Función para procesar el shortcode 
 */
function datos_cursos_shortcode($atts) {

    // Atributos del shortcode
    $atts = shortcode_atts(
        array(
            'curso' => 1,
            'campo' => 'fecha',
        ),
        $atts,
        'datos_cursos'
    );

    // Obtener el array de cursos
    $arr_cursos = get_option('arr_cursos', array());

    // Verificar si el curso existe en el array
    if (isset($arr_cursos[$atts['curso'] - 1])) {
        $curso = $arr_cursos[$atts['curso'] - 1];

        // Verificar si el campo solicitado existe en el curso
        if (isset($curso[$atts['campo']])) {
            return esc_html($curso[$atts['campo']]);
        }
    }

    // Devuelve un mensaje de error si el curso o campo no existe
    return "Curso o campo no encontrado";
    
}
add_shortcode('datos_cursos', 'datos_cursos_shortcode');

/* 
 * Función que es llamada al clickar en eliminar curso
 */
function eliminar_curso_callback() {

	//Para comprobar que la función es llamada (depurar)
	error_log("Función eliminar_curso_callback ejecutada");

    check_ajax_referer('datos-cursos-security-nonce', 'security');

    if (current_user_can('manage_options')) {
        $curso_id = intval($_POST['curso_id']);
        $arr_cursos = get_option('arr_cursos');
        
        if (isset($arr_cursos[$curso_id])) {
            unset($arr_cursos[$curso_id]);
            $arr_cursos = array_values($arr_cursos);
            update_option('arr_cursos', $arr_cursos);
            wp_send_json_success('Curso eliminado correctamente.');
        } else {
            wp_send_json_error('Curso no encontrado.');
        }
    } else {
        wp_send_json_error('No tienes permiso para realizar esta acción.');
    }

    wp_die();
    
}
add_action('wp_ajax_eliminar_curso', 'eliminar_curso_callback');