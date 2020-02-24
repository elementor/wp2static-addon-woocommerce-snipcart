<?php

namespace WP2StaticWooCommerceSnipcart;

class Controller {
    const WP2STATIC_WOOCOMMERCE_SNIPCART_VERSION = '0.1';

    public function run() : void {
        // initialize options DB
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_addon_woocommerce_snipcart_options';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            value VARCHAR(255) NOT NULL,
            label VARCHAR(255) NULL,
            description VARCHAR(255) NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // check for seed data
        // if deployment_url option doesn't exist, create:
        $options = $this->getOptions();

        if ( ! isset( $options['publicAPIKey'] ) ) {
            $this->seedOptions();
        }

        add_filter(
            'wp2static_add_menu_items',
            [ 'WP2StaticWooCommerceSnipcart\Controller', 'addSubmenuPage' ]
        );

        add_action(
            'admin_post_wp2static_woocommerce_snipcart_save_options',
            [ $this, 'saveOptionsFromUI' ],
            15,
            1
        );

        add_action(
            'wp2static_process_html_complete',
            [ $this, 'wooToSnipcart' ],
            15,
            1
        );

        // if ( defined( 'WP_CLI' ) ) {
        // \WP_CLI::add_command(
        // 'wp2static snipcart',
        // [ 'WP2StaticWooCommerceSnipcart\CLI', 'snipcart' ]);
        // }
    }

    /**
     *  Get all add-on options
     *
     *  @return mixed[] All options
     */
    public static function getOptions() : array {
        global $wpdb;
        $options = [];

        $table_name = $wpdb->prefix . 'wp2static_addon_woocommerce_snipcart_options';

        $rows = $wpdb->get_results( "SELECT * FROM $table_name" );

        foreach ( $rows as $row ) {
            $options[ $row->name ] = $row;
        }

        return $options;
    }

    /**
     * Seed options
     */
    public static function seedOptions() : void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_addon_woocommerce_snipcart_options';

        $query_string =
            "INSERT INTO $table_name (name, value, label, description)
            VALUES (%s, %s, %s, %s);";

        $query = $wpdb->prepare(
            $query_string,
            'publicAPIKey',
            '',
            'Snipcart public API key',
            ''
        );

        $wpdb->query( $query );
    }

    /**
     * Save options
     *
     * @param mixed $value value to save
     */
    public static function saveOption( string $name, $value ) : void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_addon_woocommerce_snipcart_options';

        $query_string = "INSERT INTO $table_name (name, value) VALUES (%s, %s);";
        $query = $wpdb->prepare( $query_string, $name, $value );

        $wpdb->query( $query );
    }

    public static function renderWooCommerceSnipcartPage() : void {
        $view = [];
        $view['nonce_action'] = 'wp2static-snipcart';
        $view['uploads_path'] = \WP2Static\SiteInfo::getPath( 'uploads' );
        $view['options'] = self::getOptions();

        require_once __DIR__ . '/../views/woocommerce-snipcart-page.php';
    }

    /*
     * Naive encypting/decrypting
     *
     * @throws WP2StaticException
     */
    public static function encrypt_decrypt( string $action, string $string ) : string {
        $encrypt_method = 'AES-256-CBC';

        $secret_key =
            defined( 'AUTH_KEY' ) ?
            constant( 'AUTH_KEY' ) :
            'LC>_cVZv34+W.P&_8d|ejfr]d31h)J?z5n(LB6iY=;P@?5/qzJSyB3qctr,.D$[L';

        $secret_iv =
            defined( 'AUTH_SALT' ) ?
            constant( 'AUTH_SALT' ) :
            'ec64SSHB{8|AA_ThIIlm:PD(Z!qga!/Dwll 4|i.?UkC§NNO}z?{Qr/q.KpH55K9';

        $key = hash( 'sha256', $secret_key );
        $variate = substr( hash( 'sha256', $secret_iv ), 0, 16 );

        if ( $action == 'decrypt' ) {
            return (string) openssl_decrypt(
                (string) base64_decode( $string ),
                $encrypt_method,
                $key,
                0,
                $variate
            );
        }

        $output = openssl_encrypt( $string, $encrypt_method, $key, 0, $variate );

        return (string) base64_encode( (string) $output );
    }

    public static function activate_for_single_site() : void {
        error_log( 'activating WP2Static WooCommerce Snipcart Add-on' );
    }

    public static function deactivate_for_single_site() : void {
        error_log( 'deactivating WP2Static WooCommerce Snipcart Add-on, maintaining options' );
    }

    public static function deactivate( bool $network_wide = null ) : void {
        error_log( 'deactivating WP2Static WooCommerce Snipcart Add-on' );
        if ( $network_wide ) {
            global $wpdb;

            $query = 'SELECT blog_id FROM %s WHERE site_id = %d;';

            $site_ids = $wpdb->get_col(
                sprintf(
                    $query,
                    $wpdb->blogs,
                    $wpdb->siteid
                )
            );

            foreach ( $site_ids as $site_id ) {
                switch_to_blog( $site_id );
                self::deactivate_for_single_site();
            }

            restore_current_blog();
        } else {
            self::deactivate_for_single_site();
        }
    }

    public static function activate( bool $network_wide = null ) : void {
        error_log( 'activating woocommerce snipcart  addon' );
        if ( $network_wide ) {
            global $wpdb;

            $query = 'SELECT blog_id FROM %s WHERE site_id = %d;';

            $site_ids = $wpdb->get_col(
                sprintf(
                    $query,
                    $wpdb->blogs,
                    $wpdb->siteid
                )
            );

            foreach ( $site_ids as $site_id ) {
                switch_to_blog( $site_id );
                self::activate_for_single_site();
            }

            restore_current_blog();
        } else {
            self::activate_for_single_site();
        }
    }

    /**
     * Add submenu to WP2Static sidebar menu
     *
     * @param mixed[] $submenu_pages list of menu items
     * @return mixed[] list of menu items
     */
    public static function addSubmenuPage( array $submenu_pages ) : array {
        $submenu_pages['snipcart'] = [ 'WP2StaticWooCommerceSnipcart\Controller', 'renderWooCommerceSnipcartPage' ];

        return $submenu_pages;
    }

    public static function saveOptionsFromUI() : void {
        check_admin_referer( 'wp2static-snipcart' );

        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_addon_woocommerce_snipcart_options';

        $personal_api_key =
            $_POST['publicAPIKey'] ?
            self::encrypt_decrypt(
                'encrypt',
                sanitize_text_field( $_POST['publicAPIKey'] )
            ) : '';

        $wpdb->update(
            $table_name,
            [ 'value' => $personal_api_key ],
            [ 'name' => 'publicAPIKey' ]
        );

        wp_safe_redirect( admin_url( 'admin.php?page=wp2static-snipcart' ) );
        exit;
    }

    /**
     * Get option value
     *
     * @return string option value
     */
    public static function getValue( string $name ) : string {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_addon_woocommerce_snipcart_options';

        $sql = $wpdb->prepare(
            "SELECT value FROM $table_name WHERE" . ' name = %s LIMIT 1',
            $name
        );

        $option_value = $wpdb->get_var( $sql );

        if ( ! is_string( $option_value ) ) {
            return '';
        }

        return $option_value;
    }

    public static function wooToSnipcart( $filename ) : void {
        \WP2Static\WsLog::l( 'Starting WooCommerce to Snipcart conversion.' );

        $woocommerce_snipcart_processor = new Processor();

        \WP2Static\WsLog::l( 'WooCommerce to Snipcart conversion complete.' );
    }
}

