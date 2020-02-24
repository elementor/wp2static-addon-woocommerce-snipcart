<?php

/**
 * Plugin Name:       WP2Static Add-on: WooCommerce Snipcart
 * Plugin URI:        https://wp2static.com
 * Description:       WooCommerce Snipcart deployment add-on for WP2Static.
 * Version:           0.1
 * Author:            Leon Stafford
 * Author URI:        https://ljs.dev
 * License:           Unlicense
 * License URI:       http://unlicense.org
 * Text Domain:       wp2static-addon-woocommerce-snipcart
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'WP2STATIC_WOOCOMMERCE_SNIPCART_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP2STATIC_WOOCOMMERCE_SNIPCART_VERSION', '0.1' );

require WP2STATIC_WOOCOMMERCE_SNIPCART_PATH . 'vendor/autoload.php';

function run_wp2static_addon_woocommerce_snipcart() {
    $controller = new WP2StaticWooCommerceSnipcart\Controller();
    $controller->run();
}

register_activation_hook(
    __FILE__,
    [ 'WP2StaticWooCommerceSnipcart\Controller', 'activate' ]
);

register_deactivation_hook(
    __FILE__,
    [ 'WP2StaticWooCommerceSnipcart\Controller', 'deactivate' ]
);

run_wp2static_addon_woocommerce_snipcart();

