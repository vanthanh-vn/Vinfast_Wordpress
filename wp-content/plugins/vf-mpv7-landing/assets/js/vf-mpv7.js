(function () {
	'use strict';

	function formatVnd(value) {
		return new Intl.NumberFormat('vi-VN').format(value) + ' VND';
	}

	function setActive(button, selector) {
		var group = button.closest('.vf-option-group');
		if (!group) {
			return;
		}

		group.querySelectorAll(selector).forEach(function (item) {
			item.classList.remove('is-active');
		});
		button.classList.add('is-active');
	}

	function updatePage(page) {
		var version = page.querySelector('[data-vf-version].is-active');
		var battery = page.querySelector('[data-vf-battery].is-active');
		var color = page.querySelector('[data-vf-color].is-active');
		var interior = page.querySelector('[data-vf-interior].is-active');

		var basePrice = version ? parseInt(version.dataset.vfPrice || '0', 10) : 0;
		var batteryPrice = battery ? parseInt(battery.dataset.vfPrice || '0', 10) : 0;
		var colorPrice = color ? parseInt(color.dataset.vfPrice || '0', 10) : 0;
		var total = basePrice + batteryPrice + colorPrice;

		page.querySelectorAll('[data-vf-total]').forEach(function (node) {
			node.textContent = formatVnd(total);
		});

		var values = {
			version: version ? version.dataset.vfVersion : '',
			battery: battery ? battery.dataset.vfBattery : '',
			color: color ? color.dataset.vfColor : '',
			interior: interior ? interior.dataset.vfInterior : '',
			total: String(total)
		};

		Object.keys(values).forEach(function (key) {
			var input = page.querySelector('[data-vf-input="' + key + '"]');
			var summary = page.querySelector('[data-vf-summary="' + key + '"]');
			if (input) {
				input.value = values[key];
			}
			if (summary) {
				summary.textContent = values[key];
			}
		});

		if (color && color.dataset.vfColorCode) {
			page.style.setProperty('--vf-car-color', color.dataset.vfColorCode);
		}
	}

	function updateCartCountText(page, count) {
		var countText = page.querySelector('[data-vf-cart-count-text]');
		if (countText) {
			countText.textContent = 'Bạn có ' + count + ' sản phẩm trong giỏ hàng';
		}

		document.querySelectorAll('.cart-icon strong, .cart-icon .cart-icon-count').forEach(function (node) {
			node.textContent = String(count);
		});
	}

	function setHtml(selector, html, root) {
		var node = (root || document).querySelector(selector);
		if (node) {
			node.innerHTML = html;
		}
	}

	function refreshCartFragments() {
		if (window.jQuery && document.body) {
			window.jQuery(document.body).trigger('wc_fragment_refresh');
		}
	}

	function updateCartTotals(page, data) {
		setHtml('[data-vf-cart-subtotal]', data.cartSubtotal, page);
		setHtml('[data-vf-cart-discount]', data.discount, page);
		setHtml('[data-vf-cart-total]', data.total, page);
		updateCartCountText(page, data.cartCount);
		refreshCartFragments();
	}

	function renderEmptyCart(page) {
		var layout = page.querySelector('.vf-cart-layout');
		var continueLink = page.querySelector('.vf-cart-continue');
		var shopUrl = continueLink ? continueLink.getAttribute('href') : '/';
		var emptyHtml = [
			'<section class="vf-cart-empty">',
			'<div class="vf-cart-empty-icon">VF</div>',
			'<h2>Giỏ hàng đang trống</h2>',
			'<p>Bạn chưa thêm sản phẩm nào vào giỏ hàng. Hãy chọn một mẫu xe để xem tổng tiền và tiếp tục thanh toán.</p>',
			'<a class="vf-cta" href="' + shopUrl + '">Vào cửa hàng</a>',
			'</section>'
		].join('');

		if (layout) {
			layout.insertAdjacentHTML('afterend', emptyHtml);
			layout.remove();
		}
	}

	function setFormUpdating(form, isUpdating) {
		form.classList.toggle('is-updating', isUpdating);
		form.querySelectorAll('button').forEach(function (button) {
			button.disabled = isUpdating;
		});
	}

	function submitCartAjax(form, action) {
		var formData = new FormData(form);
		formData.set('action', action);

		return fetch(window.vfMpv7Cart.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData
		}).then(function (response) {
			return response.json();
		});
	}

	function initCartQuantityUpdates() {
		if (typeof window.vfMpv7Cart === 'undefined' || !window.vfMpv7Cart.ajaxUrl) {
			return;
		}

		document.querySelectorAll('[data-vf-cart-qty-form]').forEach(function (form) {
			form.addEventListener('submit', function (event) {
				event.preventDefault();

				var submitter = event.submitter || document.activeElement;
				var quantity = submitter && submitter.name === 'quantity' ? submitter.value : '';
				var item = form.closest('[data-vf-cart-item]');
				var page = form.closest('.vf-cart-modern');

				if (!quantity || !item || !page) {
					form.submit();
					return;
				}

				var formData = new FormData(form);
				formData.set('action', 'vf_cart_update_ajax');
				formData.set('quantity', quantity);

				setFormUpdating(form, true);

				fetch(window.vfMpv7Cart.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					body: formData
				})
					.then(function (response) {
						return response.json();
					})
					.then(function (payload) {
						if (!payload || !payload.success || !payload.data) {
							throw new Error('Cart update failed');
						}

						var data = payload.data;
						var input = form.querySelector('[data-vf-cart-qty-input]');
						var buttons = form.querySelectorAll('button[name="quantity"]');

						if (input) {
							input.value = data.quantity;
						}
						if (buttons[0]) {
							buttons[0].value = data.minusQty;
							buttons[0].dataset.vfCartQuantity = data.minusQty;
						}
						if (buttons[1]) {
							buttons[1].value = data.plusQty;
							buttons[1].dataset.vfCartQuantity = data.plusQty;
						}

						setHtml('[data-vf-cart-item-subtotal]', data.itemSubtotal, item);
						updateCartTotals(page, data);
					})
					.catch(function () {
						form.submit();
					})
					.finally(function () {
						setFormUpdating(form, false);
					});
			});
		});
	}

	function initCartRemoveActions() {
		if (typeof window.vfMpv7Cart === 'undefined' || !window.vfMpv7Cart.ajaxUrl) {
			return;
		}

		document.querySelectorAll('[data-vf-cart-remove-form]').forEach(function (form) {
			form.addEventListener('submit', function (event) {
				event.preventDefault();

				var item = form.closest('[data-vf-cart-item]');
				var page = form.closest('.vf-cart-modern');

				if (!item || !page) {
					form.submit();
					return;
				}

				setFormUpdating(form, true);

				submitCartAjax(form, 'vf_cart_remove_ajax')
					.then(function (payload) {
						if (!payload || !payload.success || !payload.data) {
							throw new Error('Cart remove failed');
						}

						var data = payload.data;
						item.remove();
						updateCartTotals(page, data);

						if (parseInt(data.cartCount || '0', 10) <= 0) {
							renderEmptyCart(page);
						}
					})
					.catch(function () {
						form.submit();
					})
					.finally(function () {
						setFormUpdating(form, false);
					});
			});
		});

		document.querySelectorAll('[data-vf-cart-clear-form]').forEach(function (form) {
			form.addEventListener('submit', function (event) {
				event.preventDefault();

				var page = form.closest('.vf-cart-modern');
				if (!page) {
					form.submit();
					return;
				}

				setFormUpdating(form, true);

				submitCartAjax(form, 'vf_cart_clear_ajax')
					.then(function (payload) {
						if (!payload || !payload.success || !payload.data) {
							throw new Error('Cart clear failed');
						}

						updateCartTotals(page, payload.data);
						renderEmptyCart(page);
					})
					.catch(function () {
						form.submit();
					})
					.finally(function () {
						setFormUpdating(form, false);
					});
			});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.vf-page').forEach(function (page) {
			page.querySelectorAll('.vf-swatch').forEach(function (swatch) {
				if (swatch.dataset.vfColorCode) {
					swatch.style.setProperty('--swatch', swatch.dataset.vfColorCode);
				}
			});

			page.addEventListener('click', function (event) {
				var target = event.target.closest('[data-vf-battery], [data-vf-color], [data-vf-interior], [data-vf-version]');
				if (!target || !page.contains(target)) {
					return;
				}

				if (target.matches('[data-vf-color]')) {
					setActive(target, '.vf-swatch');
				} else {
					setActive(target, '.vf-choice');
				}
				updatePage(page);
			});

			updatePage(page);
		});

		document.querySelectorAll('.vf-ch-page').forEach(function (page) {
			var slides = Array.prototype.slice.call(page.querySelectorAll('.vf-ch-hero-slide'));
			var dots = Array.prototype.slice.call(page.querySelectorAll('.vf-ch-hero-dots button'));
			var current = 0;

			function showSlide(index) {
				if (!slides.length) {
					return;
				}
				current = (index + slides.length) % slides.length;
				slides.forEach(function (slide, slideIndex) {
					slide.classList.toggle('is-active', slideIndex === current);
				});
				dots.forEach(function (dot, dotIndex) {
					dot.classList.toggle('is-active', dotIndex === current);
				});
			}

			dots.forEach(function (dot) {
				dot.addEventListener('click', function () {
					showSlide(parseInt(dot.dataset.vfSlide || '0', 10));
				});
			});

			if (slides.length > 1) {
				window.setInterval(function () {
					showSlide(current + 1);
				}, 5200);
			}

			page.querySelectorAll('.vf-ch-tab').forEach(function (tab) {
				tab.addEventListener('click', function () {
					var filter = tab.dataset.filter || 'all';
					page.querySelectorAll('.vf-ch-tab').forEach(function (item) {
						item.classList.toggle('is-active', item === tab);
					});
					page.querySelectorAll('.vf-ch-product-card').forEach(function (card) {
						var category = card.dataset.category || '';
						card.classList.toggle('is-hidden', filter !== 'all' && category !== filter);
					});
				});
			});
		});

		document.querySelectorAll('.vf-shop-page').forEach(function (page) {
			page.querySelectorAll('.vf-shop-filter').forEach(function (filterButton) {
				filterButton.addEventListener('click', function () {
					var filter = filterButton.dataset.shopFilter || 'all';
					page.querySelectorAll('.vf-shop-filter').forEach(function (item) {
						item.classList.toggle('is-active', item === filterButton);
					});
					page.querySelectorAll('.vf-shop-card').forEach(function (card) {
						var category = card.dataset.shopCategory || '';
						card.classList.toggle('is-hidden', filter !== 'all' && category !== filter);
					});
				});
			});
		});

		initCartQuantityUpdates();
		initCartRemoveActions();
	});
}());

