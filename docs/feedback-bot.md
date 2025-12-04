## Feedback Collection Flow (MVP)

Channels
- Telegram bot (primary)
- QR on label → short web form
- Email fallback

Linking logic
- Each `OrderItem` gets a unique token (UUID) → embedded in QR and deep link.
- Bot command `/rate <token>` resolves to `order_item_id` and shows 5 buttons (⭐1–⭐5), then optional comment.

Bot interactions
1) One-tap rating
2) Optional comment prompt
3) Thank-you + quick links (manage preferences)

Data captured
- rating (1–5), comment, sentiment (auto), channel, timestamps

Tech
- Telegram Bot API
- Minimal webhook endpoint (serverless or WordPress REST route)
- Store into `Feedback` with FK to `Orders`/`OrderItems`

Privacy
- Tokens scoped to specific order item; no PII exposed in links.




