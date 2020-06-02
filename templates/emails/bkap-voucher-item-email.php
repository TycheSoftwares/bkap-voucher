<?php
/**
 * Voucher Email
 */
$order = new WC_order( $item_data->order_id );
$url = get_permalink( $item_data->product_id );
$billing_first_name = ( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) < 0 ) ? $order->billing_first_name : $order->get_billing_first_name();
$billing_last_name  = ( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) < 0 ) ? $order->billing_last_name : $order->get_billing_last_name(); 

do_action( 'woocommerce_email_header', $email_heading );

?>
<div>
<br/>
<p><?php printf( __( 'Dear %s,','bkap-vouchers' ), $item_data->bkapef_name ); ?></p>
<br/>
<p><?php printf( __( 'A gift voucher, for use at %s, was purchased for you by %s on %s.','bkap-vouchers' ), get_bloginfo( 'name' ), $billing_first_name . ' ' . $billing_last_name, $item_data->order_date ); ?></p>
<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<tbody>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Product', 'bkap-vouchers' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo '<a href="'.get_permalink( $item_data->product_id ).'">'.get_the_title( $item_data->product_id ).'</a>'; ?></td>
		</tr>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Quantity', 'bkap-vouchers' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $item_data->qty; ?></td>
		</tr>
	</tbody>
</table>
<br/>
<p><?php printf( __( 'Please use the following redemption code on the payment page to redeem your gift voucher.','bkap-vouchers' ) ); ?></p>
<p><?php printf( __( 'Gift Voucher Redemption Code: %s','bkap-vouchers' ), $item_data->bkapef_coupon_code ); ?></p>
<p><?php printf( __( 'If you have any problems using your gift voucher, please contact us at customerservice@email.com','bkap-vouchers' ) ); ?></p>
<p><?php printf( __( 'Enjoy your gift..!','bkap-vouchers' ) ); ?></p>
<br/>
<p><?php printf( __( 'Sincerely,','bkap-vouchers' ) ); ?></p>
<?php echo get_bloginfo( 'name' ); ?>
</div>
<br/>
<p><i><?php _e( 'Note:The coupon will be expired in 7 days so please avail the offer before the expiry of coupon.', 'bkap-vouchers' ); ?></i></p>

<?php do_action( 'woocommerce_email_footer' ); ?>