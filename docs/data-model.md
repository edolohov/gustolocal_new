## Unified Data Model (MVP)

Storage choice (MVP): Airtable base with the following tables. CSV templates mirror these schemas for batch import/export.

Tables
1) Customers
- customer_id (PK)
- full_name
- phone
- email
- address_line
- city
- postal_code
- delivery_notes
- preferences (free text)

2) Preferences
- preference_id (PK)
- customer_id (FK → Customers)
- preference_type (enum: dietary, portion, allergens, time_window)
- key (e.g., sugar_free, less_meat, child_lunch)
- value (e.g., true, medium_portion)
- active (bool)

3) Menus
- menu_id (PK)
- week_start (date, ISO Monday)
- notes
- status (draft|published)

4) Dishes
- dish_id (PK)
- name
- category (soup|main|side|salad|dessert|drink|other)
- allergens (array/text)
- tags (array/text: low_sugar, gluten_free, kid_friendly)
- base_portion_grams (int)
- active (bool)

5) MenuItems
- menu_item_id (PK)
- menu_id (FK → Menus)
- dish_id (FK → Dishes)
- available_portions (int)
- price (money)
- variants (text: e.g., 250g/350g)

6) Orders
- order_id (PK from WooCommerce)
- order_datetime (UTC)
- customer_id (FK → Customers)
- delivery_date
- delivery_window (text)
- total_amount
- raw_source (json: original WC payload)

7) OrderItems
- order_item_id (PK)
- order_id (FK → Orders)
- menu_item_id (nullable; if mapping exact)
- dish_id (FK → Dishes)
- variant (text)
- quantity
- portion_grams (int)
- notes

8) Feedback
- feedback_id (PK)
- order_id (FK)
- order_item_id (FK)
- customer_id (FK)
- dish_id (FK)
- rating (1–5)
- sentiment (optional: positive|neutral|negative)
- comment
- channel (telegram|email|qr|sms|whatsapp)
- created_at

9) Deliveries
- delivery_id (PK)
- order_id (FK)
- courier_name
- status (planned|out_for_delivery|delivered|failed)
- failure_reason (nullable)
- delivered_at

Key normalizations
- Keep `Dishes` stable across weeks; `MenuItems` link dishes to weeks.
- `OrderItems` map WooCommerce options to structured fields (dish, variant, grams, quantity).
- `Feedback` links to exact `order_item_id` for precision.

CSV templates
- Place CSV headers matching columns above in `docs/templates/*.csv` to bootstrap data.

Scaling path
- Start in Airtable for speed; migrate to Postgres with the same schema when needed. Keep Airtable as admin UI via sync.




