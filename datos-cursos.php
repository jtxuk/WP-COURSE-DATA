<?php
/*
Plugin Name: Datos Cursos
Plugin URI: https://github.com/jtxuk/WP-COURSE-DATA
Description: Un plugin para agregar y gestionar los datos de todos los cursos de forma sencilla.
Author: Josep TXK
Version: 1.1
*/


// Incluye la librería de actualización desde GitHub
require 'plugin-update-checker/plugin-update-checker.php';

$actualizador = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/jtxuk/WP-COURSE-DATA/', // URL de tu repo
    __FILE__, // Archivo principal
    'datos-cursos' // Slug único
);

/**
 * Añade las páginas del menú de administración.
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

/**
 * Carga los estilos solo en las páginas del plugin.
 */
function datos_cursos_admin_styles($hook) {
    if ($hook === 'toplevel_page_datos-cursos' || $hook === 'datos-cursos_page_datos-cursos-anadir') {
        wp_enqueue_style('datos-cursos-admin', plugins_url('admin.css', __FILE__));
    }
}
add_action('admin_enqueue_scripts', 'datos_cursos_admin_styles');

/**
 * Carga el script JS solo en la página principal del plugin y pasa variables PHP.
 */
function datos_cursos_admin_scripts($hook) {
    if ($hook !== 'toplevel_page_datos-cursos') return;

    wp_enqueue_script('datos-cursos-admin', plugins_url('admin.js', __FILE__), array('jquery'), '1.1', true);
    wp_localize_script('datos-cursos-admin', 'datos_cursos_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('datos-cursos-security-nonce'),
    ));
}
add_action('admin_enqueue_scripts', 'datos_cursos_admin_scripts');

/**
 * Formulario para añadir cursos y guardar los datos.
 */
function datos_cursos_anadir() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['datos_cursos_form_nonce']) && wp_verify_nonce($_POST['datos_cursos_form_nonce'], 'datos_cursos_form')) {
        $curso = array(
            'nombre' => sanitize_text_field($_POST['nombre']),
            'fecha' => sanitize_text_field($_POST['fecha']),
            'horario' => sanitize_text_field($_POST['horario']),
            'precio' => sanitize_text_field($_POST['precio'])
        );
        // Validación básica
        if ($curso['nombre'] && $curso['fecha'] && $curso['horario'] && $curso['precio']) {
            $arr_cursos = get_option('arr_cursos', array());
            $arr_cursos[] = $curso;
            update_option('arr_cursos', $arr_cursos);
            echo '<div class="updated notice"><p>Curso añadido correctamente.</p></div>';
        } else {
            echo '<div class="error notice"><p>Todos los campos son obligatorios.</p></div>';
        }
    }
    ?>
    <div class="formulario-anadir-curso">
        <h1>Formulario Para Añadir Curso</h1>
        <form method="post">
            <?php wp_nonce_field('datos_cursos_form', 'datos_cursos_form_nonce'); ?>
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" required><br>
            <label for="fecha">Fecha:</label>
            <input type="text" name="fecha" required><br>
            <label for="horario">Horario:</label>
            <input type="text" name="horario" required><br>
            <label for="precio">Precio:</label>
            <input type="text" name="precio" required><br>
            <input type="submit" value="Añadir Curso">
        </form>
    </div>
    <?php
}

/**
 * Listado de cursos en la página principal.
 */
function datos_cursos_pagina_principal() {
    $arr_cursos = get_option('arr_cursos', array());
    ?>
    <h1>Listado de Cursos</h1>
    <p>Este plugin sirve para manejar de forma fácil y sencilla los datos de todos los cursos.</p>
    <h4>Forma de utilizarlo:</h4>
    <p>Para mostrar la fecha del curso en la posición 1: <b>[datos_cursos curso=1 campo=fecha]</b></p>
    <table class="widefat">
        <thead>
            <tr>
                <th>Número</th>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Horario</th>
                <th>Precio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($arr_cursos as $key => $curso): ?>
            <tr>
                <td><?php echo $key + 1; ?></td>
                <td contenteditable="true"><?php echo esc_html($curso['nombre']); ?></td>
                <td contenteditable="true"><?php echo esc_html($curso['fecha']); ?></td>
                <td contenteditable="true"><?php echo esc_html($curso['horario']); ?></td>
                <td contenteditable="true"><?php echo esc_html($curso['precio']); ?></td>
                <td><button class="eliminar-curso button" data-curso-id="<?php echo $key; ?>">Eliminar</button></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <button id="guardar-cambios" class="button button-primary">Actualizar</button>
    <div id="mensaje-actualizacion"></div>
    <?php
}

/**
 * AJAX: Guardar cambios en los cursos (edición en línea).
 */
function guardar_cambios_cursos_callback() {
    check_ajax_referer('datos-cursos-security-nonce', 'security');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('No tienes permiso para realizar esta acción.');
    }
    $cursos = isset($_POST['cursos']) ? $_POST['cursos'] : array();
    if (is_array($cursos)) {
        // Sanitizar cada curso
        $cursos_sanitizados = array();
        foreach ($cursos as $curso) {
            $cursos_sanitizados[] = array(
                'nombre' => sanitize_text_field($curso['nombre']),
                'fecha' => sanitize_text_field($curso['fecha']),
                'horario' => sanitize_text_field($curso['horario']),
                'precio' => sanitize_text_field($curso['precio'])
            );
        }
        update_option('arr_cursos', $cursos_sanitizados);
        wp_send_json_success('Cambios guardados correctamente.');
    } else {
        wp_send_json_error('Datos inválidos.');
    }
    wp_die();
}
add_action('wp_ajax_guardar_cambios_cursos', 'guardar_cambios_cursos_callback');

/**
 * AJAX: Eliminar curso.
 */
function eliminar_curso_callback() {
    check_ajax_referer('datos-cursos-security-nonce', 'security');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('No tienes permiso para realizar esta acción.');
    }
    $curso_id = isset($_POST['curso_id']) ? intval($_POST['curso_id']) : -1;
    $arr_cursos = get_option('arr_cursos', array());
    if ($curso_id >= 0 && isset($arr_cursos[$curso_id])) {
        unset($arr_cursos[$curso_id]);
        $arr_cursos = array_values($arr_cursos); // Reindexar
        update_option('arr_cursos', $arr_cursos);
        wp_send_json_success('Curso eliminado correctamente.');
    } else {
        wp_send_json_error('Curso no encontrado.');
    }
    wp_die();
}
add_action('wp_ajax_eliminar_curso', 'eliminar_curso_callback');

/**
 * Shortcode para mostrar un campo de un curso específico.
 * Uso: [datos_cursos curso=1 campo=fecha]
 */
function datos_cursos_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'curso' => 1,
            'campo' => 'fecha',
        ),
        $atts,
        'datos_cursos'
    );
    $arr_cursos = get_option('arr_cursos', array());
    $curso_index = intval($atts['curso']) - 1;
    $campo = sanitize_text_field($atts['campo']);
    if (isset($arr_cursos[$curso_index]) && isset($arr_cursos[$curso_index][$campo])) {
        return esc_html($arr_cursos[$curso_index][$campo]);
    }
    return "Curso o campo no encontrado";
}
add_shortcode('datos_cursos', 'datos_cursos_shortcode');
