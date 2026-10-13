# Blue One Riyal Coupons

A free, GPL-licensed WordPress plugin that adds a **Product price = SAR 1** coupon type to WooCommerce. Works with any theme and uses the native coupon editor, eligibility rules and order discounts.

## Install

Download `blue-one-riyal-coupons.zip` from [Releases](https://github.com/pmunankarmi/blue-one-riyal-coupons/releases), upload it under **Plugins → Add New Plugin → Upload Plugin**, and activate it with WooCommerce active.

Create a coupon under **Marketing → Coupons**, choose **Product price = SAR 1**, set any restrictions and publish. No coupon is automatically published. See [readme.txt](readme.txt) for merchant instructions and tax behavior.

The plugin is being prepared for the WordPress.org directory. A GitHub release is not WordPress.org approval or a directory listing.

## Development

Requires PHP 7.4+ and WordPress 6.5+. The current integration verification uses WordPress 7.1.3, WooCommerce 11.2.1, PHP 8.5.7 and the official SQLite integration in a disposable local site. Earlier supported versions and third-party commerce extensions have not been exhaustively tested.

```sh
composer install
composer lint
composer test
python3 scripts/build.py
```

The build allowlists runtime files and excludes tests, development dependencies and Git metadata. It writes the installable ZIP beside the repository directory.

For real WooCommerce tests, use a **disposable** site with administrator email `plugin-test@example.test`. Activate WooCommerce and the plugin, then run:

```sh
wp eval-file /absolute/path/to/blue-one-riyal-coupons/tests/integration.php
```

This creates test products, variations, coupons and an order and changes store settings. Never run it against a real store. Use `wp plugin check blue-one-riyal-coupons` with the official Plugin Check plugin on the extracted release ZIP. For runtime checks, preload its CLI file using `--require=/absolute/path/to/wp-content/plugins/plugin-check/cli.php`.

## Verification for 1.1.0

- WordPress Coding Standards 3.4.1: no errors or warnings in distributed PHP.
- Official Plugin Check (including CLI runtime setup): no findings on the release package.
- 19 isolated regression checks and 17 real WooCommerce integration assertions passed.
- Integration coverage: quantities, removal, item limits, exclusions, expiry, exhausted usage, stacking, SAR-only validation, sub-riyal prices, variations, orders and inclusive/exclusive tax.
- Payment-provider transactions, browser checkout flows, subscriptions, bundles and third-party pricing integrations are not verified.

## Data and licensing

No tracking, remote services, custom tables or automatically created coupons. Removing the plugin retains WooCommerce coupon and order records. Retire custom coupons before deactivation and keep the plugin active when recalculating orders that use them.

Copyright 2026 pmunankarmi. [GNU GPL version 2 or later](LICENSE.txt).
