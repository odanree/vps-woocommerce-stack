<?php
/**
 * Storefront 1:1 headless match.
 * Replaces two-row header with single-row, removes sidebar/breadcrumbs/
 * footer links, hides front-page title. CSS injected via wp_head so it
 * is never blocked by the Redis object cache.
 */

// ── CSS ──────────────────────────────────────────────────────────────
add_action('wp_head', function () {
    echo '<style id="sf-headless-css">';
    echo '
.sf-header{background:#fff;border-bottom:1px solid #e5e7eb;}
.sf-header__inner{max-width:1280px;margin:0 auto;padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:2rem;}
.sf-logo{font-size:1.5rem;font-weight:700;color:#111827;text-decoration:none;flex-shrink:0;}
.sf-logo:hover{color:#4f46e5;}
.sf-header__right{display:flex;align-items:center;gap:1.5rem;flex:1;justify-content:flex-end;}
.sf-search{display:flex;max-width:260px;width:100%;}
.sf-search__input{width:100%;padding:.5rem .75rem;border:1px solid #e5e7eb;border-radius:.375rem;font-size:.875rem;color:#374151;background:#f9fafb;outline:none;}
.sf-search__input:focus{border-color:#4f46e5;background:#fff;box-shadow:0 0 0 2px rgba(79,70,229,.1);}
.sf-nav{display:flex;align-items:center;gap:1.5rem;}
.sf-nav__list{display:flex;align-items:center;gap:1.5rem;list-style:none;margin:0;padding:0;}
.sf-nav__list li a,.sf-nav__link{font-size:.875rem;font-weight:500;color:#374151;text-decoration:none;transition:color .2s;}
.sf-nav__list li a:hover,.sf-nav__link:hover{color:#4f46e5;}
.sf-cart__count{display:inline-flex;align-items:center;justify-content:center;background:#4f46e5;color:#fff;font-size:.7rem;font-weight:700;border-radius:9999px;min-width:1.1rem;height:1.1rem;padding:0 .25rem;margin-left:.25rem;vertical-align:middle;}
.site-header{display:none!important;}
#secondary{display:none!important;}
#primary{width:100%!important;float:none!important;}
.woocommerce-breadcrumb{display:none!important;}
.home.page .entry-header{display:none!important;}
.wp-block-cover.alignfull{display:none;}
ul.products::before,ul.products::after{display:none!important;}
.sf-hero{background:linear-gradient(135deg,#0a0a12 0%,#0f0e24 50%,#0a0a12 100%);padding:5rem 1.5rem 4.5rem;position:relative;overflow:hidden;width:100vw;margin-left:calc(50% - 50vw);}
.sf-hero::before{content:"";position:absolute;inset:0;background-image:radial-gradient(circle,rgba(79,70,229,.18) 1px,transparent 1px);background-size:28px 28px;pointer-events:none;}
.sf-hero::after{content:"";position:absolute;top:-40%;right:-10%;width:600px;height:600px;background:radial-gradient(circle,rgba(79,70,229,.12) 0%,transparent 65%);pointer-events:none;}
.sf-hero__inner{max-width:1280px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;position:relative;z-index:1;}
.sf-hero__badge{display:inline-flex;align-items:center;gap:.5rem;padding:.3rem .9rem;background:rgba(79,70,229,.15);border:1px solid rgba(99,102,241,.35);color:#a5b4fc;font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;border-radius:9999px;margin-bottom:1.75rem;}
.sf-hero__badge::before{content:"";width:6px;height:6px;border-radius:50%;background:#4f46e5;box-shadow:0 0 6px #4f46e5;}
.sf-hero__headline{font-size:clamp(2.4rem,4.5vw,3.8rem);font-weight:800;line-height:1.08;color:#fff;margin:0 0 1.25rem;letter-spacing:-.03em;}
.sf-hero__accent{background:linear-gradient(90deg,#818cf8 0%,#4f46e5 50%,#7c3aed 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.sf-hero__sub{color:#9ca3af;font-size:1.05rem;line-height:1.65;margin:0 0 2.25rem;max-width:420px;}
.sf-hero__actions{display:flex;gap:.875rem;flex-wrap:wrap;margin-bottom:3rem;}
.sf-hero__cta-primary{display:inline-flex;align-items:center;gap:.4rem;padding:.8rem 1.875rem;background:#4f46e5;color:#fff!important;font-weight:700;font-size:.9rem;border-radius:.5rem;text-decoration:none!important;transition:background .2s,transform .15s,box-shadow .2s;box-shadow:0 4px 20px rgba(79,70,229,.45);}
.sf-hero__cta-primary:hover{background:#4338ca;transform:translateY(-2px);box-shadow:0 8px 28px rgba(79,70,229,.55);}
.sf-hero__cta-secondary{display:inline-flex;align-items:center;gap:.4rem;padding:.8rem 1.5rem;background:transparent;color:#d1d5db!important;font-weight:600;font-size:.9rem;border-radius:.5rem;text-decoration:none!important;border:1px solid rgba(255,255,255,.12);transition:border-color .2s,color .2s,background .2s;}
.sf-hero__cta-secondary:hover{border-color:rgba(255,255,255,.3);color:#fff!important;background:rgba(255,255,255,.05);}
.sf-hero__stats{display:flex;gap:2.5rem;}
.sf-hero__stat{display:flex;flex-direction:column;}
.sf-hero__stat strong{color:#fff;font-size:1.4rem;font-weight:800;line-height:1;}
.sf-hero__stat span{color:#6b7280;font-size:.78rem;margin-top:.3rem;letter-spacing:.02em;}
.sf-hero__visual{position:relative;display:flex;align-items:center;justify-content:center;min-height:340px;}
.sf-hero__ring{position:absolute;width:360px;height:360px;border:1px solid rgba(79,70,229,.2);border-radius:50%;animation:sf-spin 25s linear infinite;}
.sf-hero__ring::before{content:"";position:absolute;width:8px;height:8px;background:#4f46e5;border-radius:50%;top:-4px;left:50%;transform:translateX(-50%);box-shadow:0 0 10px rgba(79,70,229,.8);}
.sf-hero__ring-2{position:absolute;width:270px;height:270px;border:1px solid rgba(124,58,237,.15);border-radius:50%;animation:sf-spin 18s linear infinite reverse;}
@keyframes sf-spin{to{transform:rotate(360deg)}}
.sf-hero__card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:1rem;padding:0;overflow:hidden;width:100%;max-width:340px;position:relative;z-index:1;box-shadow:0 24px 48px rgba(0,0,0,.5),inset 0 1px 0 rgba(255,255,255,.08);}
.sf-hero__card-bar{display:flex;align-items:center;gap:.5rem;padding:.75rem 1rem;background:rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.07);}
.sf-hero__card-dot{width:10px;height:10px;border-radius:50%;}
.sf-hero__card-dot:nth-child(1){background:#ff5f57;}
.sf-hero__card-dot:nth-child(2){background:#ffbd2e;}
.sf-hero__card-dot:nth-child(3){background:#28c840;}
.sf-hero__card-title{color:#6b7280;font-size:.72rem;font-family:monospace;margin-left:.25rem;}
.sf-hero__code{padding:1.5rem;display:flex;flex-direction:column;gap:.6rem;font-family:"Fira Code","Cascadia Code","Courier New",monospace;font-size:.84rem;line-height:1.5;}
.sf-code-ln{color:#374151;margin-right:.75rem;user-select:none;font-size:.75rem;}
.sf-code-kw{color:#818cf8;}
.sf-code-var{color:#f9fafb;}
.sf-code-op{color:#9ca3af;}
.sf-code-str{color:#86efac;}
.sf-code-fn{color:#fbbf24;}
.sf-code-cm{color:#374151;}
.sf-code-cursor{display:inline-block;width:2px;height:.9em;background:#4f46e5;vertical-align:middle;animation:sf-blink 1.1s step-end infinite;margin-left:1px;}
@keyframes sf-blink{0%,100%{opacity:1}50%{opacity:0}}
.sf-hero__section-label{display:flex;align-items:center;gap:.75rem;margin:0 auto 1.5rem;max-width:1280px;padding:0 1.5rem;}
.sf-hero__section-label::before,.sf-hero__section-label::after{content:"";flex:1;height:1px;background:#e5e7eb;}
.sf-hero__section-label span{color:#6b7280;font-size:.8rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap;}
@media(max-width:900px){.sf-hero__inner{grid-template-columns:1fr;}.sf-hero__visual{display:none;}.sf-hero__sub{max-width:100%;}.sf-hero__stats{gap:1.75rem;}}
@media(max-width:480px){.sf-hero{padding:3.5rem 1.25rem 3rem;}.sf-hero__stats{gap:1.25rem;}.sf-hero__actions{flex-direction:column;}.sf-hero__cta-primary,.sf-hero__cta-secondary{justify-content:center;}}
ul.products{display:grid!important;grid-template-columns:repeat(4,1fr)!important;gap:1.5rem!important;list-style:none!important;padding:0!important;margin:0 auto!important;max-width:1280px;}
@media(max-width:900px){ul.products{grid-template-columns:repeat(2,1fr)!important;}}
@media(max-width:480px){ul.products{grid-template-columns:1fr!important;}}
ul.products li.product{width:100%!important;float:none!important;margin:0!important;border:1px solid #e5e7eb!important;border-radius:.5rem!important;overflow:hidden;transition:box-shadow .3s ease;padding:0!important;background:#fff;}
ul.products li.product:hover{box-shadow:0 10px 15px -3px rgba(0,0,0,.1),0 4px 6px -2px rgba(0,0,0,.05)!important;}
ul.products li.product a{text-decoration:none;color:inherit;}
ul.products li.product a img{width:100%;aspect-ratio:1/1;object-fit:cover;display:block;margin:0!important;transition:transform .3s ease;}
ul.products li.product:hover a img{transform:scale(1.05);}
ul.products li.product .woocommerce-loop-product__title{font-size:.95rem!important;font-weight:600!important;padding:1rem 1rem .25rem!important;transition:color .2s;color:#111827;}
ul.products li.product:hover .woocommerce-loop-product__title{color:#4f46e5;}
ul.products li.product .price{padding:.25rem 1rem 0!important;font-size:1.1rem!important;font-weight:700!important;color:#111827!important;display:block;}
ul.products li.product .button{margin:.75rem 1rem 1rem!important;display:block;text-align:center;font-size:.875rem!important;font-weight:600!important;border-radius:.375rem!important;padding:.6rem 1rem!important;background:#4f46e5!important;color:#fff!important;border:none!important;transition:background .2s;}
ul.products li.product .button:hover{background:#4338ca!important;}
.footer-widgets{display:none;}
.site-info{text-align:center;padding:1.5rem;font-size:.85rem;color:#9ca3af;border-top:1px solid #e5e7eb;}
.site-footer a{color:#6b7280;}
    ';
    echo '</style>';
}, 1);

// ── Header replacement ───────────────────────────────────────────────
add_action('after_setup_theme', function () {
    remove_action('storefront_header', 'storefront_header_container',              10);
    remove_action('storefront_header', 'storefront_site_branding',                 20);
    remove_action('storefront_header', 'storefront_secondary_navigation',          30);
    remove_action('storefront_header', 'storefront_product_search',                40);
    remove_action('storefront_header', 'storefront_header_container_close',        41);
    remove_action('storefront_header', 'storefront_primary_navigation_wrapper',    42);
    remove_action('storefront_header', 'storefront_primary_navigation',            50);
    remove_action('storefront_header', 'storefront_header_cart',                   60);
    remove_action('storefront_header', 'storefront_header_widget_region',          65);
    remove_action('storefront_header', 'storefront_primary_navigation_wrapper_close', 68);
    remove_action('storefront_sidebar', 'storefront_get_sidebar',                  10);
}, 20);

add_action('storefront_before_header', function () {
    $count = function_exists('WC') && WC()->cart
        ? WC()->cart->get_cart_contents_count() : 0;
    ?>
    <div class="sf-header">
        <div class="sf-header__inner">
            <a class="sf-logo" href="<?php echo esc_url(home_url('/')); ?>">
                <?php bloginfo('name'); ?>
            </a>
            <div class="sf-header__right">
                <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="sf-search">
                    <input class="sf-search__input" type="search" placeholder="Search products…"
                           value="<?php echo esc_attr(get_search_query()); ?>" name="s">
                    <input type="hidden" name="post_type" value="product">
                </form>
                <nav class="sf-nav">
                    <?php wp_nav_menu([
                        'theme_location' => 'primary',
                        'container'      => false,
                        'menu_class'     => 'sf-nav__list',
                        'depth'          => 1,
                        'fallback_cb'    => function () {
                            $links = ['Shop' => '/shop', 'My Account' => '/my-account'];
                            echo '<ul class="sf-nav__list">';
                            foreach ($links as $label => $url) {
                                echo '<li><a href="' . esc_url(home_url($url)) . '">' . esc_html($label) . '</a></li>';
                            }
                            echo '</ul>';
                        },
                    ]); ?>
                    <a class="sf-nav__link sf-cart" href="<?php echo esc_url(wc_get_cart_url()); ?>">
                        Cart<?php if ($count > 0): ?>
                        <span class="sf-cart__count"><?php echo esc_html($count); ?></span>
                        <?php endif; ?>
                    </a>
                </nav>
            </div>
        </div>
    </div>
    <?php
}, 5);

// ── Breadcrumbs ──────────────────────────────────────────────────────
add_action('init', function () {
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
});

// ── Footer ───────────────────────────────────────────────────────────
remove_action('storefront_footer', 'storefront_footer_links', 10);
add_filter('storefront_credit_links_output', '__return_empty_string');

// ── Page title on front page ─────────────────────────────────────────
add_filter('storefront_show_page_title', function ($show) {
    return is_front_page() ? false : $show;
});
