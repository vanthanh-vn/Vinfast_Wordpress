<?php
/**
 * Flatsome functions and definitions
 *
 * @package flatsome
 */
update_option( get_template() . '_wup_purchase_code', '*******' );
update_option( get_template() . '_wup_supported_until', '01.01.2050' );
update_option( get_template() . '_wup_buyer', 'Licensed' );
require get_template_directory() . '/inc/init.php';

flatsome()->init();

function vf_enqueue_cart_styles() {
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		wp_enqueue_style(
			'vf-cart',
			get_template_directory_uri() . '/assets/css/vf-cart.css',
			array(),
			'1.0.0'
		);
	}
}
add_action( 'wp_enqueue_scripts', 'vf_enqueue_cart_styles', 20 );

function vf_empty_cart_request() {
	if ( isset( $_GET['empty-cart'] ) && '1' === $_GET['empty-cart'] && function_exists( 'WC' ) && WC()->cart ) {
		WC()->cart->empty_cart();
		wp_safe_redirect( wc_get_cart_url() );
		exit;
	}
}
add_action( 'template_redirect', 'vf_empty_cart_request' );

/**
 * It's not recommended to add any custom code here. Please use a child theme
 * so that your customizations aren't lost during updates.
 *
 * Learn more here: https://developer.wordpress.org/themes/advanced-topics/child-themes/
 */
