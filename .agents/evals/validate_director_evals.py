#!/usr/bin/env python3
"""Static integrity check for the Director control-plane eval contract.

This does not execute a live Director. It only proves that the repository's
machine-readable eval definitions are internally complete and consistent.
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

ids = re.findall(r"^\s*- id: (DIR-EVAL-\d{3})\s*$", eval_text, re.M)
expected = re.findall(r"^\s*expected:\s*([A-Z0-9_]+)\s*$", eval_text, re.M)

if len(ids) != 12:
    print(f"FAIL: expected 12 eval scenarios, found {len(ids)}")
    sys.exit(1)

if len(set(ids)) != len(ids):
    print("FAIL: duplicate Director eval IDs")
    sys.exit(1)

fixture_ids = re.findall(r"^\s*scenario:\s*(DIR-EVAL-\d{3})\s*$", fixture_text, re.M)

missing = sorted(set(ids) - set(fixture_ids))
duplicates = sorted({x for x in fixture_ids if fixture_ids.count(x) > 1})

if missing:
    print("FAIL: scenarios without fixtures:", ", ".join(missing))
    sys.exit(1)

if duplicates:
    print("FAIL: duplicate fixtures:", ", ".join(duplicates))
    sys.exit(1)

if len(set(fixture_ids)) != 12:
    print(f"FAIL: expected fixtures for 12 scenarios, found {len(set(fixture_ids))}")
    sys.exit(1)

for disposition in expected:
    if disposition not in fixture_text:
        print(f"FAIL: expected disposition {disposition} is not represented by fixtures")
        sys.exit(1)

print("PASS: 12 Director eval scenarios and fixtures are structurally complete.")
print("NOTE: this validates the eval contract only; it does not execute a live Director.")
