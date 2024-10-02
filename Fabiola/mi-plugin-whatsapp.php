<?php

/*
Plugin Name: Botón de WhatsApp en Productos
Description: Agrega un botón de consulta de WhatsApp en cada producto de WooCommerce con opciones configurables.
Version: 1.1
Author: Tu Nombre
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Salir si se accede directamente.
}

// 1. Encolar el archivo CSS
function boton_whatsapp_enqueue_styles() {
    wp_enqueue_style( 'boton-whatsapp-estilos', plugin_dir_url( __FILE__ ) . 'css/estilo-whatsapp.css' );
}
add_action( 'wp_enqueue_scripts', 'boton_whatsapp_enqueue_styles' );

// 2. Agregar página de ajustes del plugin
function boton_whatsapp_menu() {
    add_options_page(
        'Ajustes del Botón de WhatsApp',
        'Botón de WhatsApp',
        'manage_options',
        'boton-whatsapp',
        'boton_whatsapp_settings_page'
    );
}
add_action('admin_menu', 'boton_whatsapp_menu');

// 3. Crear el formulario de ajustes
function boton_whatsapp_settings_page() {
    ?>
    <div class="wrap">
        <h1>Ajustes del Botón de WhatsApp</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('boton_whatsapp_options_group');
            do_settings_sections('boton-whatsapp');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Número de WhatsApp</th>
                    <td><input type="text" name="boton_whatsapp_numero" value="<?php echo esc_attr(get_option('boton_whatsapp_numero')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Texto del Botón</th>
                    <td><input type="text" name="boton_whatsapp_texto" value="<?php echo esc_attr(get_option('boton_whatsapp_texto', 'Solicitar info por WhatsApp')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Posición del Botón en la Página de Producto</th>
                    <td>
                        <select name="boton_whatsapp_posicion">
                            <option value="woocommerce_after_add_to_cart_button" <?php selected(get_option('boton_whatsapp_posicion'), 'woocommerce_after_add_to_cart_button'); ?>>Después del botón "Añadir al carrito"</option>
                            <option value="woocommerce_before_add_to_cart_button" <?php selected(get_option('boton_whatsapp_posicion'), 'woocommerce_before_add_to_cart_button'); ?>>Antes del botón "Añadir al carrito"</option>
                            <option value="woocommerce_product_meta_end" <?php selected(get_option('boton_whatsapp_posicion'), 'woocommerce_product_meta_end'); ?>>Después de la sección meta del producto</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// 4. Registrar los ajustes
function boton_whatsapp_register_settings() {
    register_setting('boton_whatsapp_options_group', 'boton_whatsapp_numero');
    register_setting('boton_whatsapp_options_group', 'boton_whatsapp_texto');
    register_setting('boton_whatsapp_options_group', 'boton_whatsapp_posicion');
}
add_action('admin_init', 'boton_whatsapp_register_settings');

// 5. Función para agregar el botón de WhatsApp en productos
function agregar_boton_whatsapp_en_producto() {
    global $product;

    // Obtener el nombre y enlace del producto
    if (!$product) {
        return; // Evitar errores si no hay un producto en el contexto.
    }
    $product_name = $product->get_name();
    $product_url = get_permalink($product->get_id());

    // Obtener el precio sin HTML
    if ($product->is_on_sale()) {
        $product_price = $product->get_sale_price();
    } else {
        $product_price = $product->get_regular_price();
    }

    // Obtener el símbolo de la moneda
    $currency_symbol = get_woocommerce_currency_symbol();
    $final_price = $currency_symbol . ' ' . number_format((float)$product_price, 2, '.', '');

    // Obtener el número de WhatsApp desde los ajustes
    $numero_whatsapp = get_option('boton_whatsapp_numero', '');

    // Si el número de WhatsApp no está configurado, mostrar un mensaje
    if (empty($numero_whatsapp)) {
        echo '<p><strong>Por favor, configure un número de teléfono de WhatsApp en los ajustes del plugin para mostrar el botón.</strong></p>';
        return;
    }

    // Obtener el mensaje predefinido y el texto del botón
    $mensaje = 'Hola, estoy interesado en el producto "' . $product_name . '" que aparece en ' . $product_url . ' con un precio de ' . $final_price;
    $texto_boton = get_option('boton_whatsapp_texto', 'Solicitar info por WhatsApp');

    // URL de WhatsApp con el mensaje
    $whatsapp_url = 'https://wa.me/' . $numero_whatsapp . '?text=' . urlencode($mensaje);

   
    // HTML del botón con el estilo de botón redondeado
echo '
<a href="' . esc_url($whatsapp_url) . '" target="_blank" class="boton-whatsapp">' . esc_html($texto_boton) . '</a>';

    
}

// 6. Hook dinámico para insertar el botón en la página de producto según la posición seleccionada
function boton_whatsapp_agregar_hook() {
    $posicion = get_option('boton_whatsapp_posicion', 'woocommerce_after_add_to_cart_button');
    add_action($posicion, 'agregar_boton_whatsapp_en_producto');
}
add_action('wp', 'boton_whatsapp_agregar_hook');

?>



