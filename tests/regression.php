<?php
// Isolated plugin regression checks; no WordPress installation required.
define( 'ABSPATH', __DIR__ );
$hooks = array();
function add_action( $name, $callback, $priority = 10, $args = 1 ) { $GLOBALS['hooks'][$name] = $callback; }
function add_filter( $name, $callback, $priority = 10, $args = 1 ) { $GLOBALS['hooks'][$name] = $callback; }
function __( $text, $domain = '' ) { return $text; }
function esc_html__( $text, $domain = '' ) { return htmlspecialchars( $text ); }
function get_woocommerce_currency() { return $GLOBALS['currency']; }
class WC_Coupon {
 public $type;
 public $amount = 0;
 public $individual = false;
 public function __construct( $type ) { $this->type = $type; }
 public function is_type( $type ) { return $this->type === $type; }
 public function set_amount( $amount ) { $this->amount = $amount; }
 public function set_individual_use( $value ) { $this->individual = $value; }
}
class WC_Order {
 public function get_currency() { return 'USD'; }
}
class WC_Order_Item_Product {
 public function get_quantity() { return 3; }
}
class Discount_Context {
 public $object;
 public function __construct( $object = null ) { $this->object = $object; }
 public function get_object() { return $this->object; }
}
require __DIR__ . '/../blue-one-riyal-coupons.php';
function check( $condition, $label ) {
 if ( ! $condition ) { throw new Exception( 'FAIL: ' . $label ); }
 echo 'PASS: ' . $label . PHP_EOL;
}
$currency = 'SAR';
Blue_One_Riyal_Coupons::boot();
$special = new WC_Coupon( Blue_One_Riyal_Coupons::TYPE );
$normal = new WC_Coupon( 'percent' );
$callback = $hooks['woocommerce_coupon_get_discount_amount'];
check( abs( $callback( 0, 1500, array( 'quantity' => 1 ), true, $special ) - 1499 ) < 0.00001, 'SAR 1500 product leaves SAR 1' );
check( abs( 2499.95 - $callback( 0, 2499.95, null, true, $special ) - 1 ) < 0.00001, 'Decimal price leaves SAR 1' );
check( abs( 600 - $callback( 0, 200, array( 'quantity' => 3 ), true, $special ) * 3 - 3 ) < 0.00001, 'Three units leave SAR 3 when core multiplies unit discount' );
check( abs( $callback( 0, 600, array( 'quantity' => 3 ), false, $special ) - 597 ) < 0.00001, 'Legacy line amount accounts for quantity' );
check( abs( $callback( 0, 600, new WC_Order_Item_Product(), false, $special ) - 597 ) < 0.00001, 'Order item line amount accounts for quantity' );
foreach ( array( 0, 0.5, 1 ) as $price ) {
 check( 0.0 === $callback( 0, $price, null, true, $special ), 'No price increase or negative discount at ' . $price );
}
check( 15 === $callback( 15, 100, null, true, $normal ), 'Other coupon calculation preserved' );
check( false === Blue_One_Riyal_Coupons::validate( false, $special, new Discount_Context() ), 'Earlier validation rejection preserved' );
check( true === Blue_One_Riyal_Coupons::validate( true, $special, new Discount_Context() ), 'SAR cart accepted' );
$currency = 'USD';
check( 0.0 === $callback( 0, 500, null, true, $special ), 'Non-SAR calculation gives no discount' );
$rejected = false;
try { Blue_One_Riyal_Coupons::validate( true, $special, new Discount_Context() ); } catch ( Exception $e ) { $rejected = true; }
check( $rejected, 'Non-SAR cart rejected' );
$currency = 'SAR';
$rejected = false;
try { Blue_One_Riyal_Coupons::validate( true, $special, new Discount_Context( new WC_Order() ) ); } catch ( Exception $e ) { $rejected = true; }
check( $rejected, 'Non-SAR order rejected in SAR store' );
check( false === Blue_One_Riyal_Coupons::allow_stacking( true, $normal, $special, array() ), 'Later coupon cannot stack' );
check( array() === Blue_One_Riyal_Coupons::keep_coupons( array( 'old' ), $special, array( 'old' ) ), 'Existing coupons removed for individual use' );
check( true === Blue_One_Riyal_Coupons::allow_stacking( true, $normal, $normal, array() ), 'Other stacking rules preserved' );
Blue_One_Riyal_Coupons::normalize( $special );
check( 1 === $special->amount && true === $special->individual, 'Saved special coupon has fixed amount and individual use' );
Blue_One_Riyal_Coupons::normalize( $normal );
check( 0 === $normal->amount && false === $normal->individual, 'Normal coupon not changed on save' );
echo "All isolated regression checks passed. See integration.php for the real WooCommerce suite.\n";
