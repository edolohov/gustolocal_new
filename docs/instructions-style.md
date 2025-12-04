## Cooking Instruction Style Guide

Use this format when asking the LLM to generate reheating/serving tips for ready-made dishes.

Structure per dish:
1. **Preparation** – quick reminder to check packaging/portion and list main ingredients.
2. **Heating/Cooking** – specific method (stovetop/oven/skillet), temperature cues, duration (range in minutes).
3. **Serving & Storage** – garnish ideas, sauces, storage window (e.g., “охладите и храните до 36 часов”).

Tone (согласно “Инструкция для LLM .pdf”):
- Деловой, без смайликов и поварского жаргона.
- Поясняем, что вкуснее готовить на плите/в аэрогриле, но допускаем микроволновку.
- Напоминаем, что блюда могут быть готовыми, полуфабрикатами или заморозкой.
- Всегда заканчиваем рекомендацией по хранению остатков.

Output format for LLM prompts:
```json
[
  {
    "name": "Классический борщ",
    "instruction": "1) ...\\n2) ...\\n3) ...",
    "allergens": "молоко (со сметаной)"
  }
]
```

Fallback rule if LLM unavailable:
- Use general template: preparation → heating (with temperature/technique based on category) → serving/storage reminder.

