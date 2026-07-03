# AGENTS.md

Operational instructions for Codex on the Driving Faith Python integrations.

---

## What This Integration Is

This folder contains Python scripts that support Laravel Driving Faith app integration workflows.

The Laravel app is the primary project. Python lives under `integrations` and shares Laravel's `.env` file, database, and deployment foundation.

The current POD mailer workflow does not generate PDFs locally. Laravel renders dynamic HTML documents at public tokenized render URLs, Python sends those URLs to Lob, and Lob generates the print-ready PDF output on its side.

## POD Bible Study Campaign Workflow

- Laravel owns the admin UI, teams/ministry groups, contacts, campaigns, campaign mailing steps, print layout templates, enrollments, planned mailing records, and dynamic render URLs.
- Python owns operational mail processing: planning enrollment mailings, selecting due mailings, calling Lob, recording deliveries/failures, and advancing enrollment state.
- Do not assume this workflow must be rebuilt as Laravel console commands. The established entrypoint is `python -m pod.send_campaign_mailings` from the `integrations` directory.
- Planning is idempotent. Re-running `python -m pod.send_campaign_mailings --plan-campaign CAMPAIGN_ID` ensures missing `pod_enrollment_mailings` rows exist for all enrollments in that campaign without duplicating existing planned rows.
- For production cron, prefer `python -m pod.send_campaign_mailings --plan-active-campaigns` or `integrations/bin/run_pod_campaign_mailings.sh` so new active campaigns do not require new cron entries.
- Sending is also guarded by delivery state. `python -m pod.send_campaign_mailings --all --send` processes due planned mailings and skips deliveries already marked sent.
- Dry runs are default. Use `--prepare-render-urls` to create real render tokens/URLs for inspection without sending to Lob.
- Real mail requires `--send`.
- Laravel render URLs use `LOB_PUBLIC_DOMAIN`, not necessarily local `APP_URL`, because Lob must fetch the HTML from a public URL.
- Letters/Bible studies use one rendered artifact URL. Future postcards must use separate rendered artifact URLs for front and back.

---

## Non-Negotiable Rules

1. **Use Laravel `.env` as the source of truth.** Do not reintroduce a separate `config.ini` unless explicitly asked.
2. **Keep scripts idempotent.** A re-run must not duplicate mail processing. Respect the database completion/state fields.
3. **Default to dry runs.** Commands that send real mail must require an explicit send flag.
4. **Keep HTML/template behavior service-compatible.** Do not add local PDF layout assumptions to this project.
5. **Keep Laravel and Python boundaries clear.** Laravel owns the app, UI, scheduling, queues, and shared config. Python owns the service integration logic.

---

## Edit Conventions

- Make surgical edits and keep unrelated refactors out of feature changes.
- Preserve existing module names and command entrypoints unless the user approves a rename.
- Keep secrets out of source files and documentation.
- Prefer structured output when adding machine-readable command results.

---

## Setup

From the Laravel project root:

```bash
cd integrations
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

Run preview mode first:

```bash
python -m pod.send_intro_letters
```

Send real mail only with `--send`.
