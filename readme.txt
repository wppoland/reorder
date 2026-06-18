=== Reorder - Quick Reorder for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, reorder, buy again, repeat order, order again
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds an "Order again" button to past WooCommerce orders. One click re-adds the still-available items to the cart and sends the customer onward.

== Description ==

Reorder adds an **"Order again"** button to each past order in WooCommerce **My Account → Orders**. When a customer clicks it, every still-purchasable item from that order goes back into the cart, and they land on the cart page or the checkout — whichever you've chosen in the settings.

Items that are gone (deleted, hidden, or out of stock) are left out, and the customer sees a notice naming what couldn't be added so there are no silent surprises in the cart.

What the plugin does:

* Re-adds a whole order's worth of products in one click, instead of the customer searching for each item again.
* Keeps the original variation — if they bought the medium in blue, that's the variation that comes back.
* Checks the nonce on every reorder link and confirms the logged-in customer owns the order before touching the cart, so one customer can't reorder another's order.
* Skips unavailable products with a notice rather than leaving the customer with a broken or half-filled cart.
* Adds no front-end JavaScript and no extra markup. The button is a normal WooCommerce order action with a small, themeable stylesheet (loaded only on the orders page), so it stands out without shifting your account-page layout.

Settings live under **WooCommerce → Reorder**: change the button label, pick which order statuses get the button (Completed, Processing, On hold), and decide whether reordering lands on the cart or the checkout.

Source and bug reports live on GitHub at https://github.com/wppoland/reorder — issues and patches welcome.

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
