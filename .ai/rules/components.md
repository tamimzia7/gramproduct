---
paths:
  - resources/views/components/trust-feature-card.blade.php
  - resources/views/components/our-story.blade.php
  - 'resources/views/components/testimonials-*'
  - 'resources/views/components/order-*'
  - 'resources/views/components/delivery-info-*'
  - resources/views/components/site-footer.blade.php
  - resources/views/components/navbar.blade.php
---

# Components

## Homepage trust cards are static lang content
The homepage trust/why-choose-us cards ("কেন আমাদের কাছ থেকে কিনবেন?") are static UI content sourced from `why.items` in lang/bn/home.php (icon + title + description). Add/edit cards there only — never query the DB for them. The <x-trust-feature-card> component must stay props-only (no DB, no lang calls). Section must not claim unsupported things (no "১০০% অর্গানিক", "সরাসরি কৃষকের কাছ থেকে", "২৪ ঘণ্টায় ডেলিভারি").

## Our Story section: static lang + inline SVG, no b-image reuse
The homepage Our Story section is static lang-driven (home.story in lang/bn/home.php) and must stay props-only (no DB). Visual is a hand-crafted inline SVG rural scene — never reuse the b-image*.jpg files (they are layered Hero backgrounds) and do not add downloaded/generated images. No About page route exists, so "আমাদের সম্পর্কে জানুন" links to categories.index as the closest existing destination; if an About page is ever added, re-point that CTA.

## Real reviews only; testimonials hidden when empty
No review system exists (no Review model/table). The testimonials section must ONLY render real, approved reviews — never fabricate names/ratings/counts. `HomepageService::testimonials()` is the forward-integration seam (guarded by `class_exists(App\Models\Review::class)`, expects an `approved()` scope + `product` relation); the section is hidden when empty. All review text must be Blade-escaped; privacy: no phone/email/address/order id.

## Order Process section is static lang per order-*
The Order Process / How It Works section ("কীভাবে অর্ডার করবেন?") is fully static lang-driven (`home.order` in lang/bn/home.php: title/subtitle/cta/steps with number+icon+title+description). `<x-order-step>` is props-only and formats its number as zero-padded Bengali (০১–০৪) via BengaliNumber. `<x-order-process>` reads home.order internally, renders 4 steps + CTA to products.index. No DB, no JS, no delivery-time/free-delivery claims; icons are existing bi-* classes.

## Delivery section static; no zones/tracking; COD card
The Delivery Information section ("আপনার ঠিকানায় পণ্য পৌঁছে দিই") is fully static lang-driven (`home.delivery` in lang/bn/home.php). `<x-delivery-info-card>` is props-only. There are NO delivery zones/areas tables and NO order-tracking system, so: never hard-code cities, never show a fixed charge (`config/delivery.php` fees exist but are intentionally not displayed on the homepage), never promise delivery times, and do NOT show an "অর্ডার ট্র্যাকিং" card — the third card is the real Cash on Delivery feature (matching checkout.cod_*).

## Global footer: composer-driven shared data, no placeholders
Global footer (site-footer) is a SINGLE reusable component rendered once from the main layout (components/layouts/app.blade.php) — every page inherits it; never render it per-page. All data arrives via a View::composer('components.site-footer') in AppServiceProvider (existing shared-data architecture): footerQuickLinks (fixed: home/products.index/categories.index — NO "আমাদের সম্পর্কে"/"যোগাযোগ"/legal links because those pages don't exist; never invent links), footerCategories (Cache 'footer.categories' 30min, take(limit+1) to detect overflow → shows "সব ক্যাটাগরি দেখুন"), footerContactActions (reuses HomepageService::contactActions() so footer & homepage CTA stay in sync), footerAddress, footerSocialItems. Contact/social come from config('shop.contact')/config('shop.social') (see contact rule) — hide items when unset, never show placeholders (the old footer hardcoded contact@example.com — that must never come back). Copyright year uses @bn(now()->year) per Bengali-numeral convention. Replaced the old components/footer.blade.php (deleted).

## Global navbar is the 3-band site header
The navbar is the global header: dark-green info topbar (hidden < lg) on top, white main header (logo + rounded search w/ category select + green search button, then wishlist/cart-with-total/account dropdown), then a nav row with the green 'সব ক্যাটাগরি' dropdown button and horizontally-scrollable Bengali nav links. It is shared via View::composer('components.navbar') in AppServiceProvider, which injects cartCount, cartTotal, wishlistCount, topCategories and navCategories (active root-level categories).
