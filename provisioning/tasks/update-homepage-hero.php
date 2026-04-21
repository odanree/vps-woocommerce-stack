<?php
/**
 * Updates the homepage post content with a modern split-layout hero section.
 * Run via: wp eval-file /tmp/update-homepage-hero.php
 */

$hero = <<<'HTML'
<!-- wp:html -->
<section class="sf-hero">
  <div class="sf-hero__inner">
    <div class="sf-hero__content">
      <div class="sf-hero__badge">
        <span>Dev Merch 2025</span>
      </div>
      <h1 class="sf-hero__headline">
        Developer Gear<br>
        <span class="sf-hero__accent">For People Who Ship</span>
      </h1>
      <p class="sf-hero__sub">Premium t-shirts for engineers, designers, and builders who live in the terminal and deploy on Fridays.</p>
      <div class="sf-hero__actions">
        <a href="/shop" class="sf-hero__cta-primary">Shop Now &rarr;</a>
        <a href="/shop" class="sf-hero__cta-secondary">Browse All</a>
      </div>
      <div class="sf-hero__stats">
        <div class="sf-hero__stat"><strong>10+</strong><span>Products</span></div>
        <div class="sf-hero__stat"><strong>Free</strong><span>Ship over $50</span></div>
        <div class="sf-hero__stat"><strong>100%</strong><span>Cotton</span></div>
      </div>
    </div>
    <div class="sf-hero__visual">
      <div class="sf-hero__ring"></div>
      <div class="sf-hero__ring-2"></div>
      <div class="sf-hero__card">
        <div class="sf-hero__card-bar">
          <span class="sf-hero__card-dot"></span>
          <span class="sf-hero__card-dot"></span>
          <span class="sf-hero__card-dot"></span>
          <span class="sf-hero__card-title">checkout.ts</span>
        </div>
        <div class="sf-hero__code">
          <div><span class="sf-code-ln">1</span><span class="sf-code-kw">import</span><span class="sf-code-var"> { gear } </span><span class="sf-code-kw">from</span><span class="sf-code-str"> "./shop"</span><span class="sf-code-var">;</span></div>
          <div><span class="sf-code-ln">2</span>&nbsp;</div>
          <div><span class="sf-code-ln">3</span><span class="sf-code-kw">const</span><span class="sf-code-var"> order </span><span class="sf-code-op">= </span><span class="sf-code-kw">await </span><span class="sf-code-fn">checkout</span><span class="sf-code-var">({</span></div>
          <div><span class="sf-code-ln">4</span><span class="sf-code-var">&nbsp;&nbsp;item: gear</span><span class="sf-code-op">.</span><span class="sf-code-fn">find</span><span class="sf-code-var">(</span><span class="sf-code-str">"tee"</span><span class="sf-code-var">),</span></div>
          <div><span class="sf-code-ln">5</span><span class="sf-code-var">&nbsp;&nbsp;deploy: </span><span class="sf-code-str">"friday"</span><span class="sf-code-var">,</span></div>
          <div><span class="sf-code-ln">6</span><span class="sf-code-var">});</span></div>
          <div><span class="sf-code-ln">7</span>&nbsp;</div>
          <div><span class="sf-code-ln">8</span><span class="sf-code-cm">// ✓ shipped in 2 days</span><span class="sf-code-cursor"></span></div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- /wp:html -->

<!-- wp:html -->
<div class="sf-section-head">
  <span class="sf-section-head__eyebrow">Our collection</span>
  <h2 class="sf-section-head__title">Featured Products</h2>
  <p class="sf-section-head__sub">Wear the stack you build with.</p>
</div>
<!-- /wp:html -->

<!-- wp:shortcode -->
[products limit="8" columns="4" orderby="date" order="DESC"]
<!-- /wp:shortcode -->

<!-- wp:spacer {"height":"64px"} -->
<div style="height:64px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->
HTML;

$home_id = (int) get_option('page_on_front');
if (!$home_id) {
    $page = get_page_by_path('home-storefront');
    $home_id = $page ? $page->ID : 0;
}

if (!$home_id) {
    echo "ERROR: could not find homepage post\n";
    exit(1);
}

$result = wp_update_post([
    'ID'           => $home_id,
    'post_content' => $hero,
]);

if (is_wp_error($result)) {
    echo 'ERROR: ' . $result->get_error_message() . "\n";
} else {
    echo "Updated post ID $result\n";
}
