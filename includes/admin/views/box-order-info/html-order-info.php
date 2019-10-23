<?php
/**
 * Infos view : WooCommerce order.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$locale = new Lengow_Translation();
?>

<style>
    <?php include WP_PLUGIN_DIR . '/lengow-woocommerce/assets/css/lengow-box-order.css'  ?>
</style>
<div id="lgw-box-order-info">
    <ul>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.marketplace_sku' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->marketplace_sku; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.marketplace' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->marketplace_name; ?><span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.currency' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->currency; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.total' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->total_paid; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.commission' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->commission; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.address_id' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->delivery_address_id; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.customer_name' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->customer_name; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.customer_email' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->customer_email; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.message' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->message; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.imported_date' ); ?></span>
            <span class="lgw-order-label"><?php echo get_date_from_gmt($order_lengow->created_at); ?></span>
        </li>
    </ul>
    <ul>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.carrier' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->carrier; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.carrier_method' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->carrier_method; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.relay_id' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->carrier_id_relay; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.tracking_number' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->carrier_tracking; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.shipped_by_marketplace' ); ?></span>
            <span class="lgw-order-label"><?php
			    if ( $order_lengow->sent_marketplace ) {
				    echo $locale->t( 'meta_box.order_info.boolean_yes' );
			    } else {
				    echo $locale->t( 'meta_box.order_info.boolean_no' );
			    }
			    ?>
            </span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"
                  id="lgw-order-title-json"><?php echo $locale->t( 'meta_box.order_info.json_format' ); ?></span><br>
            <textarea readonly><?php echo $order_lengow->extra; ?></textarea>
        </li>
    </ul>
</div>