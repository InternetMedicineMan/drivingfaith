# AGENTS.md

Operational instructions for Codex on the Driving Faith Python integrations.

---

## What This Integration Is

This folder contains Python scripts that support Laravel Driving Faith app integration workflows.

The Laravel app is the primary project. Python lives under `integrations` and shares Laravel's `.env` file, database, and deployment foundation.

The current POD mailer workflow does not generate PDFs locally. Mailer HTML/templates are sent to the mail/POD service, and that service generates the print-ready PDF output on its side.

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
