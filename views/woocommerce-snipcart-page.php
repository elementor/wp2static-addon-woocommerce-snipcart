<br>

<form
    name="wp2static-woocommerce-snipcart-save-options"
    method="POST"
    action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">

    <?php wp_nonce_field( $view['nonce_action'] ); ?>
    <input name="action" type="hidden" value="wp2static_woocommerce_snipcart_save_options" />

<table class="widefat striped">
    <tbody>

        <tr>
            <td style="width:50%;">
                <label
                    for="<?php echo $view['options']['publicAPIKey']->name; ?>"
                ><?php echo $view['options']['publicAPIKey']->label; ?></label>
            </td>
            <td>
                <input
                    id="<?php echo $view['options']['publicAPIKey']->name; ?>"
                    name="<?php echo $view['options']['publicAPIKey']->name; ?>"
                    type="password"
                    value="<?php echo $view['options']['publicAPIKey']->value !== '' ?
                        \WP2StaticWooCommerceSnipcart\Controller::encrypt_decrypt('decrypt', $view['options']['publicAPIKey']->value) :
                        ''; ?>"
                />
            </td>
        </tr>

    </tbody>
</table>

<br>

    <button class="button btn-primary">Save WooCommerce Snipcart Options</button>
</form>

