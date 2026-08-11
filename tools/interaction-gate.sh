#!/usr/bin/env bash
# interaction-gate.sh — Run the interaction parity Dusk gate and publish result.
# Exit non-zero if any checklist row is unmapped or fails.
#
# Usage: ./tools/interaction-gate.sh
# Outputs: storage/app/interaction-gate-result.json (build artifact)

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_DIR"

echo "=== Interaction Parity Phase Gate ==="
echo ""
echo "Running gate validation test..."
echo ""

php artisan test --filter=RunInteractionGateTest 2>&1 | tee /tmp/interaction-gate-output.txt

EXIT_CODE=${PIPESTATUS[0]}

if [ -f storage/app/interaction-gate-result.json ]; then
    echo ""
    echo "Gate result artifact:"
    cat storage/app/interaction-gate-result.json
    echo ""
fi

if [ $EXIT_CODE -eq 0 ]; then
    echo "✓ Phase gate PASSED — all checklist rows mapped to existing tests."
else
    echo "✗ Phase gate FAILED — unmapped or missing test references."
    echo "  Review storage/app/interaction-gate-result.json for details."
fi

exit $EXIT_CODE
