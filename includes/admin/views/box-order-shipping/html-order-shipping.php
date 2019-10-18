<?php
/**
 * Shipping view : WooCommerce order.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/** @var Lengow_Marketplace $marketplace */
$locale                = new Lengow_Translation();
$carriers              = $marketplace->carriers;
$marketplace_arguments = $marketplace->get_marketplace_arguments( 'ship' );
$accept_custom_carrier = $marketplace->accept_custom_carrier();
$carrier               = get_post_meta( $post->ID, '_lengow_carrier', true );
$carrier               = strlen( $carrier ) > 0 ? $carrier : $order_lengow->carrier;
$custom_carrier        = get_post_meta( $post->ID, '_lengow_custom_carrier', true );
$tracking_number       = get_post_meta( $post->ID, '_lengow_tracking_number', true );
$tracking_url          = get_post_meta( $post->ID, '_lengow_tracking_url', true );
?>

<style>
    <?php include WP_PLUGIN_DIR . '/lengow-woocommerce/assets/css/lengow-box-order.css'  ?>
</style>
<div id="lgw-box-order-shipping">
    <ul class="order_shipping submitbox">
		<?php if ( ! empty( $carriers ) ) : ?>
            <li>
                <label for="lengow_carrier">
                    <?php echo $locale->t( 'meta_box.order_shipping.carrier' ); ?>
                </label>
                <select name="lengow_carrier">
                    <option value="">
						<?php echo $locale->t( 'meta_box.order_shipping.choose_a_carrier' ); ?>
                    </option>
					<?php foreach ( $carriers as $code => $label ) : ?>
                        <option value="<?php echo $code; ?>" <?php echo $carrier === $code ? 'selected' : ''; ?>>
							<?php echo $label; ?>
                        </option>
					<?php endforeach; ?>
                </select>
            </li>
		<?php endif; ?>
	    <?php if ( $accept_custom_carrier ) : ?>
            <li>
                <label for="lengow_custom_carrier">
                    <?php echo $locale->t( 'meta_box.order_shipping.custom_carrier' ); ?>
                </label>
                <input type="text" name="lengow_custom_carrier" id="lengow_custom_carrier"
                       value="<?php echo $custom_carrier; ?>"/>
            </li>
	    <?php endif; ?>
	    <?php if ( array_key_exists('tracking_number', $marketplace_arguments ) ) : ?>
            <li>
                <label for="lengow_tracking_number">
                    <?php echo $locale->t( 'meta_box.order_shipping.tracking_number' ); ?>
                </label>
                <input type="text" name="lengow_tracking_number" id="lengow_tracking_number"
                       value="<?php echo $tracking_number; ?>"/>
            </li>
	    <?php endif; ?>
	    <?php if ( array_key_exists('tracking_url', $marketplace_arguments ) ) : ?>
            <li>
                <label for="lengow_tracking_url">
                    <?php echo $locale->t( 'meta_box.order_shipping.tracking_url' ); ?>
                </label>
                <input type="text" name="lengow_tracking_url" id="lengow_tracking_url"
                       value="<?php echo $tracking_url; ?>"/>
            </li>
	    <?php endif; ?>
    </ul>
</div>
