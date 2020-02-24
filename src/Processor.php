<?php

namespace WP2StaticWooCommerceSnipcart;

/**
 * WooCommerce Snipcart Converter
 *
 */
class Processor {

    // process HTML file to use Snipcart markup instead of Woo
    public function woo_to_snipcart( string $html_file ) : void {
        $raw_html = file_get_contents( $html_file );

        // check if file has Woo checkout links
        if ( strpos( $raw_html, 'add_to_cart_button' ) !== false) {
            error_log( "Detected WooCommerce checkout button in: $html_file" );
        }

        // open file as DOMDocument

        // modify Woo markup to support Snipcart
    }
}

