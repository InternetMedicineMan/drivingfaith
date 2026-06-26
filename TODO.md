# DrivingFaith TODO

## Current Priorities

1. Move billing from users to teams.
   - Teams are the paid workspace.
   - Users can belong to multiple teams and switch between them.
   - Avoid destructive migrations; preserve existing users and user billing data during transition.

2. Add team dashboard metrics.
   - Team members / ministry directory count.
   - Active Bible Study contacts.
   - Billing or subscription status.
   - Credits remaining once the credit ledger exists.
   - Scheduled mailings and replies needing attention.

3. Build the ministry member directory.
   - Directory people are not always platform users.
   - Access should attach to a directory person when needed.
   - Role/access language should fit ministry: pastor, board, volunteer, member, viewer.
   - Support multiple titles per person for small ministries where people wear several hats.

4. Move authentication toward passwordless access.
   - OAuth providers: Google, X, GitHub.
   - Email one-time login link or code.
   - Later: SMS one-time code.
   - Hide/remove password-first registration, login, reset, and profile flows when ready.

5. Add team credits.
   - Use a ledger, not only a mutable balance.
   - Credits belong to teams.
   - Spending should be traceable to deliveries or other usage.

6. Add custom domain mapping.
   - Start with team-owned verified domains.
   - Resolve a team from the host when appropriate.
   - Consider full tenancy only if domain mapping is not enough.

## Data Safety Notes

- Normal code changes should not delete local users.
- Avoid `migrate:fresh`, `db:wipe`, and broad reseeding on the local app database unless explicitly approved.
- Before destructive or high-risk billing data migrations, take a database backup or provide a clear warning.
