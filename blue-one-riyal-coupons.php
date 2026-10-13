<?php
/**
 * Plugin Name: Blue One Riyal Coupons
 * Description: Create WooCommerce coupons that reduce eligible products to SAR 1 each, with native product restrictions and usage limits.
 * Version: 1.1.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * Author: pmunankarmi
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: blue-one-riyal-coupons
 *
 * @package Blue_One_Riyal_Coupons
 */

defined( 'ABSPATH' ) || exit;

define( 'BLUE_ONE_RIYAL_COUPONS_VERSION', '1.1.0' );
define( 'BLUE_ONE_RIYAL_COUPONS_FILE', __FILE__ );

require_once __DIR__ . '/includes/class-blue-one-riyal-coupons.php';

add_action( 'plugins_loaded', array( 'Blue_One_Riyal_Coupons', 'boot' ), 20 );
