#!/usr/bin/env bash
set -euo pipefail

APP_NAME="${1:-erp-monitoring-po}"

if [ ! -d "$APP_NAME" ]; then
  echo "[ERROR] folder tidak ditemukan: $APP_NAME"
  exit 1
fi

echo "[INFO] continue_after_error digabung ke setup_erp.sh"
echo "[INFO] Menjalankan setup_erp.sh dalam mode existing project..."
APP_NAME="$APP_NAME" bash "$(dirname "$0")/setup_erp.sh"
