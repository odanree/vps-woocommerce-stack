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
/* ── Free shipping bar ───────────────────────────────────────────────────── */
.sf-shipping-bar{background:#111827;color:#fff;font-size:.8rem;text-align:center;}
.sf-shipping-bar--unlocked{background:linear-gradient(90deg,#4f46e5,#7c3aed);}
.sf-shipping-bar__inner{max-width:1280px;margin:0 auto;padding:.55rem 1.5rem;display:flex;align-items:center;justify-content:center;gap:1rem;}
.sf-shipping-bar__msg{white-space:nowrap;}
.sf-shipping-bar__msg strong{color:#a5b4fc;}
.sf-shipping-bar--unlocked .sf-shipping-bar__msg strong{color:#fff;}
.sf-shipping-bar__track{flex:1;max-width:160px;height:4px;background:rgba(255,255,255,.2);border-radius:9999px;overflow:hidden;}
.sf-shipping-bar__fill{height:100%;background:linear-gradient(90deg,#818cf8,#7c3aed);border-radius:9999px;transition:width .4s ease;}
.sf-shipping-bar--unlocked .sf-shipping-bar__track{display:none;}
/* ── Size swatches on product grid ──────────────────────────────────────────── */
.sf-grid-swatches{display:flex;flex-wrap:wrap;gap:.3rem;margin:.4rem 0 .2rem;}
.sf-grid-swatch{font-size:.65rem;font-weight:700;padding:.2rem .45rem;border:1px solid #e5e7eb;border-radius:.25rem;color:#6b7280;background:#fff;letter-spacing:.04em;line-height:1.4;}
/* ── Trust strip ─────────────────────────────────────────────────────────── */
.sf-trust-strip{background:#f9fafb;border-bottom:1px solid #f3f4f6;}
.sf-trust-strip__inner{max-width:1280px;margin:0 auto;padding:.5rem 1.5rem;display:flex;align-items:center;justify-content:center;gap:1.25rem;flex-wrap:wrap;}
.sf-trust-strip__item{display:inline-flex;align-items:center;gap:.375rem;font-size:.75rem;font-weight:500;color:#6b7280;}
.sf-trust-strip__item svg{color:#4f46e5;flex-shrink:0;}
.sf-trust-strip__sep{width:1px;height:12px;background:#e5e7eb;}
/* ── Header ──────────────────────────────────────────────────────────────── */
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
/* ── Mega dropdown ───────────────────────────────────────────────────────── */
.sf-nav__item--dropdown{position:relative;}
.sf-nav__dropdown-toggle{display:inline-flex;align-items:center;gap:.3rem;}
.sf-nav__mega{display:flex;position:absolute;top:calc(100% + .5rem);left:50%;transform:translateX(-50%);background:#fff;border:1px solid #f3f4f6;border-radius:.75rem;box-shadow:0 8px 30px rgba(0,0,0,.1);padding:1.25rem 1.5rem;gap:2rem;min-width:320px;z-index:999;opacity:0;visibility:hidden;pointer-events:none;transition:opacity .15s,visibility .15s;}
.sf-nav__item--dropdown.sf-nav--open .sf-nav__mega{opacity:1;visibility:visible;pointer-events:auto;}
.sf-nav__mega-col{display:flex;flex-direction:column;gap:.5rem;min-width:130px;}
.sf-nav__mega-heading{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#9ca3af;margin:0 0 .25rem;}
.sf-nav__mega-col a{font-size:.85rem;font-weight:500;color:#374151;text-decoration:none;transition:color .15s;}
.sf-nav__mega-col a:hover{color:#4f46e5;}
.sf-cart__count{display:inline-flex;align-items:center;justify-content:center;background:#4f46e5;color:#fff;font-size:.7rem;font-weight:700;border-radius:9999px;min-width:1.1rem;height:1.1rem;padding:0 .25rem;margin-left:.25rem;vertical-align:middle;}
.site-header{display:none!important;}
#secondary{display:none!important;}
#primary{width:100%!important;float:none!important;}
.storefront-breadcrumb{padding:0!important;margin:0!important;}
.woocommerce-breadcrumb{display:flex!important;flex-wrap:wrap;align-items:center;gap:.25rem;font-size:.78rem!important;color:#9ca3af!important;margin:0!important;padding:.5rem 0!important;border-bottom:1px solid #f3f4f6!important;background:none!important;}
.woocommerce-breadcrumb a{color:#6b7280!important;text-decoration:none!important;transition:color .15s!important;}
.woocommerce-breadcrumb a:hover{color:#4f46e5!important;}
.woocommerce-breadcrumb .breadcrumb-separator,.woocommerce-breadcrumb span:not([class]){color:#d1d5db!important;}
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
.sf-section-head{text-align:center;padding:5rem 1.5rem 0;max-width:1280px;margin:0 auto;}
.sf-section-head__eyebrow{font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#4f46e5;margin:0 0 .75rem;display:block;}
.sf-section-head__title{font-size:2.25rem;font-weight:800;color:#111827;letter-spacing:-.025em;margin:0 0 .75rem;line-height:1.1;}
.sf-section-head__sub{color:#6b7280;font-size:1rem;max-width:420px;margin:0 auto 3rem;line-height:1.65;}
.woocommerce-products-header{display:block!important;padding:2rem 0 1.25rem!important;border-bottom:2px solid #f3f4f6!important;margin-bottom:0!important;}
.woocommerce-products-header__title,.woocommerce-products-header .page-title{font-size:2rem!important;font-weight:800!important;color:#111827!important;letter-spacing:-.03em!important;margin:0!important;line-height:1.1!important;}
/* ── Shop toolbar: sort dropdown + result count ──────────────────────────── */
.storefront-sorting{display:flex!important;align-items:center!important;justify-content:space-between!important;padding:.75rem 0!important;margin-bottom:1.5rem!important;border-bottom:1px solid #f3f4f6!important;gap:1rem!important;}
.woocommerce-notices-wrapper:empty{display:none!important;}
.woocommerce-ordering{float:none!important;margin:0!important;}
.woocommerce-ordering select{padding:.45rem 2rem .45rem .75rem!important;border:1px solid #e5e7eb!important;border-radius:.5rem!important;font-size:.825rem!important;font-weight:500!important;color:#374151!important;background:#fff!important;box-shadow:none!important;outline:none!important;cursor:pointer;-webkit-appearance:none;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2712%27 height=%278%27 viewBox=%270 0 12 8%27%3E%3Cpath fill=%27%236b7280%27 d=%27M1 1l5 5 5-5%27/%3E%3C/svg%3E")!important;background-repeat:no-repeat!important;background-position:right .65rem center!important;}
.woocommerce-ordering select:focus{border-color:#4f46e5!important;box-shadow:0 0 0 3px rgba(79,70,229,.1)!important;}
.woocommerce-result-count{float:none!important;font-size:.8rem!important;color:#9ca3af!important;font-weight:400!important;margin:0!important;padding:0!important;}
.single-product .page-header,.single-product .entry-header{display:none!important;}
.single-product #primary,.single-product .site-main,.single-product article.hentry,.single-product .type-product,.single-product .entry-content,.single-product .hentry{padding-top:0!important;margin-top:0!important;padding-bottom:0!important;}
.single-product div.product div.images,.single-product div.product .woocommerce-product-gallery{float:none!important;width:100%!important;max-width:none!important;margin:0!important;}
.single-product div.product div.summary,.single-product div.product .summary.entry-summary{float:none!important;width:100%!important;max-width:none!important;margin:0!important;}
.single-product div.product{display:grid;grid-template-columns:1fr 1fr;gap:2.5rem;align-items:start;padding-top:.75rem;}
.single-product div.product .woocommerce-notices-wrapper{grid-column:1/-1;grid-row:1;}
.single-product div.product .woocommerce-product-gallery,.single-product div.product div.images{grid-column:1!important;grid-row:2;min-width:0;}
.single-product div.product .summary.entry-summary{grid-column:2!important;grid-row:2;min-width:0;}
.woocommerce-message,.woocommerce-error{display:flex!important;align-items:center!important;gap:.875rem!important;background:#eef2ff!important;color:#3730a3!important;border:1px solid #c7d2fe!important;border-top:none!important;border-radius:.75rem!important;padding:1rem 1.25rem!important;margin:0 0 1.25rem!important;box-shadow:0 4px 16px rgba(79,70,229,.1)!important;font-size:.875rem!important;font-weight:500!important;list-style:none!important;}
.woocommerce-message::before{content:"\2713"!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;width:1.4rem!important;height:1.4rem!important;min-width:1.4rem!important;background:#4f46e5!important;color:#fff!important;font-size:.75rem!important;font-weight:800!important;border-radius:50%!important;flex-shrink:0!important;position:static!important;float:none!important;margin:0!important;font-family:inherit!important;line-height:1!important;}
.woocommerce-error{background:#fef2f2!important;color:#991b1b!important;border-color:#fecaca!important;box-shadow:0 4px 16px rgba(239,68,68,.1)!important;}
.woocommerce-error::before{content:"\0021"!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;width:1.4rem!important;height:1.4rem!important;min-width:1.4rem!important;background:#ef4444!important;color:#fff!important;font-size:.8rem!important;font-weight:800!important;border-radius:50%!important;flex-shrink:0!important;position:static!important;float:none!important;margin:0!important;font-family:inherit!important;line-height:1!important;}
.woocommerce-message .button,.woocommerce-error .button{order:2!important;margin-left:auto!important;flex-shrink:0!important;padding:.4rem 1rem!important;background:transparent!important;color:#4f46e5!important;border:1px solid #a5b4fc!important;border-radius:.5rem!important;font-size:.8rem!important;font-weight:600!important;text-decoration:none!important;transition:background .2s,color .2s,border-color .2s!important;}
.woocommerce-message .button:hover{background:#4f46e5!important;color:#fff!important;border-color:#4f46e5!important;}
.woocommerce-info{display:flex!important;align-items:center!important;background:#f9fafb!important;color:#374151!important;border:1px solid #e5e7eb!important;border-top:3px solid #4f46e5!important;border-radius:.5rem!important;padding:.75rem 1.25rem!important;margin:0 0 1.5rem!important;font-size:.875rem!important;font-weight:500!important;list-style:none!important;gap:.5rem!important;}
.woocommerce-info::before{display:none!important;content:""!important;}
.woocommerce-info a{color:#4f46e5!important;font-weight:600!important;text-decoration:none!important;}
.woocommerce-info a:hover{text-decoration:underline!important;}
.woocommerce-info a.showcoupon{order:2!important;margin-left:auto!important;flex-shrink:0!important;}
.woocommerce-product-gallery{border-radius:1.25rem;overflow:hidden;box-shadow:0 20px 40px -10px rgba(0,0,0,.12);}
.woocommerce-product-gallery .flex-viewport{overflow:hidden;border-radius:1.25rem;}
.woocommerce-product-gallery .flex-viewport img{width:100%;aspect-ratio:1/1;object-fit:cover;display:block;transition:transform .6s ease;}
.woocommerce-product-gallery .flex-control-thumbs img{aspect-ratio:1/1;object-fit:cover;}
.woocommerce-product-gallery:hover .flex-viewport img{transform:scale(1.04);}
.single-product .entry-summary{padding:0;}
.single-product .entry-summary .entry-title{font-size:2rem;font-weight:800;color:#111827;letter-spacing:-.03em;line-height:1.15;margin:0 0 .75rem;}
.single-product p.price,.single-product span.price{background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;font-size:2rem!important;font-weight:800!important;margin:0 0 1.5rem!important;display:inline-block;}
.woocommerce-variation-price .price{font-size:1.5rem!important;}
.woocommerce-product-details__short-description{color:#6b7280;font-size:.95rem;line-height:1.7;margin-bottom:1.25rem;border-bottom:1px solid #f3f4f6;padding-bottom:1.25rem;}
.variations{width:100%;margin-bottom:.5rem;}
.variations td,.variations th{padding:.4rem 0;vertical-align:top;border:none;}
.variations label{font-size:.72rem;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.1em;line-height:2.5;}
.sf-size-pills{display:flex;flex-wrap:wrap;gap:.5rem;margin:.25rem 0 .75rem;}
.sf-pill{padding:.45rem 1.1rem;border:1.5px solid #e5e7eb;border-radius:9999px;font-size:.85rem;font-weight:600;color:#374151;background:#fff;cursor:pointer;transition:all .18s;line-height:1;font-family:inherit;}
.sf-pill:hover{border-color:#4f46e5;color:#4f46e5;}
.sf-pill.is-active{border-color:#4f46e5;background:#4f46e5;color:#fff;box-shadow:0 0 0 3px rgba(79,70,229,.15);}
.sf-select-hidden{position:absolute!important;left:-9999px!important;opacity:0!important;pointer-events:none!important;height:1px!important;width:1px!important;}
.reset_variations{font-size:.72rem;color:#9ca3af;margin-top:.5rem;display:inline-block;text-decoration:underline;cursor:pointer;}
.quantity .qty{width:64px;padding:.65rem .5rem;border:1px solid #e5e7eb;border-radius:.5rem;text-align:center;font-size:.95rem;font-weight:600;color:#111827;}
.woocommerce-variation-add-to-cart{display:flex;align-items:center;gap:.875rem;margin-top:1rem;}
.single_add_to_cart_button{position:relative;overflow:hidden;background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%)!important;color:#fff!important;font-weight:700!important;font-size:.95rem!important;padding:.9rem 2rem!important;border-radius:.625rem!important;border:none!important;cursor:pointer;box-shadow:0 4px 20px rgba(79,70,229,.4)!important;transition:opacity .2s,transform .15s,box-shadow .2s!important;}
.woocommerce-variation-add-to-cart .single_add_to_cart_button{flex:1;}
form.cart:not(.variations_form) .single_add_to_cart_button{width:100%;display:block;margin-top:1rem;}
.single_add_to_cart_button::after{content:"";position:absolute;top:0;right:0;bottom:0;left:0;background:linear-gradient(90deg,transparent 0%,rgba(255,255,255,.22) 50%,transparent 100%);transform:translateX(-100%);transition:transform .5s ease;}
.single_add_to_cart_button:not(.disabled):not(:disabled):hover::after{transform:translateX(100%);}
.single_add_to_cart_button:hover{opacity:.9!important;transform:translateY(-2px);box-shadow:0 10px 28px rgba(79,70,229,.5)!important;}
.single_add_to_cart_button:disabled,.single_add_to_cart_button.disabled{opacity:.4!important;cursor:not-allowed!important;transform:none!important;box-shadow:none!important;}
.sf-trust{display:flex;margin-top:1rem;border:1px solid #f3f4f6;border-radius:.75rem;overflow:hidden;}
.sf-trust__item{display:flex;align-items:center;gap:.5rem;flex:1;padding:.875rem 1rem;font-size:.75rem;font-weight:600;color:#374151;background:#f9fafb;border-right:1px solid #f3f4f6;}
.sf-trust__item:last-child{border-right:none;}
.sf-trust__item::before{content:"\2713";color:#4f46e5;font-weight:800;font-size:.9rem;flex-shrink:0;}
.product_meta{display:none;}
.woocommerce-tabs{grid-column:1/-1;margin-top:.75rem;}
.woocommerce-tabs .tabs,.woocommerce-tabs .wc-tabs{float:none!important;width:100%!important;display:flex!important;flex-wrap:wrap;gap:0;list-style:none!important;padding:0!important;margin:0!important;border:none!important;border-bottom:2px solid #f3f4f6!important;background:none!important;}
.woocommerce-tabs .tabs li,.woocommerce-tabs .wc-tabs li,.woocommerce-tabs .tabs li.active,.woocommerce-tabs .wc-tabs li.active{float:none!important;width:auto!important;margin:0!important;padding:0!important;border:none!important;border-radius:0!important;background:none!important;box-shadow:none!important;}
.woocommerce-tabs .tabs li a,.woocommerce-tabs .wc-tabs li a{display:block!important;padding:.75rem 1.5rem!important;font-size:.875rem!important;font-weight:600!important;color:#9ca3af!important;text-decoration:none!important;border:none!important;border-bottom:2px solid transparent!important;margin-bottom:-2px!important;transition:color .2s,border-color .2s!important;background:none!important;white-space:nowrap!important;letter-spacing:.01em!important;}
.woocommerce-tabs .tabs li a:hover,.woocommerce-tabs .wc-tabs li a:hover{color:#374151!important;background:none!important;}
.woocommerce-tabs .tabs li.active a,.woocommerce-tabs .wc-tabs li.active a{color:#4f46e5!important;border-bottom:2px solid #4f46e5!important;background:none!important;}
.woocommerce-tabs .panel,.woocommerce-tabs .woocommerce-Tabs-panel{float:none!important;width:100%!important;padding:1.5rem 0!important;color:#374151;font-size:.95rem;line-height:1.75;border:none!important;margin:0!important;}
.related.products{grid-column:1/-1;margin-top:1rem!important;}.related.products>h2{font-size:1.5rem;font-weight:800;color:#111827;margin-bottom:1.25rem;letter-spacing:-.02em;}
@media(max-width:768px){.single-product div.product{grid-template-columns:1fr;gap:2rem;padding-top:1rem;}.single-product div.product .woocommerce-product-gallery,.single-product div.product div.images{grid-column:1!important;grid-row:2;}.single-product div.product .summary.entry-summary{grid-column:1!important;grid-row:3;}}
@media(max-width:480px){.single-product .entry-summary .entry-title{font-size:1.5rem;}.sf-trust{flex-direction:column;}.sf-trust__item{border-right:none;border-bottom:1px solid #f3f4f6;}.sf-trust__item:last-child{border-bottom:none;}}
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
/* ── Shared form inputs ──────────────────────────────────────────────────── */
.woocommerce input[type="text"],.woocommerce input[type="email"],.woocommerce input[type="tel"],.woocommerce input[type="number"],.woocommerce input[type="password"],.woocommerce input[type="search"],.woocommerce textarea,.woocommerce select{width:100%;padding:.65rem .875rem!important;border:1px solid #e5e7eb!important;border-radius:.5rem!important;font-size:.9rem!important;color:#111827!important;background:#fff!important;outline:none!important;transition:border-color .2s,box-shadow .2s!important;box-shadow:none!important;-webkit-appearance:none;appearance:none;}
.woocommerce input[type="text"]:focus,.woocommerce input[type="email"]:focus,.woocommerce input[type="tel"]:focus,.woocommerce input[type="number"]:focus,.woocommerce input[type="password"]:focus,.woocommerce textarea:focus,.woocommerce select:focus{border-color:#4f46e5!important;box-shadow:0 0 0 3px rgba(79,70,229,.12)!important;}
.woocommerce label{font-size:.75rem!important;font-weight:700!important;color:#374151!important;text-transform:uppercase!important;letter-spacing:.07em!important;margin-bottom:.35rem!important;display:block!important;}
.woocommerce .form-row{margin-bottom:1.1rem!important;}
.woocommerce .form-row abbr[title]{color:#ef4444;text-decoration:none;}
/* ── Checkout page ───────────────────────────────────────────────────────── */
.woocommerce-checkout h3,.woocommerce-checkout h2{font-size:1.1rem!important;font-weight:700!important;color:#111827!important;letter-spacing:-.01em!important;margin:0 0 1.25rem!important;padding-bottom:.75rem!important;border-bottom:2px solid #f3f4f6!important;}
.woocommerce-checkout .woocommerce-billing-fields,.woocommerce-checkout .woocommerce-shipping-fields,.woocommerce-checkout .woocommerce-additional-fields{background:#fff;border:1px solid #f3f4f6;border-radius:.75rem;padding:1.5rem;}
/* Override WC float layout - #customer_details and its columns fill the flex main column */
.wc-checkout-main #customer_details{float:none!important;width:100%!important;margin:0!important;}
.wc-checkout-main #customer_details .col-1,.wc-checkout-main #customer_details .col-2{float:none!important;width:100%!important;margin:0!important;padding:0!important;}
/* ── Step indicator: always visible above steps ─────────────────────────── */
.wc-multistep-progress{display:flex!important;}
/* Heading hidden by default (WC floats it oddly); JS moves it into sidebar */
#order_review_heading{display:none;}
.wc-checkout-sidebar #order_review_heading{display:block!important;}
/* ── Order review table ─────────────────────────────────────────────────── */
.wc-checkout-sidebar #order_review_heading{font-size:1.1rem!important;font-weight:700!important;color:#111827!important;margin:0 0 1rem!important;}
.woocommerce-checkout-review-order-table{width:100%;border-collapse:collapse;font-size:.9rem;}
.woocommerce-checkout-review-order-table thead th{font-size:.7rem!important;font-weight:700!important;text-transform:uppercase!important;letter-spacing:.08em!important;color:#9ca3af!important;padding:.5rem 0 .875rem!important;border-bottom:2px solid #e5e7eb!important;}
.woocommerce-checkout-review-order-table tbody tr td{padding:.875rem 0!important;border-bottom:1px solid #f3f4f6!important;color:#374151!important;vertical-align:middle!important;}
.woocommerce-checkout-review-order-table tbody .product-name{font-weight:600!important;color:#111827!important;}
.woocommerce-checkout-review-order-table tbody .product-name .product-quantity{font-weight:400;color:#6b7280;font-size:.85rem;}
.woocommerce-checkout-review-order-table tfoot tr td,.woocommerce-checkout-review-order-table tfoot tr th{padding:.75rem 0!important;border-bottom:1px solid #f3f4f6!important;font-size:.9rem!important;color:#374151!important;}
.woocommerce-checkout-review-order-table tfoot .cart-subtotal td,.woocommerce-checkout-review-order-table tfoot .cart-subtotal th{border-top:2px solid #e5e7eb!important;}
.woocommerce-checkout-review-order-table tfoot .order-total td,.woocommerce-checkout-review-order-table tfoot .order-total th{border-bottom:none!important;font-weight:800!important;font-size:1.05rem!important;color:#111827!important;padding-top:1rem!important;}
.woocommerce-checkout-review-order-table tfoot .order-total td{background:linear-gradient(135deg,#4f46e5,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
/* Sidebar order review */
.wc-checkout-sidebar #order_review{background:#f9fafb;border:1px solid #f3f4f6;border-radius:.875rem;padding:1.5rem!important;float:none!important;width:100%!important;margin:0!important;}
/* place order button — real WC button (kept hidden, triggered by JS) + trigger clone */
#place_order,.woocommerce-checkout #place_order,.wc-place-order-trigger{background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%)!important;color:#fff!important;font-weight:700!important;font-size:1rem!important;padding:1rem 2rem!important;border-radius:.625rem!important;border:none!important;width:100%!important;cursor:pointer!important;box-shadow:0 4px 20px rgba(79,70,229,.35)!important;transition:opacity .2s,transform .15s,box-shadow .2s!important;margin-top:1rem!important;}
#place_order:hover,.wc-place-order-trigger:hover{opacity:.9!important;transform:translateY(-2px)!important;box-shadow:0 8px 28px rgba(79,70,229,.45)!important;}
/* coupon form */
.woocommerce-checkout .checkout_coupon,.woocommerce-cart .coupon{display:flex;gap:.75rem;align-items:flex-end;margin-top:1rem;}
.woocommerce-checkout .checkout_coupon input,.woocommerce-cart .coupon input{flex:1;}
.woocommerce-checkout .checkout_coupon .button,.woocommerce-cart .coupon .button{flex-shrink:0;padding:.65rem 1.25rem!important;background:#4f46e5!important;color:#fff!important;border:none!important;border-radius:.5rem!important;font-weight:600!important;font-size:.875rem!important;cursor:pointer!important;white-space:nowrap;transition:background .2s!important;}
.woocommerce-checkout .checkout_coupon .button:hover,.woocommerce-cart .coupon .button:hover{background:#4338ca!important;}
/* validation errors */
.woocommerce-invalid input,.woocommerce-invalid select{border-color:#ef4444!important;box-shadow:0 0 0 3px rgba(239,68,68,.1)!important;}
.woocommerce-invalid-required-field .woocommerce-error,.validate-required .woocommerce-error{color:#ef4444;font-size:.78rem;margin-top:.25rem;}
/* ── WooCommerce page titles (Cart, Checkout, My Account) ───────────────── */
.woocommerce-page .entry-header{padding:1rem 0 0!important;margin:0!important;}
.woocommerce-page .entry-title{font-size:1.75rem!important;font-weight:800!important;color:#111827!important;margin:0 0 1rem!important;padding:0!important;}
/* ── Cart page ───────────────────────────────────────────────────────────── */
.woocommerce-cart table.cart{width:100%;border-collapse:collapse;font-size:.9rem;}
.woocommerce-cart table.cart th{font-size:.72rem!important;font-weight:700!important;text-transform:uppercase!important;letter-spacing:.07em!important;color:#6b7280!important;border-bottom:2px solid #f3f4f6!important;padding:.75rem .5rem!important;}
.woocommerce-cart table.cart td{padding:1rem .5rem!important;border-bottom:1px solid #f3f4f6!important;vertical-align:middle!important;}
.woocommerce-cart table.cart .product-name a{font-weight:600;color:#111827;text-decoration:none;}
.woocommerce-cart table.cart .product-name a:hover{color:#4f46e5;}
.woocommerce-cart table.cart .product-price,.woocommerce-cart table.cart .product-subtotal{font-weight:700;color:#111827;}
.woocommerce-cart table.cart input.qty{width:64px!important;padding:.5rem .4rem!important;border:1px solid #e5e7eb!important;border-radius:.5rem!important;text-align:center!important;font-weight:600!important;font-size:.9rem!important;}
.woocommerce-cart table.cart input.qty:focus{border-color:#4f46e5!important;box-shadow:0 0 0 3px rgba(79,70,229,.12)!important;}
.woocommerce-cart table.cart a.remove{color:#9ca3af!important;font-size:1.2rem!important;text-decoration:none!important;transition:color .2s!important;}
.woocommerce-cart table.cart a.remove:hover{color:#ef4444!important;}
.woocommerce-cart .actions{display:flex;justify-content:flex-end;gap:.75rem;padding-top:1rem;}
.woocommerce-cart .actions .button,.woocommerce-cart .actions button{padding:.65rem 1.25rem!important;border-radius:.5rem!important;font-weight:600!important;font-size:.875rem!important;cursor:pointer!important;border:1px solid #e5e7eb!important;background:#fff!important;color:#374151!important;transition:border-color .2s,color .2s!important;}
.woocommerce-cart .actions .button:hover{border-color:#4f46e5!important;color:#4f46e5!important;}
.cart_totals{background:#f9fafb;border:1px solid #f3f4f6;border-radius:.75rem;padding:1.5rem;}
.cart_totals h2{font-size:1rem!important;font-weight:700!important;color:#111827!important;margin:0 0 1rem!important;}
.cart_totals table{width:100%;border-collapse:collapse;}
.cart_totals table th,.cart_totals table td{padding:.6rem 0!important;border-bottom:1px solid #f3f4f6!important;font-size:.9rem!important;}
.cart_totals table .order-total th,.cart_totals table .order-total td{font-weight:800!important;font-size:1.05rem!important;color:#111827!important;border-bottom:none!important;}
.wc-proceed-to-checkout .checkout-button{display:block!important;width:100%!important;text-align:center!important;background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%)!important;color:#fff!important;font-weight:700!important;font-size:.95rem!important;padding:.9rem 1.5rem!important;border-radius:.625rem!important;border:none!important;margin-top:1rem!important;cursor:pointer!important;box-shadow:0 4px 20px rgba(79,70,229,.35)!important;text-decoration:none!important;transition:opacity .2s,transform .15s,box-shadow .2s!important;}
.wc-proceed-to-checkout .checkout-button:hover{opacity:.9!important;transform:translateY(-2px)!important;box-shadow:0 8px 28px rgba(79,70,229,.45)!important;}
/* ── Cart Block overrides ────────────────────────────────────────────────── */
/* 2-column layout: items left, totals right */
.wp-block-woocommerce-cart.alignwide{max-width:100%!important;}
.wp-block-woocommerce-filled-cart-block{display:flex!important;gap:2.5rem!important;align-items:flex-start!important;}
.wp-block-woocommerce-cart-items-block{flex:1!important;min-width:0!important;}
.wp-block-woocommerce-cart-totals-block{width:380px!important;flex-shrink:0!important;position:sticky!important;top:1.5rem!important;}
/* Order summary panel */
.wc-block-cart__totals,.wp-block-woocommerce-cart-totals-block{background:#f9fafb!important;border:1px solid #f3f4f6!important;border-radius:.875rem!important;padding:1.5rem!important;}
.wc-block-cart__totals-title{font-size:1.1rem!important;font-weight:700!important;color:#111827!important;border-bottom:2px solid #f3f4f6!important;padding-bottom:.75rem!important;margin-bottom:1rem!important;}
/* Totals rows */
.wc-block-components-totals-item{padding:.6rem 0!important;border-bottom:1px solid #f3f4f6!important;font-size:.9rem!important;color:#374151!important;}
.wc-block-components-totals-item:last-child{border-bottom:none!important;}
.wc-block-components-totals-footer-item{padding:.875rem 0 0!important;border-top:2px solid #e5e7eb!important;margin-top:.25rem!important;}
.wc-block-components-totals-footer-item .wc-block-components-totals-item__label{font-size:1rem!important;font-weight:800!important;color:#111827!important;}
.wc-block-components-totals-footer-item .wc-block-components-totals-item__value{background:linear-gradient(135deg,#4f46e5,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;font-size:1.1rem!important;font-weight:800!important;}
/* Proceed to checkout button */
.wc-block-cart__submit-button,.wp-block-woocommerce-proceed-to-checkout-block .wc-block-cart__submit-button{background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%)!important;border:none!important;border-radius:.625rem!important;font-weight:700!important;font-size:1rem!important;padding:1rem 2rem!important;box-shadow:0 4px 20px rgba(79,70,229,.35)!important;transition:opacity .2s,transform .15s,box-shadow .2s!important;width:100%!important;}
.wc-block-cart__submit-button:hover{opacity:.9!important;transform:translateY(-2px)!important;box-shadow:0 8px 28px rgba(79,70,229,.45)!important;}
/* Coupon input + button */
.wc-block-components-coupon .wc-block-components-text-input input{border:1px solid #e5e7eb!important;border-radius:.5rem!important;padding:.65rem .875rem!important;font-size:.875rem!important;color:#111827!important;}
.wc-block-components-coupon .wc-block-components-text-input input:focus{border-color:#4f46e5!important;box-shadow:0 0 0 3px rgba(79,70,229,.12)!important;outline:none!important;}
.wc-block-components-coupon__button,.wc-block-components-coupon .components-button{background:#4f46e5!important;color:#fff!important;border:none!important;border-radius:.5rem!important;font-weight:600!important;font-size:.875rem!important;padding:.65rem 1.25rem!important;cursor:pointer!important;transition:background .2s!important;}
.wc-block-components-coupon__button:hover,.wc-block-components-coupon .components-button:hover{background:#4338ca!important;}
/* Line items table */
.wc-block-cart-items__header{font-size:.7rem!important;font-weight:700!important;text-transform:uppercase!important;letter-spacing:.07em!important;color:#9ca3af!important;border-bottom:2px solid #f3f4f6!important;padding-bottom:.75rem!important;}
.wc-block-cart-item{border-bottom:1px solid #f3f4f6!important;padding:.875rem 0!important;}
.wc-block-cart-item__product-name a{font-weight:600!important;color:#111827!important;text-decoration:none!important;}
.wc-block-cart-item__product-name a:hover{color:#4f46e5!important;}
.wc-block-cart-item__prices .wc-block-components-product-price{font-weight:700!important;color:#111827!important;}
.wc-block-components-quantity-selector input{border:1px solid #e5e7eb!important;border-radius:.5rem!important;font-weight:600!important;color:#111827!important;}
.wc-block-components-quantity-selector__button{color:#6b7280!important;border:1px solid #e5e7eb!important;}
.wc-block-components-quantity-selector__button:hover{color:#4f46e5!important;border-color:#4f46e5!important;}
.wc-block-cart-item__remove-link{color:#9ca3af!important;font-size:.8rem!important;text-decoration:none!important;}
.wc-block-cart-item__remove-link:hover{color:#ef4444!important;}
    ';
    echo '</style>';
}, 999);

// ── PDP: Trust badges (after cart form, before meta) ────────────────
add_action('woocommerce_single_product_summary', function () {
    ?>
    <div class="sf-trust">
        <div class="sf-trust__item">Free shipping over $50</div>
        <div class="sf-trust__item">Easy 30-day returns</div>
        <div class="sf-trust__item">100% premium cotton</div>
    </div>
    <?php
}, 41);

// ── Dequeue WC variation script (crashes via CSP no-eval) ───────────
// wp.template() uses _.template() → new Function() which CSP blocks.
// Our own JS below replaces everything that script does.
add_action('wp_enqueue_scripts', function () {
    if (is_product()) {
        wp_dequeue_script('wc-add-to-cart-variation');
    }
}, 100);

// ── PDP: Size pills + variation fix ─────────────────────────────────
add_action('wp_footer', function () {
    if (!is_product()) return;
    ?>
    <script>
    jQuery(function($) {
        var $form = $('form.variations_form');
        if (!$form.length) return;

        var varData = $form.data('product_variations') || [];

        // ── Resolve variation from current select state ──────────────────
        function resolveVariation() {
            var chosen = {};
            $form.find('.variations select').each(function() {
                chosen[this.name] = this.value;
            });

            var allChosen = Object.keys(chosen).every(function(k) { return chosen[k] !== ''; });
            var $vi  = $form.find('input[name="variation_id"]');
            var $btn = $form.find('.single_add_to_cart_button');

            if (!allChosen || !varData.length) {
                $vi.val(0);
                $btn.addClass('disabled').prop('disabled', true);
                return;
            }

            var match = null;
            for (var i = 0; i < varData.length; i++) {
                var v = varData[i], ok = true;
                for (var attr in chosen) {
                    var vv = v.attributes[attr];
                    if (vv && vv !== chosen[attr]) { ok = false; break; }
                }
                if (ok) { match = v; break; }
            }

            if (match) {
                $vi.val(match.variation_id);
                $btn.removeClass('disabled wc-variation-selection-needed').prop('disabled', false);
            } else {
                $vi.val(0);
                $btn.addClass('disabled').prop('disabled', true);
            }
        }

        // ── Sync pills to reflect current select values ──────────────────
        function syncPills() {
            $form.find('.variations select').each(function() {
                var cur = $(this).val();
                $(this).closest('td').find('.sf-pill')
                    .each(function() { $(this).toggleClass('is-active', $(this).data('sfVal') === cur); });
            });
            resolveVariation();
        }

        // ── Build pills from select options ──────────────────────────────
        $form.find('.variations select').each(function() {
            var $sel  = $(this);
            var $wrap = $sel.closest('td');
            $sel.addClass('sf-select-hidden');
            var $pills = $('<div class="sf-size-pills"></div>');
            $sel.find('option').each(function() {
                var val = this.value, txt = this.text;
                if (!val) return;
                var $p = $('<button type="button" class="sf-pill"></button>').text(txt).data('sfVal', val);
                $p.on('click', function() {
                    $form.find('.sf-pill').removeClass('is-active');
                    $p.addClass('is-active');
                    $sel.val(val);
                    resolveVariation();
                });
                $pills.append($p);
            });
            $wrap.prepend($pills);
        });

        // ── "Clear" button ───────────────────────────────────────────────
        $form.on('click', '.reset_variations', function(e) {
            e.preventDefault();
            $form.find('.variations select').val('');
            $form.find('.sf-pill').removeClass('is-active');
            resolveVariation();
        });

        // ── Guard on submit ──────────────────────────────────────────────
        $form.on('submit', function(e) {
            resolveVariation();
            if (parseInt($form.find('input[name="variation_id"]').val() || 0, 10) === 0) {
                e.preventDefault();
                window.alert('Please select a size before adding to your cart.');
                return false;
            }
        });

        // ── Initial state (PHP may have pre-selected a default attr) ─────
        syncPills();
    });
    </script>
    <?php
});

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
                    <ul class="sf-nav__list">
                        <li class="sf-nav__item sf-nav__item--dropdown">
                            <a href="<?php echo esc_url(home_url('/shop')); ?>" class="sf-nav__link sf-nav__dropdown-toggle">
                                Shop
                                <svg width="10" height="10" viewBox="0 0 12 8" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1l5 5 5-5"/></svg>
                            </a>
                            <div class="sf-nav__mega">
                                <div class="sf-nav__mega-col">
                                    <p class="sf-nav__mega-heading">By Type</p>
                                    <a href="<?php echo esc_url(home_url('/product-category/t-shirts')); ?>">T-Shirts</a>
                                    <a href="<?php echo esc_url(home_url('/product-category/hoodies')); ?>">Hoodies</a>
                                    <a href="<?php echo esc_url(home_url('/shop')); ?>">All Products</a>
                                </div>
                                <div class="sf-nav__mega-col">
                                    <p class="sf-nav__mega-heading">By Occasion</p>
                                    <a href="<?php echo esc_url(home_url('/product-category/gifts-under-50')); ?>">Gifts Under $50</a>
                                    <a href="<?php echo esc_url(home_url('/product-category/new-arrivals')); ?>">New Arrivals</a>
                                    <a href="<?php echo esc_url(home_url('/product-category/best-sellers')); ?>">Best Sellers</a>
                                </div>
                            </div>
                        </li>
                        <li><a href="<?php echo esc_url(home_url('/my-account')); ?>" class="sf-nav__link">My Account</a></li>
                    </ul>
                    <a class="sf-nav__link sf-cart" href="<?php echo esc_url(wc_get_cart_url()); ?>">
                        Cart<?php if ($count > 0): ?>
                        <span class="sf-cart__count"><?php echo esc_html($count); ?></span>
                        <?php endif; ?>
                    </a>
                </nav>
            </div>
        </div>
    </div>
    <div class="sf-trust-strip">
        <div class="sf-trust-strip__inner">
            <span class="sf-trust-strip__item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l5 5L20 7"/></svg>
                Free Shipping &amp; Returns
            </span>
            <span class="sf-trust-strip__sep"></span>
            <span class="sf-trust-strip__item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                Complimentary Gift Wrap
            </span>
            <span class="sf-trust-strip__sep"></span>
            <span class="sf-trust-strip__item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                Secure Checkout
            </span>
            <span class="sf-trust-strip__sep"></span>
            <span class="sf-trust-strip__item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                Ships in 2&ndash;3 Days
            </span>
        </div>
    </div>
    <?php
}, 5);

// ── Register legacy woocommerce-layout handle (removed in newer WC, still depended on) ──
add_action('wp_enqueue_scripts', function () {
    if (!wp_style_is('woocommerce-layout', 'registered')) {
        wp_register_style('woocommerce-layout', false, [], null);
    }
}, 1);

// ── Free shipping announcement bar ───────────────────────────────────────────
add_action('storefront_before_header', function () {
    if (!function_exists('WC')) return;
    $threshold  = 50;
    $cart       = WC()->cart;
    $total      = $cart ? (float) $cart->get_cart_contents_total() : 0;
    $remaining  = max(0, $threshold - $total);
    $pct        = $threshold > 0 ? min(100, round(($total / $threshold) * 100)) : 0;
    $unlocked   = $remaining <= 0;

    if ($unlocked) {
        $msg = '<strong>Free shipping unlocked!</strong> Enjoy free delivery on your order.';
    } elseif ($total > 0) {
        $msg = 'Add <strong>' . wc_price($remaining) . '</strong> more to unlock <strong>free shipping</strong>';
    } else {
        $msg = 'Free shipping on all orders over <strong>' . wc_price($threshold) . '</strong>';
    }
    ?>
    <div class="sf-shipping-bar<?php echo $unlocked ? ' sf-shipping-bar--unlocked' : ''; ?>">
        <div class="sf-shipping-bar__inner">
            <span class="sf-shipping-bar__msg"><?php echo wp_kses_post($msg); ?></span>
            <div class="sf-shipping-bar__track">
                <div class="sf-shipping-bar__fill" style="width:<?php echo esc_attr($pct); ?>%"></div>
            </div>
        </div>
    </div>
    <?php
}, 1);

// ── Size swatches on product grid ─────────────────────────────────────────────
add_action('woocommerce_after_shop_loop_item_title', function () {
    global $product;
    if (!$product || !$product->is_type('variable')) return;

    $attributes = $product->get_variation_attributes();
    if (empty($attributes)) return;

    // Prefer pa_size; fall back to first attribute
    $terms = isset($attributes['pa_size']) ? $attributes['pa_size'] : reset($attributes);
    if (empty($terms)) return;

    echo '<div class="sf-grid-swatches">';
    foreach ($terms as $term) {
        echo '<span class="sf-grid-swatch">' . esc_html(strtoupper($term)) . '</span>';
    }
    echo '</div>';
}, 12);

// ── Post-add-to-cart: redirect back to product page (prevents refresh resubmit) ──
add_filter('woocommerce_add_to_cart_redirect', function ($url) {
    if (!empty($_SERVER['HTTP_REFERER'])) {
        return $_SERVER['HTTP_REFERER'];
    }
    return $url;
});

// ── Breadcrumbs ──────────────────────────────────────────────────────
add_action('init', function () {
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
});

// ── Nav mega-menu: JS-driven open/close with 300ms close delay ───────────────
add_action('wp_footer', function () {
    ?>
    <script>
    (function () {
        document.querySelectorAll('.sf-nav__item--dropdown').forEach(function (item) {
            var closeTimer;
            item.addEventListener('mouseover', function () {
                clearTimeout(closeTimer);
                item.classList.add('sf-nav--open');
            });
            item.addEventListener('mouseout', function (e) {
                if (!item.contains(e.relatedTarget)) {
                    closeTimer = setTimeout(function () {
                        item.classList.remove('sf-nav--open');
                    }, 300);
                }
            });
        });
    })();
    </script>
    <?php
});

// ── Footer ───────────────────────────────────────────────────────────
remove_action('storefront_footer', 'storefront_footer_links', 10);
add_filter('storefront_credit_links_output', '__return_empty_string');

// ── Page title on front page ─────────────────────────────────────────
add_filter('storefront_show_page_title', function ($show) {
    return is_front_page() ? false : $show;
});
