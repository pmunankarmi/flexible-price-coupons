=== Blue One Riyal Coupons ===
Contributors: pmunankarmi
Tags: coupons, discounts, woocommerce, sar, pricing
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Requires Plugins: woocommerce
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create coupons that reduce eligible WooCommerce products to SAR 1 each, with native product restrictions, expiry dates and usage limits.

== Description ==

Blue One Riyal Coupons adds a "Product price = SAR 1" discount type to the WooCommerce coupon editor. Store owners choose their own coupon codes and publish them using the normal coupon workflow.

* Discount each eligible unit to one Saudi riyal.
* Use the same coupon with simple products and product variations.
* Apply to all products, or restrict by product or category.
* Keep WooCommerce's expiry, email restrictions and usage limits.
* Limit the number of discounted items using the native item-limit field.
* Prevent combining this coupon with other coupons.
* Keep catalog prices unchanged: the reduction is recorded as a coupon discount.

The plugin requires WooCommerce and Saudi riyals (SAR) as the transaction currency. Installation does not automatically create or publish a coupon. It works independently of your theme.

= Prices, tax and shipping =

The target price follows WooCommerce's price-entry tax basis. If prices are entered excluding tax, applicable tax is added to SAR 1. If prices are entered including tax, WooCommerce calculates the included tax. Customer tax exemptions, location adjustments and rounding follow WooCommerce rules. Shipping and fees are separate: this does not make an entire order cost SAR 1.

Two discounted units cost SAR 2 on that tax basis. Products already priced below SAR 1 keep their lower price. Applying this coupon removes other coupons using WooCommerce's individual-use behavior.

= Privacy and external services =

This plugin makes no external service requests, contains no tracking, and adds no separate customer database. Coupon and order data remain in WooCommerce. It loads a small local script only in the coupon editor.

= Compatibility =

Designed for standard WooCommerce simple products and variations. Third-party subscriptions, bundles, dynamic pricing and multi-currency extensions require separate testing. Other extensions can alter prices, coupon filters or taxes. Test your store's checkout configuration before running a promotion.

== Installation ==

1. Install and activate WooCommerce. Set the store currency to Saudi riyals (SAR).
2. Upload the plugin ZIP using Plugins > Add New Plugin > Upload Plugin, and activate it.
3. Enable coupons in WooCommerce > Settings > General if needed.
4. Open Marketing > Coupons > Add coupon and enter a code such as ONESR.
5. Select "Product price = SAR 1" as the discount type. The amount is fixed at 1.
6. Leave product and category restrictions empty for all products. Leave "Exclude sale items" unchecked to include sale products.
7. Set any desired expiry date, email restrictions and usage limits, then publish.
8. Apply the code in the cart or checkout.

== Frequently Asked Questions ==

= Does this work without WooCommerce? =

No. WooCommerce provides the cart, products, checkout and coupon management.

= Can I choose my own coupon code? =

Yes. Create any available coupon code using WooCommerce's native coupon editor.

= Does it discount every product in the cart? =

Every eligible unit is discounted unless you set an item limit. Product/category restrictions and sale exclusions are respected. Set "Limit usage to X items" to 1 for one discounted unit per cart. WooCommerce selects eligible items in its own discount order; this is not a customer-selected item picker.

= Is the final order total exactly SAR 1? =

No. The target is per eligible product unit, on your store's tax-entry basis. Shipping, fees, taxes and other cart items can increase the order total.

= Does the coupon work in other currencies? =

No. It is specifically for SAR transactions. Currency conversion integrations are not supported automatically.

= What happens when I deactivate or delete the plugin? =

The custom discount calculation stops. Existing coupons and historical orders are retained. Retire these coupons before deactivating. Keep the plugin active when recalculating orders that use this custom coupon type.

= Does this plugin send data to external services? =

No. It makes no external requests and includes no analytics or advertising.

== Changelog ==

= 1.1.0 =
* Prepare the first public distribution with directory metadata and documentation.
* Load editor assets through the WordPress enqueue API on coupon screens only.
* Preserve the coupon type identifier for coupons created with version 1.0.0.

= 1.0.0 =
* Initial SAR 1 coupon implementation.

== Upgrade Notice ==

= 1.1.0 =
Public distribution preparation. Existing one-riyal coupons retain their discount type and behavior.
