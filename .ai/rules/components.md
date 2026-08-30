---
paths:
  - resources/views/components/trust-feature-card.blade.php
  - resources/views/components/our-story.blade.php
  - 'resources/views/components/testimonials-*'
  - 'resources/views/components/order-*'
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
