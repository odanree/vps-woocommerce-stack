<?php
/**
 * Local dev seed — runs once on wp-env boot.
 * Creates 8 demo WooCommerce products, sets up the nav menu,
 * and configures the homepage to match the headless reference design.
 *
 * Delete this file (or flip SEED_DONE option) to re-seed.
 */

add_action('woocommerce_init', function () {
    // Run once — guard against re-seeding on every page load
    if (get_option('_sf_seed_done')) {
        return;
    }

    if (!class_exists('WooCommerce')) {
        return;
    }

    // ── Products ──────────────────────────────────────────────────────────────
    $products = [
        [
            'name'  => 'Developer Black Tee',
            'price' => '34.99',
            'desc'  => 'Classic black tee for people who ship code at 2 AM.',
            'color' => '1a1a1a',
        ],
        [
            'name'  => 'Ship It Hoodie',
            'price' => '64.99',
            'desc'  => 'Heavyweight pullover hoodie. Deploy with confidence.',
            'color' => '4f46e5',
        ],
        [
            'name'  => 'localhost:3000 Tee',
            'price' => '29.99',
            'desc'  => 'The URL that never judges your hot reloads.',
            'color' => '374151',
        ],
        [
            'name'  => 'Production Rollback Hoodie',
            'price' => '69.99',
            'desc'  => 'Sometimes you need to go back. Look good doing it.',
            'color' => '991b1b',
        ],
        [
            'name'  => 'git commit -m "fix" Tee',
            'price' => '32.99',
            'desc'  => 'Commit message accuracy sold separately.',
            'color' => '065f46',
        ],
        [
            'name'  => 'Rubber Duck Debug Tee',
            'price' => '27.99',
            'desc'  => 'Explain your code to a duck. It works, somehow.',
            'color' => 'b45309',
        ],
        [
            'name'  => 'On-Call Survivor Hoodie',
            'price' => '74.99',
            'desc'  => 'You answered the 3 AM page. You earned this.',
            'color' => '1e3a5f',
        ],
        [
            'name'  => '99 Bugs Tee',
            'price' => '31.99',
            'desc'  => '99 bugs in the code, you fix one, 127 bugs in the code.',
            'color' => '4c1d95',
        ],
    ];

    foreach ($products as $data) {
        // Skip if already exists by name
        $existing = get_posts([
            'post_type'   => 'product',
            'post_status' => 'publish',
            'title'       => $data['name'],
            'fields'      => 'ids',
            'numberposts' => 1,
        ]);
        if ($existing) {
            continue;
        }

        $product = new WC_Product_Simple();
        $product->set_name($data['name']);
        $product->set_status('publish');
        $product->set_regular_price($data['price']);
        $product->set_description($data['desc']);
        $product->set_short_description($data['desc']);
        $product->set_manage_stock(false);
        $product->set_stock_status('instock');
        $product->set_catalog_visibility('visible');

        $product_id = $product->save();

        // Use a placeholder image (via placeholder service) as featured image
        $image_url = 'https://picsum.photos/seed/' . urlencode($data['name']) . '/600/600';
        $image_id  = _sf_sideload_image($image_url, $product_id, $data['name']);
        if ($image_id && !is_wp_error($image_id)) {
            set_post_thumbnail($product_id, $image_id);
        }
    }

    // ── WooCommerce pages ─────────────────────────────────────────────────────
    $wc_pages = [
        'shop'       => ['title' => 'Shop',       'option' => 'woocommerce_shop_page_id'],
        'cart'       => ['title' => 'Cart',       'option' => 'woocommerce_cart_page_id',       'content' => '<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->'],
        'checkout'   => ['title' => 'Checkout',   'option' => 'woocommerce_checkout_page_id',   'content' => '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->'],
        'my-account' => ['title' => 'My Account', 'option' => 'woocommerce_myaccount_page_id', 'content' => '<!-- wp:shortcode -->[woocommerce_my_account]<!-- /wp:shortcode -->'],
    ];

    foreach ($wc_pages as $slug => $cfg) {
        $existing = get_page_by_path($slug);
        if (!$existing) {
            $id = wp_insert_post([
                'post_title'   => $cfg['title'],
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => $cfg['content'] ?? '',
            ]);
        } else {
            $id = $existing->ID;
            // Always enforce shortcode content — WC 8+ pre-creates these pages
            // with the Checkout/Cart Block, which breaks traditional PHP gateways.
            if (!empty($cfg['content'])) {
                wp_update_post(['ID' => $id, 'post_content' => $cfg['content']]);
            }
        }
        update_option($cfg['option'], $id);
    }

    // ── Homepage ──────────────────────────────────────────────────────────────
    $homepage_content = '<!-- wp:cover {"customOverlayColor":"#1a1a1a","minHeight":480,"align":"full","isDark":true} -->'
        . '<div class="wp-block-cover alignfull is-dark" style="min-height:480px">'
        . '<span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim" style="background-color:#1a1a1a"></span>'
        . '<div class="wp-block-cover__inner-container">'
        . '<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"2.8rem","fontWeight":"700"}},"textColor":"white"} -->'
        . '<h1 class="wp-block-heading has-text-align-center has-white-color has-text-color" style="font-size:2.8rem;font-weight:700">Developer Gear for People Who Ship</h1>'
        . '<!-- /wp:heading -->'
        . '<!-- wp:paragraph {"align":"center","textColor":"white"} -->'
        . '<p class="has-text-align-center has-white-color has-text-color">Premium t-shirts for developers, designers, and builders.</p>'
        . '<!-- /wp:paragraph -->'
        . '<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->'
        . '<div class="wp-block-buttons"><!-- wp:button -->'
        . '<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/shop">Shop Now</a></div>'
        . '<!-- /wp:button --></div>'
        . '<!-- /wp:buttons -->'
        . '</div></div>'
        . '<!-- /wp:cover -->'
        . '<!-- wp:spacer {"height":"56px"} --><div style="height:56px" aria-hidden="true" class="wp-block-spacer"></div><!-- /wp:spacer -->'
        . '<!-- wp:heading {"textAlign":"center","level":2} -->'
        . '<h2 class="wp-block-heading has-text-align-center">Featured Products</h2>'
        . '<!-- /wp:heading -->'
        . '<!-- wp:paragraph {"align":"center"} -->'
        . '<p class="has-text-align-center">Wear the stack you build with.</p>'
        . '<!-- /wp:paragraph -->'
        . '<!-- wp:shortcode -->[products limit="8" columns="4" orderby="date" order="DESC"]<!-- /wp:shortcode -->'
        . '<!-- wp:spacer {"height":"56px"} --><div style="height:56px" aria-hidden="true" class="wp-block-spacer"></div><!-- /wp:spacer -->';

    $existing_home = get_page_by_path('home-storefront');
    if ($existing_home) {
        wp_update_post(['ID' => $existing_home->ID, 'post_content' => $homepage_content]);
        $home_id = $existing_home->ID;
    } else {
        $home_id = wp_insert_post([
            'post_title'   => 'Home',
            'post_name'    => 'home-storefront',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => $homepage_content,
        ]);
    }

    update_option('show_on_front', 'page');
    update_option('page_on_front', $home_id);

    // ── Storefront settings ───────────────────────────────────────────────────
    set_theme_mod('storefront_layout', 'full-width');
    update_option('blogdescription', '');

    // Clear header/sidebar widget areas
    $sw = get_option('sidebars_widgets', []);
    $sw['header-1'] = [];
    $sw['sidebar-1'] = [];
    update_option('sidebars_widgets', $sw);

    // ── Navigation menu ───────────────────────────────────────────────────────
    $locations = get_nav_menu_locations();
    if (!empty($locations['primary'])) {
        wp_delete_nav_menu($locations['primary']);
    }

    $menu_id = wp_create_nav_menu('Primary Navigation');

    foreach (['shop' => 'Shop', 'my-account' => 'My Account'] as $slug => $label) {
        $page = get_page_by_path($slug);
        if ($page) {
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title'     => $label,
                'menu-item-object'    => 'page',
                'menu-item-object-id' => $page->ID,
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ]);
        }
    }

    $locs            = get_nav_menu_locations();
    $locs['primary'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locs);

    // ── Activate Storefront theme if not already ──────────────────────────────
    if (get_option('stylesheet') !== 'storefront') {
        switch_theme('storefront');
    }

    // Mark as done
    update_option('_sf_seed_done', true);
});

// ── Helper: sideload a remote image into the WP media library ────────────────
function _sf_sideload_image(string $url, int $post_id, string $desc): int|WP_Error {
    if (!function_exists('media_sideload_image')) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }
    return media_sideload_image($url, $post_id, $desc, 'id');
}
