<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VF_MPV7_QR_Gateway extends WC_Payment_Gateway {
	public function __construct() {
		$this->id                 = 'vf_qr_bank_transfer';
		$this->icon               = '';
		$this->has_fields         = true;
		$this->method_title       = 'VinFast QR Bank Transfer';
		$this->method_description = 'Thanh toán bằng QR chuyển khoản ngân hàng cho đơn hàng WooCommerce.';

		$this->init_form_fields();
		$this->init_settings();

		$this->title        = $this->get_option( 'title', 'Chuyển khoản QR' );
		$this->description  = $this->get_option( 'description', 'Quét mã QR và chuyển khoản đúng số tiền, đúng nội dung đơn hàng.' );
		$this->bank_id      = $this->get_option( 'bank_id', '970422' );
		$this->account_no   = $this->get_option( 'account_no', '3129072005' );
		$this->account_name = $this->get_option( 'account_name', 'NGUYEN VAN THANH' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'      => array(
				'title'   => 'Bật/tắt',
				'type'    => 'checkbox',
				'label'   => 'Kích hoạt thanh toán QR',
				'default' => 'yes',
			),
			'title'        => array(
				'title'   => 'Tên hiển thị',
				'type'    => 'text',
				'default' => 'Chuyển khoản QR',
			),
			'description'  => array(
				'title'   => 'Mô tả',
				'type'    => 'textarea',
				'default' => 'Quét mã QR và chuyển khoản đúng số tiền, đúng nội dung đơn hàng.',
			),
			'bank_id'      => array(
				'title'       => 'Mã ngân hàng VietQR',
				'type'        => 'text',
				'description' => '970422 là MB Bank.',
				'default'     => '970422',
			),
			'account_no'   => array(
				'title'   => 'Số tài khoản',
				'type'    => 'text',
				'default' => '3129072005',
			),
			'account_name' => array(
				'title'   => 'Tên chủ tài khoản',
				'type'    => 'text',
				'default' => 'NGUYEN VAN THANH',
			),
		);
	}

	private function get_static_qr_url() {
		$qr_file = dirname( __DIR__ ) . '/assets/images/payment-qr.jpg';
		$version = file_exists( $qr_file ) ? (string) filemtime( $qr_file ) : '1';

		return add_query_arg(
			'v',
			$version,
			plugins_url( 'assets/images/payment-qr.jpg', dirname( __DIR__ ) . '/vf-mpv7-landing.php' )
		);
	}

	private function render_qr_details( $order = null ) {
		$qr_url  = $this->get_static_qr_url();
		$total   = '';
		$content = 'VINFAST';

		if ( $order instanceof WC_Order ) {
			$total   = $order->get_formatted_order_total();
			$content = 'VF' . $order->get_order_number();
		} elseif ( function_exists( 'WC' ) && WC()->cart ) {
			$total = WC()->cart->get_total();
		}

		echo '<div class="vf-qr-box">';
		echo '<img src="' . esc_url( $qr_url ) . '" alt="QR thanh toán MB Bank" loading="lazy">';
		echo '<div class="vf-qr-detail-list">';
		echo '<p><strong>Ngân hàng:</strong> MB Bank - Ngân hàng Quân Đội</p>';
		echo '<p><strong>Số tài khoản:</strong> ' . esc_html( $this->account_no ) . '</p>';
		echo '<p><strong>Chủ tài khoản:</strong> ' . esc_html( $this->account_name ) . '</p>';
		if ( $total ) {
			echo '<p><strong>Số tiền:</strong> ' . wp_kses_post( $total ) . '</p>';
		}
		echo '<p><strong>Nội dung:</strong> ' . esc_html( $content ) . '</p>';
		echo '</div>';
		echo '</div>';
	}

	public function payment_fields() {
		echo '<div class="vf-qr-checkout">';
		if ( $this->description ) {
			echo '<p>' . wp_kses_post( wpautop( $this->description ) ) . '</p>';
		}
		echo '<p class="vf-qr-checkout-note">Mã QR và thông tin chuyển khoản sẽ hiện sau khi bạn bấm nút Đặt hàng.</p>';
		echo '</div>';
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return array( 'result' => 'failure' );
		}

		$order->update_status( 'on-hold', 'Chờ thanh toán chuyển khoản QR.' );
		wc_reduce_stock_levels( $order_id );
		WC()->cart->empty_cart();

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	public function thankyou_page( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		echo '<section class="vf-qr-payment">';
		echo '<h2>Quét QR để thanh toán</h2>';
		$this->render_qr_details( $order );
		echo '</section>';
	}
}
