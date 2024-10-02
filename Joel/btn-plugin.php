<?php
/**
 * Plugin Name: Botón de WhatsApp Configurable para WooCommerce
 * Description: Agrega un botón de consulta vía WhatsApp en las páginas de productos de WooCommerce con opciones configurables.
 * Version: 1.2
 * Author: Joel Rodríguez Carvajal 
 */

// Asegura que WordPress no permita acceso directo al archivo del plugin
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Crear el menú de ajustes del plugin en el panel de administración
function whatsapp_bot_settings_menu() {
    add_menu_page(
        'WhatsApp Botón Configurable', // Título de la página
        'WhatsApp Config',             // Título del menú
        'manage_options',              // Capacidad requerida
        'whatsapp-boton-config',       // Slug del menú
        'whatsapp_bot_settings_page',  // Función que muestra el contenido de la página
        'dashicons-whatsapp',          // Icono del menú
        60                             // Posición en el menú
    );
}
add_action('admin_menu', 'whatsapp_bot_settings_menu');

// Página de ajustes del plugin
function whatsapp_bot_settings_page() {
    ?>
    <div class="wrap">
        <h1>Configuración del Botón de WhatsApp</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('whatsapp_bot_settings_group');
            do_settings_sections('whatsapp-boton-config');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Inicializar las configuraciones del plugin
function whatsapp_bot_settings_init() {
    register_setting('whatsapp_bot_settings_group', 'whatsapp_phone_number');
    register_setting('whatsapp_bot_settings_group', 'whatsapp_button_text');
    register_setting('whatsapp_bot_settings_group', 'whatsapp_whatsapp_message');
    register_setting('whatsapp_bot_settings_group', 'whatsapp_hook_position');
    
    add_settings_section(
        'whatsapp_bot_main_section',      // ID de la sección
        'Configuraciones del Botón de WhatsApp',  // Título
        'whatsapp_bot_main_section_cb',   // Función de descripción
        'whatsapp-boton-config'           // Página de ajustes
    );

    add_settings_field(
        'whatsapp_phone_number',          // ID del campo
        'Número de WhatsApp',             // Título del campo
        'whatsapp_phone_number_cb',       // Función de renderizado
        'whatsapp-boton-config',          // Página de ajustes
        'whatsapp_bot_main_section'       // ID de la sección
    );

    add_settings_field(
        'whatsapp_button_text',           // ID del campo
        'Texto del botón',                // Título del campo
        'whatsapp_button_text_cb',        // Función de renderizado
        'whatsapp-boton-config',          // Página de ajustes
        'whatsapp_bot_main_section'       // ID de la sección
    );

    add_settings_field(
        'whatsapp_whatsapp_message',      // ID del campo
        'Mensaje de WhatsApp',            // Título del campo
        'whatsapp_message_cb',            // Función de renderizado
        'whatsapp-boton-config',          // Página de ajustes
        'whatsapp_bot_main_section'       // ID de la sección
    );

    add_settings_field(
        'whatsapp_hook_position',         // ID del campo
        'Posición del botón en la página del producto',  // Título del campo
        'whatsapp_hook_position_cb',      // Función de renderizado
        'whatsapp-boton-config',          // Página de ajustes
        'whatsapp_bot_main_section'       // ID de la sección
    );
}
add_action('admin_init', 'whatsapp_bot_settings_init');

// Función de descripción de la sección
function whatsapp_bot_main_section_cb() {
    echo 'Ajusta las configuraciones del botón de WhatsApp para las páginas de productos de WooCommerce.';
}

// Renderizar el campo para el número de WhatsApp
function whatsapp_phone_number_cb() {
    $whatsapp_phone_number = get_option('whatsapp_phone_number', '');
    echo '<input type="text" name="whatsapp_phone_number" value="' . esc_attr($whatsapp_phone_number) . '" placeholder="Ingrese su número de WhatsApp">';
    if (empty($whatsapp_phone_number)) {
        echo '<p style="color:red;">Ingrese su número de WhatsApp para mostrar el botón.</p>';
    }
}

// Renderizar el campo para el texto del botón
function whatsapp_button_text_cb() {
    $whatsapp_button_text = get_option('whatsapp_button_text', 'Solicitar info por WhatsApp');
    echo '<input type="text" name="whatsapp_button_text" value="' . esc_attr($whatsapp_button_text) . '" placeholder="Texto del botón">';
}

// Renderizar el campo para el mensaje de WhatsApp
function whatsapp_message_cb() {
    $whatsapp_message = get_option('whatsapp_whatsapp_message', 'Estoy interesado en el producto {nombre producto} en {nombre sitio} con el valor de {precio producto}.');
    echo '<textarea name="whatsapp_whatsapp_message" rows="4" cols="50" placeholder="Mensaje de WhatsApp">' . esc_textarea($whatsapp_message) . '</textarea>';
    echo '<p>Use los siguientes placeholders: {nombre producto}, {nombre sitio}, {precio producto}</p>';
}

// Renderizar el campo de posición del botón
function whatsapp_hook_position_cb() {
    $whatsapp_hook_position = get_option('whatsapp_hook_position', 'woocommerce_single_product_summary');
    $positions = [
        'woocommerce_single_product_summary' => 'Antes del botón "Añadir al carrito"',
        'woocommerce_before_single_product_summary' => 'Antes del resumen del producto',
        'woocommerce_after_single_product_summary' => 'Después del resumen del producto'
    ];
    echo '<select name="whatsapp_hook_position">';
    foreach ($positions as $value => $label) {
        $selected = ($value === $whatsapp_hook_position) ? 'selected' : '';
        echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}

// Función para agregar el botón de WhatsApp en la página de productos
function agregar_boton_whatsapp_producto() {
    $whatsapp_phone_number = get_option('whatsapp_phone_number', '');
    $whatsapp_button_text = get_option('whatsapp_button_text', 'Solicitar info por WhatsApp');
    $whatsapp_message_template = get_option('whatsapp_whatsapp_message', 'Estoy interesado en el producto {nombre producto} en {nombre sitio} con el valor de {precio producto}.');
    
    if (empty($whatsapp_phone_number)) {
        echo '<p style="color:red;">Ingrese un número de WhatsApp en la configuración del plugin para mostrar el botón.</p>';
        return;
    }

    global $product;
    $nombre_producto = $product->get_name();
    
    // Obtener precio sin etiquetas HTML
    $precio_producto = html_entity_decode(strip_tags(wc_price($product->get_price())));
    
    $nombre_sitio = get_bloginfo('name');

    // Reemplazar placeholders en el mensaje de WhatsApp
    $whatsapp_message = str_replace(
        ['{nombre producto}', '{precio producto}', '{nombre sitio}'],
        [$nombre_producto, $precio_producto, $nombre_sitio],
        $whatsapp_message_template
    );

    // URL de WhatsApp con el mensaje dinámico
    $url_whatsapp = 'https://wa.me/' . $whatsapp_phone_number . '?text=' . urlencode($whatsapp_message);

    // HTML del botón
    echo '<a href="' . esc_url($url_whatsapp) . '" target="_blank" class="boton-whatsapp">
            ' . esc_html($whatsapp_button_text) . '
        </a>';
}

// Agregar el botón de WhatsApp en la posición seleccionada
add_action(get_option('whatsapp_hook_position', 'woocommerce_single_product_summary'), 'agregar_boton_whatsapp_producto', 35);

// Agregar estilos CSS para el botón
function estilos_boton_whatsapp() {
    echo '
    <style>
    .boton-whatsapp {
        display: inline-block;
        background-color: #25D366; /* Verde WhatsApp */
        color: white;
        padding: 10px 15px;
        font-size: 16px;
        font-weight: bold;
        border-radius: 5px;
        text-decoration: none;
        margin-top: 10px;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .boton-whatsapp:hover {
        background-color: #1DA851; /* Color al pasar el mouse */
        cursor: pointer;
        transform: scale(1.05); /* Pequeño zoom en hover */
    }
    </style>
    ';
}
add_action('wp_head', 'estilos_boton_whatsapp');