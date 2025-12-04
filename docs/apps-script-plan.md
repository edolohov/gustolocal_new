## Google Apps Script Plan (One-Click Draft Menu)

Bound script to the spreadsheet. Add custom menu: "Menu → Generate Draft".

Functions
- onOpen(): add custom menu
- generateDraftMenu(weekStart):
  1) Read `Preferences` for constraints
  2) Read `Dishes` and `History_Normalized`
  3) Build candidate lists per category honoring max_repeats_weeks
  4) Call LLM (UrlFetchApp to your API) with prompt from `menu-generation-spec.md`
  5) Validate with rules engine; backfill if needed
  6) Write rows to `Menu_Draft` and freeze header

Security
- Store API key in Script Properties

MVP Notes
- If LLM not configured, fallback to deterministic suggestion: pick least-recently-used dishes per category.




