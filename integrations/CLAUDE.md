# CLAUDE.md

Operational instructions for the Driving Faith Python integrations.

The Laravel app is the primary project. This folder contains Python scripts under `integrations` for mailer/POD service workflows and future Python integrations. The integration folder shares Laravel's `.env` file and database settings.

The current POD mailer workflow does not generate PDFs locally. Mailer HTML/templates are sent to the service, and the service generates print-ready output.

Rules:

- Use Laravel `.env`; do not reintroduce a separate `config.ini` without explicit approval.
- Keep mail processing idempotent.
- Default commands to dry-run behavior.
- Require an explicit flag, such as `--send`, before sending real mail.
- Keep secrets out of source files.
- Keep Python focused on integration logic while Laravel owns app/UI/scheduling concerns.
