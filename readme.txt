=== Flexible Price Coupons ===
Contributors: pmunankarmi
Tags: coupons, discounts, woocommerce, pricing, currency
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Requires Plugins: woocommerce
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create WooCommerce coupons with an admin-defined target product price and currency, using native product restrictions and usage limits.

== Description ==

Flexible Price Coupons lets store administrators and authorized coupon managers choose the final price of each eligible product unit and the currency for each coupon.

Select the "Target product price" discount type, enter a price such as 1, 2, 5, 10 or 12.50 in Coupon amount, and choose a Target price currency. For example, a USD 5 coupon reduces a USD 100 product to USD 5. The amount is the target price, not the amount subtracted.

* Set any non-negative target price per coupon, including zero for free eligible products.
* Choose from the currencies supported by WooCommerce.
* Apply only when the active checkout currency matches the coupon currency.
* Support simple products and variations without changing catalog prices.
* Use native product/category restrictions, expiry dates, email restrictions and usage limits.
* Limit the number of discounted units using the native item-limit field.
* Prevent coupon stacking and never increase prices already below the target.

Works independently of your theme. WooCommerce is required. No coupons are created or published automatically.

= Currency behavior =

The chosen currency is saved with the coupon. Changing the store currency does not change saved coupon currencies. A USD 5 coupon is rejected at an EUR checkout; it does not become EUR 5. The plugin does not switch storefront currency or perform exchange-rate conversion. A store must already support checkout in the selected currency. Third-party multi-currency extensions require compatibility testing. Order recalculation requires the order currency, active WooCommerce currency and coupon currency to match.

= Tax, shipping and quantities =

The target uses the store's price-entry tax basis. If prices are entered excluding tax, applicable tax is added to the target. For tax-inclusive prices, WooCommerce calculates the included tax. Exemptions, location adjustments and currency rounding follow WooCommerce settings. Shipping and fees remain separate.

A target of 5 with two eligible units leaves a product total of 10 on that tax basis. Products below the target retain their lower price. Applying a target-price coupon removes other coupons using individual-use behavior.

= Privacy and external services =

No external service requests, tracking, advertising or separate customer database. Coupon currency is stored in WooCommerce coupon metadata. A local editor script loads only on coupon editing screens. WooCommerce retains coupon and order data.

== Installation ==

1. Install and activate WooCommerce.
2. Upload flexible-price-coupons.zip through Plugins > Add New Plugin > Upload Plugin and activate it.
3. Enable coupons under WooCommerce > Settings > General if necessary.
4. Open Marketing > Coupons > Add coupon and enter your chosen code.
5. Choose "Target product price" as the discount type.
6. Enter the final unit price in Coupon amount, then select Target price currency.
7. Set product restrictions and usage limits if desired, then publish.
8. Apply the coupon at a checkout using the same currency.

== Frequently Asked Questions ==

= Can I set prices such as 1, 2, 5 or 10? =

Yes. Enter any non-negative amount using WooCommerce's normal price field. Decimal prices are rounded according to the store's currency precision. Zero makes eligible product units free; shipping and fees may still apply.

= Who can change the price and currency? =

Administrators and users authorized to edit the coupon, such as shop managers. The native WooCommerce form handles price permissions. Currency changes require coupon-edit permission, a verified nonce and a supported currency code.

= Does choosing USD convert my store to USD? =

No. Currency selection controls where the coupon is eligible. It does not add currency switching or conversion to the store.

= Does it apply to every product? =

Leave product/category restrictions empty to allow all eligible products. Set "Limit usage to X items" to limit discounted units. WooCommerce determines the order in which eligible items are discounted.

= What happens to coupons from Blue One Riyal Coupons? =

Deactivate the old plugin before activating this one. Existing legacy coupons continue to target SAR 1 and remain restricted to SAR. To use a different price or currency, edit the coupon and select "Target product price". Do not run both plugins together.

= What happens when I remove the plugin? =

The custom calculation stops. Existing coupons, currency metadata and historical orders remain. Retire these coupons before deactivation and keep the plugin active when recalculating orders that use its discount types.

= Are bundles, subscriptions and currency-switcher plugins supported? =

They require separate compatibility checks. Standard simple products and variations use native WooCommerce discount calculations. Test your store's checkout and extensions before running a promotion.

== Changelog ==

= 1.2.0 =
* Rename the plugin to Flexible Price Coupons.
* Add configurable target prices and per-coupon currency selection.
* Validate currency saving with permissions, nonce verification and an allowlist.
* Preserve existing SAR 1 coupons using a legacy discount type.

= 1.1.0 =
* Add WordPress directory metadata and scoped editor assets.

= 1.0.0 =
* Initial limited-currency coupon implementation.

== Upgrade Notice ==

= 1.2.0 =
Deactivate Blue One Riyal Coupons before installing Flexible Price Coupons. Legacy coupons retain SAR 1 behavior until you change their discount type.
