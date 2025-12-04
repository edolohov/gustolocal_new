## AI-Assisted Weekly Menu Pipeline (MVP)

Inputs
- Historical Menus, Orders, Feedback, Preferences
- Constraints: allergens, max repeats, cost targets, category mix

Process
1) Data prep: aggregate dish scores per segment (global + per-customer)
2) Candidate set: top-rated dishes, freshness window, diversity
3) Draft menu: LLM prompt with constraints → propose 10–20 items
4) Validate: rules engine (no allergens conflicts, variety, budget)
5) Human review: chef tweaks in Airtable/Sheet

Outputs
- `Menus` + `MenuItems` for the week

Tech
- Start with prompt-based LLM (no fine-tune). Optionally use embeddings for similarity and diversity.
- Keep deterministic rules in code to ensure safety.

Safety
- Hard constraints enforced post-LLM; never rely on model for allergens.




