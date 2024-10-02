<?php
// Función para agregar el botón de WhatsApp en productos
function agregar_boton_whatsapp_en_producto() {
    global $product;

    // Obtener el nombre y enlace del producto
    $product_name = $product->get_name();
    $product_url = get_permalink($product->get_id());

    // Obtener el precio sin HTML
    if ($product->is_on_sale()) {
        // Si el producto está en oferta, obtener el precio de oferta
        $product_price = $product->get_sale_price();
    } else {
        // Si no está en oferta, obtener el precio regular
        $product_price = $product->get_regular_price();
    }

    // Obtener el símbolo de la moneda
    $currency_symbol = get_woocommerce_currency_symbol(); // Esto obtiene el símbolo de la moneda

    // Formatear el precio como número, asegurándonos de que no tenga HTML
    $formatted_price = number_format((float)$product_price, 2, '.', ''); // Solo el precio numérico

    // Combinar el símbolo de moneda con el precio formateado
    $final_price = $currency_symbol . ' ' . $formatted_price; // Ejemplo: "$ 199.00"

    // Obtener el número de WhatsApp desde los ajustes
    $numero_whatsapp = get_option('boton_whatsapp_numero', '50662607939');

    // Si el número de WhatsApp no está configurado, mostrar un mensaje en lugar del botón
    if (empty($numero_whatsapp)) {
        echo '<p><strong>Por favor, configure un número de teléfono de WhatsApp en los ajustes del plugin para mostrar el botón.</strong></p>';
        return;
    }

    // Mensaje predefinido, ahora incluye el precio del producto sin HTML
    $mensaje = 'Hola, estoy interesado en el producto "' . $product_name . '" que aparece en ' . $product_url . ' con un precio de ' . $final_price;

    // Obtener el texto personalizado del botón (con valor por defecto)
    $texto_boton = get_option('boton_whatsapp_texto', 'Solicitar info por WhatsApp');

    // URL de WhatsApp con el mensaje
    $whatsapp_url = 'https://wa.me/' . $numero_whatsapp . '?text=' . urlencode($mensaje);

    // HTML del botón con el texto personalizado
    echo '
    <a href="' . esc_url($whatsapp_url) . '" target="_blank" class="boton-whatsapp">' . esc_html($texto_boton) . '</a>';
}

// Hook para mostrar el botón después del botón "Añadir al carrito"
add_action('woocommerce_after_add_to_cart_button', 'agregar_boton_whatsapp_en_producto');

