---
paths:
  - resources/views/components/trust-feature-card.blade.php
  - resources/views/components/our-story.blade.php
---

# Components

## Homepage trust cards are static lang content
The homepage trust/why-choose-us cards ("কেন আমাদের কাছ থেকে কিনবেন?") are static UI content sourced from `why.items` in lang/bn/home.php (icon + title + description). Add/edit cards there only — never query the DB for them. The <x-trust-feature-card> component must stay props-only (no DB, no lang calls). Section must not claim unsupported things (no "১০০% অর্গানিক", "সরাসরি কৃষকের কাছ থেকে", "২৪ ঘণ্টায় ডেলিভারি").

## Our Story section: static lang + inline SVG, no b-image reuse
The homepage Our Story section is static lang-driven (home.story in lang/bn/home.php) and must stay props-only (no DB). Visual is a hand-crafted inline SVG rural scene — never reuse the b-image*.jpg files (they are layered Hero backgrounds) and do not add downloaded/generated images. No About page route exists, so "আমাদের সম্পর্কে জানুন" links to categories.index as the closest existing destination; if an About page is ever added, re-point that CTA.
