# Driving Faith Python Integrations

Python scripts for Driving Faith integration workflows.

This folder lives inside the Laravel app and shares Laravel's `.env` file. The current POD mailer workflow does not generate PDFs locally; mailer HTML/templates are sent to the service, and the service handles print-ready PDF generation on its side.

## Setup

From the Laravel project root:

```bash
cd integrations
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

## Configuration

The scripts read Laravel's `.env` file from the project root. The database connection uses Laravel's standard MySQL keys:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=drivingfaith
DB_USERNAME=root
DB_PASSWORD=
```

The mailer settings use:

```env
LOB_API_KEY=
LOB_INTRO_TEMPLATE_ID=tmpl_a36258afb8ff682
LOB_FROM_ADDRESS_ID=adr_3d00b791666b3b2a
POD_SOURCE_ID=3
POD_BATCH_LIMIT=25
```

Direct process environment variables override values from `.env`. To point the integration at a different env file, set `DRIVINGFAITH_ENV_FILE`.

## Send Intro Letters

Preview the pending `drive_contacts` rows where `source_id = 3` and `complete = 0`:

```bash
cd integrations
python -m pod.send_intro_letters
```

Preview a specific number of matching contacts:

```bash
python -m pod.send_intro_letters --limit 1
python -m pod.send_intro_letters --limit 5
python -m pod.send_intro_letters --all
```

To send real letters, add `--send`:

```bash
python -m pod.send_intro_letters --limit 1 --send
python -m pod.send_intro_letters --limit 5 --send
python -m pod.send_intro_letters --all --send
```

After Lob accepts a letter, the script updates that contact row with `complete = 1`.
