#!/usr/bin/env python3
"""Build maca-njuvs-sv_SE.po, .mo and block editor JSON translations."""
from __future__ import annotations

import hashlib
import json
import os
import re
import subprocess
import sys
from typing import Dict, List

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
LANG_DIR = os.path.join(ROOT, 'languages')
DOMAIN = 'maca-njuvs'
LOCALE = 'sv_SE'

PATTERNS = [
    re.compile(r"""__\(\s*'((?:\\'|[^'])*)'\s*,\s*'maca-njuvs'\s*\)"""),
    re.compile(r'''__\(\s*"((?:\\"|[^"])*)"\s*,\s*"maca-njuvs"\s*\)'''),
    re.compile(r"""esc_html__\(\s*'((?:\\'|[^'])*)'\s*,\s*'maca-njuvs'\s*\)"""),
    re.compile(r"""esc_attr__\(\s*'((?:\\'|[^'])*)'\s*,\s*'maca-njuvs'\s*\)"""),
    re.compile(r"""_e\(\s*'((?:\\'|[^'])*)'\s*,\s*'maca-njuvs'\s*\)"""),
    re.compile(r"""esc_html_e\(\s*'((?:\\'|[^'])*)'\s*,\s*'maca-njuvs'\s*\)"""),
    re.compile(r"""esc_attr_e\(\s*'((?:\\'|[^'])*)'\s*,\s*'maca-njuvs'\s*\)"""),
]

MAP_PATH = os.path.join(os.path.dirname(__file__), 'sv-translations-map.json')


def unescape(value: str) -> str:
    return value.replace("\\'", "'").replace('\\"', '"').strip()


def extract_strings() -> List[str]:
    found: set[str] = set()
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
            for pat in PATTERNS:
                for match in pat.finditer(text):
                    found.add(unescape(match.group(1)))
    return sorted(found)


def po_escape(value: str) -> str:
    return value.replace('\\', '\\\\').replace('"', '\\"')


def build_po(strings: List[str], translations: Dict[str, str]) -> str:
    header = f'''# Swedish translations for maca Njuvs.
# Copyright (C) 2026 Maca Development
msgid ""
msgstr ""
"Project-Id-Version: maca Njuvs\\n"
"Report-Msgid-Bugs-To: https://maca.se/\\n"
"Language: sv_SE\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
"Plural-Forms: nplurals=2; plural=(n != 1);\\n"
"X-Domain: maca-njuvs\\n"

'''
    body = []
    missing = []
    for msgid in strings:
        msgstr = translations.get(msgid, '')
        if msgstr == '':
            missing.append(msgid)
            msgstr = msgid
        body.append(f'msgid "{po_escape(msgid)}"\nmsgstr "{po_escape(msgstr)}"\n')
    if missing:
        print(f'Warning: {len(missing)} untranslated strings (using English):', file=sys.stderr)
        for item in missing[:10]:
            print(f'  - {item}', file=sys.stderr)
    return header + '\n'.join(body)


def build_jed(translations: Dict[str, str], script_rel: str) -> dict:
  entries = {
    '': {
      'domain': DOMAIN,
      'lang': LOCALE,
      'plural-forms': 'nplurals=2; plural=(n != 1);',
    }
  }
  for msgid, msgstr in sorted(translations.items()):
    if msgid:
      entries[msgid] = [msgstr]
  return {
    'translation-revision-date': '2026-06-19 12:00+0000',
    'generator': 'maca-njuvs build-sv-translations.py',
    'domain': DOMAIN,
    'locale_data': {DOMAIN: entries},
  }


def main() -> int:
    strings = extract_strings()
    with open(MAP_PATH, encoding='utf-8') as handle:
        translations: Dict[str, str] = json.load(handle)

    po_path = os.path.join(LANG_DIR, f'{DOMAIN}-{LOCALE}.po')
    mo_path = os.path.join(LANG_DIR, f'{DOMAIN}-{LOCALE}.mo')
    po_content = build_po(strings, translations)
    os.makedirs(LANG_DIR, exist_ok=True)
    with open(po_path, 'w', encoding='utf-8', newline='\n') as handle:
        handle.write(po_content)

    msgfmt = 'msgfmt'
    for candidate in (
        'msgfmt',
        r'C:\Users\maca5\AppData\Local\Programs\gettext-iconv\bin\msgfmt.exe',
    ):
        if os.path.isfile(candidate) or candidate == 'msgfmt':
            msgfmt = candidate
            break

    subprocess.run([msgfmt, '-o', mo_path, po_path], check=True)

    for script in ('blocks/info-news/editor.js', 'blocks/info-events/editor.js'):
        rel_from_plugins = f'maca-njuvs/{script}'
        digest = hashlib.md5(rel_from_plugins.encode('utf-8')).hexdigest()
        json_name = f'{DOMAIN}-{LOCALE}-{digest}.json'
        json_path = os.path.join(LANG_DIR, json_name)
        jed = build_jed(translations, script)
        with open(json_path, 'w', encoding='utf-8', newline='\n') as handle:
            json.dump(jed, handle, ensure_ascii=False, indent=2)
            handle.write('\n')
        print(json_path)

    print(po_path)
    print(mo_path)
    print(f'Strings: {len(strings)}')
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
