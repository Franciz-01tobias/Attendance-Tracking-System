#!/usr/bin/env bash
set -euo pipefail
php tests/unit/submission_policy_test.php
php tests/integration/marazone_readonly_test.php
php tests/integration/marazone_contract_test.php
php tests/smoke/workflow_smoke.php
