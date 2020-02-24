<?php

namespace WP2StaticWooCommerceSnipcart;

/**
 * WooCommerce Snipcart Converter
 *
 */
class Processor {

    // process HTML file to use Snipcart markup instead of Woo
    public function woo_to_snipcart( string $html_file ) : void {
        error_log( "processing HTML file to Snipcart markup" );

        // check if file has Woo checkout links

        // open file as DOMDocument

        // modify Woo markup to support Snipcart
    }
}

