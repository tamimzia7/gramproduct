---
paths:
  - 'resources/views/**/*.blade.php'
  - resources/views/home.blade.php
---

# Views

## All user-facing text must be in Bengali
Every UI string (labels, buttons, notifications, errors, validation messages, confirm dialogs, seed-data display names) must be written in natural professional Bengali. Keep technical identifiers untouched: routes, class/function names, DB fields, brand names, and terms like Laravel, PHP, MySQL, API, Controller, Model, Migration, Route, Middleware, Blade, JavaScript, Vite, SEO, Slug. Code comments should be in Bengali when practical. Existing English-only legacy strings get converted opportunistically without changing functionality.

## Homepage ends with Trust section before footer
Homepage section order (settled): Hero → Categories → Main products → চাল → তাজা মাছ → dynamic sections → Trust ("কেন আমাদের কাছ থেকে কিনবেন?") → Footer. The old Promotional Banner, plain Trust strip, and CTA sections were intentionally removed; do not re-add them. lang keys home.promo / home.trust / home.cta were deleted accordingly.

## Homepage section order now ends with Order Process
Settled homepage section order: Hero → All Categories → Main products → চাল → তাজা মাছ → dynamic sections → Trust ("কেন আমাদের কাছ থেকে কিনবেন?") → Our Story ("গ্রাম থেকে আপনার ঘরে") → Testimonials (hidden unless approved reviews exist) → Order Process ("কীভাবে অর্ডার করবেন?") → Footer. Old Promotional Banner/Trust strip/CTA sections were removed; do not re-add.

## Homepage section order ends with Delivery Info section
Settled homepage section order: Hero → All Categories → Main products → চাল → তাজা মাছ → dynamic sections → Trust → Our Story → Testimonials (hidden unless approved reviews exist) → Order Process → Delivery Information → Footer. Old Promotional Banner/Trust strip/CTA sections were removed; do not re-add.

## Special Offers section: discount-driven, auto-hidden
Special Offers (বিশেষ অফার) is database-driven and placed between Delivery Information and the Footer (per spec §29). Offers come ONLY from active products with a real, card-visible discount: active display variant compare_at_price > price, OR (when no active variants) product compare_at_price > COALESCE(discount_price, base_price) — i.e. exactly what x-product-card shows. No date-based offer system exists, so no expiry logic. Configured via config('shop.homepage.offer_limit') (default 4). Component: x-special-offers-section, props-only, reuses x-product-grid (cols=4) and x-product-card. Section is NOT rendered at all when HomepageService::offerProducts() is empty — never show a fake/empty offers band.

## Seasonal showcase: existing is_seasonal flag, auto-hidden
Seasonal / Fresh showcase (এ সময়ের পণ্য) is placed between Special Offers and the Footer (per spec §24). It is 100% database-driven from the EXISTING products.is_seasonal flag + seasonal_info (admin-controlled checkbox "মৌসুমি" + text; no new fields). No start/end dates exist, so no expiry logic is needed — admin simply toggles is_seasonal. Query: HomepageService::seasonalProducts() = active() + scopeSeasonal(), ordered in-stock-first → featured → sort_order → newest, limited by config('shop.homepage.seasonal_limit') (default 4). Do NOT fabricate seasonality or claims like "আজকের তোলা"; hide the whole section when the collection is empty (Blade @if guard + props-only <x-seasonal-products-showcase>). Category tabs intentionally NOT implemented (spec marks them optional; with ≤4 products filtering adds no value — documented decision).
