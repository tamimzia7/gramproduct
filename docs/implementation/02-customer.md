# Customer Module — Implementation Record

## Module ID

02-customer

## Status

COMPLETED

## What Was Implemented

Customer profile management, address book, account settings, and order history boundary per `docs/02-customer.md` and master overview section 14.

### Implemented Features

- **Customer Profile** — View and edit name, email, phone with validation
- **Address Book** — Full CRUD for addresses (create, read, update, delete) with ownership enforcement
- **Address Types** — Billing and Shipping address types
- **Default Address** — Set address as default; setting a new default of same type clears the previous default
- **Account Settings** — Password change with current password verification
- **Order History** — Placeholder page (Order module not yet implemented)
- **Authorization** — CustomerPolicy ensures users can only access their own profile and addresses
- **Bangla UI** — All customer-facing text in Bangla, Bootstrap 5 responsive layout
- **Account Sidebar** — Consistent navigation sidebar across all customer pages

### Deferred (Module Dependencies)

- Order history data (requires Module 10 — Order)
- Wishlist (requires Module 08 — Wishlist)
- Reviews (requires Module 14 — Review & Rating)

## Files Created

### Migration

- `database/migrations/2026_08_25_000001_create_addresses_table.php`

### Models

- `app/Models/Address.php` — user_id, label, type, name, phone, address fields, is_default; scopes: default(), ofType(); setAsDefault() method

### Updated Models

- `app/Models/User.php` — Added addresses() HasMany relationship and defaultAddress() method

### Form Requests

- `app/Http/Requests/UpdateProfileRequest.php` — name (required), email (required, unique:users except self), phone (nullable)
- `app\Http\Requests\AddressRequest.php` — label (nullable), type (required, in:billing|shipping), name (required), phone (nullable), address fields (required/optional), is_default (nullable boolean)

### Policy

- `app\Policies\CustomerPolicy.php` — viewProfile, updateProfile, manageAddresses (all: user owns resource)

### Controllers

- `app\Http\Controllers\Customer\ProfileController.php` — index (view profile), update (edit profile), settings (view settings), updatePassword (change password)
- `app\Http\Controllers\Customer\AddressController.php` — index, create, store, edit, update, destroy (full CRUD with ownership check)

### Views (Bootstrap 5, Bangla)

- `resources/views/customer/profile.blade.php` — Profile edit form with sidebar
- `resources/views/customer/addresses/index.blade.php` — Address list with empty state
- `resources/views/customer/addresses/create.blade.php` — Create address form
- `resources/views/customer/addresses/edit.blade.php` — Edit address form
- `resources/views/customer/order-history.blade.php` — Placeholder with dependency note
- `resources/views/customer/settings.blade.php` — Password change form

### Factory

- `database/factories/AddressFactory.php` — With billing/shipping/default states

### Tests

- `tests/Feature/CustomerTest.php` — 30 tests

## Database Changes

- New `addresses` table: id, user_id (FK → users), label, type (enum: billing/shipping), name, phone, address_line_1, address_line_2, city, state, postal_code, country (default: Bangladesh), is_default, timestamps

## Routes

| Method | URI | Name | Middleware |
|--------|-----|------|------------|
| GET | /customer/profile | customer.profile | auth |
| PUT | /customer/profile | customer.profile.update | auth |
| GET | /customer/settings | customer.settings | auth |
| PUT | /customer/password | customer.password.update | auth |
| GET | /customer/order-history | customer.order-history | auth |
| GET | /customer/addresses | customer.addresses.index | auth |
| GET | /customer/addresses/create | customer.addresses.create | auth |
| POST | /customer/addresses | customer.addresses.store | auth |
| GET | /customer/addresses/{address}/edit | customer.addresses.edit | auth |
| PUT | /customer/addresses/{address} | customer.addresses.update | auth |
| DELETE | /customer/addresses/{address} | customer.addresses.destroy | auth |

## Relationships

- User hasMany Address
- Address belongsTo User
- User defaultAddress(type) returns default address of given type

## Validation

- Profile: name required, email required + unique (except self), phone nullable
- Address: name/type/address_line_1/city/country required; type must be billing or shipping; label/phone/state/postal_code/address_line_2 optional
- Password: current_password required + current_password rule, new password min:8 + confirmed

## Authorization

- CustomerPolicy registered for User model in AppServiceProvider
- All customer routes require `auth` middleware
- Address operations check user ownership via abort_unless
- Users cannot view/edit/update/delete another user's addresses

## Tests Executed

30 CustomerTest + 28 AuthenticationTest = 58 total

## Test Result

All 58 tests pass.

## Verification Result

- [x] Authentication integration works
- [x] Customer profile functionality works
- [x] Customer authorization works
- [x] Customer input validation works
- [x] Address functionality works (CRUD + default)
- [x] Dashboard/account functionality works
- [x] Order history boundary handled (placeholder)
- [x] Empty states handled (no addresses)
- [x] Missing states handled (validation errors)
- [x] Invalid states handled (wrong type enum, missing required fields)
- [x] Responsive UI works (Bootstrap 5)
- [x] Relevant tests pass
- [x] Existing Authentication functionality still works
- [x] No unrelated module implemented
- [x] No undocumented business rule invented
- [x] Documentation tracking updated

## Open Questions / Documentation Gaps

- `docs/02-customer.md` does not specify exact fields for addresses — address schema inferred from standard e-commerce patterns and master overview requirements
- `docs/02-customer.md` mentions "order history" but Module 10 — Order is not implemented — handled as placeholder
- `docs/02-customer.md` mentions "saved information" — unclear what this refers to beyond profile/address — interpreted as profile data and addresses
- `docs/02-customer.md` mentions "wishlist" and "reviews" — these belong to separate modules (08, 14) — not implemented here
- No specific field-level requirements in `docs/02-customer.md` for customer profile fields — used existing User model fields (name, email, phone)

## Remaining Work

- Order history integration when Module 10 is implemented
- Wishlist integration when Module 08 is implemented
- Reviews integration when Module 14 is implemented
- Admin customer management (if required by Module 20)
