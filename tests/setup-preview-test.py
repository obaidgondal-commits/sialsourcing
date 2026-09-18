#!/usr/bin/env python3
"""Verify the actual public CMS snapshot restores without private records."""
import hashlib
import importlib.util
import json
from pathlib import Path
import sqlite3
import tempfile

ROOT = Path(__file__).resolve().parents[1]
spec = importlib.util.spec_from_file_location('setup_preview', ROOT / 'scripts/setup-preview.py')
setup = importlib.util.module_from_spec(spec)
spec.loader.exec_module(setup)
source = json.loads((ROOT / 'data/site-content.json').read_text())
checks = 0

def check(condition, message):
    global checks
    if not condition:
        raise AssertionError(message)
    checks += 1

with tempfile.TemporaryDirectory(prefix='sial-cms-restore-') as tmp:
    output = Path(tmp) / 'private/cms.sqlite'
    counts = setup.restore(ROOT / 'data/site-content.json', output)
    check(counts == {name: len(table['rows']) for name, table in source['tables'].items()}, 'Every real public CMS row must be restored.')
    check(output.stat().st_mode & 0o777 == 0o600, 'The CMS file must remain private.')
    with sqlite3.connect(output) as cn:
        cn.row_factory = sqlite3.Row
        names = {r[0] for r in cn.execute("SELECT name FROM sqlite_master WHERE type='table'")}
        check(names == setup.PUBLIC_TABLES | set(setup.EMPTY_FORMS), 'Only public content and empty staging inbox schemas may exist.')
        for name, table in source['tables'].items():
            actual = [dict(r) for r in cn.execute(f'SELECT * FROM "{name}" ORDER BY id')]
            expected = []
            integer_names = {c['name'] for c in table['columns'] if c['type'].split('(')[0] in {'int', 'tinyint'}}
            for row in table['rows']:
                expected.append({k: int(v) if k in integer_names and v is not None else v for k, v in row.items()})
            check(actual == sorted(expected, key=lambda r: r['id']), f'{name} content must be preserved exactly without a URL replacement option.')
        for name in setup.EMPTY_FORMS:
            check(cn.execute(f'SELECT COUNT(*) FROM {name}').fetchone()[0] == 0, 'Production inbox/application records must not be copied.')
        cn.execute("INSERT INTO contact_submissions(name,email,company,phone,subject,message) VALUES ('Test','test@example.invalid','','','Test','Preview only')")
        row = cn.execute('SELECT id,is_read,submitted_at FROM contact_submissions').fetchone()
        check(row['id'] == 1 and row['is_read'] == 0 and bool(row['submitted_at']), 'The original contact INSERT must work with its id, status and timestamp defaults.')
        cn.execute("INSERT INTO manufacturer_applications(company_name,contact_name,email) VALUES ('Test','Test','test@example.invalid')")
        row = cn.execute('SELECT id,status,submitted_at FROM manufacturer_applications').fetchone()
        check(row['id'] == 1 and row['status'] == 'new' and bool(row['submitted_at']), 'Manufacturer applications must retain their original defaults.')
        cn.commit()
    before = hashlib.sha256(output.read_bytes()).digest()
    try:
        setup.restore(ROOT / 'data/site-content.json', output)
        raise AssertionError('Existing output was accepted.')
    except FileExistsError:
        check(hashlib.sha256(output.read_bytes()).digest() == before, 'Refusing overwrite must preserve existing preview entries.')
    dangling = Path(tmp) / 'existing-link.sqlite'
    dangling.symlink_to(Path(tmp) / 'not-created.sqlite')
    try:
        setup.restore(ROOT / 'data/site-content.json', dangling)
        raise AssertionError('Existing symbolic link was accepted.')
    except FileExistsError:
        check(not (Path(tmp) / 'not-created.sqlite').exists(), 'An existing symlink must not redirect preview creation.')
    try:
        setup.restore(ROOT / 'data/site-content.json', ROOT / 'unsafe-preview.sqlite')
        raise AssertionError('A database under the document root was accepted.')
    except ValueError:
        check(not (ROOT / 'unsafe-preview.sqlite').exists(), 'Public database path must be rejected before writing.')
    url = 'http://127.0.0.1:8878'
    check(setup.rewrite_first_party('<a href="https://sialsourcing.com/contact">Contact</a>', url) == '<a href="http://127.0.0.1:8878/contact">Contact</a>', 'First-party content links should point at the preview.')
    for unchanged in ['https://sialsourcing.com.attacker.invalid/path', 'https://sialsourcing.com@attacker.invalid/path', 'https://example.com/path', 'http://sialsourcing.com/path']:
        check(setup.rewrite_first_party(unchanged, url) == unchanged, 'URL rewriting must not replace other origins or protocols.')
    unsafe = json.loads(json.dumps(source))
    unsafe['tables']['admin_users'] = {'columns': [], 'rows': []}
    unsafe_path = Path(tmp) / 'unsafe.json'
    unsafe_path.write_text(json.dumps(unsafe))
    try:
        setup.restore(unsafe_path, Path(tmp) / 'unsafe.sqlite')
        raise AssertionError('Private table accepted.')
    except ValueError:
        check(not (Path(tmp) / 'unsafe.sqlite').exists(), 'An unexpected private table must reject the entire restore.')
print(f'{checks} preview setup checks passed using all {sum(counts.values())} public content rows.')
