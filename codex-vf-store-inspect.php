<?php
require __DIR__ . '/wp-load.php';

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

echo 'woocommerce=' . ( is_plugin_active( 'woocommerce/woocommerce.php' ) ? 'active' : 'inactive' ) . PHP_EOL;
echo 'theme=' . wp_get_theme()->get_stylesheet() . PHP_EOL;
echo 'front_page=' . get_option( 'page_on_front' ) . PHP_EOL;
echo 'shop=' . get_option( 'woocommerce_shop_page_id' ) . PHP_EOL;
echo 'cart=' . get_option( 'woocommerce_cart_page_id' ) . PHP_EOL;
echo 'checkout=' . get_option( 'woocommerce_checkout_page_id' ) . PHP_EOL;

$pages = get_posts(
	array(
		'post_type'   => 'page',
		'numberposts' => 30,
		'post_status' => 'any',
		'orderby'     => 'ID',
		'order'       => 'ASC',
	)
);

foreach ( $pages as $page ) {
	echo 'page=' . $page->ID . '|' . $page->post_name . '|' . $page->post_title . PHP_EOL;
}
