<?php
/**
 * Plugin Name: Lessform
 * Description: Autorrellena los campos de un formulario de Contact Form 7 con los datos enviados en la misma sesión.
 * Author: ablancodev
 * Author URI: https://ablancodev.com
 * Version: 1.0
 */

// on contact form 7 send data, we store a cookie with the data
function lessform_cf7_send_data( $cf7 ) {
    $submission = WPCF7_Submission::get_instance();
    $posted_data = $submission->get_posted_data();
    
    // Codificar los datos en JSON
    $json_data = json_encode($posted_data);
    
    // Verificar si hubo un error al codificar el JSON
    if (json_last_error() === JSON_ERROR_NONE) {
        // Establecer la cookie con los datos codificados en JSON
        setcookie('lessform_data', $json_data, time() + 3600, '/');
    } else {
        // Manejar el error de codificación JSON
        error_log('Error al codificar JSON: ' . json_last_error_msg());
    }
}
add_action( 'wpcf7_mail_sent', 'lessform_cf7_send_data' );

// cuando se visita una página se muestra la información del formulario guardada
function lessform_show_data() {
    if (isset($_COOKIE['lessform_data'])) {
        $data = $_COOKIE['lessform_data'];
        $data = json_decode(stripslashes($data), true);

        if (json_last_error() === JSON_ERROR_NONE) {

            // si existe un formulario de CF7, intentamos rellenar los campos que haya en la cookie
            foreach ($data as $key => $value) {
                // usando javascript tendremos que rellenar los campos
                echo '<script>';
                echo '
                if (document.querySelector(\'input[name="' . $key . '"]\')) {
                    document.querySelector(\'input[name="' . $key . '"]\').value = \'' . $value . '\';
                }
                if (document.querySelector(\'textarea[name="' . $key . '"]\')) {
                    document.querySelector(\'textarea[name="' . $key . '"]\').value = \'' . $value . '\';
                }
                if (document.querySelector(\'select[name="' . $key . '"]\')) {
                    document.querySelector(\'select[name="' . $key . '"]\').value = \'' . $value . '\';
                }
                ';
                echo '</script>';

            }

        }
    }
}
add_action( 'wp_footer', 'lessform_show_data' );