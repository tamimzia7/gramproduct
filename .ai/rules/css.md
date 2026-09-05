---
paths:
  - resources/css/app.css
---

# Css

## Bootstrap Icons must be imported in app.css
The app uses `bi bi-*` classes everywhere, but without `@import 'bootstrap-icons/font/bootstrap-icons.min.css';` at the top of resources/css/app.css the icons render as empty glyphs. Do not remove that import; Vite bundles the font automatically. When adding a new icon, verify it exists in bootstrap-icons and just use its class.
