<?php
/**
 * Plugin Name: Flexible Price Coupons
 * Description: Set a target product price per WooCommerce coupon in your store currency, with native product restrictions and usage limits.
 * Version: 1.2.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * Author: pmunankarmi
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: flexible-price-coupons
 *
 * @package Flexible_Price_Coupons
 */

defined( 'ABSPATH' ) || exit;

define( 'FLEXIBLE_PRICE_COUPONS_VERSION', '1.2.0' );
define( 'FLEXIBLE_PRICE_COUPONS_FILE', __FILE__ );

require_once __DIR__ . '/includes/class-flexible-price-coupons.php';

add_action( 'plugins_loaded', array( 'Flexible_Price_Coupons', 'boot' ), 20 );
