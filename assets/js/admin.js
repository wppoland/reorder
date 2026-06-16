/**
 * Reorder – Settings preview
 *
 * Keeps the live button preview in sync as the merchant edits the settings,
 * so they see the storefront button change before they save. Vanilla JS, no
 * dependencies; degrades gracefully — without it, the preview simply shows the
 * saved values rendered server-side.
 */
( function () {
	'use strict';

	var root = document.querySelector( '.reorder-preview' );
	if ( ! root ) {
		return;
	}

	var label = root.querySelector( '.reorder-preview__button-label' );
	var dest = root.querySelector( '.reorder-preview__dest' );
	var input = document.getElementById( 'button_text' );
	var data = root.dataset || {};

	var fallback = data.fallbackLabel || '';
	var cartLabel = data.cartLabel || '';
	var checkoutLabel = data.checkoutLabel || '';

	function syncLabel() {
		if ( ! label || ! input ) {
			return;
		}
		var value = input.value.trim();
		label.textContent = value !== '' ? value : fallback;
	}

	function syncDest() {
		if ( ! dest ) {
			return;
		}
		var checked = document.querySelector(
			'input[type="radio"][value="checkout"]:checked, input[type="radio"][value="cart"]:checked'
		);
		var target = checked ? checked.value : 'cart';
		dest.textContent = target === 'checkout' ? checkoutLabel : cartLabel;
	}

	if ( input ) {
		input.addEventListener( 'input', syncLabel );
	}

	document
		.querySelectorAll( 'input[type="radio"][value="cart"], input[type="radio"][value="checkout"]' )
		.forEach( function ( radio ) {
			radio.addEventListener( 'change', syncDest );
		} );

	syncLabel();
	syncDest();
} )();
