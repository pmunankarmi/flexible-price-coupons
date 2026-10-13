/**
 * Coupon editor enhancements. Validation is enforced on the server.
 *
 * @license GPL-2.0-or-later
 */
(function ( $ ) {
	'use strict';

	$( function () {
		function updateFields() {
			var type = $( '#discount_type' ).val();
			var legacy = type === 'blue_one_riyal';
			var active = legacy || type === 'fpc_target_price';
			$( '.fpc-currency-field' ).toggle( active && ! legacy );
			$( '.fpc-target-price-help' ).prop( 'hidden', ! active );
			$( '#coupon_amount' ).prop( 'readonly', legacy );
			if ( active ) {
				if ( legacy ) {
					$( '#coupon_amount' ).val( '1' );
				}
				$( '#individual_use' ).prop( 'checked', true );
				$( '.limit_usage_to_x_items_field' ).show();
			}
		}
		$( '#discount_type' ).on( 'change', updateFields );
		updateFields();
	} );
})( jQuery );
