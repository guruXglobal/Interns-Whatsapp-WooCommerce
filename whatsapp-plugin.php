<?php
/**
 * Plugin Name: WhatsApp Button for WooCommerce
 * Description: Agrega un botón de WhatsApp en las páginas de productos de WooCommerce.
 * Version: 1.2
 * Author: Steven
 */

// Agregar botón de WhatsApp en las páginas de productos
function boton_whatsapp_producto() {
    global $product;

    // Obtener el número de WhatsApp desde la configuración
    $numero_whatsapp = get_option('numero_whatsapp', '');
    if (empty($numero_whatsapp)) {
        echo '<p style="color: red;">Por favor, configure el número de WhatsApp en los ajustes del plugin.</p>';
        return;
    }

    // Texto por defecto que puede ser cambiado
    $texto_boton = get_option('texto_boton_whatsapp', 'Solicitar info por WhatsApp');

    // Crear mensaje con placeholders
    $nombre_producto = $product->get_name();
    $nombre_sitio = get_bloginfo('name');
    $precio_producto = $product->get_price();

    // Formatear el precio como texto
    $precio_texto = '$' . number_format((float)$precio_producto, 2, '.', '');

    $mensaje = sprintf(
        'Hola, me interesa el producto: %s que vi en: %s y que cuesta: %s.',
        $nombre_producto,
        $nombre_sitio,
        $precio_texto
    );

    $url_whatsapp = 'https://wa.me/' . $numero_whatsapp . '?text=' . urlencode($mensaje);
    $icono_whatsapp = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
        <path d="M13.6 2.6A8 8 0 0 0 8.002.002h-.004C3.588.002.004 3.588.004 7.998a7.95 7.95 0 0 0 1.13 4.077l-1.2 4.27 4.39-1.149a7.942 7.942 0 0 0 4.683 1.372h.004c4.41 0 7.996-3.587 7.996-7.997a7.952 7.952 0 0 0-2.406-5.774ZM8.003 14.8c-1.6 0-3.1-.485-4.37-1.37l-3.076.806.84-2.985A6.93 6.93 0 0 1 .994 7.998C.994 4.14 4.146 1 8 1a6.975 6.975 0 0 1 6.998 6.998c0 3.854-3.151 6.996-6.995 6.996Zm3.97-5.277c-.21-.104-1.24-.612-1.432-.683-.194-.07-.334-.104-.475.106-.14.211-.545.683-.668.822-.123.14-.246.157-.456.053-.21-.104-.883-.325-1.68-.983-.621-.508-1.038-1.135-1.16-1.346-.123-.21-.013-.324.091-.428.094-.093.21-.246.315-.368.104-.123.14-.211.21-.35.07-.14.035-.263-.017-.368-.053-.105-.475-1.145-.65-1.562-.17-.413-.343-.36-.475-.36-.123-.004-.263-.004-.403-.004-.14 0-.368.053-.562.263-.194.211-.735.716-.735 1.743 0 1.026.752 2.02.856 2.162.105.14 1.481 2.265 3.596 3.044.503.21.893.335 1.2.429.503.16.961.137 1.32.082.402-.06 1.24-.505 1.415-.992.175-.487.175-.904.123-.992-.052-.087-.192-.14-.402-.246Z"/>
    </svg>';

    echo '<a href="' . esc_url($url_whatsapp) . '" target="_blank" class="boton-whatsapp">' . esc_html($texto_boton) . ' ' . $icono_whatsapp . '</a>';
}

// Función para mostrar el botón en la posición seleccionada
function mostrar_boton_whatsapp() {
    if (is_product()) {
        // Remover el botón de cualquier acción previa
        remove_action('woocommerce_single_product_summary', 'boton_whatsapp_producto');
        remove_action('woocommerce_before_add_to_cart_form', 'boton_whatsapp_producto');

        // Añadir el botón en la posición seleccionada
        $posicion = get_option('posicion_boton_whatsapp', 'woocommerce_single_product_summary');
        add_action($posicion, 'boton_whatsapp_producto', 35);
    }
}

add_action('wp', 'mostrar_boton_whatsapp');

// Estilos CSS para el botón
function estilos_boton_whatsapp() {
    echo '
    <style>
    .boton-whatsapp {
        display: inline-flex;
        align-items: center;
        background-color: #25D366; /* Verde WhatsApp */
        color: white;
        padding: 8px 13px; /* Ajustar el padding para que no esté tan ancho */
        border-radius: 35px;
        font-size: 16px;
        font-weight: bold;
        text-decoration: none;
        margin-top: 5px; /* Subir un poco el botón */
        transition: background-color 0.3s ease, transform 0.3s ease;
        max-width: 250px; /* Limitar el ancho máximo del botón */
        overflow: hidden; /* Ocultar el desbordamiento */
        text-overflow: ellipsis; /* Agregar puntos suspensivos si el texto es demasiado largo */
        white-space: nowrap; /* No permitir saltos de línea */
    }

    .boton-whatsapp:hover {
        background-color: #1DA851;
        cursor: pointer;
        transform: scale(1.05);
    }

    .boton-whatsapp svg {
        margin-left: 8px; /* Espacio entre el texto y el icono */
        vertical-align: middle;
    }
    </style>
    ';
}

add_action('wp_head', 'estilos_boton_whatsapp');

// Añadir opciones de configuración en el admin
function configuracion_boton_whatsapp() {
    add_options_page('Configuración Botón WhatsApp', 'WhatsApp Button', 'manage_options', 'whatsapp-button', 'pagina_configuracion_boton_whatsapp');
}

add_action('admin_menu', 'configuracion_boton_whatsapp');

function pagina_configuracion_boton_whatsapp() {
    ?>
    <div class="wrap">
        <h1>Configuración del Botón de WhatsApp</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('whatsapp-button-settings');
            do_settings_sections('whatsapp-button-settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Número de WhatsApp</th>
                    <td><input type="text" name="numero_whatsapp" value="<?php echo esc_attr(get_option('numero_whatsapp', '')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Texto del Botón</th>
                    <td><input type="text" name="texto_boton_whatsapp" value="<?php echo esc_attr(get_option('texto_boton_whatsapp', 'Solicitar info por WhatsApp')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Posición del Botón</th>
                    <td>
                        <select name="posicion_boton_whatsapp">
                            <option value="woocommerce_single_product_summary" <?php selected('woocommerce_single_product_summary', get_option('posicion_boton_whatsapp', 'woocommerce_single_product_summary')); ?>>Después de agregar al carrito</option>
                            <option value="woocommerce_before_add_to_cart_button" <?php selected('woocommerce_before_add_to_cart_button', get_option('posicion_boton_whatsapp', 'woocommerce_single_product_summary')); ?>>Antes del botón de agregar</option>
                            <option value="woocommerce_after_single_product_summary" <?php selected('woocommerce_after_single_product_summary', get_option('posicion_boton_whatsapp', 'woocommerce_single_product_summary')); ?>>Después de la descripción del producto</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Guardar opciones de configuración
function guardar_opciones_boton_whatsapp() {
    register_setting('whatsapp-button-settings', 'numero_whatsapp');
    register_setting('whatsapp-button-settings', 'texto_boton_whatsapp');
    register_setting('whatsapp-button-settings', 'posicion_boton_whatsapp');
}

add_action('admin_init', 'guardar_opciones_boton_whatsapp');
