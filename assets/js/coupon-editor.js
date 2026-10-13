/**
 * Coupon editor enhancements. Validation is enforced on the server.
 *
 * @license GPL-2.0-or-later
 */
(function ( $ ) {
	'use strict';

	$( function () {
		function updateFields() {
			var active = $( '#discount_type' ).val() === 'blue_one_riyal';
			$( '.blue-one-riyal-help' ).prop( 'hidden', ! active );
			$( '#coupon_amount' ).prop( 'readonly', active );
			if ( active ) {
				$( '#coupon_amount' ).val( '1' );
				$( '#individual_use' ).prop( 'checked', true );
				$( '.limit_usage_to_x_items_field' ).show();
			}
		}
		$( '#discount_type' ).on( 'change', updateFields );
		updateFields();
	} );
})( jQuery );
