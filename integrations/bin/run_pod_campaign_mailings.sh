#!/usr/bin/env bash
set -euo pipefail

APP_ROOT="${APP_ROOT:-/home/forge/drivingfaith.com/current}"
INTEGRATIONS_ROOT="${APP_ROOT}/integrations"
PYTHON_BIN="${PYTHON_BIN:-${INTEGRATIONS_ROOT}/.venv/bin/python}"
LOCK_FILE="${LOCK_FILE:-/tmp/drivingfaith-pod-campaign-mailings.lock}"

cd "${INTEGRATIONS_ROOT}"

exec 9>"${LOCK_FILE}"
if ! flock -n 9; then
    echo "POD campaign mailing job already running; exiting."
    exit 0
fi

"${PYTHON_BIN}" -m pod.send_campaign_mailings --plan-active-campaigns
"${PYTHON_BIN}" -m pod.send_campaign_mailings --all --send
