/**
 * We Catering Public JS
 */
(function($){
	'use strict';

	$(function(){
		var $form = $('#we-catering-order-form');
		var prices = {};
		var orderWindowOpen = true; // Default to true, will be updated by AJAX

		// Build price map from DOM
		$('.we-catering-menu-card').each(function(){
			var $card = $(this);
			var id = ($card.find('input[type="number"]').attr('name') || '').match(/items\[(\d+)\]/);
			if (id && id[1]) {
				var priceText = $card.find('.we-catering-price').text().replace('$', '').trim();
				prices[id[1]] = parseFloat(priceText || '0');
			}
		});

		function recalc(){
			var total = 0;
			$form.find('input[type="number"]').each(function(){
				var m = (this.name || '').match(/items\[(\d+)\]/);
				if (m && m[1]) {
					var qty = parseInt($(this).val(), 10) || 0;
					var price = prices[m[1]] || 0;
					total += qty * price;
				}
			});
			$('#we-catering-total').text('$' + total.toFixed(2));
		}

		$form.on('input change', 'input[type="number"]', recalc);
		recalc();

		// Check order window status
		function checkOrderWindowStatus() {
			$.post((window.We_Catering && We_Catering.ajax_url) || '', {
				action: 'we_catering_get_order_window_status',
				nonce: (window.We_Catering && We_Catering.nonce) || ''
			})
			.done(function(res) {
				if (res && res.success) {
					var status = res.data;
					orderWindowOpen = status.is_open;
					
					// Update UI based on window status
					updateOrderWindowUI(status);
				}
			})
			.fail(function() {
				console.log('Failed to check order window status');
			});
		}

		// Update UI based on order window status
		function updateOrderWindowUI(status) {
			var $statusContainer = $('.we-catering-order-window-status');
			var $submitBtn = $form.find('button[type="submit"]');
			var $quantityInputs = $form.find('input[type="number"]');
			
			if (status.is_open) {
				$statusContainer.removeClass('closed').addClass('open');
				$form.removeClass('disabled');
				$('.we-catering-menu-card').removeClass('disabled');
				$quantityInputs.prop('disabled', false).removeClass('disabled');
				$submitBtn.prop('disabled', false).text('Place Order');
			} else {
				$statusContainer.removeClass('open').addClass('closed');
				$form.addClass('disabled');
				$('.we-catering-menu-card').addClass('disabled');
				$quantityInputs.prop('disabled', true).addClass('disabled');
				$submitBtn.prop('disabled', true).text('Order Window Closed');
			}
		}

		// Check order window status on page load
		checkOrderWindowStatus();

		// Check order window status every 30 seconds
		setInterval(checkOrderWindowStatus, 30000);

		$form.on('submit', function(e){
			e.preventDefault();

			// Check if order window is open
			if (!orderWindowOpen) {
				alert('Order window is currently closed. Please try again during the order window.');
				return;
			}

			// Build order items array
			var orderItems = [];
			$form.find('input[type="number"]').each(function(){
				var m = (this.name || '').match(/items\[(\d+)\]/);
				if (m && m[1]) {
					var qty = parseInt($(this).val(), 10) || 0;
					if (qty > 0) {
						orderItems.push({ menu_item_id: parseInt(m[1], 10), quantity: qty, unit_price: prices[m[1]] || 0 });
					}
				}
			});

			if (orderItems.length === 0) {
				alert('Please select at least one item.');
				return;
			}

			var totalText = ($('#we-catering-total').text() || '0').replace('$','').trim();
			var data = {
				action: 'we_catering_public_create_order',
				nonce: (window.We_Catering && We_Catering.nonce) || '',
				organization_id: parseInt($form.find('input[name="organization_id"]').val() || '0', 10),
				order_date: $form.find('input[name="date"]').val(),
				total_amount: parseFloat(totalText || '0'),
				notes: '',
				user_id: (window.We_Catering && We_Catering.current_user_id) || 0,
				order_items: JSON.stringify(orderItems)
			};

			var $btn = $form.find('button[type="submit"]').prop('disabled', true).text('Placing...');
			$.post((window.We_Catering && We_Catering.ajax_url) || '', data)
				.done(function(res){
					if (res && res.success) {
						alert('Order placed successfully.');
						$form[0].reset();
						recalc();
					} else {
						alert((res && res.data && res.data.message) || 'Failed to place order.');
					}
				})
				.fail(function(){
					alert('Network error.');
				})
				.always(function(){
					$btn.prop('disabled', false).text('Place Order');
				});
		});
	});
})(jQuery);
