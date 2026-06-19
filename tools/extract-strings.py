#!/usr/bin/env python3
"""Extract maca-njuvs translatable strings from PHP and JS sources."""
import os
import re
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

PATTERNS = [
    re.compile(r"""__\(\s*'((?:\\'|[^'])*)'\s*,\s*'maca-njuvs'\s*\)"""),
    re.compile(r'''__\(\s*"((?:\\"|[^"])*)"\s*,\s*"maca-njuvs"\s*\)'''),
    re.compile(r"""esc_html__\(\s*'((?:\\'|[^'])*)'\s*,\s*'maca-njuvs'\s*\)"""),
    re.compile(r"""esc_attr__\(\s*'((?:\\'|[^'])*)'\s*,\s*'maca-njuvs'\s*\)"""),
    re.compile(r"""_e\(\s*'((?:\\'|[^'])*)'\s*,\s*'maca-njuvs'\s*\)"""),
    re.compile(r"""esc_html_e\(\s*'((?:\\'|[^'])*)'\s*,\s*'maca-njuvs'\s*\)"""),
    re.compile(r"""esc_attr_e\(\s*'((?:\\'|[^'])*)'\s*,\s*'maca-njuvs'\s*\)"""),
    re.compile(r"""_n\(\s*'((?:\\'|[^'])*)'\s*,\s*'((?:\\'|[^'])*)'\s*,"""),
]

def unescape(s: str) -> str:
    return s.replace("\\'", "'").replace('\\"', '"')

def main() -> int:
    strings: set[str] = set()
    for dirpath, _, files in os.walk(ROOT):
        if any(part in dirpath for part in ('node_modules', '.git', 'tools')):
            continue
        for name in files:
            if not name.endswith(('.php', '.js')):
                continue
            path = os.path.join(dirpath, name)
            try:
                text = open(path, encoding='utf-8').read()
            except OSError:
                continue
            for pat in PATTERNS[:7]:
                for match in pat.finditer(text):
                    strings.add(unescape(match.group(1)))

    out_path = os.path.join(os.path.dirname(__file__), 'strings.txt')
    with open(out_path, 'w', encoding='utf-8') as handle:
        for s in sorted(strings):
            handle.write(s + '\n')
        handle.write(f'# total: {len(strings)}\n')
    print(out_path)
    return 0

if __name__ == '__main__':
    raise SystemExit(main())
