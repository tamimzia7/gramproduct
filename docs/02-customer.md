# Customer Module

## Module ID

02-customer

## Purpose

Customer profile, addresses, account dashboard, order history, saved
information, account status.

## Scope

This module is part of the Laravel-based village food and agricultural
e-commerce platform. The platform sells rice as a major product category
while also supporting fish, vegetables, seeds, dal, spices, oil, honey,
fruits and other village products.

## Core Responsibilities

-   Define the module's data and business responsibilities.
-   Keep business logic separated from presentation.
-   Use reusable Laravel components and services where appropriate.
-   Validate all user/admin input.
-   Enforce authorization and ownership rules.
-   Keep the module compatible with the overall e-commerce architecture.

## Main Actors

-   Customer
-   Admin
-   Authorized Staff

## Functional Requirements

-   The module must provide the core functionality described in the
    purpose.
-   Admin operations must require appropriate permissions.
-   Customer-facing operations must respect product, order and account
    state.
-   Important state changes should be recorded where auditability is
    required.

## Laravel Implementation Guidelines

-   Use Controllers for HTTP orchestration.
-   Use Form Requests for validation.
-   Use Eloquent Models and relationships for persistence.
-   Use Services/Actions for non-trivial business logic.
-   Use Policies/Gates for authorization.
-   Use Blade Components for reusable UI.
-   Keep database access out of Blade components.
-   Use migrations, seeders and factories for database setup and
    testing.

## Suggested Deliverables

-   Database migration(s)
-   Eloquent model(s)
-   Relationships
-   Form Request classes
-   Policy/authorization rules where needed
-   Service/Action classes where needed
-   Controllers
-   Blade views/components
-   Routes
-   Feature/unit tests

## Non-Goals

This module must not duplicate responsibilities owned by another module.
Cross-module behavior should use clear service boundaries or
relationships.

## Acceptance Criteria

-   Core functionality works end-to-end.
-   Validation errors are handled cleanly.
-   Unauthorized actions are blocked.
-   Empty, missing and invalid states are handled.
-   Responsive customer/admin UI is maintained where applicable.
-   Tests cover important business rules.

## Dependencies

This module may depend on shared authentication, database, product,
customer, order, inventory or configuration services depending on its
specific responsibility.

## Future Extensibility

The module should be designed so that new product categories, delivery
methods, payment methods, staff roles and notification channels can be
added without rewriting the core architecture.
