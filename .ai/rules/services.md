---
paths:
  - app/Services/HomepageService.php
---

# Services

## Dedicated showcase sections must be excluded from generic sections
Homepage has dedicated showcase sections (rice_showcase, fish_showcase) driven by config/shop.php `homepage.<key>_showcase` (slug-mapped categories). Each dedicated showcase must ALSO be listed in `homepage.sections` config and excluded in HomepageService::sections() via ->except([...]) so the same products don't render twice. To add another dedicated showcase: add a `<key>_showcase` config entry, implement a thin `xxxShowcase()` wrapper calling the private `showcaseFromConfig()`, pass it in HomeController::home(), and mount the props-only `<x-xxx-showcase>` component in home.blade.php in the desired position. Never put DB queries in showcase Blade components.
