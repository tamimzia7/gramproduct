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
