<?php
/**
 * Coupon behavior and admin integration.
 *
 * @package Blue_One_Riyal_Coupons
 */

defined( 'ABSPATH' ) || exit;

/**
 * Implements the one-riyal coupon type through native WooCommerce hooks.
 */
final class Blue_One_Riyal_Coupons {
	/** Coupon type identifier; retained for existing saved coupons. */
	const TYPE = 'blue_one_riyal';

	/**
	 * Register hooks after WooCommerce loads.
	 *
	 * @return void
	 */
	public static function boot() {
		if ( ! class_exists( 'WC_Coupon' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'missing_woocommerce' ) );
			return;
		}
		add_filter( 'woocommerce_coupon_discount_types', array( __CLASS__, 'types' ) );
		add_filter( 'woocommerce_product_coupon_types', array( __CLASS__, 'product_types' ) );
		add_filter( 'woocommerce_coupon_get_discount_amount', array( __CLASS__, 'discount' ), 10, 5 );
		add_filter( 'woocommerce_coupon_is_valid', array( __CLASS__, 'validate' ), 10, 3 );
		add_filter( 'woocommerce_coupon_get_individual_use', array( __CLASS__, 'individual_use' ), 10, 2 );
		add_filter( 'woocommerce_apply_individual_use_coupon', array( __CLASS__, 'keep_coupons' ), 100, 2 );
		add_filter( 'woocommerce_apply_with_individual_use_coupon', array( __CLASS__, 'allow_stacking' ), 100, 3 );
		add_action( 'woocommerce_before_coupon_object_save', array( __CLASS__, 'normalize' ) );
		add_action( 'woocommerce_coupon_options', array( __CLASS__, 'instructions' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Add the public discount type.
	 *
	 * @param array $types Existing coupon types.
	 * @return array
	 */
	public static function types( $types ) {
		$types[ self::TYPE ] = __( 'Product price = SAR 1', 'blue-one-riyal-coupons' );
		return $types;
	}

	/**
	 * Use native per-product eligibility checks.
	 *
	 * @param array $types Existing product coupon types.
	 * @return array
	 */
	public static function product_types( $types ) {
		$types[] = self::TYPE;
		return array_unique( $types );
	}

	/**
	 * Calculate the reduction without raising prices below one riyal.
	 *
	 * @param float                            $discount Original discount.
	 * @param float                            $amount Amount eligible for discount.
	 * @param array|WC_Order_Item_Product|null $item Cart or order item.
	 * @param bool                             $single Whether the amount is for one unit.
	 * @param WC_Coupon                        $coupon Coupon being applied.
	 * @return float
	 */
	public static function discount( $discount, $amount, $item, $single, $coupon ) {
		if ( ! $coupon->is_type( self::TYPE ) ) {
			return $discount;
		}
		if ( 'SAR' !== get_woocommerce_currency() ) {
			return 0.0;
		}
		// WC_Discounts supplies a unit price and multiplies by the eligible quantity.
		// Keep a line-total fallback for callers using the legacy coupon API.
		$quantity = 1;
		if ( ! $single && is_array( $item ) && isset( $item['quantity'] ) ) {
			$quantity = max( 0, (float) $item['quantity'] );
		} elseif ( ! $single && $item instanceof WC_Order_Item_Product ) {
			$quantity = max( 0, (float) $item->get_quantity() );
		}
		return max( 0.0, (float) $amount - $quantity );
	}

	/**
	 * Reject this coupon outside Saudi-riyal transactions.
	 *
	 * @param bool         $valid Existing validation result.
	 * @param WC_Coupon    $coupon Coupon being checked.
	 * @param WC_Discounts $discounts Discount context.
	 * @throws Exception When the transaction currency is unsupported.
	 * @return bool
	 */
	public static function validate( $valid, $coupon, $discounts ) {
		if ( ! $coupon->is_type( self::TYPE ) ) {
			return $valid;
		}
		$object   = $discounts->get_object();
		$currency = $object instanceof WC_Order ? $object->get_currency() : get_woocommerce_currency();
		if ( 'SAR' !== $currency || 'SAR' !== get_woocommerce_currency() ) {
			throw new Exception( esc_html__( 'This coupon is available only for orders in Saudi riyals (SAR).', 'blue-one-riyal-coupons' ) );
		}
		return $valid;
	}

	/**
	 * Require exclusive use for this coupon type.
	 *
	 * @param bool      $value Existing individual-use value.
	 * @param WC_Coupon $coupon Coupon being checked.
	 * @return bool
	 */
	public static function individual_use( $value, $coupon ) {
		return $coupon->is_type( self::TYPE ) ? true : $value;
	}

	/**
	 * Remove existing coupons when this exclusive coupon is applied.
	 *
	 * @param array     $keep Coupon codes to retain.
	 * @param WC_Coupon $coupon Coupon being applied.
	 * @return array
	 */
	public static function keep_coupons( $keep, $coupon ) {
		return $coupon->is_type( self::TYPE ) ? array() : $keep;
	}

	/**
	 * Prevent other coupons from stacking with this coupon type.
	 *
	 * @param bool      $allowed Existing stacking decision.
	 * @param WC_Coupon $coupon Incoming coupon.
	 * @param WC_Coupon $existing Existing coupon.
	 * @return bool
	 */
	public static function allow_stacking( $allowed, $coupon, $existing ) {
		return ( $coupon->is_type( self::TYPE ) || $existing->is_type( self::TYPE ) ) ? false : $allowed;
	}

	/**
	 * Persist invariants using the WooCommerce CRUD API.
	 *
	 * @param WC_Coupon $coupon Coupon being saved.
	 * @return void
	 */
	public static function normalize( $coupon ) {
		if ( $coupon->is_type( self::TYPE ) ) {
			$coupon->set_amount( 1 );
			$coupon->set_individual_use( true );
		}
	}

	/**
	 * Show contextual help in the native coupon editor.
	 *
	 * @return void
	 */
	public static function instructions() {
		echo '<p class="form-field blue-one-riyal-help" hidden>' . esc_html__( 'Each eligible unit costs SAR 1 after discount. Leave product/category restrictions empty for all products. The amount is fixed at 1 and this coupon cannot be combined with other coupons. Tax follows your store price-entry setting; shipping is separate. Products already below SAR 1 keep their lower price.', 'blue-one-riyal-coupons' ) . '</p>';
	}

	/**
	 * Load the editor script only on coupon editing screens.
	 *
	 * @return void
	 */
	public static function enqueue_assets() {
		$screen = get_current_screen();
		if ( ! $screen || 'shop_coupon' !== $screen->post_type || 'post' !== $screen->base ) {
			return;
		}
		wp_enqueue_script( 'blue-one-riyal-coupons-editor', plugins_url( 'assets/js/coupon-editor.js', BLUE_ONE_RIYAL_COUPONS_FILE ), array( 'jquery' ), BLUE_ONE_RIYAL_COUPONS_VERSION, true );
	}

	/**
	 * Show a dependency notice to plugin administrators.
	 *
	 * @return void
	 */
	public static function missing_woocommerce() {
		if ( current_user_can( 'activate_plugins' ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Blue One Riyal Coupons requires WooCommerce to be active.', 'blue-one-riyal-coupons' ) . '</p></div>';
		}
	}
}
