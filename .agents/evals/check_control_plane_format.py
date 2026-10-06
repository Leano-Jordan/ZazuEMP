#!/usr/bin/env python3
"""Fail when control-plane Markdown/YAML contains literal backslash-n text."""

from pathlib import Path
import re
import sys

ROOT = Path(__file__).resolve().parents[2]
SCAN_ROOTS = [ROOT / ".ai", ROOT / ".agents", ROOT / "docs"]
GITHUB_MARKDOWN_ROOT = ROOT / ".github"
ROOT_FILES = [ROOT / "AGENTS.md", ROOT / "CLAUDE.md", ROOT / "README.md", ROOT / "memory.md"]
EXTENSIONS = {".md", ".yml", ".yaml"}

violations = []
read_order_violations = []


def scan_tree(base: Path, extensions: set[str]) -> None:
    if not base.exists():
        return
    for path in base.rglob("*"):
        if path.is_file() and path.suffix.lower() in extensions:
            try:
                text = path.read_text(encoding="utf-8")
            except UnicodeDecodeError:
                continue
            for line_no, line in enumerate(text.splitlines(), 1):
                if "\\n" in line:
                    violations.append(f"{path.relative_to(ROOT)}:{line_no}")


for base in SCAN_ROOTS:
    scan_tree(base, EXTENSIONS)

# .github contains executable workflow YAML where literal "\n" can be
# intentional inside scripts. Only Markdown under .github is control-plane prose.
scan_tree(GITHUB_MARKDOWN_ROOT, {".md"})

# A numbered read step naming the historical router is stale authority.
for base in SCAN_ROOTS:
    if not base.exists():
        continue
    for path in base.rglob("*.md"):
        try:
            text = path.read_text(encoding="utf-8")
        except UnicodeDecodeError:
            continue
        for line_no, line in enumerate(text.splitlines(), 1):
            if ".ai/engineering/00_ENGINE_ROUTER.md" in line and re.match(r"^\s*\d+\.", line):
                read_order_violations.append(f"{path.relative_to(ROOT)}:{line_no}")

for path in ROOT_FILES:
    if path.is_file():
        try:
            text = path.read_text(encoding="utf-8")
        except UnicodeDecodeError:
            continue
        for line_no, line in enumerate(text.splitlines(), 1):
            if ".ai/engineering/00_ENGINE_ROUTER.md" in line and re.match(r"^\s*\d+\.", line):
                read_order_violations.append(f"{path.relative_to(ROOT)}:{line_no}")

if read_order_violations:
    print("FAIL: historical router appears as a numbered read-order step:")
    print("\n".join(read_order_violations))
    sys.exit(1)

for path in ROOT_FILES:
    if path.is_file():
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

print("PASS: no literal \\n found in control-plane roots, .github Markdown, or designated root entry files.")
