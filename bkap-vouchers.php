<?php

/**
 * Plugin Name: Vouchers for Booking & Appointment Plugin for WooCommerce 
 * Plugin URI: https://www.tychesoftwares.com/
 * Description: This plugin allows to send the vouchers to recipient.
 * Version: 1.0
 * Author: Tyche Softwares
 * Author URI: https://tychesoftwares.com
 * Text Domain: bkap-vouchers
 * Requires PHP: 5.6
 * WC requires at least: 3.0.0
 * WC tested up to: 4.0
 *
 * @package BKAP-Voucher
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BKAP_Voucher' ) ) :

	/**
	 * Booking & Appointment Plugin Voucher Class
	 *
	 * @class BKAP_Voucher
	 */

	class BKAP_Voucher {
		/**
		 * Default constructor
		 *
		 * @since 1.0
		 */

		public function __construct() {

			add_action( 'admin_init', 							array( $this, 'bkapef_include_files' ) );
			add_action( 'init', 								array( $this, 'bkapef_include_files' ) );
			//Language Translation
			add_action( 'init', 								array( &$this, 'bkapef_update_po_file' ) );

			add_action( 'woocommerce_before_add_to_cart_form', 	array( $this, 'bkapef_on_woocommerce_before_add_to_cart_form' ) );

			add_filter( 'woocommerce_add_cart_item_data', 		array( $this, 'bkapef_add_item_data'), 25, 2 );
			add_filter( 'woocommerce_get_item_data', 			array( $this, 'bkapef_get_item_data' ), 25, 2 );

			//add_action( 'woocommerce_add_order_item_meta', array( $this, 'bkapef_add_order_item_meta' ) , 10, 2);

			// https://stackoverflow.com/questions/29666820/woocommerce-which-hook-to-replace-deprecated-woocommerce-add-order-item-meta
			add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'bkapef_custom_checkout_create_order_line_item' ), 20, 4 );

			//add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'bkapef_processing_notification' ), 10, 1 );

			//add_action('woocommerce_checkout_update_order_meta', array( $this, 'bkapef_custom_checkout_field_update_order_meta' ), 10, 2 );
			add_action( 'wp_enqueue_scripts', array( $this, 'after_bkap_load_product_scripts_js_callback' ), 10 );
			
			add_filter( 'woocommerce_hidden_order_itemmeta',        array( &$this, 'bkap_hidden_order_itemmeta'), 10, 1 );
		}

		/**
		 * Load plugin text domain and specify the location of localization po & mo files
		 * 
		 * @since 1.0
		 */

		public static function bkapef_update_po_file(){
			$domain = 'bkap-vouchers';

			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
			
			if ( $loaded = load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '-' . $locale . '.mo' ) ) {
				return $loaded;
			} else {
				load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
			}
		}


		public static function bkap_hidden_order_itemmeta( $arr ) {
			$arr[] = '_bkapef_mail';
			$arr[] = '_bkapef_name';
			$arr[] = '_bkapef_coupon_id';
			$arr[] = '_bkapef_coupon_code';

			return $arr;
		}

		/**
		 * Including files for the plugin.
		 */
		public static function bkapef_include_files() {
			include_once 'includes/bkapef-functions.php';
			include_once 'bkap-voucher-email-manager.php';
		}

		public static function bkapef_custom_checkout_field_update_order_meta( $order_id, $cart_item ) {
			global $wpdb;
			$order_item_ids = array();
			foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
				if ( isset( $values['bkapef_email'] ) ) {

					$_product  		= $values['data'];
					$parent_id 		= $_product->get_parent_id();
					$variation_id 	= ( isset( $values[ 'variation_id' ] ) ) ? $values[ 'variation_id' ] : 0;

					$post_id	= $values['product_id'];
					$quantity   = $values['quantity'];
					$post_title = $_product->get_name();

					// Fetch line item
					if ( count( $order_item_ids ) > 0 ) {
						$order_item_ids_to_exclude  = implode( ",", $order_item_ids );
						$sub_query                  = " AND order_item_id NOT IN (".$order_item_ids_to_exclude.")";
					}

					$query 				= "SELECT order_item_id,order_id FROM `".$wpdb->prefix."woocommerce_order_items`
											WHERE order_id = %s AND order_item_name LIKE %s".$sub_query;
					$results            = $wpdb->get_results( $wpdb->prepare( $query, $order_id, trim( $post_title," " ) . '%' ) );

					$order_item_ids[]   = $results[0]->order_item_id;
					$item_id            = $results[0]->order_item_id;
					$email 				= $values['bkapef_email'];
					$name  				= $values['bkapef_name'];

					wc_add_order_item_meta( $item_id, '_bkapef_mail', '' );
					wc_add_order_item_meta( $item_id, '_bkapef_name', '' );
				}
			}
		}

		public static function bkapef_custom_checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {

			// Get cart item custom data and update order item meta
			if ( isset( $values['bkapef_email'] ) ) {
				$item->update_meta_data( 'Email', $values['bkapef_email'] );
				$item->update_meta_data( 'Name', $values['bkapef_name'] );
				$item->update_meta_data( '_bkapef_mail', $values['bkapef_email'] );
				$item->update_meta_data( '_bkapef_name', $values['bkapef_name'] );

				$coupon = bkapef_coupon_code( $values['product_id'], $values['line_total'] );
				$item->update_meta_data( '_bkapef_coupon_id', $coupon['id'] );
				$item->update_meta_data( '_bkapef_coupon_code', $coupon['coupon'] );
			}
		}

		public static function bkapef_add_item_data( $cart_item_data, $product_id ){
			/*Here, We are adding item in WooCommerce session with, wdm_user_custom_data_value name*/
			global $woocommerce;

			$email = ( isset( $_POST['bkapef_email'] ) && '' != $_POST['bkapef_email'] ) ? $_POST['bkapef_email'] : '';
			$name = ( isset( $_POST['bkapef_name'] ) && '' != $_POST['bkapef_name'] ) ? $_POST['bkapef_name'] : ''; 

			if ( '' !== $email && '' !== $name ) {
				$cart_item_data['bkapef_name'] = $name;
				$cart_item_data['bkapef_email'] = $email;
			}

			return $cart_item_data;
		}

		/**
		 * Display the custom data on cart and checkout page
		 */
		public static function bkapef_get_item_data ( $other_data, $cart_item ) {

			if ( isset( $cart_item['bkapef_email'] ) ) {
				$other_data[] = array( 'name' => 'Name', 'display' => $cart_item['bkapef_name'] );
				$other_data[] = array( 'name' => 'E-mail', 'display' => $cart_item['bkapef_email'] );
			}

			return $other_data;
		}

		/**
		 * Add order item meta
		 */
		public static function bkapef_add_order_item_meta ( $item_id, $values ) {

			wp_mail( 'kartik@tychesoftwares.com', "VALUES", print_r( $values, true ) );

			if ( isset( $values[ 'bkapef_email' ] ) ) {
				wc_add_order_item_meta( $item_id, 'Name:', $values['bkapef_name'] );
				wc_add_order_item_meta( $item_id, 'Email:', $values['bkapef_email'] );
			}
		}

		/**
		 * Showing field after Booking Form.
		 */
		public function bkapef_on_woocommerce_before_add_to_cart_form() {

			if ( "product" === get_post_type() ) {
				$product = wc_get_product( get_the_ID() );		
				if ( "variable" === $product->get_type() ) {
					add_action( 'woocommerce_single_variation', array( $this, 'bkapef_booking_after_add_to_cart' ), 10 );
				} else {
					add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'bkapef_booking_after_add_to_cart' ), 10 );
				}
			}
		}

		/**
		 * Showing field after Booking Form.
		 */
		public static function bkapef_booking_after_add_to_cart() {
			global $post;

			$duplicate_of     =   bkap_common::bkap_get_product_id( $post->ID );
			$booking_settings =   bkap_common::bkap_product_setting( $duplicate_of );
			
			if ( $booking_settings == "" || ( isset( $booking_settings['booking_enable_date'] ) && $booking_settings['booking_enable_date'] != "on" ) ) {
				return;
			}
			?>
			<div class="bkapef_field" style="margin:10px 0;">
				<div class="ekapef-field">
					<label for="bkapef_name"><?php _e( 'Name:', 'bkap-vouchers' ); ?></label>
					<input type="text" id="bkapef_name" name="bkapef_name" size="40" style="width:200px;">
				</div>
				<div class="ekapef-field" style="margin:10px 0;">
					<label for="bkapef_email"><?php _e( 'Email:', 'bkap-vouchers' ); ?></label>
					<input type="email" id="bkapef_email" name="bkapef_email" style="width:300px;">
				</div>
			</div>
			<?php
		}

		public function after_bkap_load_product_scripts_js_callback(){

			if ( "product" === get_post_type() ) {
				wp_register_script(
					'bkap-voucher',
					plugins_url() . '/bkap-vouchers/assets/js/bkap-voucher.js',
					'',
					'1.0',
					true
				);
	
				wp_enqueue_script( 'bkap-voucher' );
			}

			
		}
	}
	$bkap_specific_dates_dropdown = new BKAP_Voucher();

endif;
