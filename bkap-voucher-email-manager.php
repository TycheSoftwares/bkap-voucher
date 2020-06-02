<?php
/**
 * Handles email sending
 */
class Bkap_Voucher_Email_Manager {

	/**
	 * Constructor sets up actions
	 */
	public function __construct() {

		define( 'BKAP_VOUCHER_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' ); // template path
		add_action( 'woocommerce_order_status_processing', array( &$this, 'bkap_voucher_trigger_email_action' ), 10, 2 ); // hook for when order status is changed
		add_filter( 'woocommerce_email_classes', array( &$this, 'bkap_voucher_init_emails' ) ); // include the email class files

		// Email Actions - Triggers
		$email_actions = array(
			'bkap_voucher_pending_email',
			'bkap_voucher_item_email',
		);

		foreach ( $email_actions as $action ) {
			add_action( $action, array( 'WC_Emails', 'send_transactional_email' ), 10, 10 );
		}

		add_filter( 'woocommerce_template_directory', array( $this, 'bkap_voucher_template_directory' ), 10, 2 );
	}

	public function bkap_voucher_init_emails( $emails ) {
		// Include the email class file if it's not included already.
		if ( ! isset( $emails[ 'Bkap_Voucher_Email' ] ) ) {
			$emails[ 'Bkap_Voucher_Email' ] = include_once( 'emails/class-bkap-voucher-email.php' );
		}

		return $emails;
	}

	public function bkap_voucher_trigger_email_action( $order_id, $posted ) {
		// add an action for our email trigger if the order id is valid
		wp_mail("kartik@tychesoftwares.com", "bkap_voucher_trigger_email_action", "bkap_voucher_trigger_email_action" );
		if ( isset( $order_id ) && 0 != $order_id ) {
			new WC_Emails();
			wp_mail("kartik@tychesoftwares.com", "bkap_voucher_pending_email_notification", "bkap_voucher_pending_email_notification" );
			do_action( 'bkap_voucher_pending_email_notification', $order_id );
		}
	}

	public function bkap_voucher_template_directory( $directory, $template ) {
		// ensure the directory name is correct.
		if ( false !== strpos( $template, '-custom' ) ) {
			return 'bkap-vouchers';
		}

		return $directory;
	}

}// end of class
new Bkap_Voucher_Email_Manager();
?>