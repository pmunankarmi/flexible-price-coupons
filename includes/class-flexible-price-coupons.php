<?php
/**
 * Coupon behavior and admin integration.
 *
 * @package Flexible_Price_Coupons
 */

defined( 'ABSPATH' ) || exit;

/**
 * Implements target-price coupons through native WooCommerce hooks.
 */
final class Flexible_Price_Coupons {
	/** Public coupon type identifier. */
	const TYPE = 'fpc_target_price';

	/** Previous coupon type, supported for upgrades only. */
	const LEGACY_TYPE = 'blue_one_riyal';

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
		add_action( 'woocommerce_before_data_object_save', array( __CLASS__, 'normalize' ) );
		add_action( 'woocommerce_coupon_options', array( __CLASS__, 'instructions' ), 10, 2 );
		add_action( 'woocommerce_coupon_options_save', array( __CLASS__, 'save_currency' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Add the public discount type.
	 *
	 * @param array $types Existing coupon types.
	 * @return array
	 */
	public static function types( $types ) {
		$types[ self::TYPE ]        = __( 'Target product price', 'flexible-price-coupons' );
		$types[ self::LEGACY_TYPE ] = __( 'Legacy product price = SAR 1', 'flexible-price-coupons' );
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
		$types[] = self::LEGACY_TYPE;
		return array_unique( $types );
	}

	/**
	 * Calculate the reduction without raising prices below the target.
	 *
	 * @param float                            $discount Original discount.
	 * @param float                            $amount Amount eligible for discount.
	 * @param array|WC_Order_Item_Product|null $item Cart or order item.
	 * @param bool                             $single Whether the amount is for one unit.
	 * @param WC_Coupon                        $coupon Coupon being applied.
	 * @return float
	 */
	public static function discount( $discount, $amount, $item, $single, $coupon ) {
		if ( ! $coupon->is_type( array( self::TYPE, self::LEGACY_TYPE ) ) ) {
			return $discount;
		}
		if ( self::currency( $coupon ) !== get_woocommerce_currency() ) {
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
		$target = $coupon->is_type( self::LEGACY_TYPE ) ? 1.0 : max( 0.0, (float) $coupon->get_amount() );
		return max( 0.0, (float) $amount - $quantity * $target );
	}

	/**
	 * Validate the transaction currency and preserve legacy restrictions.
	 *
	 * @param bool         $valid Existing validation result.
	 * @param WC_Coupon    $coupon Coupon being checked.
	 * @param WC_Discounts $discounts Discount context.
	 * @throws Exception When the transaction currency is unsupported.
	 * @return bool
	 */
	public static function validate( $valid, $coupon, $discounts ) {
		if ( ! $coupon->is_type( array( self::TYPE, self::LEGACY_TYPE ) ) ) {
			return $valid;
		}
		$object   = $discounts->get_object();
		$currency = $object instanceof WC_Order ? $object->get_currency() : get_woocommerce_currency();
		if ( self::currency( $coupon ) !== $currency || get_woocommerce_currency() !== $currency ) {
			throw new Exception( esc_html__( 'This coupon cannot be applied in this transaction currency.', 'flexible-price-coupons' ) );
		}
		return $valid;
	}

	/**
	 * Get the explicitly configured coupon currency.
	 *
	 * @param WC_Coupon $coupon Coupon object.
	 * @return string
	 */
	public static function currency( $coupon ) {
		if ( $coupon->is_type( self::LEGACY_TYPE ) ) {
			return 'SAR';
		}
		$currency = $coupon->get_meta( '_fpc_currency', true );
		return $currency ? $currency : get_woocommerce_currency();
	}

	/**
	 * Save a validated currency from the native coupon editor.
	 *
	 * @param int       $coupon_id Coupon ID.
	 * @param WC_Coupon $coupon Coupon object.
	 * @return void
	 */
	public static function save_currency( $coupon_id, $coupon ) {
		if ( ! current_user_can( 'edit_post', $coupon_id ) || ! isset( $_POST['fpc_currency_nonce'] ) || ! is_string( $_POST['fpc_currency_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fpc_currency_nonce'] ) ), 'fpc_save_currency_' . $coupon_id ) ) {
			return;
		}
		if ( ! $coupon->is_type( self::TYPE ) || ! isset( $_POST['_fpc_currency'] ) || ! is_string( $_POST['_fpc_currency'] ) ) {
			return;
		}
		$currency   = sanitize_text_field( wp_unslash( $_POST['_fpc_currency'] ) );
		$currencies = get_woocommerce_currencies();
		if ( ! isset( $currencies[ $currency ] ) ) {
			WC_Admin_Meta_Boxes::add_error( __( 'Choose a valid target price currency. The previous currency was retained.', 'flexible-price-coupons' ) );
			return;
		}
		$coupon->update_meta_data( '_fpc_currency', $currency );
		$coupon->save();
	}

	/**
	 * Require exclusive use for this coupon type.
	 *
	 * @param bool      $value Existing individual-use value.
	 * @param WC_Coupon $coupon Coupon being checked.
	 * @return bool
	 */
	public static function individual_use( $value, $coupon ) {
		return $coupon->is_type( array( self::TYPE, self::LEGACY_TYPE ) ) ? true : $value;
	}

	/**
	 * Remove existing coupons when this exclusive coupon is applied.
	 *
	 * @param array     $keep Coupon codes to retain.
	 * @param WC_Coupon $coupon Coupon being applied.
	 * @return array
	 */
	public static function keep_coupons( $keep, $coupon ) {
		return $coupon->is_type( array( self::TYPE, self::LEGACY_TYPE ) ) ? array() : $keep;
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
		return ( $coupon->is_type( array( self::TYPE, self::LEGACY_TYPE ) ) || $existing->is_type( array( self::TYPE, self::LEGACY_TYPE ) ) ) ? false : $allowed;
	}

	/**
	 * Persist invariants using the WooCommerce CRUD API.
	 *
	 * @param WC_Coupon $coupon Coupon being saved.
	 * @return void
	 */
	public static function normalize( $coupon ) {
		if ( ! $coupon instanceof WC_Coupon ) {
			return;
		}
		if ( $coupon->is_type( array( self::TYPE, self::LEGACY_TYPE ) ) ) {
			if ( $coupon->is_type( self::LEGACY_TYPE ) ) {
				$coupon->set_amount( 1 );
			}
			$coupon->set_individual_use( true );
			if ( ! $coupon->get_meta( '_fpc_currency', true ) ) {
				$coupon->update_meta_data( '_fpc_currency', self::currency( $coupon ) );
			}
		}
	}

	/**
	 * Show contextual help in the native coupon editor.
	 *
	 * @param int       $coupon_id Coupon ID.
	 * @param WC_Coupon $coupon Coupon object.
	 * @return void
	 */
	public static function instructions( $coupon_id, $coupon ) {
		wp_nonce_field( 'fpc_save_currency_' . $coupon_id, 'fpc_currency_nonce' );
		woocommerce_wp_select(
			array(
				'id'            => '_fpc_currency',
				'label'         => __( 'Target price currency', 'flexible-price-coupons' ),
				'options'       => get_woocommerce_currencies(),
				'value'         => self::currency( $coupon ),
				'wrapper_class' => 'fpc-currency-field',
				'desc_tip'      => true,
				'description'   => __( 'The coupon applies only when checkout uses this currency. No automatic currency conversion is performed.', 'flexible-price-coupons' ),
			)
		);
		echo '<p class="form-field fpc-target-price-help" hidden>' . esc_html__( 'Enter the target price per eligible unit in Coupon amount, using the selected target price currency. Zero makes eligible units free. This is the final unit price, not the amount to subtract. Products below the target keep their lower price. Coupons cannot be combined. Tax follows the store price-entry setting; shipping and fees are separate. Legacy coupons remain SAR 1 only.', 'flexible-price-coupons' ) . '</p>';
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
		wp_enqueue_script( 'flexible-price-coupons-editor', plugins_url( 'assets/js/coupon-editor.js', FLEXIBLE_PRICE_COUPONS_FILE ), array( 'jquery' ), FLEXIBLE_PRICE_COUPONS_VERSION, true );
	}

	/**
	 * Show a dependency notice to plugin administrators.
	 *
	 * @return void
	 */
	public static function missing_woocommerce() {
		if ( current_user_can( 'activate_plugins' ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Flexible Price Coupons requires WooCommerce to be active.', 'flexible-price-coupons' ) . '</p></div>';
		}
	}
}
