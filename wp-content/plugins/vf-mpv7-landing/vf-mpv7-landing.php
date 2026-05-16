<?php
/**
 * Plugin Name: VF MPV 7 Landing for Elementor
 * Description: Landing page and reservation flow for a VinFast-style VF MPV 7 page. Use shortcode [vinfast_mpv7_landing] inside Elementor.
 * Version: 1.0.25
 * Author: Local Developer
 * Text Domain: vf-mpv7-landing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class VF_MPV7_Landing {
	const VERSION = '1.0.25';
	const CPT     = 'vf_reservation';

	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'setup_account_features' ), 20 );
		add_action( 'init', array( $this, 'ensure_news_page' ), 30 );
		add_shortcode( 'vinfast_mpv7_landing', array( $this, 'render_shortcode' ) );
		add_shortcode( 'vinfast_homepage', array( $this, 'render_homepage_shortcode' ) );
		add_shortcode( 'vinfast_shop', array( $this, 'render_shop_shortcode' ) );
		add_shortcode( 'vinfast_cart', array( $this, 'render_cart_shortcode' ) );
		add_shortcode( 'vinfast_news', array( $this, 'render_news_shortcode' ) );
		add_action( 'admin_post_vf_mpv7_reserve', array( $this, 'handle_reservation' ) );
		add_action( 'admin_post_nopriv_vf_mpv7_reserve', array( $this, 'handle_reservation' ) );
		add_action( 'admin_post_vf_cart_update', array( $this, 'handle_cart_update' ) );
		add_action( 'admin_post_nopriv_vf_cart_update', array( $this, 'handle_cart_update' ) );
		add_action( 'wp_ajax_vf_cart_update_ajax', array( $this, 'handle_cart_update_ajax' ) );
		add_action( 'wp_ajax_nopriv_vf_cart_update_ajax', array( $this, 'handle_cart_update_ajax' ) );
		add_action( 'admin_post_vf_cart_remove', array( $this, 'handle_cart_remove' ) );
		add_action( 'admin_post_nopriv_vf_cart_remove', array( $this, 'handle_cart_remove' ) );
		add_action( 'admin_post_vf_cart_clear', array( $this, 'handle_cart_clear' ) );
		add_action( 'admin_post_nopriv_vf_cart_clear', array( $this, 'handle_cart_clear' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_filter( 'manage_' . self::CPT . '_posts_columns', array( $this, 'reservation_columns' ) );
		add_action( 'manage_' . self::CPT . '_posts_custom_column', array( $this, 'reservation_column_content' ), 10, 2 );
		add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widget' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 99 );
		add_action( 'wp', array( $this, 'setup_single_product_layout' ) );
		add_action( 'wp_body_open', array( $this, 'render_global_store_header' ), 1 );
		add_action( 'wp_footer', array( $this, 'render_global_footer' ), 5 );
		add_action( 'woocommerce_before_customer_login_form', array( $this, 'render_account_guest_intro' ) );
		add_action( 'woocommerce_before_account_navigation', array( $this, 'render_account_member_intro' ) );
		add_filter( 'woocommerce_coming_soon_exclude', array( $this, 'exclude_products_from_coming_soon' ) );
		add_filter( 'woocommerce_product_get_image', array( $this, 'replace_product_image' ), 10, 5 );
		add_filter( 'woocommerce_get_cart_url', array( $this, 'get_custom_cart_url' ) );
		add_filter( 'wp_nav_menu_objects', array( $this, 'remove_deposit_menu_item' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'account_menu_items' ) );
	}

	public function register_post_type() {
		register_post_type(
			self::CPT,
			array(
				'labels'       => array(
					'name'          => 'VF MPV 7 Reservations',
					'singular_name' => 'VF MPV 7 Reservation',
					'menu_name'     => 'VF Reservations',
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => true,
				'menu_icon'    => 'dashicons-car',
				'supports'     => array( 'title' ),
				'capability_type' => 'post',
			)
		);
	}

	public function render_shortcode() {
		$this->enqueue_assets();

		$status = isset( $_GET['vf_mpv7_status'] ) ? sanitize_key( wp_unslash( $_GET['vf_mpv7_status'] ) ) : '';
		$admin_post_url = esc_url( admin_url( 'admin-post.php' ) );

		ob_start();
		?>
		<div class="vf-page" id="vf-mpv7">
			<header class="vf-sticky">
				<a class="vf-brand" href="#vf-mpv7" aria-label="VinFast VF MPV 7">
					<span class="vf-brand-mark">VF</span>
					<span>MPV 7</span>
				</a>
				<a class="vf-back-home" href="<?php echo esc_url( home_url( '/' ) ); ?>">&larr; Trang chủ</a>
				<nav class="vf-nav" aria-label="VF MPV 7">
					<a href="#tong-quan">Tổng quan</a>
					<a href="#thong-so">Thông số</a>
					<a href="#cau-hinh">Cấu hình</a>
					<a href="#dat-coc">Đặt cọc</a>
				</nav>
				<a class="vf-cta vf-cta-small" href="#dat-coc">Đặt cọc ngay</a>
			</header>

			<section class="vf-hero" id="tong-quan">
				<div class="vf-hero-media" role="img" aria-label="VF MPV 7 electric family car"></div>
				<div class="vf-hero-copy">
					<p class="vf-eyebrow">Ô tô điện đa dụng 7 chỗ</p>
					<h1>VinFast VF MPV 7</h1>
					<p>Không gian rộng rãi, vận hành điện êm ái và gói đặt cọc ưu tiên giao xe cho khách hàng sớm.</p>
					<div class="vf-hero-actions">
						<a class="vf-cta" href="#dat-coc">Đặt cọc 50.000.000 VND</a>
						<a class="vf-secondary" href="#cau-hinh">Cấu hình xe</a>
					</div>
				</div>
			</section>

			<section class="vf-strip" aria-label="Thong tin nhanh">
				<div><strong>819 triệu</strong><span>Giá từ</span></div>
				<div><strong>450 km</strong><span>NEDC mỗi lần sạc</span></div>
				<div><strong>30 phút</strong><span>Sạc 10%-70%</span></div>
				<div><strong>7 chỗ</strong><span>MPV gia đình</span></div>
			</section>

			<section class="vf-section" id="thong-so">
				<div class="vf-section-head">
					<p class="vf-eyebrow">Thông số nổi bật</p>
					<h2>Thiết kế cho di chuyển mỗi ngày</h2>
				</div>
				<div class="vf-spec-grid">
					<div><span>Kích thước</span><strong>4740 x 1872 x 1734 mm</strong></div>
					<div><span>Chiều dài cơ sở</span><strong>2840 mm</strong></div>
					<div><span>Công suất tối đa</span><strong>150 kW</strong></div>
					<div><span>Mô-men xoắn</span><strong>280 Nm</strong></div>
					<div><span>Pin khả dụng</span><strong>60,13 kWh</strong></div>
					<div><span>Sạc nhanh DC</span><strong>Tối đa 80 kW</strong></div>
				</div>
			</section>

			<section class="vf-section vf-config" id="cau-hinh">
				<div class="vf-config-preview">
					<div class="vf-car-card">
						<div class="vf-car-shape" aria-hidden="true">
							<span class="vf-window"></span>
							<span class="vf-wheel vf-wheel-one"></span>
							<span class="vf-wheel vf-wheel-two"></span>
						</div>
					</div>
					<div class="vf-summary">
						<p class="vf-eyebrow">Giá tạm tính</p>
						<strong data-vf-total>899.000.000 VND</strong>
						<span>Đã bao gồm pin, chưa bao gồm chi phí lăn bánh</span>
					</div>
				</div>

				<div class="vf-config-panel">
					<p class="vf-eyebrow">Cấu hình xe</p>
					<h2>Chọn phiên bản và màu sắc</h2>

					<div class="vf-option-group">
						<label>Phiên bản</label>
						<button class="vf-choice is-active" type="button" data-vf-version="VF MPV 7 Tiêu chuẩn" data-vf-price="819000000">
							<span>VF MPV 7 Tiêu chuẩn</span>
							<strong>819.000.000 VND</strong>
						</button>
					</div>

					<div class="vf-option-group">
						<label>Dịch vụ pin</label>
						<div class="vf-toggle-row">
							<button class="vf-choice is-active" type="button" data-vf-battery="Bao gồm pin" data-vf-price="80000000">Bao gồm pin</button>
							<button class="vf-choice" type="button" data-vf-battery="Thuê pin" data-vf-price="0">Thuê pin</button>
						</div>
					</div>

					<div class="vf-option-group">
						<label>Màu ngoại thất</label>
						<div class="vf-swatches" role="list">
							<button class="vf-swatch is-active" type="button" title="Infinity Blanc" data-vf-color="Infinity Blanc" data-vf-color-code="#f2f4f5" data-vf-price="0"></button>
							<button class="vf-swatch" type="button" title="Jet Black" data-vf-color="Jet Black" data-vf-color-code="#111318" data-vf-price="0"></button>
							<button class="vf-swatch" type="button" title="Zenith Grey" data-vf-color="Zenith Grey" data-vf-color-code="#767d83" data-vf-price="0"></button>
							<button class="vf-swatch" type="button" title="Solar Ruby" data-vf-color="Solar Ruby" data-vf-color-code="#9f2630" data-vf-price="0"></button>
							<button class="vf-swatch" type="button" title="Moonlit Ocean (+10 trieu)" data-vf-color="Moonlit Ocean" data-vf-color-code="#1b5368" data-vf-price="10000000"></button>
							<button class="vf-swatch" type="button" title="Introspective Brown (+10 trieu)" data-vf-color="Introspective Brown" data-vf-color-code="#6a4a3c" data-vf-price="10000000"></button>
						</div>
						<p class="vf-muted">Màu nâng cao phụ thu 10.000.000 VND.</p>
					</div>

					<div class="vf-option-group">
						<label>Nội thất</label>
						<div class="vf-toggle-row">
							<button class="vf-choice is-active" type="button" data-vf-interior="Black">Black</button>
							<button class="vf-choice" type="button" data-vf-interior="Mocca Brown">Mocca Brown</button>
						</div>
					</div>
				</div>
			</section>

			<section class="vf-section vf-reserve" id="dat-coc">
				<div class="vf-section-head">
					<p class="vf-eyebrow">Đặt cọc online</p>
					<h2>Gửi thông tin tư vấn và giữ thứ tự ưu tiên</h2>
				</div>

				<?php if ( 'success' === $status ) : ?>
					<div class="vf-notice vf-notice-success">Da ghi nhan thong tin dat coc. Bo phan tu van se lien he de xac nhan don hang.</div>
				<?php elseif ( 'error' === $status ) : ?>
					<div class="vf-notice vf-notice-error">Vui long kiem tra lai thong tin bat buoc va dieu khoan dat coc.</div>
				<?php endif; ?>

				<form class="vf-form" method="post" action="<?php echo $admin_post_url; ?>">
					<input type="hidden" name="action" value="vf_mpv7_reserve">
					<input type="hidden" name="vf_version" data-vf-input="version" value="VF MPV 7 Tiêu chuẩn">
					<input type="hidden" name="vf_battery" data-vf-input="battery" value="Bao gồm pin">
					<input type="hidden" name="vf_color" data-vf-input="color" value="Infinity Blanc">
					<input type="hidden" name="vf_interior" data-vf-input="interior" value="Black">
					<input type="hidden" name="vf_total" data-vf-input="total" value="899000000">
					<?php wp_nonce_field( 'vf_mpv7_reserve', 'vf_mpv7_nonce' ); ?>

					<div class="vf-form-grid">
						<div class="vf-form-main">
							<div class="vf-fieldset">
								<legend>Chủ sở hữu</legend>
								<div class="vf-radio-row">
									<label><input type="radio" name="owner_type" value="Cá nhân" checked> Cá nhân</label>
									<label><input type="radio" name="owner_type" value="Doanh nghiệp"> Doanh nghiệp</label>
								</div>
							</div>

							<div class="vf-fields">
								<label>Họ và tên / Tên doanh nghiệp
									<input type="text" name="customer_name" required placeholder="Nguyen Van A">
								</label>
								<label>Số điện thoại
									<input type="tel" name="phone" required pattern="[0-9+\s]{9,15}" placeholder="0901234567">
								</label>
								<label>Email
									<input type="email" name="email" required placeholder="email@example.com">
								</label>
								<label>CCCD / Mã số doanh nghiệp
									<input type="text" name="id_number" required placeholder="Nhập số giấy tờ">
								</label>
								<label>Tỉnh thành
									<select name="province" required>
										<option value="">Chọn tỉnh thành</option>
										<option>Ha Noi</option>
										<option>TP Ho Chi Minh</option>
										<option>Da Nang</option>
										<option>Hai Phong</option>
										<option>Can Tho</option>
									</select>
								</label>
								<label>Showroom nhận xe
									<select name="showroom" required>
										<option value="">Chọn showroom</option>
										<option>VinFast Landmark 81</option>
										<option>VinFast Long Bien</option>
										<option>VinFast Da Nang</option>
										<option>VinFast Can Tho</option>
									</select>
								</label>
								<label>Mã ưu đãi / e-voucher
									<input type="text" name="voucher" placeholder="Nhập nếu có">
								</label>
								<label>Nhân viên tư vấn
									<input type="text" name="consultant" placeholder="Tên nhân viên nếu có">
								</label>
							</div>

							<div class="vf-programs">
								<label><input type="checkbox" name="programs[]" value="Giới thiệu khách hàng mới"> Giới thiệu khách hàng mới</label>
								<label><input type="checkbox" name="programs[]" value="CBNV Vingroup"> CBNV Vingroup</label>
								<label><input type="checkbox" name="programs[]" value="Đối tác VinFast"> Đối tác VinFast</label>
								<label><input type="checkbox" name="programs[]" value="Ưu đãi O2O"> Ưu đãi kênh O2O</label>
							</div>
						</div>

						<aside class="vf-order">
							<h3>Tóm tắt đặt cọc</h3>
							<dl>
								<div><dt>Xe</dt><dd data-vf-summary="version">VF MPV 7 Tiêu chuẩn</dd></div>
								<div><dt>Pin</dt><dd data-vf-summary="battery">Bao gồm pin</dd></div>
								<div><dt>Ngoại thất</dt><dd data-vf-summary="color">Infinity Blanc</dd></div>
								<div><dt>Nội thất</dt><dd data-vf-summary="interior">Black</dd></div>
								<div><dt>Tổng tạm tính</dt><dd data-vf-total>899.000.000 VND</dd></div>
								<div><dt>Tiền cọc</dt><dd>50.000.000 VND</dd></div>
							</dl>
							<div class="vf-benefits">
								<span>Ưu đãi 30 triệu đồng</span>
								<span>Ưu tiên giao xe theo thứ tự cọc</span>
								<span>Có thể chuyển nhượng đơn cọc</span>
							</div>
							<label class="vf-check"><input type="checkbox" name="terms" value="1" required> Tôi xác nhận thông tin chính xác và đồng ý điều khoản đặt cọc.</label>
							<label class="vf-check"><input type="checkbox" name="privacy" value="1" required> Tôi đồng ý việc xử lý dữ liệu cá nhân để VinFast tư vấn.</label>
							<button class="vf-submit" type="submit">Gửi thông tin đặt cọc</button>
							<p class="vf-muted">Đây là form thu lead local. Tích hợp cổng thanh toán cần cấu hình OnePay/Payoo riêng.</p>
						</aside>
					</div>
				</form>
			</section>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_homepage_shortcode() {
		$this->enqueue_assets();

		$products = array();
		if ( function_exists( 'wc_get_products' ) ) {
			$products = wc_get_products(
				array(
					'status' => 'publish',
					'limit'  => 8,
					'orderby' => 'menu_order',
					'order'  => 'ASC',
				)
			);
		}

		$shop_url     = home_url( '/cua-hang/' );
		$cart_url     = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/gio-hang/' );
		$checkout_url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/thanh-toan/' );
		$product_images = $this->get_product_images();
		$hero_images = array(
			array( 'src' => $product_images['VF7'], 'alt' => 'VinFast VF 7' ),
			array( 'src' => $product_images['VF6'], 'alt' => 'VinFast VF 6' ),
			array( 'src' => $product_images['VF8'], 'alt' => 'VinFast VF 8' ),
		);

		ob_start();
		?>
		<div class="vf-page vf-ch-page">
			<section class="vf-ch-hero">
				<div class="vf-ch-hero-slides">
					<?php foreach ( $hero_images as $index => $hero ) : ?>
						<div class="vf-ch-hero-slide <?php echo 0 === $index ? 'is-active' : ''; ?>">
							<img src="<?php echo esc_url( $hero['src'] ); ?>" alt="<?php echo esc_attr( $hero['alt'] ); ?>">
						</div>
					<?php endforeach; ?>
				</div>
				<div class="vf-ch-hero-overlay"></div>
				<div class="vf-ch-hero-content">
					<p class="vf-ch-eyebrow"><span></span> Tìm xe bán và cho thuê gần bạn</p>
					<h1>Tìm Chiếc Xe<br><em>Mơ Ước</em> Của Bạn</h1>
					<p>Hàng trăm mẫu xe VinFast chính hãng, hỗ trợ giỏ hàng và thanh toán QR.</p>
					<div class="vf-ch-hero-actions">
						<a class="vf-ch-primary vf-ch-primary-lg" href="#vf-ch-products">Khám phá xe ngay</a>
					</div>
				</div>
				<div class="vf-ch-hero-dots" aria-label="Hero slides">
					<button class="is-active" type="button" data-vf-slide="0"></button>
					<button type="button" data-vf-slide="1"></button>
					<button type="button" data-vf-slide="2"></button>
				</div>
			</section>

			<section class="vf-ch-section vf-ch-section-alt" id="vf-ch-categories">
				<div class="vf-ch-container">
					<h2>Duyệt Theo Danh Mục</h2>
					<p class="vf-ch-section-sub">Chọn phân khúc xe phù hợp với nhu cầu của bạn</p>
					<div class="vf-ch-types-grid">
						<a href="<?php echo esc_url( $shop_url ); ?>" class="vf-ch-type-card"><span>PT</span><strong>Phổ Thông</strong></a>
						<a href="<?php echo esc_url( $shop_url ); ?>" class="vf-ch-type-card"><span>TC</span><strong>Trung Cấp</strong></a>
						<a href="<?php echo esc_url( $shop_url ); ?>" class="vf-ch-type-card"><span>CC</span><strong>Cao Cấp</strong></a>
						<a href="#vf-ch-products" class="vf-ch-type-card"><span>OT</span><strong>Ô Tô</strong></a>
						<a href="<?php echo esc_url( $checkout_url ); ?>" class="vf-ch-type-card"><span>QR</span><strong>Thanh Toán</strong></a>
					</div>
				</div>
			</section>

			<section class="vf-ch-section">
				<div class="vf-ch-container">
					<div class="vf-ch-cta-grid">
						<div class="vf-ch-cta-card vf-ch-cta-blue">
							<div>
								<h3>Bạn Đang Tìm<br>Mua Xe?</h3>
								<p>Khám phá các mẫu xe VinFast chính hãng với giá niêm yết và hình ảnh sản phẩm rõ ràng.</p>
								<a href="<?php echo esc_url( $shop_url ); ?>">Khám phá ngay</a>
							</div>
							<span>VF</span>
						</div>
					</div>
				</div>
			</section>

			<section class="vf-ch-section vf-ch-section-alt" id="vf-ch-products">
				<div class="vf-ch-container">
					<h2>Xe Được Tìm Nhiều Nhất</h2>
					<div class="vf-ch-tabs">
						<button class="vf-ch-tab is-active" type="button" data-filter="all">Tất Cả</button>
						<button class="vf-ch-tab" type="button" data-filter="compact">Đô Thị</button>
						<button class="vf-ch-tab" type="button" data-filter="suv">SUV</button>
						<button class="vf-ch-tab" type="button" data-filter="mpv">MPV</button>
						<button class="vf-ch-tab" type="button" data-filter="motorbike">Xe máy điện</button>
						<button class="vf-ch-tab" type="button" data-filter="accessory">Phụ kiện</button>
					</div>
					<div class="vf-ch-product-grid">
					<?php foreach ( $products as $index => $product ) : ?>
						<?php
						$color = get_post_meta( $product->get_id(), '_vf_card_color', true );
						$color = $color ? $color : '#f2f4f5';
						$sku = $product->get_sku();
						$image_url = $this->get_product_image_url_by_sku( $sku );
						$filter = in_array( $sku, array( 'VF3', 'VF5' ), true ) ? 'compact' : 'suv';
						$filter = 'VFMPV7' === $sku ? 'mpv' : $filter;
						$filter = 0 === strpos( $sku, 'VFM-' ) ? 'motorbike' : $filter;
						$filter = 0 === strpos( $sku, 'VF-ACC-' ) ? 'accessory' : $filter;
						?>
						<article class="vf-ch-product-card" data-category="<?php echo esc_attr( $filter ); ?>" style="--vf-card-car: <?php echo esc_attr( $color ); ?>">
							<div class="vf-ch-product-img">
								<span class="vf-ch-badge"><?php echo esc_html( get_post_meta( $product->get_id(), '_vf_segment', true ) ?: 'Ô tô điện' ); ?></span>
								<?php if ( $image_url ) : ?>
									<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" loading="lazy">
								<?php else : ?>
									<div class="vf-mini-car" aria-hidden="true"><span></span></div>
								<?php endif; ?>
							</div>
							<div class="vf-ch-product-body">
								<h3><?php echo esc_html( $product->get_name() ); ?></h3>
								<p>Ô tô VinFast chính hãng</p>
								<div class="vf-ch-specs">
									<span>Điện</span>
									<span>Pin LFP</span>
									<span>BH 3 năm</span>
								</div>
								<div class="vf-ch-product-footer">
									<strong><?php echo wp_kses_post( $product->get_price_html() ); ?></strong>
									<a href="<?php echo esc_url( $product->get_permalink() ); ?>">Xem chi tiết</a>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
					</div>
				</div>
			</section>

			<section class="vf-ch-section">
				<div class="vf-ch-container vf-ch-checkout">
					<div>
						<h2>Giỏ hàng và QR chuyển khoản</h2>
						<p>Chọn xe, thêm vào giỏ hàng, điền thông tin thanh toán và chọn phương thức QR.</p>
					</div>
					<div class="vf-ch-steps">
						<span>1. Chọn xe</span>
						<span>2. Thêm vào giỏ</span>
						<span>3. Thanh toán QR</span>
					</div>
				</div>
			</section>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_shop_shortcode() {
		$this->enqueue_assets();

		$products = array();
		if ( function_exists( 'wc_get_products' ) ) {
			$products = wc_get_products(
				array(
					'status'  => 'publish',
					'limit'   => -1,
					'orderby' => 'menu_order',
					'order'   => 'ASC',
				)
			);
		}

		$product_images = $this->get_product_images();
		$cart_url       = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/gio-hang/' );
		$checkout_url   = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/thanh-toan/' );
		ob_start();
		?>
		<div class="vf-page vf-shop-page">
			<header class="vf-shop-hero">
				<div>
					<p class="vf-eyebrow">Cửa hàng VinFast</p>
					<h1>Chọn xe theo phân cấp phù hợp</h1>
					<p>Danh mục xe được chia theo nhóm phổ thông, trung cấp, cao cấp và MPV/dịch vụ để khách hàng dễ so sánh trước khi thêm vào giỏ hàng.</p>
					<div class="vf-shop-actions">
						<a class="vf-cta" href="#vf-shop-products">Xem sản phẩm</a>
						<a class="vf-secondary" href="<?php echo esc_url( $cart_url ); ?>">Xem giỏ hàng</a>
					</div>
				</div>
				<img src="<?php echo esc_url( $product_images['VFMPV7'] ); ?>" alt="VinFast VF MPV 7">
			</header>
			<section class="vf-shop-category-band" aria-label="Phân cấp loại xe">
				<button class="vf-shop-filter is-active" type="button" data-shop-filter="all">Tất cả</button>
				<button class="vf-shop-filter" type="button" data-shop-filter="pho-thong">Phổ thông</button>
				<button class="vf-shop-filter" type="button" data-shop-filter="trung-cap">Trung cấp</button>
				<button class="vf-shop-filter" type="button" data-shop-filter="cao-cap">Cao cấp</button>
				<button class="vf-shop-filter" type="button" data-shop-filter="mpv-dich-vu">MPV / Dịch vụ</button>
				<button class="vf-shop-filter" type="button" data-shop-filter="xe-may-dien">Xe máy điện</button>
				<button class="vf-shop-filter" type="button" data-shop-filter="phu-kien">Phụ kiện</button>
				<button class="vf-shop-filter" type="button" data-shop-filter="o-to-dien">Ô tô điện</button>
			</section>

			<section class="vf-section vf-shop-products" id="vf-shop-products">
				<div class="vf-section-head">
					<p class="vf-eyebrow">Sản phẩm</p>
					<h2>Danh sách xe VinFast</h2>
				</div>

				<div class="vf-shop-grid">
					<?php foreach ( $products as $product ) : ?>
						<?php
						$sku        = $product->get_sku();
						$image_url  = $this->get_product_image_url_by_sku( $sku );
						$group      = $this->get_product_group( $sku );
						$group_name = $this->get_product_group_label( $group );
						$shop_specs = $this->get_shop_specs( $group );
						?>
						<article class="vf-shop-card" data-shop-category="<?php echo esc_attr( $group ); ?>">
							<div class="vf-shop-card-media">
								<span><?php echo esc_html( $group_name ); ?></span>
								<?php if ( $image_url ) : ?>
									<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" loading="lazy">
								<?php endif; ?>
							</div>
							<div class="vf-shop-card-body">
								<h3><?php echo esc_html( $product->get_name() ); ?></h3>
								<p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 20 ) ); ?></p>
								<div class="vf-shop-specs">
									<?php foreach ( $shop_specs as $spec ) : ?>
										<span><?php echo esc_html( $spec ); ?></span>
									<?php endforeach; ?>
								</div>
								<div class="vf-shop-card-footer">
									<strong><?php echo wp_kses_post( $product->get_price_html() ); ?></strong>
									<div>
										<a class="vf-secondary vf-secondary-small" href="<?php echo esc_url( $product->get_permalink() ); ?>">Chi tiết</a>
										<a class="vf-cta vf-cta-small" href="<?php echo esc_url( $product->add_to_cart_url() ); ?>">Thêm vào giỏ</a>
									</div>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="vf-shop-checkout">
				<div>
					<h2>Sẵn sàng thanh toán?</h2>
					<p>Kiểm tra lại giỏ hàng, nhập thông tin khách hàng và chọn phương thức chuyển khoản QR.</p>
				</div>
				<div class="vf-shop-actions">
					<a class="vf-secondary" href="<?php echo esc_url( $cart_url ); ?>">Giỏ hàng</a>
					<a class="vf-cta" href="<?php echo esc_url( $checkout_url ); ?>">Thanh toán</a>
				</div>
			</section>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_cart_shortcode() {
		$this->enqueue_assets();

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return '<div class="vf-page vf-cart-page"><p>WooCommerce chưa sẵn sàng.</p></div>';
		}

		$product_images = $this->get_product_images();
		$shop_url       = home_url( '/cua-hang/' );
		$checkout_url   = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/thanh-toan/' );
		$admin_post_url = esc_url( admin_url( 'admin-post.php' ) );
		$cart_items     = WC()->cart->get_cart();
		$cart_count     = WC()->cart->get_cart_contents_count();
		$discount_total = (float) WC()->cart->get_discount_total();
		$current_uri    = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$header_active  = false !== strpos( $current_uri, '/thanh-toan' ) ? 'checkout' : 'cart';

		ob_start();
		?>
		<div class="vf-page vf-cart-page vf-cart-pro vf-cart-modern">
			<header class="vf-cart-titlebar">
				<h1>Giỏ Hàng Của Bạn</h1>
				<p data-vf-cart-count-text>Bạn có <?php echo esc_html( $cart_count ); ?> sản phẩm trong giỏ hàng</p>
			</header>

			<a class="vf-cart-continue" href="<?php echo esc_url( $shop_url ); ?>">← Tiếp tục mua sắm</a>

			<?php if ( empty( $cart_items ) ) : ?>
				<section class="vf-cart-empty">
					<div class="vf-cart-empty-icon">VF</div>
					<h2>Giỏ hàng đang trống</h2>
					<p>Bạn chưa thêm sản phẩm nào vào giỏ hàng. Hãy chọn một mẫu xe để xem tổng tiền và tiếp tục thanh toán.</p>
					<a class="vf-cta" href="<?php echo esc_url( $shop_url ); ?>">Vào cửa hàng</a>
				</section>
			<?php else : ?>
				<section class="vf-cart-layout">
					<div class="vf-cart-list">
						<div class="vf-cart-list-head">
							<span>SẢN PHẨM</span>
							<span>GIÁ</span>
							<span>SỐ LƯỢNG</span>
							<span>TỔNG</span>
						</div>
						<?php foreach ( $cart_items as $cart_item_key => $item ) : ?>
							<?php
							$product = isset( $item['data'] ) ? $item['data'] : null;
							if ( ! $product || ! $product->exists() ) {
								continue;
							}
							$sku       = $product->get_sku();
							$image_url = isset( $product_images[ $sku ] ) ? $product_images[ $sku ] : '';
							$quantity  = isset( $item['quantity'] ) ? (int) $item['quantity'] : 1;
							?>
							<article class="vf-cart-item" data-vf-cart-item="<?php echo esc_attr( $cart_item_key ); ?>">
								<div class="vf-cart-image">
									<?php if ( $image_url ) : ?>
										<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>">
									<?php else : ?>
										<?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?>
									<?php endif; ?>
								</div>
								<div class="vf-cart-info">
									<h3><?php echo esc_html( $product->get_name() ); ?></h3>
									<span><?php echo esc_html( $this->get_product_group_label( $this->get_product_group( $sku ) ) ); ?></span>
								</div>
								<div class="vf-cart-price">
									<?php echo wp_kses_post( wc_price( (float) $product->get_price() ) ); ?>
								</div>
								<form class="vf-cart-qty" method="post" action="<?php echo $admin_post_url; ?>" data-vf-cart-qty-form>
									<input type="hidden" name="action" value="vf_cart_update">
									<input type="hidden" name="cart_item_key" value="<?php echo esc_attr( $cart_item_key ); ?>">
									<?php wp_nonce_field( 'vf_cart_update', 'vf_cart_nonce' ); ?>
									<button type="submit" name="quantity" value="<?php echo esc_attr( max( 1, $quantity - 1 ) ); ?>" data-vf-cart-quantity="<?php echo esc_attr( max( 1, $quantity - 1 ) ); ?>" aria-label="Giảm số lượng">−</button>
									<input type="number" min="1" max="9" value="<?php echo esc_attr( $quantity ); ?>" readonly data-vf-cart-qty-input aria-label="Số lượng">
									<button type="submit" name="quantity" value="<?php echo esc_attr( min( 9, $quantity + 1 ) ); ?>" data-vf-cart-quantity="<?php echo esc_attr( min( 9, $quantity + 1 ) ); ?>" aria-label="Tăng số lượng">+</button>
								</form>
								<div class="vf-cart-subtotal">
									<strong data-vf-cart-item-subtotal><?php echo wp_kses_post( WC()->cart->get_product_subtotal( $product, $quantity ) ); ?></strong>
									<form method="post" action="<?php echo $admin_post_url; ?>">
										<input type="hidden" name="action" value="vf_cart_remove">
										<input type="hidden" name="cart_item_key" value="<?php echo esc_attr( $cart_item_key ); ?>">
										<?php wp_nonce_field( 'vf_cart_remove', 'vf_cart_nonce' ); ?>
										<button type="submit" aria-label="Xóa khỏi giỏ">×</button>
									</form>
								</div>
							</article>
						<?php endforeach; ?>
					</div>

					<aside class="vf-cart-summary">
						<div class="vf-cart-summary-head">
							<h2>▣ Tóm Tắt Đơn Hàng</h2>
						</div>
						<form class="vf-cart-coupon" method="post" action="<?php echo esc_url( wc_get_cart_url() ); ?>">
							<input type="text" name="coupon_code" placeholder="Nhập mã giảm giá...">
							<button type="submit" name="apply_coupon" value="Áp dụng">Áp dụng</button>
						</form>
						<dl>
							<div><dt>Tạm tính</dt><dd data-vf-cart-subtotal><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></dd></div>
							<div><dt>Phí vận chuyển</dt><dd class="is-free">Miễn phí</dd></div>
							<div><dt>Giảm giá</dt><dd data-vf-cart-discount>− <?php echo wp_kses_post( wc_price( $discount_total ) ); ?></dd></div>
							<div class="vf-cart-total"><dt>Tổng cộng</dt><dd data-vf-cart-total><?php echo wp_kses_post( WC()->cart->get_total() ); ?></dd></div>
						</dl>
						<a class="vf-cta" href="<?php echo esc_url( $checkout_url ); ?>">Thanh Toán Ngay →</a>
						<form class="vf-cart-clear-form" method="post" action="<?php echo $admin_post_url; ?>">
							<input type="hidden" name="action" value="vf_cart_clear">
							<?php wp_nonce_field( 'vf_cart_clear', 'vf_cart_nonce' ); ?>
							<button type="submit">Xóa sạch giỏ hàng</button>
						</form>
						<div class="vf-cart-assurance">
							<span><b>♢</b>Bảo mật<br>thanh toán</span>
							<span><b>↻</b>Đổi trả<br>30 ngày</span>
							<span><b>☎</b>Hỗ trợ<br>24/7</span>
						</div>
					</aside>
				</section>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public function handle_cart_update() {
		if (
			empty( $_POST['vf_cart_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vf_cart_nonce'] ) ), 'vf_cart_update' )
			|| ! function_exists( 'WC' )
			|| ! WC()->cart
		) {
			wp_safe_redirect( home_url( '/gio-hang/' ) );
			exit;
		}

		$key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
		$qty = isset( $_POST['quantity'] ) ? max( 1, (int) $_POST['quantity'] ) : 1;
		if ( $key ) {
			WC()->cart->set_quantity( $key, $qty, true );
		}

		wp_safe_redirect( home_url( '/gio-hang/' ) );
		exit;
	}

	public function handle_cart_update_ajax() {
		if (
			empty( $_POST['vf_cart_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vf_cart_nonce'] ) ), 'vf_cart_update' )
			|| ! function_exists( 'WC' )
			|| ! WC()->cart
		) {
			wp_send_json_error( array( 'message' => 'Không thể cập nhật giỏ hàng.' ), 403 );
		}

		$key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
		$qty = isset( $_POST['quantity'] ) ? min( 9, max( 1, (int) $_POST['quantity'] ) ) : 1;

		if ( ! $key || empty( WC()->cart->cart_contents[ $key ] ) ) {
			wp_send_json_error( array( 'message' => 'Sản phẩm không còn trong giỏ hàng.' ), 404 );
		}

		WC()->cart->set_quantity( $key, $qty, true );
		WC()->cart->calculate_totals();

		$item    = WC()->cart->cart_contents[ $key ];
		$product = isset( $item['data'] ) ? $item['data'] : null;

		if ( ! $product || ! $product->exists() ) {
			wp_send_json_error( array( 'message' => 'Sản phẩm không hợp lệ.' ), 404 );
		}

		wp_send_json_success(
			array(
				'quantity'     => $qty,
				'minusQty'     => max( 1, $qty - 1 ),
				'plusQty'      => min( 9, $qty + 1 ),
				'cartCount'    => WC()->cart->get_cart_contents_count(),
				'itemSubtotal' => WC()->cart->get_product_subtotal( $product, $qty ),
				'cartSubtotal' => WC()->cart->get_cart_subtotal(),
				'discount'     => '− ' . wc_price( (float) WC()->cart->get_discount_total() ),
				'total'        => WC()->cart->get_total(),
			)
		);
	}

	public function handle_cart_remove() {
		if (
			empty( $_POST['vf_cart_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vf_cart_nonce'] ) ), 'vf_cart_remove' )
			|| ! function_exists( 'WC' )
			|| ! WC()->cart
		) {
			wp_safe_redirect( home_url( '/gio-hang/' ) );
			exit;
		}

		$key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
		if ( $key ) {
			WC()->cart->remove_cart_item( $key );
		}

		wp_safe_redirect( home_url( '/gio-hang/' ) );
		exit;
	}

	public function handle_cart_clear() {
		if (
			empty( $_POST['vf_cart_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vf_cart_nonce'] ) ), 'vf_cart_clear' )
			|| ! function_exists( 'WC' )
			|| ! WC()->cart
		) {
			wp_safe_redirect( home_url( '/gio-hang/' ) );
			exit;
		}

		WC()->cart->empty_cart();

		wp_safe_redirect( home_url( '/gio-hang/' ) );
		exit;
	}

	public function ensure_news_page() {
		if ( wp_installing() ) {
			return;
		}

		$page = get_page_by_path( 'tin-tuc', OBJECT, 'page' );
		if ( $page instanceof WP_Post ) {
			if ( false === strpos( $page->post_content, '[vinfast_news' ) ) {
				$content = trim( $page->post_content );
				$content = $content ? $content . "\n\n[vinfast_news]" : '[vinfast_news]';
				wp_update_post(
					array(
						'ID'           => $page->ID,
						'post_content' => $content,
					)
				);
			}
			return;
		}

		wp_insert_post(
			array(
				'post_title'   => 'Tin tức VinFast',
				'post_name'    => 'tin-tuc',
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '[vinfast_news]',
				'post_author'  => get_current_user_id() ? get_current_user_id() : 1,
			)
		);
	}

	public function render_news_shortcode() {
		$this->enqueue_assets();

		$articles       = $this->get_news_articles();
		$requested_slug = isset( $_GET['bai-viet'] ) ? sanitize_title( wp_unslash( $_GET['bai-viet'] ) ) : '';

		if ( $requested_slug && isset( $articles[ $requested_slug ] ) ) {
			return $this->render_news_detail( $articles[ $requested_slug ], $articles );
		}

		$latest     = array_values( $articles );
		$featured   = $latest[0];
		$trending   = array_slice( $latest, 1, 3 );
		$categories = array();

		foreach ( $latest as $article ) {
			$categories[ $article['category'] ] = $article['category'];
		}

		ob_start();
		?>
		<div class="vf-page vf-news-page">
			<section class="vf-news-hero">
				<img src="<?php echo esc_url( $featured['image'] ); ?>" alt="<?php echo esc_attr( $featured['alt'] ); ?>">
				<div class="vf-news-hero-overlay"></div>
				<div class="vf-news-container vf-news-hero-content vf-news-fade">
					<p class="vf-eyebrow">VINFAST NEWS</p>
					<h1>Cập nhật tin tức xe điện mới nhất</h1>
					<p>Tin nổi bật, xu hướng công nghệ, trải nghiệm sử dụng và các câu chuyện xoay quanh hệ sinh thái xe điện VinFast.</p>
					<a class="vf-news-hero-link" href="<?php echo esc_url( $this->get_news_article_url( $featured['slug'] ) ); ?>">Đọc bài nổi bật</a>
				</div>
			</section>

			<main class="vf-news-container vf-news-main">
				<section class="vf-news-content">
					<div class="vf-news-section-head">
						<div>
							<p class="vf-eyebrow">Latest News</p>
							<h2>Tin mới nhất</h2>
						</div>
						<span><?php echo esc_html( count( $latest ) ); ?> bài viết</span>
					</div>

					<div class="vf-news-grid">
						<?php foreach ( $latest as $article ) : ?>
							<?php echo $this->render_news_card( $article ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endforeach; ?>
					</div>
				</section>

				<aside class="vf-news-sidebar">
					<div class="vf-news-widget">
						<h3>Bài viết mới</h3>
						<div class="vf-news-mini-list">
							<?php foreach ( array_slice( $latest, 0, 4 ) as $article ) : ?>
								<a href="<?php echo esc_url( $this->get_news_article_url( $article['slug'] ) ); ?>">
									<img src="<?php echo esc_url( $article['image'] ); ?>" alt="<?php echo esc_attr( $article['alt'] ); ?>">
									<span>
										<strong><?php echo esc_html( $article['title'] ); ?></strong>
										<small><?php echo esc_html( $article['date'] ); ?></small>
									</span>
								</a>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="vf-news-widget">
						<h3>Danh mục</h3>
						<div class="vf-news-category-list">
							<?php foreach ( $categories as $category ) : ?>
								<span><?php echo esc_html( $category ); ?></span>
							<?php endforeach; ?>
						</div>
					</div>
				</aside>
			</main>

			<section class="vf-news-container vf-news-trending">
				<div class="vf-news-section-head">
					<div>
						<p class="vf-eyebrow">Trending</p>
						<h2>Đang được quan tâm</h2>
					</div>
				</div>
				<div class="vf-news-trending-list">
					<?php foreach ( $trending as $article ) : ?>
						<?php echo $this->render_news_card( $article, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endforeach; ?>
				</div>
			</section>

		</div>
		<?php
		return ob_get_clean();
	}

	private function render_news_detail( $article, $articles ) {
		$related = array();
		foreach ( $articles as $item ) {
			if ( $item['slug'] !== $article['slug'] && $item['category'] === $article['category'] ) {
				$related[] = $item;
			}
		}
		foreach ( $articles as $item ) {
			if ( count( $related ) >= 3 ) {
				break;
			}
			if ( $item['slug'] !== $article['slug'] && ! in_array( $item, $related, true ) ) {
				$related[] = $item;
			}
		}

		ob_start();
		?>
		<div class="vf-page vf-news-page vf-news-detail-page">
			<article class="vf-news-article">
				<header class="vf-news-article-head vf-news-fade">
					<a class="vf-news-back" href="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>">← Quay lại tin tức</a>
					<div class="vf-news-meta">
						<span><?php echo esc_html( $article['category'] ); ?></span>
						<span><?php echo esc_html( $article['date'] ); ?></span>
						<span><?php echo esc_html( $article['read_time'] ); ?></span>
					</div>
					<h1><?php echo esc_html( $article['title'] ); ?></h1>
					<p><?php echo esc_html( $article['excerpt'] ); ?></p>
				</header>

				<figure class="vf-news-cover">
					<img src="<?php echo esc_url( $article['image'] ); ?>" alt="<?php echo esc_attr( $article['alt'] ); ?>">
				</figure>

				<div class="vf-news-article-layout">
					<div class="vf-news-article-body">
						<?php foreach ( $article['content'] as $paragraph ) : ?>
							<p><?php echo esc_html( $paragraph ); ?></p>
						<?php endforeach; ?>
					</div>
					<aside class="vf-news-article-note">
						<h2>Điểm chính</h2>
						<?php foreach ( $article['highlights'] as $highlight ) : ?>
							<span><?php echo esc_html( $highlight ); ?></span>
						<?php endforeach; ?>
					</aside>
				</div>
			</article>

			<section class="vf-news-container vf-news-related">
				<div class="vf-news-section-head">
					<div>
						<p class="vf-eyebrow">Related</p>
						<h2>Bài viết liên quan</h2>
					</div>
				</div>
				<div class="vf-news-grid vf-news-grid-related">
					<?php foreach ( array_slice( $related, 0, 3 ) as $item ) : ?>
						<?php echo $this->render_news_card( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endforeach; ?>
				</div>
			</section>

		</div>
		<?php
		return ob_get_clean();
	}

	private function render_news_card( $article, $compact = false ) {
		$class = $compact ? 'vf-news-card vf-news-card-horizontal vf-news-fade' : 'vf-news-card vf-news-fade';

		ob_start();
		?>
		<article class="<?php echo esc_attr( $class ); ?>">
			<a class="vf-news-card-media" href="<?php echo esc_url( $this->get_news_article_url( $article['slug'] ) ); ?>">
				<img src="<?php echo esc_url( $article['image'] ); ?>" alt="<?php echo esc_attr( $article['alt'] ); ?>" loading="lazy">
			</a>
			<div class="vf-news-card-body">
				<div class="vf-news-meta">
					<span><?php echo esc_html( $article['category'] ); ?></span>
					<span><?php echo esc_html( $article['date'] ); ?></span>
				</div>
				<h3><a href="<?php echo esc_url( $this->get_news_article_url( $article['slug'] ) ); ?>"><?php echo esc_html( $article['title'] ); ?></a></h3>
				<p><?php echo esc_html( $article['excerpt'] ); ?></p>
				<a class="vf-news-readmore" href="<?php echo esc_url( $this->get_news_article_url( $article['slug'] ) ); ?>">Đọc tiếp</a>
			</div>
		</article>
		<?php
		return ob_get_clean();
	}

	public function render_global_footer() {
		if ( is_admin() ) {
			return;
		}

		echo $this->render_site_footer(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	private function render_site_footer() {
		$links = array(
			'Trang chủ'  => home_url( '/' ),
			'Tin tức'   => home_url( '/tin-tuc/' ),
			'Đánh giá xe' => home_url( '/tin-tuc/?bai-viet=chon-xe-dien-phu-hop' ),
			'Công nghệ' => home_url( '/tin-tuc/?bai-viet=vf-8-2026-cong-nghe-ai' ),
			'Liên hệ'   => 'mailto:thanhnguyenvan762@gmail.com',
		);

		$socials = array(
			'Facebook'  => array( 'url' => '#', 'label' => 'f' ),
			'YouTube'   => array( 'url' => '#', 'label' => 'yt' ),
			'TikTok'    => array( 'url' => '#', 'label' => 'tt' ),
			'Instagram' => array( 'url' => '#', 'label' => 'ig' ),
		);

		ob_start();
		?>
		<footer class="vf-site-footer" role="contentinfo">
			<div class="vf-footer-inner">
				<section class="vf-footer-brand" aria-label="VinFast News">
					<a class="vf-footer-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<img src="<?php echo esc_url( $this->get_brand_logo_url() ); ?>" alt="VinFast News">
					</a>
					<span>Driving The Future</span>
					<p>Cập nhật tin tức xe điện, công nghệ và xu hướng EV mới nhất.</p>
				</section>

				<nav class="vf-footer-col" aria-label="Liên kết nhanh">
					<h2>Quick Links</h2>
					<?php foreach ( $links as $label => $url ) : ?>
						<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</nav>

				<section class="vf-footer-col vf-footer-contact" aria-label="Thông tin liên hệ">
					<h2>Liên hệ</h2>
					<p><strong>Email</strong><a href="mailto:thanhnguyenvan762@gmail.com">thanhnguyenvan762@gmail.com</a></p>
					<p><strong>Phone</strong><a href="tel:0123456789">0123 456 789</a></p>
					<p><strong>Địa chỉ</strong><span>Hà Nội, Việt Nam</span></p>
				</section>

				<section class="vf-footer-col vf-footer-connect" aria-label="Mạng xã hội và newsletter">
					<h2>Social Media</h2>
					<div class="vf-footer-social">
						<?php foreach ( $socials as $name => $social ) : ?>
							<a href="<?php echo esc_url( $social['url'] ); ?>" aria-label="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $social['label'] ); ?></a>
						<?php endforeach; ?>
					</div>
					<form class="vf-footer-newsletter" action="<?php echo esc_url( home_url( '/tin-tuc/' ) ); ?>" method="get">
						<label for="vf-footer-email">Newsletter</label>
						<div>
							<input id="vf-footer-email" type="email" name="email" placeholder="Nhập email của bạn" aria-label="Nhập email của bạn">
							<button type="submit">Đăng ký</button>
						</div>
					</form>
				</section>
			</div>

			<div class="vf-footer-bottom">
				<span>© 2026 VinFast News. All Rights Reserved.</span>
				<span>Electric Mobility For Everyone</span>
			</div>
		</footer>
		<?php
		return ob_get_clean();
	}

	private function get_news_article_url( $slug ) {
		return add_query_arg( 'bai-viet', rawurlencode( $slug ), home_url( '/tin-tuc/' ) );
	}

	private function get_news_articles() {
		$images = $this->get_product_images();

		return array(
			'vf-8-2026-cong-nghe-ai' => array(
				'slug'       => 'vf-8-2026-cong-nghe-ai',
				'title'      => 'VF 8 2026 ra mắt với công nghệ AI mới',
				'excerpt'    => 'VinFast nâng cấp trải nghiệm lái với trợ lý thông minh, hệ thống hỗ trợ người lái và khoang xe kết nối hơn.',
				'date'       => '15/05/2026',
				'read_time'  => '4 phút đọc',
				'category'   => 'Công nghệ',
				'image'      => $images['VF8'],
				'alt'        => 'VinFast VF 8 màu đỏ',
				'highlights' => array( 'AI hỗ trợ lái và điều khiển xe', 'Giao diện cabin trực quan hơn', 'Tập trung vào an toàn chủ động' ),
				'content'    => array(
					'Phiên bản VF 8 2026 được xây dựng theo hướng thông minh và dễ sử dụng hơn, ưu tiên các thao tác thường ngày như điều hướng, kiểm soát tiện nghi và cảnh báo an toàn.',
					'Không gian nội thất tiếp tục giữ tinh thần tối giản, kết hợp màn hình trung tâm lớn với các lớp thông tin rõ ràng để người lái không bị quá tải khi di chuyển trong đô thị hoặc trên cao tốc.',
					'Các nâng cấp về phần mềm giúp mẫu SUV điện này phù hợp hơn với nhóm khách hàng gia đình, người dùng công nghệ và những ai muốn một chiếc xe có khả năng cập nhật lâu dài.',
				),
			),
			'vf-3-xe-dien-do-thi'     => array(
				'slug'       => 'vf-3-xe-dien-do-thi',
				'title'      => 'VF 3 và xu hướng xe điện đô thị nhỏ gọn',
				'excerpt'    => 'Thiết kế gọn, chi phí vận hành thấp và khả năng xoay trở linh hoạt giúp VF 3 phù hợp nhịp sống thành phố.',
				'date'       => '14/05/2026',
				'read_time'  => '3 phút đọc',
				'category'   => 'Thị trường',
				'image'      => $images['VF3'],
				'alt'        => 'VinFast VF 3',
				'highlights' => array( 'Kích thước phù hợp đường phố', 'Dễ tiếp cận với người dùng mới', 'Tối ưu chi phí sử dụng hằng ngày' ),
				'content'    => array(
					'Nhóm xe điện đô thị đang nhận được nhiều quan tâm vì đáp ứng đúng nhu cầu đi lại ngắn, đỗ xe thuận tiện và tiết kiệm chi phí nhiên liệu.',
					'VF 3 nổi bật nhờ thiết kế cá tính nhưng vẫn giữ kích thước nhỏ gọn, phù hợp người dùng cá nhân, gia đình trẻ hoặc khách hàng cần chiếc xe thứ hai trong nhà.',
					'Khi hạ tầng sạc ngày càng phổ biến, dòng xe nhỏ như VF 3 có thêm lợi thế để trở thành lựa chọn thực tế cho các tuyến di chuyển hằng ngày.',
				),
			),
			'he-sinh-thai-tram-sac'   => array(
				'slug'       => 'he-sinh-thai-tram-sac',
				'title'      => 'Hệ sinh thái trạm sạc giúp xe điện dễ dùng hơn',
				'excerpt'    => 'Mạng lưới sạc, thanh toán số và thói quen lập kế hoạch hành trình là ba yếu tố khiến người dùng tự tin hơn với xe điện.',
				'date'       => '13/05/2026',
				'read_time'  => '5 phút đọc',
				'category'   => 'Hạ tầng',
				'image'      => $images['VF6'],
				'alt'        => 'VinFast VF 6',
				'highlights' => array( 'Trạm sạc phủ rộng hơn', 'Thanh toán nhanh qua ứng dụng', 'Lập hành trình thuận tiện' ),
				'content'    => array(
					'Một chiếc xe điện chỉ thực sự dễ dùng khi người lái có thể chủ động tìm điểm sạc và dự đoán thời gian nạp năng lượng trong mỗi hành trình.',
					'Các trạm sạc tại đô thị, trung tâm thương mại và tuyến đường lớn giúp giảm tâm lý lo lắng về quãng đường, đặc biệt với người mới chuyển từ xe xăng sang xe điện.',
					'Khi kết hợp với các tính năng bản đồ và thanh toán số, việc sạc xe trở thành một phần tự nhiên trong lịch trình di chuyển thay vì là một bước phức tạp.',
				),
			),
			'vf-mpv-7-gia-dinh'       => array(
				'slug'       => 'vf-mpv-7-gia-dinh',
				'title'      => 'VF MPV 7 hướng tới gia đình và dịch vụ',
				'excerpt'    => 'Không gian 7 chỗ, cấu hình linh hoạt và vận hành điện là những điểm đáng chú ý của nhóm MPV thế hệ mới.',
				'date'       => '12/05/2026',
				'read_time'  => '4 phút đọc',
				'category'   => 'Sản phẩm',
				'image'      => $images['VFMPV7'],
				'alt'        => 'VinFast VF MPV 7',
				'highlights' => array( 'Bố trí 7 chỗ linh hoạt', 'Phù hợp gia đình lớn', 'Tối ưu cho dịch vụ di chuyển' ),
				'content'    => array(
					'Phân khúc MPV luôn cần sự cân bằng giữa không gian, chi phí vận hành và độ tiện dụng. Với nền tảng điện, VF MPV 7 có thêm lợi thế về độ êm và chi phí sử dụng.',
					'Khoang xe rộng giúp mẫu xe này phù hợp cho gia đình đông thành viên, nhóm khách cần di chuyển đường dài hoặc mô hình kinh doanh vận chuyển cao cấp.',
					'Thiết kế blog và cửa hàng trên website cũng được tối ưu để người dùng có thể xem thông tin, thêm vào giỏ hàng và thanh toán QR nhanh chóng.',
				),
			),
			'bao-duong-xe-dien'       => array(
				'slug'       => 'bao-duong-xe-dien',
				'title'      => 'Bảo dưỡng xe điện cần chú ý những gì?',
				'excerpt'    => 'Xe điện có ít chi tiết cơ khí hơn, nhưng người dùng vẫn nên theo dõi pin, lốp, phanh và phần mềm định kỳ.',
				'date'       => '11/05/2026',
				'read_time'  => '4 phút đọc',
				'category'   => 'Kinh nghiệm',
				'image'      => $images['VF7'],
				'alt'        => 'VinFast VF 7',
				'highlights' => array( 'Kiểm tra pin định kỳ', 'Theo dõi lốp và phanh', 'Cập nhật phần mềm khi có bản mới' ),
				'content'    => array(
					'So với xe động cơ đốt trong, xe điện giảm đáng kể các hạng mục liên quan đến dầu máy, bugi hoặc hệ thống xả. Điều này giúp lịch bảo dưỡng đơn giản hơn.',
					'Dù vậy, người dùng vẫn cần quan tâm đến tình trạng pin, áp suất lốp, má phanh và các cảnh báo phần mềm để xe luôn hoạt động ổn định.',
					'Việc giữ thói quen sạc hợp lý, không để pin cạn quá sâu trong thời gian dài và kiểm tra xe đúng lịch sẽ giúp kéo dài tuổi thọ hệ thống truyền động điện.',
				),
			),
			'chon-xe-dien-phu-hop'    => array(
				'slug'       => 'chon-xe-dien-phu-hop',
				'title'      => 'Cách chọn xe điện phù hợp nhu cầu',
				'excerpt'    => 'Từ xe đô thị nhỏ gọn đến SUV cao cấp, người mua nên bắt đầu từ quãng đường di chuyển, số chỗ và ngân sách.',
				'date'       => '10/05/2026',
				'read_time'  => '5 phút đọc',
				'category'   => 'Tư vấn',
				'image'      => $images['VF9'],
				'alt'        => 'VinFast VF 9',
				'highlights' => array( 'Xác định nhu cầu chính', 'So sánh ngân sách sở hữu', 'Chọn kích thước xe theo gia đình' ),
				'content'    => array(
					'Người mua xe điện nên bắt đầu bằng câu hỏi xe sẽ được dùng cho việc gì nhiều nhất: đi làm hằng ngày, chở gia đình, đi xa hay phục vụ công việc.',
					'Với nhu cầu đô thị, các mẫu nhỏ gọn giúp dễ đỗ xe và tiết kiệm chi phí. Với gia đình đông người, SUV lớn hoặc MPV sẽ tạo sự thoải mái hơn trong hành trình dài.',
					'Ngoài giá bán, người mua cũng nên tính đến chi phí sạc, bảo dưỡng, bảo hiểm và hạ tầng sạc gần nhà để lựa chọn thực tế hơn.',
				),
			),
		);
	}

	public function enqueue_assets() {
		$base_url = plugin_dir_url( __FILE__ );
		wp_enqueue_style( 'vf-mpv7-landing', $base_url . 'assets/css/vf-mpv7.css', array( 'flatsome-main', 'flatsome-shop', 'flatsome-style' ), self::VERSION );
		wp_enqueue_script( 'vf-mpv7-landing', $base_url . 'assets/js/vf-mpv7.js', array(), self::VERSION, true );
		wp_localize_script(
			'vf-mpv7-landing',
			'vfMpv7Cart',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	public function exclude_products_from_coming_soon( $is_excluded ) {
		if ( function_exists( 'is_product' ) && is_product() ) {
			return true;
		}

		if (
			( function_exists( 'is_shop' ) && is_shop() )
			|| ( function_exists( 'is_cart' ) && is_cart() )
			|| ( function_exists( 'is_checkout' ) && is_checkout() )
			|| ( function_exists( 'is_account_page' ) && is_account_page() )
		) {
			return true;
		}

		$current_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		foreach ( array( '/cua-hang', '/gio-hang', '/thanh-toan', '/tai-khoan', '/san-pham' ) as $public_path ) {
			if ( false !== strpos( $current_uri, $public_path ) ) {
				return true;
			}
		}

		return $is_excluded;
	}

	public function setup_single_product_layout() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
		add_action( 'woocommerce_before_single_product_summary', array( $this, 'render_single_product_gallery' ), 20 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'render_single_product_badges' ), 7 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'render_single_product_specs' ), 35 );
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'render_single_product_details' ), 8 );
	}

	public function setup_account_features() {
		$account_options = array(
			'woocommerce_enable_myaccount_registration'       => 'yes',
			'woocommerce_enable_signup_and_login_from_checkout' => 'yes',
			'woocommerce_enable_checkout_login_reminder'      => 'yes',
			'woocommerce_registration_generate_username'      => 'no',
			'woocommerce_registration_generate_password'      => 'no',
		);

		foreach ( $account_options as $option => $value ) {
			if ( $value !== get_option( $option ) ) {
				update_option( $option, $value );
			}
		}
	}

	public function account_menu_items( $items ) {
		$labels = array(
			'dashboard'       => 'Tổng quan',
			'orders'          => 'Đơn hàng',
			'downloads'       => 'Tải xuống',
			'edit-address'    => 'Địa chỉ',
			'payment-methods' => 'Phương thức thanh toán',
			'edit-account'    => 'Thông tin tài khoản',
			'customer-logout' => 'Đăng xuất',
		);

		$ordered_items = array();
		foreach ( $labels as $endpoint => $label ) {
			if ( isset( $items[ $endpoint ] ) ) {
				$ordered_items[ $endpoint ] = $label;
			}
		}

		foreach ( $items as $endpoint => $label ) {
			if ( ! isset( $ordered_items[ $endpoint ] ) ) {
				$ordered_items[ $endpoint ] = $label;
			}
		}

		return $ordered_items;
	}

	public function render_account_guest_intro() {
		if ( function_exists( 'is_account_page' ) && ! is_account_page() ) {
			return;
		}

		$shop_url = home_url( '/cua-hang/' );
		$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/gio-hang/' );
		?>
		<section class="vf-account-hero">
			<div>
				<div class="vf-account-brand">
					<img src="<?php echo esc_url( $this->get_brand_logo_url() ); ?>" alt="VinFast">
					<span>Tài khoản khách hàng</span>
				</div>
				<h1>Tài khoản VinFast</h1>
				<p>Đăng nhập hoặc tạo tài khoản để theo dõi đơn hàng, lưu địa chỉ giao nhận và thanh toán nhanh hơn trong những lần mua tiếp theo.</p>
				<div class="vf-account-actions">
					<a class="vf-account-primary" href="<?php echo esc_url( $shop_url ); ?>">Vào cửa hàng</a>
					<a class="vf-account-secondary" href="<?php echo esc_url( $cart_url ); ?>">Xem giỏ hàng</a>
				</div>
			</div>
			<div class="vf-account-benefits" aria-label="Chức năng tài khoản">
				<span>Quản lý đơn hàng</span>
				<span>Lưu địa chỉ giao xe</span>
				<span>Thanh toán QR</span>
				<span>Cập nhật thông tin</span>
			</div>
		</section>
		<?php
	}

	public function render_account_member_intro() {
		if ( function_exists( 'is_account_page' ) && ! is_account_page() ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		$user       = wp_get_current_user();
		$name       = $user && $user->display_name ? $user->display_name : $user->user_login;
		$shop_url   = home_url( '/cua-hang/' );
		$orders_url = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : home_url( '/tai-khoan/' );
		?>
		<section class="vf-account-member-hero">
			<div class="vf-account-brand">
				<img src="<?php echo esc_url( $this->get_brand_logo_url() ); ?>" alt="VinFast">
				<span>Tài khoản VinFast</span>
			</div>
			<div>
				<h1>Xin chào, <?php echo esc_html( $name ); ?></h1>
				<p>Quản lý đơn hàng, địa chỉ giao nhận, thông tin đăng nhập và tiếp tục mua xe VinFast từ cùng một nơi.</p>
			</div>
			<div class="vf-account-actions">
				<a class="vf-account-primary" href="<?php echo esc_url( $shop_url ); ?>">Vào cửa hàng</a>
				<a class="vf-account-secondary" href="<?php echo esc_url( $orders_url ); ?>">Xem đơn hàng</a>
			</div>
		</section>
		<?php
	}

	public function render_single_product_gallery() {
		$product = wc_get_product( get_the_ID() );
		if ( ! $product ) {
			return;
		}

		$image_url = $this->get_single_product_image_url( $product );
		if ( ! $image_url ) {
			if ( function_exists( 'woocommerce_show_product_images' ) ) {
				woocommerce_show_product_images();
			}
			return;
		}

		$detail = $this->get_product_detail_data( $product->get_sku() );
		?>
		<div class="vf-single-gallery">
			<div class="vf-single-gallery-main">
				<span>Ảnh sản phẩm</span>
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>">
			</div>
			<div class="vf-single-gallery-strip">
				<div class="vf-single-thumb is-active">
					<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>">
				</div>
				<div class="vf-single-gallery-note">
					<strong><?php echo esc_html( $detail['segment'] ); ?></strong>
					<span><?php echo esc_html( $detail['body'] ); ?></span>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_single_product_badges() {
		$product = wc_get_product( get_the_ID() );
		if ( ! $product ) {
			return;
		}

		$sku    = $product->get_sku();
		$detail = $this->get_product_detail_data( $sku );
		?>
		<div class="vf-single-badges">
			<span><?php echo esc_html( $this->get_product_group_label( $this->get_product_group( $sku ) ) ); ?></span>
			<span><?php echo esc_html( $detail['body'] ); ?></span>
			<span>Bảo hành chính hãng</span>
		</div>
		<?php
	}

	public function render_single_product_specs() {
		$product = wc_get_product( get_the_ID() );
		if ( ! $product ) {
			return;
		}

		$detail = $this->get_product_detail_data( $product->get_sku() );
		$specs  = array(
			'Phân khúc'    => $detail['segment'],
			'Số chỗ'       => $detail['seats'],
			'Vận hành'     => $detail['drive'],
			'Thanh toán'   => 'QR / chuyển khoản',
		);
		?>
		<div class="vf-single-specs">
			<?php foreach ( $specs as $label => $value ) : ?>
				<div>
					<span><?php echo esc_html( $label ); ?></span>
					<strong><?php echo esc_html( $value ); ?></strong>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	public function render_single_product_details() {
		$product = wc_get_product( get_the_ID() );
		if ( ! $product ) {
			return;
		}

		$detail = $this->get_product_detail_data( $product->get_sku() );
		?>
		<section class="vf-single-detail-panel">
			<div class="vf-single-detail-copy">
				<p class="vf-eyebrow">Chi tiết sản phẩm</p>
				<h2><?php echo esc_html( $detail['headline'] ); ?></h2>
				<p><?php echo esc_html( $detail['description'] ); ?></p>
			</div>
			<div class="vf-single-highlight-grid">
				<?php foreach ( $detail['highlights'] as $highlight ) : ?>
					<div>
						<strong><?php echo esc_html( $highlight['title'] ); ?></strong>
						<span><?php echo esc_html( $highlight['text'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}

	public function render_global_store_header() {
		echo $this->render_store_header( $this->get_current_header_active(), true );
	}

	private function get_current_header_active() {
		$current_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		if ( false !== strpos( $current_uri, '/cua-hang' ) ) {
			return 'shop';
		}

		if ( false !== strpos( $current_uri, '/tin-tuc' ) ) {
			return 'news';
		}

		if ( false !== strpos( $current_uri, '/gio-hang' ) ) {
			return 'cart';
		}

		if ( false !== strpos( $current_uri, '/thanh-toan' ) ) {
			return 'checkout';
		}

		if ( false !== strpos( $current_uri, '/tai-khoan' ) || false !== strpos( $current_uri, '/my-account' ) ) {
			return 'account';
		}

		return is_front_page() ? 'home' : '';
	}

	private function get_brand_logo_url() {
		return plugin_dir_url( __FILE__ ) . 'assets/images/vinfast-brand-logo.png';
	}

	private function render_store_header( $active = '', $global = false ) {
		$shop_url     = home_url( '/cua-hang/' );
		$news_url     = home_url( '/tin-tuc/' );
		$cart_url     = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/gio-hang/' );
		$checkout_url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/thanh-toan/' );
		$account_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/tai-khoan/' );
		$classes      = 'vf-ch-page vf-shared-header' . ( $global ? ' vf-global-header' : '' );

		ob_start();
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
		<header class="vf-ch-navbar">
			<a class="vf-ch-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img src="<?php echo esc_url( $this->get_brand_logo_url() ); ?>" alt="VinFast">
			</a>
			<nav class="vf-ch-nav">
				<a class="<?php echo 'home' === $active ? 'is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>">Trang chủ</a>
				<a href="<?php echo esc_url( home_url( '/#vf-ch-categories' ) ); ?>">Danh mục</a>
				<a href="<?php echo esc_url( home_url( '/#vf-ch-products' ) ); ?>">Sản phẩm</a>
				<a class="<?php echo 'shop' === $active ? 'is-active' : ''; ?>" href="<?php echo esc_url( $shop_url ); ?>">Cửa hàng</a>
				<a class="<?php echo 'news' === $active ? 'is-active' : ''; ?>" href="<?php echo esc_url( $news_url ); ?>">Tin tức</a>
				<a class="<?php echo 'cart' === $active ? 'is-active' : ''; ?>" href="<?php echo esc_url( $cart_url ); ?>">Giỏ hàng</a>
				<a class="<?php echo 'account' === $active ? 'is-active' : ''; ?>" href="<?php echo esc_url( $account_url ); ?>">Tài khoản</a>
			</nav>
		</header>
		</div>
		<?php
		return ob_get_clean();
	}

	public function remove_deposit_menu_item( $items ) {
		return array_values(
			array_filter(
				$items,
				function ( $item ) {
					$title = isset( $item->title ) ? wp_strip_all_tags( $item->title ) : '';
					$url   = isset( $item->url ) ? $item->url : '';

					return false === stripos( $title, 'Đặt cọc VF MPV 7' )
						&& false === stripos( $url, 'vf-mpv7-dat-coc' );
				}
			)
		);
	}

	private function get_product_images() {
		$base         = plugin_dir_url( __FILE__ ) . 'assets/images/car/';
		$product_base = plugin_dir_url( __FILE__ ) . 'assets/images/product/';
		return array(
			'VF3'    => $base . 'vf3.jpg',
			'VF5'    => $base . 'vf5.jpg',
			'VF6'    => $base . 'vf6.jpg',
			'VF7'    => $base . 'vf7.jpg',
			'VF8'    => $base . 'vf8.jpg',
			'VF9'    => $base . 'vf9.jpg',
			'VFMPV7' => $base . 'vf-mpv7.jpg',
			'VFM_GENERIC' => $product_base . 'electric-motorbike.svg',
			'VF_ACC_GENERIC' => $product_base . 'accessory.svg',
			'VFC_GENERIC' => $product_base . 'electric-car.svg',
		);
	}

	private function get_product_image_url_by_sku( $sku ) {
		$images = $this->get_product_images();

		if ( isset( $images[ $sku ] ) ) {
			return $images[ $sku ];
		}

		if ( 0 === strpos( $sku, 'VFM-' ) ) {
			return $images['VFM_GENERIC'];
		}

		if ( 0 === strpos( $sku, 'VF-ACC-' ) ) {
			return $images['VF_ACC_GENERIC'];
		}

		if ( 0 === strpos( $sku, 'VFC-' ) ) {
			return $images['VFC_GENERIC'];
		}

		return '';
	}

	private function get_single_product_image_url( $product ) {
		if ( ! $product ) {
			return '';
		}

		return $this->get_product_image_url_by_sku( $product->get_sku() );
	}

	private function get_product_detail_data( $sku ) {
		$details = array(
			'VF3'    => array(
				'segment'     => 'Xe điện đô thị',
				'body'        => 'Mini SUV',
				'seats'       => '4 chỗ',
				'drive'       => 'Linh hoạt trong phố',
				'headline'    => 'VinFast VF 3 nhỏ gọn cho nhu cầu đi lại hằng ngày',
				'description' => 'Thiết kế nhỏ gọn, dễ xoay trở trong đô thị và phù hợp với khách hàng cần một mẫu xe điện cá tính, tiết kiệm chi phí vận hành.',
				'highlights'  => array(
					array( 'title' => 'Dễ sử dụng', 'text' => 'Kích thước gọn, phù hợp đường phố đông và bãi đỗ nhỏ.' ),
					array( 'title' => 'Chi phí tốt', 'text' => 'Tối ưu cho nhu cầu đi lại cá nhân và gia đình nhỏ.' ),
					array( 'title' => 'Đặt mua nhanh', 'text' => 'Thêm vào giỏ hàng và thanh toán QR ngay trên website.' ),
				),
			),
			'VF5'    => array(
				'segment'     => 'SUV phổ thông',
				'body'        => 'SUV hạng A',
				'seats'       => '5 chỗ',
				'drive'       => 'Đi phố và gia đình',
				'headline'    => 'VinFast VF 5 cân bằng giữa tiện dụng và chi phí',
				'description' => 'Không gian 5 chỗ, kiểu dáng SUV gọn gàng và khả năng vận hành điện phù hợp với gia đình trẻ hoặc nhu cầu đi lại thường xuyên.',
				'highlights'  => array(
					array( 'title' => 'Không gian 5 chỗ', 'text' => 'Phù hợp gia đình nhỏ, đi làm và di chuyển cuối tuần.' ),
					array( 'title' => 'SUV gọn gàng', 'text' => 'Dễ lái trong phố nhưng vẫn có tư thế ngồi cao.' ),
					array( 'title' => 'Bảo hành chính hãng', 'text' => 'Mua hàng qua hệ thống WooCommerce của website.' ),
				),
			),
			'VF6'    => array(
				'segment'     => 'SUV trung cấp',
				'body'        => 'SUV hạng B',
				'seats'       => '5 chỗ',
				'drive'       => 'Đa dụng hằng ngày',
				'headline'    => 'VinFast VF 6 cho khách hàng cần nhiều không gian hơn',
				'description' => 'Kiểu dáng hiện đại, khoang cabin 5 chỗ và nhóm trang bị phù hợp cho người dùng muốn nâng cấp từ xe đô thị lên SUV rộng rãi hơn.',
				'highlights'  => array(
					array( 'title' => 'Thiết kế hiện đại', 'text' => 'Ngoại hình cân đối, phù hợp cả khách hàng cá nhân và gia đình.' ),
					array( 'title' => 'Khoang xe rộng', 'text' => 'Không gian sử dụng thoải mái hơn các dòng đô thị nhỏ.' ),
					array( 'title' => 'Thanh toán thuận tiện', 'text' => 'Hỗ trợ quy trình giỏ hàng và chuyển khoản QR.' ),
				),
			),
			'VF7'    => array(
				'segment'     => 'SUV trung cấp',
				'body'        => 'SUV hạng C',
				'seats'       => '5 chỗ',
				'drive'       => 'Cá tính và mạnh mẽ',
				'headline'    => 'VinFast VF 7 nổi bật với phong cách thể thao',
				'description' => 'VF 7 hướng tới khách hàng thích thiết kế sắc nét, cảm giác lái tự tin và một mẫu SUV điện có cá tính rõ ràng.',
				'highlights'  => array(
					array( 'title' => 'Dáng xe thể thao', 'text' => 'Thiết kế nổi bật, tạo dấu ấn khi sử dụng hằng ngày.' ),
					array( 'title' => 'SUV 5 chỗ', 'text' => 'Phù hợp nhu cầu cá nhân, gia đình và công việc.' ),
					array( 'title' => 'Dễ so sánh', 'text' => 'Nằm trong nhóm trung cấp để khách hàng so với VF 6.' ),
				),
			),
			'VF8'    => array(
				'segment'     => 'SUV cao cấp',
				'body'        => 'SUV hạng D',
				'seats'       => '5 chỗ',
				'drive'       => 'Đường dài và gia đình',
				'headline'    => 'VinFast VF 8 dành cho trải nghiệm cao cấp hơn',
				'description' => 'Mẫu SUV điện rộng rãi, phù hợp khách hàng cần sự thoải mái, nhiều trang bị và khả năng phục vụ những chuyến đi xa.',
				'highlights'  => array(
					array( 'title' => 'Không gian rộng', 'text' => 'Tối ưu cho gia đình cần khoang xe thoải mái.' ),
					array( 'title' => 'Trang bị cao', 'text' => 'Phù hợp nhóm khách hàng muốn trải nghiệm cao cấp.' ),
					array( 'title' => 'Mua hàng rõ ràng', 'text' => 'Giá và giỏ hàng được hiển thị trực tiếp trước khi thanh toán.' ),
				),
			),
			'VF9'    => array(
				'segment'     => 'SUV cao cấp',
				'body'        => 'SUV 3 hàng ghế',
				'seats'       => '6-7 chỗ',
				'drive'       => 'Gia đình lớn',
				'headline'    => 'VinFast VF 9 cho gia đình cần không gian lớn',
				'description' => 'Dòng SUV điện cỡ lớn với ba hàng ghế, phù hợp gia đình đông người hoặc khách hàng cần khoang xe rộng và tiện nghi.',
				'highlights'  => array(
					array( 'title' => 'Ba hàng ghế', 'text' => 'Tối ưu khi di chuyển cùng nhiều thành viên.' ),
					array( 'title' => 'Khoang xe lớn', 'text' => 'Phù hợp chuyến đi xa và nhu cầu sử dụng cao cấp.' ),
					array( 'title' => 'Hỗ trợ đặt mua', 'text' => 'Thêm vào giỏ hàng, kiểm tra tổng tiền và thanh toán QR.' ),
				),
			),
			'VFMPV7' => array(
				'segment'     => 'MPV / dịch vụ',
				'body'        => 'MPV 7 chỗ',
				'seats'       => '7 chỗ',
				'drive'       => 'Gia đình và dịch vụ',
				'headline'    => 'VinFast VF MPV 7 hướng tới không gian linh hoạt',
				'description' => 'Cấu hình 7 chỗ giúp mẫu MPV này phù hợp cho gia đình lớn, kinh doanh dịch vụ hoặc khách hàng cần không gian chở người linh hoạt.',
				'highlights'  => array(
					array( 'title' => '7 chỗ linh hoạt', 'text' => 'Phục vụ tốt nhu cầu gia đình và vận chuyển hành khách.' ),
					array( 'title' => 'Tối ưu dịch vụ', 'text' => 'Không gian rộng, dễ dùng cho mô hình kinh doanh.' ),
					array( 'title' => 'Quy trình nhanh', 'text' => 'Đặt mua qua giỏ hàng và thanh toán chuyển khoản QR.' ),
				),
			),
		);

		if ( isset( $details[ $sku ] ) ) {
			return $details[ $sku ];
		}

		if ( 0 === strpos( $sku, 'VFM-' ) ) {
			return array(
				'segment'     => 'Xe máy điện',
				'body'        => 'Phương tiện đô thị',
				'seats'       => '2 chỗ',
				'drive'       => 'Linh hoạt hằng ngày',
				'headline'    => 'Xe máy điện VinFast cho di chuyển đô thị',
				'description' => 'Sản phẩm xe máy điện phù hợp nhu cầu đi học, đi làm và di chuyển ngắn trong thành phố với chi phí vận hành tiết kiệm.',
				'highlights'  => array(
					array( 'title' => 'Dễ sử dụng', 'text' => 'Kích thước gọn, phù hợp đường phố và bãi đỗ nhỏ.' ),
					array( 'title' => 'Chi phí tốt', 'text' => 'Vận hành điện êm, tiết kiệm cho nhu cầu hằng ngày.' ),
					array( 'title' => 'Thanh toán QR', 'text' => 'Thêm vào giỏ hàng và chuyển khoản QR ngay trên website.' ),
				),
			);
		}

		if ( 0 === strpos( $sku, 'VF-ACC-' ) ) {
			return array(
				'segment'     => 'Phụ kiện VinFast',
				'body'        => 'Phụ kiện chính hãng',
				'seats'       => 'Theo sản phẩm',
				'drive'       => 'Hỗ trợ sử dụng xe',
				'headline'    => 'Phụ kiện VinFast cho trải nghiệm tiện lợi hơn',
				'description' => 'Phụ kiện hỗ trợ sạc, bảo vệ nội thất và nâng cấp trải nghiệm sử dụng xe điện VinFast hằng ngày.',
				'highlights'  => array(
					array( 'title' => 'Chính hãng', 'text' => 'Thông tin phụ kiện được quản lý trong hệ thống cửa hàng.' ),
					array( 'title' => 'Dễ lựa chọn', 'text' => 'Mô tả ngắn, giá và giỏ hàng hiển thị rõ ràng.' ),
					array( 'title' => 'Thanh toán nhanh', 'text' => 'Hỗ trợ quy trình chuyển khoản QR.' ),
				),
			);
		}

		if ( 0 === strpos( $sku, 'VFC-' ) ) {
			return array(
				'segment'     => 'Ô tô điện',
				'body'        => 'SUV / đô thị',
				'seats'       => 'Theo phiên bản',
				'drive'       => 'Vận hành điện',
				'headline'    => 'Ô tô điện VinFast cho nhu cầu gia đình và công việc',
				'description' => 'Dòng ô tô điện VinFast phù hợp nhiều nhu cầu từ đô thị, gia đình đến đường dài, với thông tin giá và quy trình thanh toán rõ ràng.',
				'highlights'  => array(
					array( 'title' => 'Đa dạng lựa chọn', 'text' => 'Nhiều phiên bản để so sánh theo ngân sách và nhu cầu sử dụng.' ),
					array( 'title' => 'Vận hành điện', 'text' => 'Tối ưu chi phí sử dụng và trải nghiệm lái êm.' ),
					array( 'title' => 'Mua hàng rõ ràng', 'text' => 'Thêm vào giỏ hàng, kiểm tra tổng tiền và thanh toán QR.' ),
				),
			);
		}

		return array(
			'segment'     => 'Xe điện VinFast',
			'body'        => 'Ô tô điện',
			'seats'       => 'Theo phiên bản',
			'drive'       => 'Vận hành điện',
			'headline'    => 'Chi tiết sản phẩm VinFast',
			'description' => 'Sản phẩm được hiển thị trong hệ thống cửa hàng VinFast với thông tin giá, giỏ hàng và thanh toán QR.',
			'highlights'  => array(
				array( 'title' => 'Xe điện', 'text' => 'Phù hợp nhu cầu di chuyển hiện đại.' ),
				array( 'title' => 'Chính hãng', 'text' => 'Thông tin sản phẩm được quản lý trong WooCommerce.' ),
				array( 'title' => 'Thanh toán QR', 'text' => 'Hỗ trợ quy trình chuyển khoản nhanh.' ),
			),
		);
	}

	private function get_product_group( $sku ) {
		if ( 0 === strpos( $sku, 'VFM-' ) ) {
			return 'xe-may-dien';
		}

		if ( 0 === strpos( $sku, 'VF-ACC-' ) ) {
			return 'phu-kien';
		}

		if ( 0 === strpos( $sku, 'VFC-' ) ) {
			return 'o-to-dien';
		}

		if ( in_array( $sku, array( 'VF3', 'VF5' ), true ) ) {
			return 'pho-thong';
		}

		if ( in_array( $sku, array( 'VF6', 'VF7' ), true ) ) {
			return 'trung-cap';
		}

		if ( in_array( $sku, array( 'VF8', 'VF9' ), true ) ) {
			return 'cao-cap';
		}

		if ( 'VFMPV7' === $sku ) {
			return 'mpv-dich-vu';
		}

		return 'pho-thong';
	}

	private function get_product_group_label( $group ) {
		$labels = array(
			'pho-thong'  => 'Phổ thông',
			'trung-cap'  => 'Trung cấp',
			'cao-cap'    => 'Cao cấp',
			'mpv-dich-vu' => 'MPV / Dịch vụ',
			'xe-may-dien' => 'Xe máy điện',
			'phu-kien'    => 'Phụ kiện',
			'o-to-dien'   => 'Ô tô điện',
		);

		return isset( $labels[ $group ] ) ? $labels[ $group ] : 'Phổ thông';
	}

	private function get_shop_specs( $group ) {
		$specs = array(
			'xe-may-dien' => array( 'Động cơ điện', 'Pin thông minh', 'Bảo hành chính hãng' ),
			'phu-kien'    => array( 'Phụ kiện chính hãng', 'Dễ lắp đặt', 'Thanh toán QR' ),
			'o-to-dien'   => array( 'Ô tô điện', 'Pin LFP', 'Thanh toán QR' ),
		);

		return isset( $specs[ $group ] ) ? $specs[ $group ] : array( 'Động cơ điện', 'Bảo hành chính hãng', 'Thanh toán QR' );
	}

	public function replace_product_image( $image, $product, $size, $attr, $placeholder ) {
		if ( ! $product || $product->get_image_id() ) {
			return $image;
		}

		$image_url = $this->get_product_image_url_by_sku( $product->get_sku() );
		if ( ! $image_url ) {
			return $image;
		}

		$class = isset( $attr['class'] ) ? $attr['class'] : 'woocommerce-placeholder wp-post-image';
		return sprintf(
			'<img src="%s" alt="%s" class="%s" loading="lazy">',
			esc_url( $image_url ),
			esc_attr( $product->get_name() ),
			esc_attr( $class . ' vf-wc-product-image' )
		);
	}

	public function get_custom_cart_url( $url ) {
		return home_url( '/gio-hang/' );
	}

	public function register_elementor_widget( $widgets_manager ) {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}

		$widgets_manager->register(
			new class() extends \Elementor\Widget_Base {
				public function get_name() {
					return 'vf_mpv7_landing';
				}

				public function get_title() {
					return 'VF MPV 7 Landing';
				}

				public function get_icon() {
					return 'eicon-car';
				}

				public function get_categories() {
					return array( 'general' );
				}

				protected function render() {
					echo do_shortcode( '[vinfast_mpv7_landing]' );
				}
			}
		);
	}

	public function handle_reservation() {
		if (
			empty( $_POST['vf_mpv7_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vf_mpv7_nonce'] ) ), 'vf_mpv7_reserve' )
		) {
			$this->redirect_with_status( 'error' );
		}

		$required = array( 'customer_name', 'phone', 'email', 'id_number', 'province', 'showroom', 'terms', 'privacy' );
		foreach ( $required as $field ) {
			if ( empty( $_POST[ $field ] ) ) {
				$this->redirect_with_status( 'error' );
			}
		}

		$name  = sanitize_text_field( wp_unslash( $_POST['customer_name'] ) );
		$phone = sanitize_text_field( wp_unslash( $_POST['phone'] ) );
		$email = sanitize_email( wp_unslash( $_POST['email'] ) );

		if ( ! is_email( $email ) ) {
			$this->redirect_with_status( 'error' );
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => self::CPT,
				'post_status' => 'publish',
				'post_title'  => sprintf( 'VF MPV 7 - %s - %s', $name, current_time( 'Y-m-d H:i' ) ),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			$this->redirect_with_status( 'error' );
		}

		$fields = array(
			'owner_type',
			'customer_name',
			'phone',
			'email',
			'id_number',
			'province',
			'showroom',
			'voucher',
			'consultant',
			'vf_version',
			'vf_battery',
			'vf_color',
			'vf_interior',
			'vf_total',
		);

		foreach ( $fields as $field ) {
			$value = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
			update_post_meta( $post_id, '_' . $field, $value );
		}

		$programs = isset( $_POST['programs'] ) && is_array( $_POST['programs'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['programs'] ) )
			: array();
		update_post_meta( $post_id, '_programs', $programs );
		update_post_meta( $post_id, '_deposit_amount', '50000000' );

		$this->redirect_with_status( 'success' );
	}

	private function redirect_with_status( $status ) {
		$referer = wp_get_referer() ? wp_get_referer() : home_url( '/' );
		$url     = add_query_arg( 'vf_mpv7_status', $status, $referer );
		wp_safe_redirect( $url . '#dat-coc' );
		exit;
	}

	public function add_meta_boxes() {
		add_meta_box( 'vf_reservation_detail', 'Reservation detail', array( $this, 'render_meta_box' ), self::CPT, 'normal', 'high' );
	}

	public function render_meta_box( $post ) {
		$fields = array(
			'customer_name' => 'Khach hang',
			'phone'         => 'Điện thoại',
			'email'         => 'Email',
			'owner_type'    => 'Loai chu so huu',
			'id_number'     => 'Giay to',
			'province'      => 'Tinh thanh',
			'showroom'      => 'Showroom',
			'vf_version'    => 'Phien ban',
			'vf_battery'    => 'Pin',
			'vf_color'      => 'Màu ngoại thất',
			'vf_interior'   => 'Nội thất',
			'vf_total'      => 'Tổng tạm tính',
			'voucher'       => 'Voucher',
			'consultant'    => 'Nhan vien tu van',
		);
		echo '<table class="widefat striped"><tbody>';
		foreach ( $fields as $key => $label ) {
			$value = get_post_meta( $post->ID, '_' . $key, true );
			if ( 'vf_total' === $key && is_numeric( $value ) ) {
				$value = number_format_i18n( (float) $value ) . ' VND';
			}
			printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html( $label ), esc_html( $value ) );
		}
		$programs = get_post_meta( $post->ID, '_programs', true );
		printf( '<tr><th>Chuong trinh</th><td>%s</td></tr>', esc_html( implode( ', ', (array) $programs ) ) );
		echo '</tbody></table>';
	}

	public function reservation_columns( $columns ) {
		$columns['vf_customer'] = 'Khach hang';
		$columns['vf_phone']    = 'Điện thoại';
		$columns['vf_showroom'] = 'Showroom';
		return $columns;
	}

	public function reservation_column_content( $column, $post_id ) {
		if ( 'vf_customer' === $column ) {
			echo esc_html( get_post_meta( $post_id, '_customer_name', true ) );
		}
		if ( 'vf_phone' === $column ) {
			echo esc_html( get_post_meta( $post_id, '_phone', true ) );
		}
		if ( 'vf_showroom' === $column ) {
			echo esc_html( get_post_meta( $post_id, '_showroom', true ) );
		}
	}
}

new VF_MPV7_Landing();

add_action( 'plugins_loaded', 'vf_mpv7_load_qr_gateway', 20 );

function vf_mpv7_load_qr_gateway() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	require_once __DIR__ . '/includes/class-vf-mpv7-qr-gateway.php';
	add_filter( 'woocommerce_payment_gateways', 'vf_mpv7_register_qr_gateway' );
}

function vf_mpv7_register_qr_gateway( $gateways ) {
	$gateways[] = 'VF_MPV7_QR_Gateway';
	return $gateways;
}

