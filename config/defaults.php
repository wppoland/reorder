<?php
/**
 * Default settings, merged under the option key `reorder_settings`.
 *
 * @package Reorder
 *
 * @return array<string, mixed>
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

return [
    // Label shown on the reorder button.
    'button_text'      => '',
    // Order statuses (without the `wc-` prefix) the button appears on.
    'statuses'         => ['completed'],
    // Where to send the customer after items are re-added: `cart` or `checkout`.
    'redirect'         => 'cart',
];
