# DrivingFaith TODO

## Current Priorities

1. Add team dashboard metrics.
   - Team members / ministry directory count.
   - Active Bible Study contacts.
   - Billing or subscription status.
   - Credits remaining once the credit ledger exists.
   - Scheduled mailings and replies needing attention.

2. Build the ministry member directory.
   - Directory people are not always platform users.
   - Access should attach to a directory person when needed.
   - Role/access language should fit ministry: pastor, board, volunteer, member, viewer.
   - Support multiple titles per person for small ministries where people wear several hats.
   - Model members as an add-on profile for ministry contacts, likely `ministry_members.contact_id`, so outreach history and membership records stay connected without mixing every contact into the member list.
   - Make it easy to convert a ministry contact into a member while preserving the contact timeline.

3. Move authentication toward passwordless access.
   - OAuth providers: Google, X, GitHub.
   - Email one-time login link or code.
   - Later: SMS one-time code.
   - Hide/remove password-first registration, login, reset, and profile flows when ready.

4. Add team credits.
   - Use a ledger, not only a mutable balance.
   - Credits belong to teams.
   - Spending should be traceable to deliveries or other usage.
   - Monthly account billing should stay separate from one-time credit purchases.
   - Support one-time credit packs first, then optional auto-refill when the balance drops below a configured threshold.
   - Decrement credits for POD mailings and other per-event costs, with ledger entries tied back to the delivery or usage record.
   - Keep internal/self mailings possible without billing the current ministry account while the system is still private.

5. Preserve multi-artifact rendering support for future POD formats.
   - Letters and Bible studies render one complete remote file URL for Lob.
   - Postcards should render two separate remote file URLs: one for `front` and one for `back`.
   - Each postcard side can use the same or different print layout shell, but each side still has a simple `{{ content }}` slot.
   - Do not model postcards as two regions inside one HTML document; Lob expects separate front/back artwork sources.
   - Current letter work may use one selected `letter_file` layout, but naming and renderer boundaries should leave room for `postcard_front` and `postcard_back` rendered artifacts later.

6. Add custom domain mapping.
   - Start with team-owned verified domains.
   - Resolve a team from the host when appropriate.
   - Consider full tenancy only if domain mapping is not enough.

## Ready For Full-Suite Verification

- Move billing from users to teams.
  - Stripe billing now uses teams as the paid workspace.
  - Users can belong to multiple teams and billing follows the selected ministry team.
  - Legacy user billing data is preserved during the transition.
  - LemonSqueezy and Paddle are disabled for now instead of migrated.
  - Remaining checks before final closeout: run the full test suite and decide whether to hide legacy user billing fields more aggressively in admin.

## Data Safety Notes

- Normal code changes should not delete local users.
- Avoid `migrate:fresh`, `db:wipe`, and broad reseeding on the local app database unless explicitly approved.
- Before destructive or high-risk billing data migrations, take a database backup or provide a clear warning.
