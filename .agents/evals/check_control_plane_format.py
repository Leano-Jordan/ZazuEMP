#!/usr/bin/env python3
"""Fail when control-plane Markdown/YAML contains literal backslash-n text."""

from pathlib import Path
import sys

ROOT = Path(__file__).resolve().parents[2]
SCAN_ROOTS = [ROOT / ".ai", ROOT / ".agents", ROOT / "docs"]
EXTENSIONS = {".md", ".yml", ".yaml"}

violations = []
for base in SCAN_ROOTS:
    if not base.exists():
        continue
    for path in base.rglob("*"):
        if path.is_file() and path.suffix.lower() in EXTENSIONS:
            try:
                text = path.read_text(encoding="utf-8")
            except UnicodeDecodeError:
                continue
            for line_no, line in enumerate(text.splitlines(), 1):
                if "\\n" in line:
                    violations.append(f"{path.relative_to(ROOT)}:{line_no}")

if violations:
    print("FAIL: literal \\n found in control-plane Markdown/YAML:")
    print("\n".join(violations))
    sys.exit(1)

print("PASS: no literal \\n found under .ai/, .agents/ or docs/.")
