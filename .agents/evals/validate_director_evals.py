#!/usr/bin/env python3
"""Static integrity check for the Director control-plane eval contract.

This validates the machine-readable eval contract and fixture pairing.
It does not execute a live Director and therefore cannot claim behavioural PASS.
"""

from pathlib import Path
import re
import sys

ROOT = Path(__file__).resolve().parents[2]
EVALS = ROOT / ".agents" / "evals" / "director-evals.yml"
FIXTURES = ROOT / ".agents" / "evals" / "DIRECTOR_EVAL_FIXTURES.yml"

if not EVALS.exists() or not FIXTURES.exists():
    print("FAIL: Director eval files are missing")
    sys.exit(1)

eval_text = EVALS.read_text(encoding="utf-8")
fixture_text = FIXTURES.read_text(encoding="utf-8")

scenario_blocks = re.findall(
    r"(?ms)^  - id: (DIR-EVAL-\d{3})\n(.*?)(?=^  - id: DIR-EVAL-\d{3}\n|\Z)",
    eval_text,
)
expected_by_id = {}
for scenario_id, block in scenario_blocks:
    match = re.search(r"^    expected: ([A-Z0-9_]+)\s*$", block, re.M)
    if not match:
        print(f"FAIL: {scenario_id} has no expected disposition")
        sys.exit(1)
    expected_by_id[scenario_id] = match.group(1)

if len(expected_by_id) != 12:
    print(f"FAIL: expected 12 eval scenarios, found {len(expected_by_id)}")
    sys.exit(1)

fixture_blocks = re.findall(
    r"(?ms)^  ([A-Za-z0-9_-]+):\n(.*?)(?=^  [A-Za-z0-9_-]+:\n|\Z)",
    fixture_text,
)
fixture_by_id = {}
for fixture_name, block in fixture_blocks:
    scenario_match = re.search(r"^    scenario: (DIR-EVAL-\d{3})\s*$", block, re.M)
    if scenario_match:
        scenario_id = scenario_match.group(1)
        expected_match = re.search(
            r"^    expected_disposition: ([A-Z0-9_]+)\s*$", block, re.M
        )
        if not expected_match:
            print(f"FAIL: fixture {fixture_name} for {scenario_id} has no own expected_disposition")
            sys.exit(1)
        if scenario_id in fixture_by_id:
            print(f"FAIL: duplicate fixture for {scenario_id}")
            sys.exit(1)
        fixture_by_id[scenario_id] = expected_match.group(1)

missing = sorted(set(expected_by_id) - set(fixture_by_id))
extra = sorted(set(fixture_by_id) - set(expected_by_id))

if missing:
    print("FAIL: scenarios without fixtures:", ", ".join(missing))
    sys.exit(1)

if extra:
    print("FAIL: fixtures reference unknown scenarios:", ", ".join(extra))
    sys.exit(1)

mismatches = sorted(
    scenario_id
    for scenario_id, expected in expected_by_id.items()
    if fixture_by_id.get(scenario_id) != expected
)
if mismatches:
    print("FAIL: fixture expected disposition does not match scenario:")
    for scenario_id in mismatches:
        print(
            f"  {scenario_id}: scenario={expected_by_id[scenario_id]} "
            f"fixture={fixture_by_id.get(scenario_id)}"
        )
    sys.exit(1)

print("PASS: 12 Director eval scenarios each have a matching fixture and own expected disposition.")
print("NOTE: this validates the eval contract only; it does not execute a live Director.")
