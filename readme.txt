=== Reorder - Quick Reorder for WooCommerce ===
Contributors: wppoland
Tags: woocommerce, reorder, buy again, repeat order, order again
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

One-click reorder for WooCommerce: add a Buy again button to past orders so customers re-add still-purchasable items and check out fast.

== Description ==

Reorder adds a one-click **"Order again"** button to each past order in WooCommerce **My Account → Orders**. Clicking it re-adds every still-purchasable item from that order to the cart and sends the customer to the cart or straight to checkout — turning a repeat purchase into a single tap.

Anything no longer available (deleted, hidden, or out of stock) is skipped automatically, and the customer gets a clear notice listing what could not be re-added.

**Why Reorder?**

* **Fast repeat purchases.** Customers re-buy a whole order without hunting for each product again.
* **Safe by design.** Every reorder link is nonce-protected, and the handler verifies the current user actually owns the order before acting — no IDOR, no acting on someone else's order.
* **Graceful with missing items.** Unavailable products are skipped with a friendly notice instead of breaking the cart.
* **Variation aware.** Re-adds the exact variation (size, colour, etc.) that was originally ordered.
* **No layout shift, no jQuery.** The button sits in the normal WooCommerce order actions.
* **Configurable.** Choose the button label, which order statuses show the button, and whether to send shoppers to the cart or checkout.

**Settings (WooCommerce → Reorder)**

* Button text
* Which order statuses show the button (Completed, Processing, On hold)
* Redirect target after reordering (cart or checkout)

== Installation ==

1. Install and activate WooCommerce (8.0 or later).
2. Install Reorder from the WordPress plugin directory, or upload the `reorder` folder to `/wp-content/plugins/`.
3. Activate the plugin through the **Plugins** screen.
4. Optionally visit **WooCommerce → Reorder** to set the button text, statuses, and redirect; sensible defaults work out of the box.
5. An "Order again" button now appears on qualifying past orders in **My Account → Orders**.

== Frequently Asked Questions ==

= Is Reorder free? =
Yes. Reorder is free and licensed under the GPL.

= Does Reorder require WooCommerce? =
Yes. Reorder is a WooCommerce extension and requires WooCommerce 8.0 or later. It shows an admin notice and stays inactive if WooCommerce is missing or out of date.

= What happens to items that are no longer available? =
They are skipped, and the customer sees a notice naming the products that could not be re-added. Everything still purchasable is added to the cart.

= Which orders show the button? =
By default, completed orders. You can enable Processing and On hold too under **WooCommerce → Reorder**. The button only shows to the customer who owns the order.

= Does it work with product variations? =
Yes. The original variation (e.g. size and colour) is preserved, so the correct variation is added back to the cart.

= Where does the customer go after reordering? =
To the cart by default, or straight to checkout — your choice in the settings.

= How do I remove all plugin data? =
Deleting the plugin from the **Plugins** screen runs the uninstall routine, which removes the `reorder_settings` and `reorder_db_version` options. Reorder stores no custom tables.

== External Services ==

Reorder does not connect to any external services. It only re-adds items to the standard WooCommerce cart on your own site.

== Screenshots ==

1. The "Order again" button on the My Account orders list.
2. Settings page — button text, order statuses, and redirect target.

== Changelog ==

= 0.1.0 =
* Initial release: one-click reorder button on past orders, ownership-checked and nonce-protected, with configurable label, statuses, and redirect target.
