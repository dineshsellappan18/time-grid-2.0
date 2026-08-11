#!/usr/bin/env bash
# WO-012 — supply-chain constraint lint (no moving-branch Composer pins).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if grep -E '"dev-master|"dev-[^"]+"|"[^"]+#|"branch"' composer.json; then
  echo "FAIL: branch/dev constraints remain in composer.json"
  exit 1
fi

if [[ ! -f composer.lock ]]; then
  echo "FAIL: composer.lock is missing"
  exit 1
fi

echo "OK: no branch constraints; composer.lock present"

if command -v composer >/dev/null 2>&1; then
  echo "Running composer audit (informational until WO-015 raises PHP floor)..."
  composer audit --locked 2>&1 || true
fi
