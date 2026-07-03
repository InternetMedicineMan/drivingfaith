# Driving Faith Python Integrations

Python scripts for Driving Faith integration workflows.

This folder lives inside the Laravel app and shares Laravel's `.env` file. The current POD mailer workflow does not generate PDFs locally; Laravel renders dynamic HTML documents at public tokenized render URLs, Python sends those URLs to Lob, and Lob handles print-ready PDF generation on its side.

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
LOB_PUBLIC_DOMAIN=https://drivingfaith.com
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

## Send Bible Study Campaign Mailings

The Bible Study/POD campaign workflow is split between Laravel and Python:

- Laravel owns the admin UI, contacts, campaigns, enrollments, print layout templates, and dynamic render URLs.
- Python owns planning, sending, Lob delivery recording, failures, and enrollment advancement.

Create or refresh planned mailing rows for one campaign:

```bash
cd integrations
python -m pod.send_campaign_mailings --plan-campaign 1
```

Create or refresh planned mailing rows for every active campaign:

```bash
python -m pod.send_campaign_mailings --plan-active-campaigns
```

Create or refresh planned mailing rows for one enrollment:

```bash
python -m pod.send_campaign_mailings --plan-enrollment 1
```

Preview due planned mailings without sending:

```bash
python -m pod.send_campaign_mailings --limit 5
python -m pod.send_campaign_mailings --all
```

Prepare real render URLs during a dry run so they can be opened and inspected:

```bash
python -m pod.send_campaign_mailings --limit 5 --prepare-render-urls
```

Send due planned mailings:

```bash
python -m pod.send_campaign_mailings --limit 5 --send
python -m pod.send_campaign_mailings --all --send
```

The campaign planner is idempotent. Re-running `--plan-campaign` adds missing planned rows for new enrollments or newly activated campaign mailing steps without duplicating existing planned rows.

## Cron

For production, use the wrapper script so planning and sending run back-to-back under one lock:

```cron
*/15 * * * * APP_ROOT=/home/forge/drivingfaith.com/current PYTHON_BIN=/full/path/to/python /home/forge/drivingfaith.com/current/integrations/bin/run_pod_campaign_mailings.sh >> /home/forge/drivingfaith.com/current/storage/logs/pod-campaign-mailings.log 2>&1
```

The wrapper runs:

```bash
python -m pod.send_campaign_mailings --plan-active-campaigns
python -m pod.send_campaign_mailings --all --send
```

Set `PYTHON_BIN` to the full path of the Python interpreter or wrapper used on the server. Cron will not load shell aliases such as `shortpy`.
