# Open Questions

## Inventory Module (06-inventory)

**Status:** RESOLVED — Module 06 — Inventory has been implemented based on
`docs/06-inventory.md` and master overview section 12.

The following decisions were made during implementation:

### Stock Fields
- Stock tracked at both Product and ProductVariant level (nullable variant)
- Fields: quantity, reserved_quantity, damaged_quantity, wasted_quantity, low_stock_threshold, is_in_stock
- Separate `inventories` table with unique constraint on [product_id, product_variant_id]

### Stock Adjustment Workflow
- Types: stock_in, stock_out, adjustment, wastage, damage
- Each adjustment records: type, quantity, previous_quantity, new_quantity, reason, user_id
- All stock changes are atomic via database transactions

### Low-Stock Behavior
- Threshold is per-inventory record (default: 10)
- Low stock detected when quantity <= threshold and quantity > 0
- No automatic notifications (future enhancement)

### Stock History
- Every stock-changing operation creates a StockAdjustment record
- History is append-only (no modification)
- Paginated history view available in admin

### Wastage
- Wastage reduces quantity and increases wasted_quantity
- Requires reason (optional)
- Cannot wastage more than available quantity

### Fresh Products
- No special fresh product rules implemented (not documented in detail)
- Available as future enhancement

### Authorization
- Uses existing `manage-inventory` permission
- Role: inventory-manager has manage-inventory permission
- Gate defined in AppServiceProvider via RoleSeeder PERMISSIONS

### Validation
- StoreInventoryRequest: product_id (required, exists), product_variant_id (nullable, exists), quantity (required, min:1), reason (nullable, max:500)
- AdjustStockRequest: quantity (required, min:0), reason (nullable, max:500)

---

## No Other Open Questions

All other module documentation gaps have been resolved or are not
blocking current implementation.
