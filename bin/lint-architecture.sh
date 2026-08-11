#!/usr/bin/env bash
# WO-013 — architecture coupling metrics for modernization gates.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

OUT_DIR="${ROOT}/storage/architecture"
mkdir -p "$OUT_DIR"
REPORT="${OUT_DIR}/coupling-report.txt"

{
  echo "Timegrid architecture coupling report"
  echo "generated_at=$(date -u +%Y-%m-%dT%H:%M:%SZ)"
  echo
  echo "== Controller count =="
  find app/Http/Controllers -name '*.php' | wc -l | awk '{print "controllers="$1}'
  echo
  echo "== Concierge model imports from app/ =="
  rg -c "Timegridio\\\\Concierge\\\\Models" app --glob '*.php' | awk -F: '{s+=$2} END {print "concierge_model_import_lines="s+0}'
  echo
  echo "== Controllers referencing Concierge service =="
  rg -l "Timegridio\\\\Concierge\\\\Concierge" app/Http/Controllers --glob '*.php' | wc -l | awk '{print "controllers_using_concierge="$1}'
  echo
  echo "== Migrations referencing App\\\\Http =="
  if rg -l "App\\\\Http" database/migrations --glob '*.php' >/dev/null 2>&1; then
    rg -l "App\\\\Http" database/migrations --glob '*.php' | wc -l | awk '{print "migrations_coupling_http="$1}'
  else
    echo "migrations_coupling_http=0"
  fi
  echo
  echo "== Largest controllers by line count (top 10) =="
  find app/Http/Controllers -name '*.php' -print0 | xargs -0 wc -l | sort -nr | head -11
} | tee "$REPORT"

echo
echo "Report written to $REPORT"
