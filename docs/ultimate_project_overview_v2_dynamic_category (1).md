# ULTIMATE PROJECT OVERVIEW v2

# Village-Origin Food & Agricultural E-Commerce Platform

## Status

**Master Architecture & Product Specification**

## Framework

-   Laravel 12
-   PHP
-   MySQL
-   Blade
-   Bootstrap 5

------------------------------------------------------------------------

# 1. Project Vision

This project is a complete village-origin food and agricultural
e-commerce platform.

Customers will be able to purchase authentic products sourced from
villages, farms, fields, ponds, bills, jheels and other local sources.

The platform is **not a rice-only website**.

Rice is an important flagship category with approximately **35%
business/visual focus**, while all other product categories together
receive approximately **65% focus**.

## Core Business Concept

> **From the village, field and water to your home.**

The platform should build trust by showing:

-   Product
-   Source
-   Village
-   Farmer/Farm
-   Production or collection information
-   Processing information where applicable
-   Freshness information for perishable products
-   Delivery information

------------------------------------------------------------------------

# 2. Product Ecosystem

The platform must support products such as:

-   Rice
-   Fish
-   Vegetables
-   Seeds
-   Dal
-   Spices
-   Mustard Oil
-   Honey
-   Fruits
-   Other village/farm products

The system must NOT hard-code these categories.

The Admin must be able to create, edit, reorder, activate/deactivate and
organize categories dynamically.

------------------------------------------------------------------------

# 3. Critical Category Requirement

## Dynamic Category System

The category system must be fully dynamic.

The developer must NOT hard-code categories such as:

-   Rice
-   Nazirshail
-   Katari Bhog
-   Chinigura
-   Fish
-   Bill Fish
-   Vegetables

These are examples only.

The Admin creates and manages all categories from the Admin Panel.

------------------------------------------------------------------------

# 4. Category Hierarchy

The system must support unlimited practical parent-child category
levels.

Example:

``` text
Rice
├── Local Rice
├── Aman Rice
├── Boro Rice
├── Aus Rice
├── Nazirshail
├── Katari Bhog
├── Chinigura
├── Kalijira
├── Red Rice
└── Brown Rice
```

Another example:

``` text
Fish
├── Bill Fish
├── Jheel Fish
├── Pond Fish
├── River Fish
├── Deshi Fish
└── Seasonal Fish
```

Another example:

``` text
Vegetables
├── Leafy Vegetables
├── Seasonal Vegetables
├── Organic Vegetables
└── Local Vegetables
```

The same architecture must work for every product family.

------------------------------------------------------------------------

# 5. Category Database Design

Use one reusable `categories` table.

Suggested structure:

``` text
categories
-----------
id
parent_id
name
slug
description
image
sort_order
is_active
is_featured
seo_title
seo_description
created_at
updated_at
```

## parent_id

`parent_id = NULL`

means the category is a top-level category.

Example:

``` text
Rice
parent_id = NULL
```

A child category:

``` text
Nazirshail
parent_id = Rice ID
```

This creates a dynamic category tree.

------------------------------------------------------------------------

# 6. Category Admin Features

Admin must be able to:

-   Add category
-   Edit category
-   Delete category
-   Restore category if soft delete is used
-   Select parent category
-   Create subcategory
-   Create deeper child categories
-   Upload category image
-   Add description
-   Set slug
-   Set sort order
-   Activate/deactivate category
-   Mark featured category
-   Add SEO title
-   Add SEO description
-   View category hierarchy
-   Reorder categories

## Important

Adding a new rice category must NOT require code changes.

Example:

``` text
Admin Panel
→ Categories
→ Add Category
→ Name: New Local Rice
→ Parent: Rice
→ Save
```

The category immediately becomes available to the catalog according to
its active state and placement rules.

------------------------------------------------------------------------

# 7. Category Rules

## Rule 1

Categories are database-driven.

## Rule 2

No product category should be hard-coded in Blade.

## Rule 3

No product category should be hard-coded in controllers.

## Rule 4

Admin controls category creation.

## Rule 5

Products can belong to categories dynamically.

## Rule 6

A category can have a parent category.

## Rule 7

The UI should automatically display active categories.

## Rule 8

Inactive categories must not appear in customer-facing
navigation/catalog pages.

## Rule 9

A category cannot be deleted if it would create orphaned products unless
the system provides a safe reassignment flow.

## Rule 10

Category slugs must be unique.

------------------------------------------------------------------------

# 8. Product Architecture

Use one universal product system.

Do NOT create:

``` text
RiceProduct
FishProduct
VegetableProduct
SeedProduct
```

Instead use:

``` text
Product
├── Category
├── Variants
├── Images
├── Inventory
├── Reviews
├── Farmer/Farm
└── Origin
```

This allows every category to use the same commerce engine.

------------------------------------------------------------------------

# 9. Product Examples

## Rice

``` text
Category:
Rice
→ Nazirshail

Product:
Premium Nazirshail Rice

Variants:
1 KG
5 KG
10 KG
25 KG
```

## Fish

``` text
Category:
Fish
→ Bill Fish

Product:
Deshi Koi Fish

Variants:
500 GM
1 KG
2 KG
```

## Vegetable

``` text
Category:
Vegetables
→ Seasonal Vegetables

Product:
Fresh Local Bottle Gourd

Variants:
1 Piece
2 Pieces
```

------------------------------------------------------------------------

# 10. Product Module

Every product should support:

-   Name
-   SKU
-   Slug
-   Category
-   Short description
-   Full description
-   Product images
-   Base price
-   Discount price
-   Unit
-   Product type
-   Featured status
-   Bestseller status
-   New arrival status
-   Active/inactive status
-   Product origin
-   Farmer/Farm
-   Seasonal information
-   SEO fields

------------------------------------------------------------------------

# 11. Product Variant Module

Products may have multiple variants.

Examples:

``` text
Rice
→ 1 KG
→ 5 KG
→ 10 KG
→ 25 KG
```

``` text
Fish
→ 500 GM
→ 1 KG
→ 2 KG
```

``` text
Honey
→ 250 GM
→ 500 GM
→ 1 KG
```

Variant fields may include:

-   Name
-   SKU
-   Weight
-   Unit
-   Price
-   Discount price
-   Stock
-   Minimum order
-   Maximum order
-   Active status

------------------------------------------------------------------------

# 12. Inventory Module

Inventory must support both normal and fresh/perishable products.

## Features

-   Stock quantity
-   Available stock
-   Reserved stock
-   Stock in
-   Stock out
-   Stock adjustment
-   Low-stock threshold
-   Out-of-stock
-   Stock history
-   Wastage
-   Damaged stock

## Fresh Products

Fish, vegetables and fruits may require:

-   Daily stock
-   Collection/harvest date
-   Freshness status
-   Limited availability
-   Delivery restrictions

------------------------------------------------------------------------

# 13. Authentication Module

Features:

-   Customer registration
-   Login
-   Logout
-   Password reset
-   Email verification where needed
-   Session management
-   Account activation/deactivation
-   Admin authentication

------------------------------------------------------------------------

# 14. Customer Module

Features:

-   Customer profile
-   Phone
-   Email
-   Address book
-   Default address
-   Account dashboard
-   Order history
-   Wishlist
-   Reviews
-   Account settings

------------------------------------------------------------------------

# 15. Shopping Cart Module

Features:

-   Add to cart
-   Remove item
-   Update quantity
-   Variant selection
-   Stock validation
-   Price validation
-   Subtotal
-   Persistent cart
-   Guest cart
-   Customer cart
-   Mini cart

------------------------------------------------------------------------

# 16. Wishlist Module

Features:

-   Add product
-   Remove product
-   Wishlist listing
-   Move to cart
-   Stock awareness
-   Product availability awareness

------------------------------------------------------------------------

# 17. Checkout Module

Checkout flow:

``` text
Cart
↓
Customer Information
↓
Delivery Address
↓
Delivery Method
↓
Coupon
↓
Payment Method
↓
Order Review
↓
Place Order
```

Features:

-   Guest checkout
-   Customer checkout
-   Address selection
-   New address
-   Delivery charge
-   Coupon
-   Payment selection
-   Final total
-   Validation

------------------------------------------------------------------------

# 18. Order Module

Order lifecycle:

``` text
Pending
↓
Confirmed
↓
Processing
↓
Packed
↓
Shipped
↓
Out for Delivery
↓
Delivered
```

Additional states:

-   Cancelled
-   Failed
-   Returned
-   Refunded

Features:

-   Order creation
-   Order items
-   Status management
-   Customer order history
-   Cancellation
-   Re-order
-   Invoice
-   Admin notes

------------------------------------------------------------------------

# 19. Payment Module

Initial methods:

-   Cash on Delivery
-   Online Payment

Features:

-   Payment record
-   Transaction ID
-   Payment status
-   Gateway response
-   Verification
-   Failed payment handling
-   Refund status

Payment gateway integrations must be pluggable.

------------------------------------------------------------------------

# 20. Delivery Module

Features:

-   Delivery zones
-   District
-   Upazila
-   Area
-   Delivery charges
-   Weight-based charges
-   Product-based rules
-   Estimated delivery time
-   Shipping status
-   Courier reference

## Fresh Fish Rules

Fish may require:

-   Restricted delivery area
-   Same-day delivery
-   Delivery slot
-   Special packaging
-   Freshness guarantee rules

------------------------------------------------------------------------

# 21. Coupon & Promotion Module

Features:

-   Coupon code
-   Fixed discount
-   Percentage discount
-   Minimum order
-   Maximum discount
-   Product-specific discount
-   Category-specific discount
-   First-order discount
-   Seasonal campaign
-   Flash sale
-   Bundle offer

------------------------------------------------------------------------

# 22. Review & Rating Module

Features:

-   1--5 star rating
-   Written review
-   Verified purchase
-   Customer image
-   Review moderation
-   Average rating
-   Rating breakdown
-   Review status

Only eligible purchases should receive verified purchase status.

------------------------------------------------------------------------

# 23. Farmer / Farm Module

This module connects products to real sources.

Features:

-   Farmer profile
-   Farmer image
-   Farm profile
-   Farm images
-   Farming method
-   Production information
-   Farmer story
-   Product relationship

Example:

``` text
Product
↓
Farm
↓
Farmer
```

------------------------------------------------------------------------

# 24. Village / Product Origin Module

Features:

-   Village profile
-   Location
-   Product origin
-   Harvest information
-   Collection information
-   Source story
-   Village images

Example:

``` text
Nazirshail Rice
↓
Village
↓
Farm
↓
Farmer
```

Fish example:

``` text
Deshi Fish
↓
Bill/Jheel/Pond
↓
Village/Area
```

------------------------------------------------------------------------

# 25. Content / Blog Module

Content types:

-   Village stories
-   Farmer stories
-   Rice stories
-   Fish stories
-   Recipes
-   Farming tips
-   Seasonal product guides
-   Food information

Features:

-   Post CRUD
-   Category
-   Tags
-   Featured image
-   SEO title
-   SEO description
-   Slug
-   Draft/published
-   Author

------------------------------------------------------------------------

# 26. Notification Module

Notifications:

-   Registration
-   Order confirmation
-   Payment confirmation
-   Order status
-   Delivery status
-   Cancellation
-   Promotional alerts
-   Back-in-stock
-   Low-stock admin alert

Channels:

-   In-app
-   Email
-   SMS-ready

------------------------------------------------------------------------

# 27. Reports & Analytics Module

Dashboard:

-   Total sales
-   Today's sales
-   Monthly sales
-   Total orders
-   Pending orders
-   Delivered orders
-   Customers
-   Products
-   Low stock
-   Best sellers

Reports:

-   Sales
-   Revenue
-   Products
-   Categories
-   Rice sales
-   Fish sales
-   Vegetable sales
-   Customer
-   Inventory
-   Orders

------------------------------------------------------------------------

# 28. Admin & Roles Module

## Roles

### Super Admin

Full access.

### Admin

Business management.

### Product Manager

Products and categories.

### Order Manager

Orders.

### Inventory Manager

Stock.

### Content Manager

Blog and stories.

### Delivery Manager

Delivery.

Features:

-   Admin dashboard
-   Staff users
-   Roles
-   Permissions
-   Activity logs
-   System settings

------------------------------------------------------------------------

# 29. Admin Dashboard

The dashboard should show:

``` text
Today's Sales
Monthly Sales
Orders
Pending Orders
Customers
Products
Low Stock
Top Products
Revenue
```

Charts may include:

-   Sales over time
-   Orders over time
-   Category performance
-   Top products

------------------------------------------------------------------------

# 30. Customer Website Structure

## Main Navigation

``` text
Home
Shop
Categories
Rice
Fish
Vegetables
Seeds
Other Products
About
Stories
Blog
Contact
```

However, category links should be dynamically generated from the
database where appropriate.

The navigation must not depend on hard-coded category names.

------------------------------------------------------------------------

# 31. Homepage Structure

## Hero

Show the overall brand rather than only rice.

Hero should communicate:

-   Village
-   Farm
-   Freshness
-   Authenticity
-   Direct sourcing

Primary CTA:

``` text
Shop Now
```

Secondary CTA:

``` text
Explore Products
```

## Homepage Sections

1.  Hero
2.  Featured Categories
3.  Featured Products
4.  Rice Collection
5.  Fresh Fish
6.  Fresh Vegetables
7.  Seeds
8.  Other Village Products
9.  Best Sellers
10. Village/Farmer Story
11. Farm-to-Home Process
12. Reviews
13. Promotional CTA

Rice receives stronger visual treatment but does not dominate the
website.

------------------------------------------------------------------------

# 32. Product Discovery

Customers should be able to discover products through:

-   Search
-   Category
-   Subcategory
-   Price
-   Availability
-   Product type
-   Featured
-   Bestseller
-   New arrival
-   Seasonal
-   Origin

Sorting:

-   Popular
-   Newest
-   Price low to high
-   Price high to low
-   Best selling

------------------------------------------------------------------------

# 33. Search Requirements

Search should support:

-   Product name
-   SKU
-   Category name
-   Subcategory
-   Relevant searchable metadata

Example:

Searching:

``` text
Nazirshail
```

can return products assigned to the Nazirshail category.

Searching:

``` text
Rice
```

can return relevant rice products/categories.

------------------------------------------------------------------------

# 34. Dynamic Category UX

Admin creates:

``` text
Rice
```

Customer automatically sees it where the active category is configured
to appear.

Admin later adds:

``` text
New Local Rice
Parent = Rice
```

The customer catalog can automatically display it under Rice.

No code deployment should be necessary.

------------------------------------------------------------------------

# 35. Database High-Level Model

Core tables may include:

``` text
users
roles
permissions
categories
products
product_variants
product_images
inventory
inventory_transactions
addresses
carts
cart_items
wishlists
wishlist_items
orders
order_items
payments
deliveries
coupons
coupon_usages
reviews
farmers
farms
origins
villages
blog_posts
blog_categories
notifications
activity_logs
```

Additional tables may be introduced when required by implementation
details.

------------------------------------------------------------------------

# 36. Key Relationships

``` text
Category
  └── hasMany Products
  └── belongsTo Parent Category

Product
  ├── belongsTo Category
  ├── hasMany Variants
  ├── hasMany Images
  ├── hasMany Reviews
  ├── belongsTo Farmer/Farm
  └── belongsTo Origin

User
  ├── hasMany Orders
  ├── hasMany Addresses
  ├── hasMany Reviews
  └── hasMany Wishlist Items

Order
  ├── belongsTo User
  ├── hasMany Order Items
  ├── hasOne Payment
  └── hasOne Delivery
```

------------------------------------------------------------------------

# 37. Laravel Architecture Rules

Use:

-   Controllers for HTTP orchestration
-   Form Requests for validation
-   Models for relationships
-   Services/Actions for complex business logic
-   Policies/Gates for authorization
-   Events/Listeners where appropriate
-   Jobs/Queues for heavy asynchronous tasks
-   Blade Components for reusable UI

Avoid putting business logic directly into:

-   Blade
-   Routes
-   Large controllers

------------------------------------------------------------------------

# 38. Blade Component Rules

Reusable components should be used.

Suggested:

``` text
components/
├── button
├── card
├── badge
├── alert
├── modal
├── product-card
├── category-card
├── rating
├── price
├── pagination
├── navbar
├── footer
├── hero
├── section-heading
├── cart
└── form
```

Components should receive data through props.

Components should not directly query the database.

------------------------------------------------------------------------

# 39. Security Requirements

Mandatory:

-   CSRF protection
-   Authentication
-   Authorization
-   Policies/Gates
-   Validation
-   Secure password hashing
-   Rate limiting
-   Secure uploads
-   Admin protection
-   Session security
-   Activity logging
-   Safe order/payment handling

------------------------------------------------------------------------

# 40. Performance Requirements

Use:

-   Database indexes
-   Eager loading
-   Pagination
-   Query optimization
-   Image optimization
-   Cache where useful
-   Queues
-   Avoid N+1 queries

Product/catalog pages must be optimized for fast response.

------------------------------------------------------------------------

# 41. SEO Requirements

Public products and categories should support:

-   SEO title
-   SEO description
-   Slug
-   Canonical URL where needed
-   Open Graph image
-   Structured metadata where appropriate

Important SEO areas:

-   Rice
-   Fish
-   Vegetables
-   Seeds
-   Local food
-   Farm products
-   Village stories

------------------------------------------------------------------------

# 42. Admin Product Creation Flow

Example:

``` text
Admin
↓
Products
↓
Add Product
↓
Select Category
↓
Select/Create Variant
↓
Upload Images
↓
Set Price
↓
Set Inventory
↓
Set Origin/Farm
↓
SEO
↓
Publish
```

The category list must be generated dynamically.

------------------------------------------------------------------------

# 43. Admin Category Creation Flow

Example:

``` text
Admin
↓
Categories
↓
Add Category
↓
Name
↓
Parent Category
↓
Description
↓
Image
↓
SEO
↓
Sort Order
↓
Active
↓
Save
```

Example:

``` text
Name: Kalijira
Parent: Rice
```

The system automatically places Kalijira under Rice.

------------------------------------------------------------------------

# 44. Product Category Assignment

A product must be assigned to an active category according to the
application's category rules.

Example:

``` text
Product:
Premium Nazirshail Rice

Category:
Rice
→ Nazirshail
```

The product should inherit navigation/breadcrumb context from the
category hierarchy.

------------------------------------------------------------------------

# 45. Category Deletion Safety

Before deleting a category:

1.  Check child categories.
2.  Check assigned products.
3.  If dependencies exist, require reassignment or safe handling.
4.  Never silently orphan products.
5.  Prefer soft deletion/archive for business-critical categories.

------------------------------------------------------------------------

# 46. Fresh Product Architecture

Fresh products need extra metadata.

Possible fields:

-   Product type
-   Freshness status
-   Harvest date
-   Collection date
-   Available date
-   Expiry/usable period where applicable
-   Delivery restriction
-   Daily stock

This is especially important for:

-   Fish
-   Vegetables
-   Fruits

------------------------------------------------------------------------

# 47. Business Differentiation

The platform should not compete only on price.

Major trust features:

``` text
Product
↓
Where it came from
↓
Who produced/collected it
↓
How it was produced
↓
How it was processed
↓
How fresh it is
↓
How it reaches the customer
```

This creates a strong village-origin identity.

------------------------------------------------------------------------

# 48. Development Phases

## Phase 01 --- Foundation

-   Laravel setup
-   Database
-   Authentication
-   Roles
-   Base layout
-   UI components

## Phase 02 --- Dynamic Catalog

-   Category
-   Dynamic hierarchy
-   Product
-   Variants
-   Images
-   Inventory

## Phase 03 --- Shopping

-   Homepage
-   Shop
-   Search
-   Product details
-   Cart
-   Wishlist

## Phase 04 --- Checkout

-   Address
-   Delivery
-   Coupon
-   COD
-   Orders
-   Invoice

## Phase 05 --- Trust System

-   Farmer
-   Farm
-   Village
-   Origin
-   Stories
-   Reviews

## Phase 06 --- Marketing

-   Promotions
-   Coupons
-   Blog
-   Notifications

## Phase 07 --- Analytics

-   Reports
-   Sales
-   Revenue
-   Inventory
-   Customer analytics

## Phase 08 --- Advanced

-   Online payment
-   SMS
-   Courier API
-   Loyalty
-   Subscription
-   Mobile/API support

------------------------------------------------------------------------

# 49. MVP

The first production-ready version should include:

-   Authentication
-   Customer
-   Dynamic Category
-   Product
-   Product Variant
-   Inventory
-   Cart
-   Wishlist
-   Checkout
-   Order
-   COD Payment
-   Delivery
-   Admin
-   Basic Reviews

The architecture must already support:

-   Dynamic categories
-   Multiple product types
-   Fresh products
-   Farmer/Farm relationships
-   Product origins

------------------------------------------------------------------------

# 50. Final Module Count

The system contains **20 core modules**:

``` text
01. Authentication
02. Customer
03. Category
04. Product
05. Product Variant
06. Inventory
07. Shopping Cart
08. Wishlist
09. Checkout
10. Order
11. Payment
12. Delivery
13. Coupon & Promotion
14. Review & Rating
15. Farmer / Farm
16. Village / Product Origin
17. Content / Blog
18. Notification
19. Reports & Analytics
20. Admin & Roles
```

------------------------------------------------------------------------

# 51. Final Product Strategy

## Rice --- approximately 35%

Rice is a flagship category.

Possible admin-created subcategories can include:

``` text
Rice
├── Nazirshail
├── Katari Bhog
├── Chinigura
├── Kalijira
├── Aman
├── Boro
├── Aus
└── Any future rice category
```

These are examples, not fixed system values.

## Other Products --- approximately 65%

Admin can dynamically create:

``` text
Fish
Vegetables
Seeds
Dal
Spices
Oil
Honey
Fruits
Other Village Products
```

and any future category.

------------------------------------------------------------------------

# 52. Final Architecture Principle

The most important principle of this project is:

> **The system must be data-driven, not hard-coded.**

Admin should be able to grow the business without developer intervention
for normal catalog operations.

### Admin can add:

-   New main category
-   New subcategory
-   New rice category
-   New fish category
-   New vegetable category
-   New product
-   New product variant
-   New farmer
-   New farm
-   New village
-   New origin
-   New promotion

The application code should provide the framework; the Admin should
control the business data.

------------------------------------------------------------------------

# 53. Final Vision

``` text
                 VILLAGE COMMERCE
                        |
        ┌───────────────┼───────────────┐
        |               |               |
      FIELD           WATER          VILLAGE
        |               |               |
      Rice            Fish         Other Foods
   Vegetables        Bill Fish       Honey
      Seeds          Jheel Fish      Oil
       Dal           Pond Fish       Spices
      Fruits                         etc.
        |               |               |
        └───────────────┼───────────────┘
                        |
                DYNAMIC CATALOG
                        |
                  ONLINE STORE
                        |
              CART → CHECKOUT
                        |
              ORDER → PAYMENT
                        |
                    DELIVERY
                        |
                  CUSTOMER HOME
```

## Final Statement

This Laravel application is a **complete village-origin food and
agricultural e-commerce platform**.

It is not limited to rice.

Rice receives approximately **35% focus**, while all other products
together receive approximately **65% focus**.

Most importantly, the catalog is **fully dynamic**. Categories,
subcategories, products and variants are managed by the Admin through
the database and Admin Panel.

No normal category expansion should require code changes.

This document is the **Ultimate Master Overview** and should be used as
the high-level source of truth before implementing the 20 individual
module specifications.
