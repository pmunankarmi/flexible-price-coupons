# Flexible Price Coupons

A free WordPress plugin for WooCommerce. Coupon managers set a **target product price** and **currency** for each coupon: USD 5, EUR 10, GBP 2, or any supported currency and non-negative price.

Upload `flexible-price-coupons.zip`, activate it with WooCommerce, then create a coupon under **Marketing → Coupons**. Choose **Target product price**, enter the final unit price in **Coupon amount**, and choose **Target price currency**. See [readme.txt](readme.txt) for complete merchant instructions.

The selected currency is an eligibility constraint, not an exchange-rate conversion. Checkout must already use that currency. Tax follows the store's price-entry basis; shipping and fees are separate. Lower-priced products are never marked up. Zero is supported. Native restrictions and usage limits are respected, and coupon stacking is blocked.

If upgrading from Blue One Riyal Coupons, deactivate it first. Existing legacy coupons retain SAR 1 behavior. Edit their discount type to use configurable prices and currencies.

## Development

```sh
composer install
composer lint
composer test
python3 scripts/build.py
```

The build writes an installable ZIP beside the repository, including only runtime files, the directory readme and GPL license. Tests, dependencies and Git metadata are excluded.

Run real WooCommerce tests only on a disposable local WordPress site whose administrator email is `plugin-test@example.test`. They create products, coupons and orders and change store settings:

```sh
wp eval-file /absolute/path/to/flexible-price-coupons/tests/integration.php
```

Use the official Plugin Check against the extracted ZIP. Preload its `cli.php` file for runtime checks:

```sh
wp plugin check flexible-price-coupons --require=/absolute/path/to/wp-content/plugins/plugin-check/cli.php
```

Validation environment: WordPress 7.1.3, WooCommerce 11.2.1, PHP 8.5.7, official SQLite integration. Payment transactions, browser checkout, older platform versions and third-party currency/pricing extensions need separate testing. WordPress.org approval is not implied by a public source repository.

## Privacy and license

No external requests, tracking or advertisements. Coupon currency metadata and historical records are retained on removal. Copyright 2026 pmunankarmi; [GPL version 2 or later](LICENSE.txt).
