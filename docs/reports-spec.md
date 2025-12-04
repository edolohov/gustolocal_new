## Report Specifications

1) Kitchen Prep Summary (CSV/Sheet)
Columns
- dish_name
- variant
- portion_grams
- total_portions
- notes (e.g., allergen flags)

Aggregation
- Group by dish_name + variant + portion_grams
- Sum `quantity` from `OrderItems`

Example
- borscht, 350g, 12
- turkey fillet, 250g, 6

2) Customer Packing List (per-order)
Columns
- order_id
- customer_name
- address
- delivery_window
- dish_name
- variant
- qty
- notes (preferences/allergens)

3) Courier Route Sheet
Columns
- stop_no
- customer_name
- phone
- address
- time_window
- packages (count)
- notes

Routing
- Sort by geographic clustering (Google Maps API/OSRM later). MVP: sort by postal_code, then city.

4) Labels (optional printable)
Fields per label
- customer_name
- dish_name
- variant
- portion_grams
- allergens
- date
- QR to feedback form with encoded order_item_id

Filters
- By delivery_date.

Format targets
- CSV + Google Sheets for MVP; later PDF exports.




