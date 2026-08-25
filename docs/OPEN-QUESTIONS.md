# Open Questions

## Wishlist Module (08-wishlist)

**Status:** RESOLVED — Module 08 — Wishlist has been implemented based on
`docs/08-wishlist.md` and master overview section 16.

The following decisions were made during implementation:

### Guest Wishlist
- **Not implemented.** Wishlist requires authentication.
- Not documented in the master overview §16.
- Guest wishlist behavior recorded as an open question.

### Variant Support
- Wishlist stores product_id + product_variant_id (nullable).
- Unique constraint on [user_id, product_id, product_variant_id].
- Follows the same pattern as Cart.

### Move to Cart
- After moving to cart, the wishlist item is **removed**.
- Not explicitly documented — implemented as the simplest behavior.

### Stock Awareness
- Wishlist displays current product/variant availability status.
- Does NOT check inventory directly — relies on product.is_active.
- Out-of-stock handling is deferred to the Cart/Checkout modules.

### Product Availability
- Inactive products show "বর্তমানে উপলব্ধ নয়" badge.
- "Move to Cart" button hidden for inactive products.
- Wishlist records are NOT deleted when products become inactive.

---

## No Other Open Questions

All other module documentation gaps have been resolved or are not
blocking current implementation.
