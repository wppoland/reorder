# Reorder - Quick Reorder for WooCommerce

Adds a one-click "Order again" button to past WooCommerce orders so customers
can re-add every still-available item to the cart and check out again fast.

## Features

- Adds a reorder button to each qualifying order in My Account → Orders.
- Re-adds every still-purchasable line item to the cart, preserving variations.
- Skips items that are no longer available and tells the customer which ones, instead of breaking the cart.
- Redirects to the cart or straight to checkout, your choice.
- Nonce-protected links with a server-side ownership check, so a customer can only reorder their own orders.
- Configure the button text, which order statuses show it, and the redirect target under WooCommerce → Reorder.

## Installation

1. Upload the plugin to `/wp-content/plugins/reorder`, or install it via Plugins → Add New.
2. Activate it. WooCommerce must be installed and active.
3. Adjust options under WooCommerce → Reorder.

## Frequently Asked Questions

**Does it require WooCommerce?**
Yes. WooCommerce must be installed and active.

**What happens if an item from the old order is no longer available?**
It is skipped and the customer is told which items could not be re-added, so the
rest of the order still reaches the cart.

Built by WPPoland — https://plogins.com

License: GPL-2.0-or-later
