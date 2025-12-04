## Google Sheets Setup for Menu Auto-Generation

Source: Your existing spreadsheet with multiple tabs (May → today):
- Link: https://docs.google.com/spreadsheets/d/1ig-Uo2foSNrJhuAVsgYEjm9DkfOcpboTx9qQJXwr6FA/edit?usp=sharing

Goal
- Keep historical tabs as-is. Add a small number of helper sheets that normalize data into a consistent table used for menu generation.

Add these sheets (new tabs)
1) Dishes (canonical reference)
- dish_name
- category (soup|main|side|salad|dessert|drink|other)
- allergens (comma-separated)
- tags (comma-separated: low_sugar, gluten_free, kid_friendly)
- base_portion_grams
- active (TRUE/FALSE)

2) History_Normalized (query of all past tabs)
- date
- dish_name
- category
- portion_grams
- cost
- was_on_menu (TRUE)
- week_id (ISO week or custom)
- rating_avg (if available later)
- times_ordered (if available later)

3) Preferences (optional for phase 1)
- key (e.g., avoid_allergens, max_repeats_weeks)
- value

4) Menu_Draft (output tab)
- week_start (same for all rows)
- dish_name
- category
- portion_grams
- notes (constraints/reasons)
- status (draft|approved)

Normalization
- Use QUERY/APPs Script to pull from legacy tabs into `History_Normalized` with unified headers. Start with dish_name, category, portion_grams, date; fill cost/tags gradually.

Permissions
- Share spreadsheet with a service account or keep user-authorized Apps Script bound to the sheet.




