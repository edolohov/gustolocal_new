## WooCommerce Order Parser Mapping (MVP)

Assumption
- Single product with options captures meal selections.

Input (examples)
- product_name
- options: { dish: "borscht", variant: "350g", qty: 2, notes: "no sour cream" }
- line_total

Mapping rules → `OrderItems`
- dish → resolve to `Dishes.dish_id` by name (case-insensitive; maintain alias table)
- variant → store raw; parse grams if present → `portion_grams`
- qty → `quantity`
- notes → `notes`
- If weekly menu exists, try to map to `MenuItems` by (menu_id, dish_id, variant)

Customer linkage
- Use WC order billing/shipping to find or create `Customers` by phone/email.

Delivery info
- delivery_date from chosen date option; else from order meta
- delivery_window from option if present; else default

Data flow
1) Pull orders via WC REST API for a date range
2) Normalize to `Orders` and `OrderItems`
3) Upsert `Customers`, `Dishes` (create if new)
4) Store original JSON in `Orders.raw_source`

Edge cases
- Unknown dish names → create `Dishes` with `active=false` and flag for review
- Missing grams → set from `Dishes.base_portion_grams` if available
- Multiple products per order still supported; iterate all line items




