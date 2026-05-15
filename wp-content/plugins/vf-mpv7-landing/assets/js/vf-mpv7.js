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
	});
}());

