<?php
/**
 * Plugin Name: WooCommerce Free Gift Coupons: Sync to Required Products
 * Plugin URI: http://www.woocommerce.com/products/free-gift-coupons/
 * Description: Sync the quantity of the Free Gift product to the quantity of a required purchased product
 * Version: 1.0.0.beta.1
 * Author: Kathy Darling
 * Author URI: http://kathyisawesome.com
 * Requires at least: 5.0
 * Tested up to: 5.3.0
 * WC requires at least: 4.0.0
 * WC tested up to: 4.2.0
 *
 * Text Domain: wc_fgc_sync
 * Domain Path: /languages/
 *
 * @package WooCommerce Free Gift Coupons
 * @category Core
 * @author Kathy Darling
 *
 * Copyright: © 2019 Kathy Darling.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */


/**
 * Change the price on the gift item to be zero
 * @access public
 * @param array $cart_item
 * @return array
 * @since 1.0
 */
function fgc_sync_add_cart_item( $cart_item ) {

	// Adjust quantity in cart if bonus item.
	if ( ! empty ( $cart_item['free_gift'] ) ){

		$coupon = new WC_Coupon( $cart_item['free_gift'] );

		if( $coupon instanceof WC_Coupon && $coupon->get_object_read() ) {

			$cart_contents = WC()->cart->get_cart_contents();
			$sync_to_products = $coupon->get_product_ids();
			$multiplication_factor = 1;

			foreach( $sync_to_products as $sync_to ) {
				foreach( $cart_contents as $per_cart_item ) {
					if( $sync_to == $per_cart_item['product_id'] ) {
						$multiplication_factor = $per_cart_item['quantity'];
						break;
					}
				}
			}

			// Stash the original quantity.
			if( ! isset( $cart_item['free_gift_original_qty'] ) ) {
				$cart_item['free_gift_original_qty'] = $cart_item['quantity'];
			}

			$cart_item['quantity'] = $cart_item['free_gift_original_qty'] * $multiplication_factor;

		}

		
	}
		
	return $cart_item;
}
add_filter( 'woocommerce_add_cart_item', 'fgc_sync_add_cart_item', 20 );

/**
 * Adjust session values on the gift item
 * @access public
 * @param array $cart_item
 * @param array $values
 * @return array
 * @since 1.0
 */
function fgc_sync_get_cart_item_from_session( $cart_item, $values ) {

	if ( ! empty( $values['free_gift_original_qty'] ) ) {
		$cart_item['free_gift_original_qty'] = $values['free_gift_original_qty'];
		$cart_item = fgc_sync_add_cart_item( $cart_item );
	}

	return $cart_item;

}
add_filter( 'woocommerce_get_cart_item_from_session', 'fgc_sync_get_cart_item_from_session', 20, 2 );
