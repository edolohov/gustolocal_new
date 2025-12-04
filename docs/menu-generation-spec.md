## Weekly Menu Generation (Google Sheets + LLM)

Inputs
- `History_Normalized`: date, dish_name, category, portion_grams, tags, allergens
- `Dishes`: dish_name, category, allergens, tags, base_portion_grams, active
- Constraints (Preferences):
  - max_repeats_weeks (e.g., do not repeat same dish within 3 weeks)
  - category_mix (e.g., soups=3, mains=5, salads=2, desserts=2)
  - cost_target (optional, later)
  - global_avoid_allergens (e.g., no peanuts)

LLM Prompt (draft)
- Summarize constraints
- Provide top candidates per category from last N weeks based on diversity and past presence
- Include dish metadata (tags, allergens)
- Ask model to propose a set matching category_mix and max_repeats_weeks
- Require rationale per chosen dish (short), output as JSON rows matching `Menu_Draft` headers

Rules Engine (deterministic checks after LLM)
- Enforce no-allergen conflicts
- Enforce no repeat within max_repeats_weeks
- Ensure category counts match exactly
- Backfill with next-best candidates if model output violates rules

Output
- Write rows to `Menu_Draft` with `status=draft`
- Chef marks rows as approved; non-approved can be regenerated

MVP Scope
- Global constraints only; per-client preferences will be phase 2




