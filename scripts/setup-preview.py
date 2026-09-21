#!/usr/bin/env python3
"""Restore the sanitized public CMS snapshot into a NEW private SQLite preview.

The original CMS table names and displayed content are retained. Production
credentials, administrator accounts, inbox entries and manufacturer applications
are never imported. Structured instrument facts remain a separate JSON snapshot.
"""
import argparse
import json
import os
from pathlib import Path
import re
import sqlite3
import tempfile
from urllib.parse import urlsplit

ROOT = Path(__file__).resolve().parents[1]
PUBLIC_TABLES = frozenset({
    'blog_posts', 'products', 'faqs', 'lab_tests', 'team_members', 'solutions',
    'product_faqs', 'product_gallery', 'settings',
})
IDENTIFIER = re.compile(r'^[a-z][a-z0-9_]*$')
FIRST_PARTY = re.compile(r'https://sialsourcing\.com(?=[/?#\s\"\'<>]|$)', re.IGNORECASE)

# Exact field names/defaults from database/schema.sql. These tables are always
# empty on restore and receive only subsequent isolated preview submissions.
EMPTY_FORMS = {
    'contact_submissions': '''CREATE TABLE contact_submissions (
        id INTEGER PRIMARY KEY,
        name TEXT DEFAULT NULL, email TEXT DEFAULT NULL, company TEXT DEFAULT NULL,
        phone TEXT DEFAULT NULL, subject TEXT DEFAULT NULL, message TEXT DEFAULT NULL,
        is_read INTEGER DEFAULT 0, submitted_at TEXT DEFAULT CURRENT_TIMESTAMP
    )''',
    'manufacturer_applications': '''CREATE TABLE manufacturer_applications (
        id INTEGER PRIMARY KEY, company_name TEXT NOT NULL,
        contact_name TEXT NOT NULL, email TEXT NOT NULL, phone TEXT DEFAULT '',
        city TEXT DEFAULT '', product_categories TEXT DEFAULT '',
        certifications TEXT DEFAULT '', website TEXT DEFAULT '', message TEXT DEFAULT NULL,
        status TEXT DEFAULT 'new', submitted_at TEXT DEFAULT CURRENT_TIMESTAMP
    )''',
}


def identifier(name):
    if not isinstance(name, str) or not IDENTIFIER.fullmatch(name):
        raise ValueError('Snapshot contains an invalid SQL identifier.')
    return '"' + name + '"'


def sqlite_type(source_type):
    if not isinstance(source_type, str):
        raise ValueError('Snapshot contains an invalid column type.')
    value = source_type.lower().strip()
    if re.fullmatch(r'(tinyint|smallint|mediumint|int|integer|bigint)(\([0-9]+\))?( unsigned)?', value):
        return 'INTEGER'
    if re.fullmatch(r'(var)?char\([0-9]+\)|(tiny|medium|long)?text|timestamp|datetime|date|time', value):
        return 'TEXT'
    raise ValueError(f'Unsupported CMS column type: {source_type}')


def default_sql(value, column_type):
    if value is None:
        return 'DEFAULT NULL'
    if isinstance(value, str) and re.fullmatch(r'current_timestamp(?:\(\))?', value, re.IGNORECASE):
        return 'DEFAULT CURRENT_TIMESTAMP'
    if column_type == 'INTEGER':
        if isinstance(value, bool) or not re.fullmatch(r'-?[0-9]+', str(value)):
            raise ValueError('Invalid integer column default.')
        return 'DEFAULT ' + str(int(value))
    if not isinstance(value, str):
        raise ValueError('Invalid text column default.')
    return "DEFAULT '" + value.replace("'", "''") + "'"


def validate_site_url(site_url):
    if site_url is None:
        return None
    parsed = urlsplit(site_url)
    if (parsed.scheme not in ('http', 'https') or not parsed.hostname or
        parsed.username is not None or parsed.password is not None or
        parsed.query or parsed.fragment or
        re.search(r'[\s\x00-\x1f<>\"\']', site_url)):
        raise ValueError('--site-url must be an HTTP(S) origin or base URL without credentials, query or fragment.')
    # Accessing .port validates malformed and out-of-range values.
    parsed.port
    return site_url.rstrip('/')


def rewrite_first_party(value, site_url):
    if site_url is not None and isinstance(value, str):
        return FIRST_PARTY.sub(lambda _: site_url, value)
    return value


def load_snapshot(path, site_url=None):
    if path.stat().st_size > 30_000_000:
        raise ValueError('The public content snapshot is too large.')
    data = json.loads(path.read_text(encoding='utf-8'))
    if not isinstance(data, dict) or data.get('schema_version') != 1 or not isinstance(data.get('tables'), dict):
        raise ValueError('Unsupported public content snapshot.')
    if set(data['tables']) != PUBLIC_TABLES:
        raise ValueError('Snapshot must contain exactly the nine public CMS tables; private tables are not accepted.')
    prepared = []
    counts = {}
    for table_name, table in data['tables'].items():
        identifier(table_name)
        if not isinstance(table, dict) or not isinstance(table.get('columns'), list) or not isinstance(table.get('rows'), list):
            raise ValueError(f'Invalid snapshot table: {table_name}')
        columns, definitions, types = [], [], []
        for column in table['columns']:
            if not isinstance(column, dict) or not isinstance(column.get('nullable'), bool):
                raise ValueError(f'Invalid column metadata in {table_name}')
            name = column.get('name')
            quoted = identifier(name)
            if name in columns:
                raise ValueError(f'Duplicate column in {table_name}')
            kind = sqlite_type(column.get('type'))
            columns.append(name)
            types.append(kind)
            if name == 'id':
                if kind != 'INTEGER':
                    raise ValueError('The CMS id column must be an integer.')
                definitions.append(quoted + ' INTEGER PRIMARY KEY')
            else:
                definition = quoted + ' ' + kind
                if not column['nullable']:
                    definition += ' NOT NULL'
                definition += ' ' + default_sql(column.get('default'), kind)
                definitions.append(definition)
        if 'id' not in columns:
            raise ValueError(f'Missing CMS id column in {table_name}')
        rows = []
        for row in table['rows']:
            if not isinstance(row, dict) or set(row) != set(columns):
                raise ValueError(f'Unexpected row fields in {table_name}')
            values = []
            for name, kind in zip(columns, types):
                value = row[name]
                if value is not None:
                    if kind == 'INTEGER':
                        if isinstance(value, bool) or not re.fullmatch(r'-?[0-9]+', str(value)):
                            raise ValueError(f'Invalid integer value in {table_name}.{name}')
                        value = int(value)
                    elif not isinstance(value, str):
                        raise ValueError(f'Invalid text value in {table_name}.{name}')
                values.append(rewrite_first_party(value, site_url))
            rows.append(values)
        create = f'CREATE TABLE {identifier(table_name)} (' + ', '.join(definitions) + ')'
        insert = f'INSERT INTO {identifier(table_name)} (' + ','.join(identifier(c) for c in columns) + ') VALUES (' + ','.join('?' for _ in columns) + ')'
        prepared.append((create, insert, rows))
        counts[table_name] = len(rows)
    return prepared, counts


def restore(snapshot_path, output, site_url=None):
    site_url = validate_site_url(site_url)
    output = Path(output).expanduser()
    if output.exists() or output.is_symlink():
        raise FileExistsError('Output already exists; choose a new path. Existing preview data is never overwritten.')
    output = output.resolve()
    if output == ROOT or ROOT in output.parents:
        raise ValueError('The SQLite database must be outside the website document root.')
    if output.exists() or output.is_symlink():
        raise FileExistsError('Output already exists; choose a new path. Existing preview data is never overwritten.')
    prepared, counts = load_snapshot(Path(snapshot_path), site_url)
    output.parent.mkdir(parents=True, exist_ok=True, mode=0o700)
    handle, temporary_name = tempfile.mkstemp(prefix='.cms-restore-', suffix='.sqlite', dir=output.parent)
    os.close(handle)
    temporary = Path(temporary_name)
    try:
        cn = sqlite3.connect(temporary)
        try:
            cn.execute('BEGIN')
            for create, insert, rows in prepared:
                cn.execute(create)
                cn.executemany(insert, rows)
            for statement in EMPTY_FORMS.values():
                cn.execute(statement)
            if cn.execute('PRAGMA integrity_check').fetchone()[0] != 'ok':
                raise ValueError('Restored preview database failed its integrity check.')
            cn.commit()
        finally:
            cn.close()
        # Same-directory hard link gives atomic visibility and refuses a race
        # which creates the destination after our earlier existence check.
        os.link(temporary, output)
    finally:
        temporary.unlink(missing_ok=True)
    return counts


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--snapshot', type=Path, default=ROOT / 'data/site-content.json')
    parser.add_argument('--output', type=Path, default=ROOT.parent / 'full-site-private/cms.sqlite')
    parser.add_argument('--site-url', help='Optional first-party URL replacement, e.g. http://127.0.0.1:8878')
    args = parser.parse_args()
    try:
        counts = restore(args.snapshot, args.output, args.site_url)
    except (ValueError, OSError, sqlite3.Error) as error:
        parser.exit(2, f'Preview restore rejected: {error}\n')
    print(f'Restored {sum(counts.values())} public content rows across {len(counts)} CMS tables.')
    print('Created empty isolated contact and manufacturer-application inboxes; no administrator accounts imported.')
    print(f'Private SQLite database: {args.output.resolve()}')


if __name__ == '__main__':
    main()
