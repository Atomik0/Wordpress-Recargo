<?php
/**
 * Plugin Name: VKTRDev Recargo Método de Pago
 * Plugin URI: https://www.vktrdev.cl/
 * Description: Añade un recargo al total del pedido cuando se selecciona un método de pago.
 * Version: 1.0.1
 * Requires Plugins: woocommerce
 * Author: Victor Huerta
 * Author URI: https://www.vktrdev.cl/
 */

if(!defined('ABSPATH')) {
    exit;
}

function wfs_recargo_settings_menu() {
    add_menu_page(
        'VKTRDev Recargo Método de Pago',
        'VKTRDev Recargo',
        'manage_options',
        'wfs-recargo-settings',
        'wfs_recargo_settings_page',
        'dashicons-admin-generic',
        56
    );
}
add_action('admin_menu', 'wfs_recargo_settings_menu');

function wfs_recargo_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Configuración de Recargo por Método de Pago', 'woocommerce-flow-surcharge'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('wfs_recargo_settings_group');
			?>
			<h4 style="margin-top: -5px;font-weight: 300;">Ajusta el % a cobrar al seleccionar Flow/Transbank</h4>
			<?php
            do_settings_sections('wfs-recargo-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function wfs_recargo_settings_init() {
    register_setting('wfs_recargo_settings_group', 'wfs_recargo_percentage');
    register_setting('wfs_recargo_settings_group', 'wfs_recargo_enabled');

    add_settings_section(
        'wfs_recargo_settings_section',
        __('Ajustes de Recargo', 'woocommerce-flow-surcharge'),
        null,
        'wfs-recargo-settings'
    );

    add_settings_field(
        'wfs_recargo_enabled',
        __('Habilitar Recargo', 'woocommerce-flow-surcharge'),
        'wfs_recargo_enabled_render',
        'wfs-recargo-settings',
        'wfs_recargo_settings_section'
    );

    add_settings_field(
        'wfs_recargo_percentage',
        __('Porcentaje de Recargo (%)', 'woocommerce-flow-surcharge'),
        'wfs_recargo_percentage_render',
        'wfs-recargo-settings',
        'wfs_recargo_settings_section'
    );
}
add_action('admin_init', 'wfs_recargo_settings_init');

function wfs_recargo_percentage_render() {
    $percentage = get_option('wfs_recargo_percentage', 3.19);
    ?>
    <input type="number" step="0.01" name="wfs_recargo_percentage" value="<?php echo esc_attr($percentage); ?>" />
    <?php
}

function wfs_recargo_enabled_render() {
    $enabled = get_option('wfs_recargo_enabled', 'yes');
    ?>
    <input type="checkbox" name="wfs_recargo_enabled" value="yes" <?php checked($enabled, 'yes'); ?> />
    <?php
}

function wfs_agregar_recargo_por_flow($cart) {
    if(is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    $enabled = get_option('wfs_recargo_enabled', 'yes');
	
    if($enabled !== 'yes') {
        return;
    }

    $percentage = get_option('wfs_recargo_percentage', 3.19) / 100;

    if(isset(WC()->session->chosen_payment_method) && WC()->session->chosen_payment_method === 'transbank_webpay_plus_rest') {
        $recargo = $cart->cart_contents_total * $percentage;
        $cart->add_fee(__('Recargo por uso de Transbank', 'woocommerce-flow-surcharge'), $recargo, true, 'standard');
    }
}
add_action('woocommerce_cart_calculate_fees', 'wfs_agregar_recargo_por_flow');
