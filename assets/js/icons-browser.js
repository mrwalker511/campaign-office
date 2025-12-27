/**
 * Heroicons Browser - Admin JavaScript
 *
 * @package CampaignPress
 * @since 2.1.0
 */

(function($) {
	'use strict';

	let searchTimeout;

	// Copy to clipboard function
	function copyToClipboard(text) {
		if (navigator.clipboard && navigator.clipboard.writeText) {
			return navigator.clipboard.writeText(text);
		} else {
			// Fallback for older browsers
			const textarea = document.createElement('textarea');
			textarea.value = text;
			textarea.style.position = 'fixed';
			textarea.style.opacity = '0';
			document.body.appendChild(textarea);
			textarea.select();
			try {
				document.execCommand('copy');
				document.body.removeChild(textarea);
				return Promise.resolve();
			} catch (err) {
				document.body.removeChild(textarea);
				return Promise.reject(err);
			}
		}
	}

	// Show copied notification
	function showCopiedNotice() {
		const notice = $('#cp-icon-copied-notice');
		notice.removeClass('hiding').show();

		setTimeout(function() {
			notice.addClass('hiding');
			setTimeout(function() {
				notice.hide().removeClass('hiding');
			}, 300);
		}, 2000);
	}

	// Update icons grid
	function updateIconsGrid(icons, style) {
		const grid = $('#cp-icons-grid');
		grid.empty().addClass('loading');

		if (icons.length === 0) {
			grid.removeClass('loading');
			grid.html('<p style="text-align: center; padding: 2rem; color: #646970;">' + cpIconsBrowser.i18n.noIcons + '</p>');
			return;
		}

		setTimeout(function() {
			icons.forEach(function(icon) {
				const item = $('<div>')
					.addClass('cp-icon-item')
					.attr('data-icon', icon.name)
					.attr('data-style', style);

				const preview = $('<div>')
					.addClass('cp-icon-preview')
					.html(icon.svg);

				const name = $('<div>')
					.addClass('cp-icon-name')
					.text(icon.name);

				const copyBtn = $('<button>')
					.addClass('cp-icon-copy-btn')
					.text('Copy')
					.attr('data-icon', icon.name)
					.attr('data-style', style);

				item.append(preview, name, copyBtn);
				grid.append(item);
			});

			grid.removeClass('loading');
		}, 100);
	}

	// Search icons
	function searchIcons() {
		const search = $('#cp-icon-search').val();
		const style = $('#cp-icon-style').val();

		$.ajax({
			url: cpIconsBrowser.ajaxUrl,
			type: 'POST',
			data: {
				action: 'cp_search_icons',
				nonce: cpIconsBrowser.nonce,
				search: search,
				style: style
			},
			success: function(response) {
				if (response.success) {
					updateIconsGrid(response.data, style);
					$('.cp-icons-count').text(response.data.length + ' icons found');
				}
			}
		});
	}

	// Load icons by category
	function loadIconsByCategory() {
		const category = $('#cp-icon-category').val();
		const style = $('#cp-icon-style').val();

		$.ajax({
			url: cpIconsBrowser.ajaxUrl,
			type: 'POST',
			data: {
				action: 'cp_get_icons_by_category',
				nonce: cpIconsBrowser.nonce,
				category: category,
				style: style
			},
			success: function(response) {
				if (response.success) {
					updateIconsGrid(response.data, style);
					$('.cp-icons-count').text(response.data.length + ' icons available');
				}
			}
		});
	}

	// Initialize
	$(document).ready(function() {

		// Search input
		$('#cp-icon-search').on('input', function() {
			clearTimeout(searchTimeout);
			searchTimeout = setTimeout(searchIcons, 300);
		});

		// Category filter
		$('#cp-icon-category').on('change', function() {
			// Clear search when changing category
			$('#cp-icon-search').val('');
			loadIconsByCategory();
		});

		// Style filter
		$('#cp-icon-style').on('change', function() {
			const search = $('#cp-icon-search').val();
			if (search) {
				searchIcons();
			} else {
				loadIconsByCategory();
			}
		});

		// Display size filter
		$('#cp-icon-size-display').on('change', function() {
			const size = $(this).val();
			$('#cp-icons-grid').attr('data-size', size);
		});

		// Set initial size
		$('#cp-icons-grid').attr('data-size', 'md');

		// Copy button click
		$(document).on('click', '.cp-icon-copy-btn', function(e) {
			e.stopPropagation();
			const icon = $(this).data('icon');
			const style = $(this).data('style');
			const phpCode = `<?php echo campaignpress_get_heroicon('${icon}', '${style}'); ?>`;

			copyToClipboard(phpCode)
				.then(function() {
					showCopiedNotice();
				})
				.catch(function(err) {
					console.error('Failed to copy:', err);
					alert(cpIconsBrowser.i18n.copyFailed);
				});
		});

		// Icon item click (alternative copy method)
		$(document).on('click', '.cp-icon-item', function(e) {
			if (!$(e.target).hasClass('cp-icon-copy-btn')) {
				$(this).find('.cp-icon-copy-btn').click();
			}
		});

	});

})(jQuery);
