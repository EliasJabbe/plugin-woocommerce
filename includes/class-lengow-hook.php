<?php
/**
 * Lengow Hooks.
 *
 * Copyright 2019 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0
 *
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  includes
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2019 Lengow SAS
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Hook Class.
 */
class Lengow_Hook {

	/**
	 * Add Meta box for Lengow Order.
	 *
	 * @param WP_Post $post Wordpress Post instance
	 */
	public static function adding_shop_order_meta_boxes( $post ) {
		if ( Lengow_Order::get_id_from_order_id( (int) $post->ID ) ) {
			$locale = new Lengow_Translation();
			add_meta_box(
				'lengow-shipping-infos',
				$locale->t( 'order_infos.box_title' ),
				array( 'Lengow_Order_Info', 'display_lengow_order_infos_meta_box' )
			);
		}
	}


}
