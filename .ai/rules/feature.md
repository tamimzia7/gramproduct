---
paths:
  - 'tests/Feature/**'
---

# Feature

## Runtime-added named routes need refreshNameLookups in tests
In Feature tests, routes added at runtime via Route::get(...)->name('contact') are NOT immediately findable: RouteCollection::nameList (66 entries) is not rebuilt when a route's fluent ->name() is applied after addRoute (which is how the fluent chain works). Route::has()/route()/getByName() will fail. Fix: call app('router')->getRoutes()->refreshNameLookups() after registering the route in the test. Does not affect behavior when routes are registered at boot (production).
