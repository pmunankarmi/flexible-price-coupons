<?php
/** Run using wp eval-file tests/integration.php against a disposable WooCommerce site. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! class_exists( 'WC_Coupon' ) ) {
	exit( "Requires WP-CLI and active WooCommerce.\n" );
}
if ( 'plugin-test@example.test' !== get_option( 'admin_email' ) ) {
	WP_CLI::error( 'Run only in the disposable test site (plugin-test@example.test).' );
}
function blue_riyal_expect( $condition, $label ) {
	if ( ! $condition ) {
		WP_CLI::error( $label );
	}
	WP_CLI::log( 'PASS: ' . $label );
}
function blue_riyal_near( $actual, $expected, $label ) {
	blue_riyal_expect( abs( (float) $actual - $expected ) < 0.011, $label . ' (' . $actual . ')' );
}
function blue_riyal_coupon( $properties = array() ) {
	$coupon = new WC_Coupon();
	$coupon->set_code( 'riyal-' . wp_generate_password( 12, false ) );
	$coupon->set_discount_type( 'blue_one_riyal' );
	$coupon->set_props( $properties );
	$coupon->save();
	return $coupon;
}
function blue_riyal_cart( $product, $quantity = 1, $variation = 0 ) {
	WC()->cart->empty_cart();
	WC()->cart->add_to_cart( $product, $quantity, $variation );
	WC()->cart->calculate_totals();
}
update_option( 'woocommerce_currency', 'SAR' );
update_option( 'woocommerce_enable_coupons', 'yes' );
update_option( 'woocommerce_calc_taxes', 'no' );
wp_set_current_user( 1 );
WC()->session = new WC_Session_Handler();
WC()->session->init();
WC()->customer = new WC_Customer( 1 );
WC()->cart = new WC_Cart();
$product = new WC_Product_Simple();
$product->set_name( 'Isolated coupon test product' );
$product->set_regular_price( '200' );
$product->set_virtual( true );
$product->set_status( 'publish' );
$id = $product->save();
$coupon = blue_riyal_coupon();
blue_riyal_cart( $id, 3 );
blue_riyal_expect( WC()->cart->apply_coupon( $coupon->get_code() ), 'Coupon accepted' );
WC()->cart->calculate_totals();
blue_riyal_near( WC()->cart->get_cart_contents_total(), 3, 'Three products cost SAR 3' );
WC()->cart->remove_coupon( $coupon->get_code() );
WC()->cart->calculate_totals();
blue_riyal_near( WC()->cart->get_cart_contents_total(), 600, 'Removing coupon restores full price' );
$limited = blue_riyal_coupon( array( 'limit_usage_to_x_items' => 1 ) );
WC()->cart->apply_coupon( $limited->get_code() );
WC()->cart->calculate_totals();
blue_riyal_near( WC()->cart->get_cart_contents_total(), 401, 'One-item limit respected' );
$excluded = blue_riyal_coupon( array( 'excluded_product_ids' => array( $id ) ) );
blue_riyal_cart( $id );
blue_riyal_expect( ! WC()->cart->apply_coupon( $excluded->get_code() ), 'Excluded product rejected' );
$expired = blue_riyal_coupon( array( 'date_expires' => time() - DAY_IN_SECONDS ) );
blue_riyal_expect( ! WC()->cart->apply_coupon( $expired->get_code() ), 'Expired coupon rejected' );
$exhausted = blue_riyal_coupon( array( 'usage_limit' => 1, 'usage_count' => 1 ) );
blue_riyal_expect( ! WC()->cart->apply_coupon( $exhausted->get_code() ), 'Exhausted usage limit rejected' );
$normal = new WC_Coupon();
$normal->set_code( 'normal-' . wp_generate_password( 10, false ) );
$normal->set_discount_type( 'percent' );
$normal->set_amount( 10 );
$normal->save();
WC()->cart->apply_coupon( $normal->get_code() );
WC()->cart->apply_coupon( $coupon->get_code() );
blue_riyal_expect( ! WC()->cart->has_discount( $normal->get_code() ), 'Applying one-riyal coupon removes previous coupon' );
blue_riyal_expect( ! WC()->cart->apply_coupon( $normal->get_code() ), 'Later coupon stacking blocked' );
update_option( 'woocommerce_currency', 'USD' );
blue_riyal_cart( $id );
blue_riyal_expect( ! WC()->cart->apply_coupon( $coupon->get_code() ), 'USD cart rejected' );
update_option( 'woocommerce_currency', 'SAR' );
$product->set_regular_price( '0.50' );
$product->save();
blue_riyal_cart( $id, 2 );
WC()->cart->apply_coupon( $coupon->get_code() );
WC()->cart->calculate_totals();
blue_riyal_near( WC()->cart->get_cart_contents_total(), 1, 'Sub-riyal products keep their lower prices' );
$parent = new WC_Product_Variable();
$parent->set_name( 'Isolated variation parent' );
$parent->set_status( 'publish' );
$parent_id = $parent->save();
$variation = new WC_Product_Variation();
$variation->set_parent_id( $parent_id );
$variation->set_regular_price( '349.95' );
$variation->set_virtual( true );
$variation->set_status( 'publish' );
$variation_id = $variation->save();
WC_Product_Variable::sync( $parent_id );
blue_riyal_cart( $parent_id, 2, $variation_id );
WC()->cart->apply_coupon( $coupon->get_code() );
WC()->cart->calculate_totals();
blue_riyal_near( WC()->cart->get_cart_contents_total(), 2, 'Variations cost SAR 1 each' );
$order = wc_create_order();
$order->set_currency( 'SAR' );
$order->add_product( $variation, 2 );
$order->calculate_totals();
$result = $order->apply_coupon( $coupon->get_code() );
blue_riyal_expect( ! is_wp_error( $result ), 'Order coupon application accepted' );
$order->calculate_totals();
blue_riyal_near( $order->get_total(), 2, 'Order recalculation retains SAR 1 per unit' );
// Verify the documented tax-entry basis with a standard Saudi VAT test rate.
update_option( 'woocommerce_calc_taxes', 'yes' );
update_option( 'woocommerce_default_country', 'SA' );
update_option( 'woocommerce_tax_based_on', 'billing' );
WC()->customer->set_billing_country( 'SA' );
WC()->customer->set_shipping_country( 'SA' );
WC()->customer->set_is_vat_exempt( false );
$rate_id = WC_Tax::_insert_tax_rate( array(
 'tax_rate_country' => 'SA', 'tax_rate_state' => '', 'tax_rate' => '15.0000',
 'tax_rate_name' => 'Test VAT', 'tax_rate_priority' => 1, 'tax_rate_compound' => 0,
 'tax_rate_shipping' => 1, 'tax_rate_order' => 0, 'tax_rate_class' => '',
) );
$product->set_regular_price( '100' );
$product->set_tax_status( 'taxable' );
$product->save();
update_option( 'woocommerce_prices_include_tax', 'no' );
blue_riyal_cart( $id );
WC()->cart->apply_coupon( $coupon->get_code() );
WC()->cart->calculate_totals();
blue_riyal_near( WC()->cart->get_cart_contents_total(), 1, 'Tax-exclusive target is SAR 1 net' );
blue_riyal_near( WC()->cart->get_cart_contents_tax(), 0.15, 'Tax-exclusive target adds applicable tax' );
update_option( 'woocommerce_prices_include_tax', 'yes' );
blue_riyal_cart( $id );
WC()->cart->apply_coupon( $coupon->get_code() );
WC()->cart->calculate_totals();
blue_riyal_near( WC()->cart->get_cart_contents_total() + WC()->cart->get_cart_contents_tax(), 1, 'Tax-inclusive target is SAR 1 gross' );
WC_Tax::_delete_tax_rate( $rate_id );
update_option( 'woocommerce_calc_taxes', 'no' );
update_option( 'woocommerce_prices_include_tax', 'no' );
WP_CLI::success( 'WooCommerce integration checks passed. Disposable site data retained for inspection.' );
