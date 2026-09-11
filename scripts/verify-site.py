#!/usr/bin/env python3
"""Verify a fresh, isolated local site. Never reads config.php, uploads, or a live DB.

Requires Python 3.9+, PHP 8.2+ with pdo_sqlite, and permission to bind localhost.
Uses the release allowlist, synthetic records, and disabled native mail. Apache
is checked separately when an executable and its required modules are available.
"""
from __future__ import annotations

import argparse
from contextlib import contextmanager
from html.parser import HTMLParser
import http.cookiejar
import importlib.util
import json
import os
from pathlib import Path
import re
import shutil
import socket
import sqlite3
import subprocess
import sys
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request
import xml.etree.ElementTree as ET

sys.dont_write_bytecode = True

ROOT = Path(__file__).resolve().parent.parent
CANONICAL = 'https://sialsourcing.com'
INTERNAL = {'404.php', 'blog-single.php', 'config.php', 'config.example.php', 'product-template.php', 'sitemap.php'}


def require(condition: bool, message: str) -> None:
    if not condition:
        raise RuntimeError(message)


class Page(HTMLParser):
    def __init__(self, html: str):
        super().__init__(convert_charrefs=True)
        self.h1 = 0
        self.title = ''
        self.in_title = False
        self.metas = {}
        self.canonicals = []
        self.references = []
        self.schemas = []
        self.json_buffer = None
        self.inputs = {}
        self.selected = {}
        self.select = None
        self.option = None
        self.option_text = ''
        self.cards = 0
        self.feed(html)
        self.close()

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == 'h1': self.h1 += 1
        if tag == 'title': self.in_title = True
        if tag == 'meta': self.metas[attrs.get('name') or attrs.get('property')] = attrs.get('content', '')
        if tag == 'link' and 'canonical' in attrs.get('rel', '').split(): self.canonicals.append(attrs.get('href', ''))
        for key in ['href', 'src']:
            if attrs.get(key): self.references.append(attrs[key])
        if attrs.get('srcset'):
            self.references.extend(part.strip().split()[0] for part in attrs['srcset'].split(',') if part.strip())
        if tag == 'script' and attrs.get('type') == 'application/ld+json': self.json_buffer = ''
        if tag == 'input' and attrs.get('name'): self.inputs[attrs['name']] = attrs.get('value', '')
        if tag == 'select': self.select = attrs.get('name')
        if tag == 'option' and self.select:
            self.option = attrs
            self.option_text = ''
        if 'blog-card' in attrs.get('class', '').split(): self.cards += 1

    def handle_endtag(self, tag):
        if tag == 'title': self.in_title = False
        if tag == 'script' and self.json_buffer is not None:
            self.schemas.append(json.loads(self.json_buffer))
            self.json_buffer = None
        if tag == 'option' and self.option is not None:
            if 'selected' in self.option:
                self.selected[self.select] = self.option.get('value', self.option_text.strip())
            self.option = None
        if tag == 'select': self.select = None

    def handle_data(self, data):
        if self.in_title: self.title += data
        if self.json_buffer is not None: self.json_buffer += data
        if self.option is not None: self.option_text += data


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None


class LocalHTTP:
    def __init__(self, port: int):
        self.origin = f'http://127.0.0.1:{port}'
        self.client = urllib.request.build_opener(
            urllib.request.ProxyHandler({}), NoRedirect(),
            urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()),
        )

    def request(self, path: str, data: dict | None = None):
        require(path.startswith('/') and not path.startswith('//'), 'Only local absolute paths may be requested.')
        url = self.origin + path
        payload = urllib.parse.urlencode(data).encode() if data is not None else None
        request = urllib.request.Request(url, data=payload)
        try:
            response = self.client.open(request, timeout=8)
        except urllib.error.HTTPError as error:
            response = error
        with response:
            return response.code, response.headers, response.read()


def free_port() -> int:
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as connection:
        connection.bind(('127.0.0.1', 0))
        return connection.getsockname()[1]


@contextmanager
def server(command, directory: Path, port: int, log: Path):
    with log.open('wb') as output:
        process = subprocess.Popen(command, cwd=directory, stdout=output, stderr=subprocess.STDOUT, start_new_session=True)
        try:
            http = LocalHTTP(port)
            for _ in range(100):
                if process.poll() is not None:
                    details = log.read_text(errors='replace')
                    if (directory / 'error.log').is_file(): details += (directory / 'error.log').read_text(errors='replace')
                    raise RuntimeError('Local server exited: ' + details[-2000:])
                try:
                    http.request('/robots.txt')
                    break
                except (urllib.error.URLError, OSError):
                    time.sleep(0.05)
            else:
                raise RuntimeError('Local server did not start within five seconds.')
            yield http
        finally:
            process.terminate()
            try: process.wait(timeout=5)
            except subprocess.TimeoutExpired:
                process.kill()
                process.wait(timeout=5)


def load_release():
    spec = importlib.util.spec_from_file_location('sial_release', ROOT / 'scripts/package-release.py')
    require(spec is not None and spec.loader is not None, 'Cannot load release allowlist.')
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module


def make_fixture(destination: Path, php: str, port: int):
    release = load_release()
    files = release.collect()
    for name, content in files.items():
        path = destination / name
        path.parent.mkdir(parents=True, exist_ok=True)
        path.write_bytes(content)
    for name in ['config.example.php', 'database/schema.sql', 'scripts/setup-preview.php', 'scripts/preview-router.php']:
        source = release.checked_file(name)
        target = destination / name
        target.parent.mkdir(parents=True, exist_ok=True)
        target.write_bytes(source.read_bytes())
    result = subprocess.run([php, '-d', 'disable_functions=mail', 'scripts/setup-preview.php', '--local'],
                            cwd=destination, capture_output=True, text=True)
    require(result.returncode == 0, 'Fresh preview setup failed: ' + result.stdout + result.stderr)
    require((destination / 'config.php').is_file(), 'Preview setup did not create local config.')
    # The maintained preview is fixed to :8765. Change only copied fixture files,
    # leaving the developer's existing preview and source code untouched.
    for name in ['config.php', 'scripts/preview-router.php']:
        path = destination / name
        text = path.read_text()
        require('8765' in text, 'Preview port contract changed; update the verifier.')
        path.write_text(text.replace('8765', str(port)))
    require(not (destination / '.git').exists() and not (destination / 'uploads').exists(), 'Private source data entered the fixture.')
    return files


def html_metadata(http: LocalHTTP, route: str, canonical: str | None = None):
    status, headers, raw = http.request(route)
    require(status == 200, f'{route}: expected 200, got {status}.')
    require('text/html' in headers.get('Content-Type', ''), f'{route}: missing HTML content type.')
    html = raw.decode('utf-8')
    page = Page(html)
    require(page.h1 == 1, f'{route}: expected one H1, got {page.h1}.')
    require(bool(page.title.strip()), f'{route}: missing title.')
    require(bool(page.metas.get('description', '').strip()), f'{route}: missing description.')
    require('noindex' in page.metas.get('robots', '') and 'noindex' in headers.get('X-Robots-Tag', ''), f'{route}: local preview must be noindex.')
    expected = canonical or CANONICAL + route
    require(page.canonicals == [expected], f'{route}: incorrect canonical {page.canonicals!r}.')
    require(page.schemas, f'{route}: no structured data found.')
    return page, html


def nodes(value):
    if isinstance(value, dict):
        yield value
        for item in value.values(): yield from nodes(item)
    elif isinstance(value, list):
        for item in value: yield from nodes(item)


def public_routes(files):
    return sorted('/' if name == 'index.php' else '/' + name[:-4]
                  for name in files if '/' not in name and name.endswith('.php') and name not in INTERNAL)


def verify_routes(http, fixture, files):
    routes = public_routes(files)
    references = set()
    category_routes = {'/' + p[:-4] for p in files if '/' not in p and p.endswith('.php')
                       and b'product-template.php' in files[p] and p != 'product-template.php'}
    for route in routes:
        page, html = html_metadata(http, route)
        if route in category_routes:
            all_nodes = list(nodes(page.schemas))
            require(any(node.get('@type') == 'CollectionPage' for node in all_nodes), f'{route}: category needs CollectionPage schema.')
            require(not any(node.get('@type') == 'Product' or 'manufacturer' in node for node in all_nodes), f'{route}: category claims an individual product or manufacturer.')
        for reference in page.references:
            absolute = urllib.parse.urljoin(CANONICAL + route, reference)
            parsed = urllib.parse.urlparse(absolute)
            if parsed.scheme not in ['http', 'https'] or parsed.netloc not in ['sialsourcing.com', urllib.parse.urlparse(http.origin).netloc]:
                continue  # External resources are never fetched by this verifier.
            if not parsed.path or (reference.startswith('#')): continue
            references.add(parsed.path + ('?' + parsed.query if parsed.query else ''))
    for reference in sorted(references):
        status, _, _ = http.request(reference)
        require(status in [200, 301, 302, 303], f'Broken local src/href: {reference} returned {status}.')
        if status in [301, 302, 303]:
            _, headers, _ = http.request(reference)
            target = urllib.parse.urlparse(headers.get('Location', ''))
            require(target.netloc in ['', 'sialsourcing.com', urllib.parse.urlparse(http.origin).netloc], f'Unexpected redirect target for {reference}.')
    status, _, raw = http.request('/sitemap.xml')
    require(status == 200, 'Sitemap did not return 200.')
    xml = ET.fromstring(raw)
    locations = [item.text for item in xml.findall('{*}url/{*}loc')]
    require(len(locations) == len(set(locations)), 'Sitemap contains duplicate URLs.')
    require(CANONICAL + '/' in locations, 'Sitemap missing homepage.')
    for location in locations:
        require(location and location.startswith(CANONICAL + '/'), 'Sitemap has an incorrect origin.')
        path = location[len(CANONICAL):]
        require(path in routes or path.startswith('/blog/'), f'Sitemap advertises unknown route {path}.')
        require(http.request(path)[0] == 200, f'Sitemap URL fails: {path}.')
    for path in ['/missing-page-fixture', '/config.php', '/admin/', '/.preview/preview.sqlite']:
        require(http.request(path)[0] == 404, f'Local protected/missing route exposed: {path}.')
    print(f'PASS routes: {len(routes)} public pages, metadata/JSON-LD, {len(references)} local references, {len(locations)} sitemap entries.')


def verify_contact(http, database):
    def count(): return database.execute('SELECT COUNT(*) FROM contact_submissions').fetchone()[0]
    page, _ = html_metadata(http, '/contact?product=home-textiles&region=Wazirabad', CANONICAL + '/contact')
    require(page.selected.get('subject') == 'Textiles & Home Linens', 'RFQ lost the home-textiles product selection.')
    require(page.selected.get('region') == 'Wazirabad', 'RFQ lost the Wazirabad region selection.')
    token = page.inputs.get('csrf_token', '')
    require(bool(token), 'RFQ missing CSRF token.')
    values = dict(name='Synthetic buyer', email='test@example.invalid', company='Synthetic fixture', phone='',
                  subject='Textiles & Home Linens', region='Wazirabad', country='Germany, Hamburg',
                  quantity='250 units', deadline='Flexible', message='Synthetic specification for verification.',
                  website='', csrf_token=token)
    before = count()
    for label, changes in [
        ('invalid CSRF', {'csrf_token': 'incorrect'}),
        ('malformed array', {'name': None, 'name[]': 'invalid'}),
        ('oversized field', {'company': 'x' * 201}),
        ('CRLF subject', {'subject': 'Textiles\r\nInjected: header'}),
        ('CRLF email', {'email': 'test@example.invalid\r\nInjected: header'}),
    ]:
        data = dict(values)
        for key, value in changes.items():
            if value is None: data.pop(key, None)
            else: data[key] = value
        status, _, raw = http.request('/contact', data)
        require(status in [200, 400, 422] and count() == before, f'RFQ {label} caused a failure or a database write.')
        require('role="alert"' in raw.decode(), f'RFQ {label} did not show a validation error.')
    status, headers, raw = http.request('/contact', values)
    require(status == 303 and headers.get('Location') == '/contact' and not raw.strip(), 'Valid RFQ must redirect before output.')
    require(count() == before + 1, 'Valid RFQ did not persist exactly once.')
    row = database.execute('SELECT subject,message FROM contact_submissions ORDER BY id DESC LIMIT 1').fetchone()
    require(row[0] == 'Textiles & Home Linens' and all(part in row[1] for part in ['Destination: Germany, Hamburg', 'Region: Wazirabad', 'Quantity: 250 units']), 'RFQ dropped destination, region, quantity or category.')
    status, _, raw = http.request('/contact')
    require(status == 200 and 'Brief received' in raw.decode(), 'RFQ success flash is missing.')
    http.request('/contact')
    require(count() == before + 1, 'Refresh after the success redirect duplicated the RFQ.')
    print('PASS RFQ: product/region context, CSRF/array/length/CRLF validation, saved brief, redirect and refresh.')


def seed_blog(database):
    for number in range(25):
        database.execute('INSERT INTO blog_posts(title,slug,excerpt,content,author,category,is_published,published_at,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?)',
                         (f'Synthetic article {number}', f'fixture-article-{number}', 'Synthetic excerpt', 'Synthetic content', 'SialSourcing Team', 'QA test', 1, '2020-01-01 00:00:00', '2020-01-01 00:00:00', '2020-02-01 00:00:00'))
    for slug, title, published, date, updated, image, category in [
        ('fixture-draft', 'Synthetic hidden draft', 0, '2020-01-01 00:00:00', '2020-01-01 00:00:00', '', 'QA test'),
        ('fixture-future', 'Synthetic future article', 1, '2999-01-01 00:00:00', '2020-01-01 00:00:00', '', 'Future only'),
        ('fixture-undated', 'Synthetic undated article', 1, None, '0000-00-00 00:00:00', '', 'QA test'),
        ('fixture-escape', 'Smoke </script><script id="injected">danger()</script>', 1, '2020-01-01 00:00:00', '0000-00-00 00:00:00', 'javascript:alert(1)', 'QA test'),
    ]:
        database.execute('INSERT INTO blog_posts(title,slug,excerpt,content,author,category,is_published,published_at,created_at,updated_at,featured_image) VALUES(?,?,?,?,?,?,?,?,?,?,?)',
                         (title, slug, 'Synthetic excerpt', 'Synthetic body', 'Test byline', category, published, date, '2020-01-01 00:00:00', updated, image))
    database.commit()


def verify_blog(http, database):
    seed_blog(database)
    for path in ['/blog/fixture-draft', '/blog/fixture-future', '/blog/missing-fixture', '/blog?page=0', '/blog?page=4', '/blog?cat=Future%20only', '/blog?cat%5B%5D=x']:
        require(http.request(path)[0] == 404, f'Invalid/draft/future blog URL was exposed: {path}.')
    for number, count in [(1, 12), (2, 12), (3, 3)]:
        path = '/blog' + (f'?page={number}' if number > 1 else '')
        page, html = html_metadata(http, path)
        require(page.cards == count, f'Blog page {number} has incorrect result count.')
        require('Synthetic hidden draft' not in html and 'Synthetic future article' not in html, 'Blog lists unpublished content.')
        if number < 3: require(f'/blog?page={number + 1}' in page.references, 'Blog pagination is not crawlable.')
    page, html = html_metadata(http, '/blog?cat=QA%20test&page=2')
    require('/blog?cat=QA%20test&page=3' in page.references, 'Blog pagination lost its category.')
    page, html = html_metadata(http, '/blog/fixture-escape')
    article = next((value for value in page.schemas if value.get('@type') == 'BlogPosting'), {})
    require(article.get('headline') == 'Smoke </script><script id="injected">danger()</script>' and '<script id="injected">' not in html, 'Article JSON-LD allows a script breakout or damages text.')
    require('image' not in article and 'javascript:' not in html, 'Unsafe article image URL was rendered.')
    require('dateModified' not in article and 'datePublished' in article, 'Article invents or drops known dates.')
    require('author' not in article, 'Arbitrary byline was assigned an unverified entity identity.')
    page, _ = html_metadata(http, '/blog/fixture-undated')
    article = next(value for value in page.schemas if value.get('@type') == 'BlogPosting')
    require('datePublished' not in article and 'dateModified' not in article, 'Unknown article dates must remain absent.')
    status, _, raw = http.request('/sitemap.xml')
    locations = [item.text for item in ET.fromstring(raw).findall('{*}url/{*}loc')]
    require(status == 200 and CANONICAL + '/blog/fixture-article-0' in locations, 'Sitemap omitted a published article.')
    require(not any('fixture-future' in location or 'fixture-draft' in location for location in locations), 'Sitemap advertises a draft/future article.')
    print('PASS blog: draft/future exclusion, pagination, canonical URLs, safe JSON-LD/images/dates and sitemap visibility.')


def apache_smoke(directory: Path, htaccess: bytes, mode: str):
    if mode == 'skip':
        print('LIMIT Apache .htaccess checks skipped by --apache=skip; PHP preview does not execute .htaccess.')
        return
    binary = shutil.which('httpd') or shutil.which('apache2')
    module_dir = next((path for path in [Path('/usr/libexec/apache2'), Path('/usr/lib/apache2/modules'), Path('/usr/lib64/httpd/modules')] if path.is_dir()), None)
    if not binary or not module_dir:
        require(mode != 'required', 'Apache executable/modules unavailable.')
        print('LIMIT Apache unavailable; .htaccess needs Hostinger staging verification. Nothing was installed.')
        return
    static = subprocess.run([binary, '-l'], capture_output=True, text=True).stdout
    modules = [('authz_core_module', 'mod_authz_core.so'), ('rewrite_module', 'mod_rewrite.so'), ('headers_module', 'mod_headers.so'), ('expires_module', 'mod_expires.so')]
    if 'mod_unixd.c' not in static: modules.insert(0, ('unixd_module', 'mod_unixd.so'))
    if not re.search(r'\bmpm_\w+\.c|\bprefork\.c|\bworker\.c|\bevent\.c', static): modules.insert(0, ('mpm_event_module', 'mod_mpm_event.so'))
    if not all((module_dir / filename).is_file() for _, filename in modules):
        require(mode != 'required', 'Required Apache modules unavailable.')
        print('LIMIT Apache modules unavailable; .htaccess needs Hostinger staging verification. Nothing was installed.')
        return
    directory.mkdir()
    public = directory / 'public'
    public.mkdir()
    (public / '.htaccess').write_bytes(htaccess)
    (public / 'index.php').write_text('Synthetic static Apache fixture. No PHP code or secrets.\n')
    (public / 'config.php').write_text('Synthetic protected file.\n')
    (public / 'docs').mkdir()
    (public / 'docs/probe.md').write_text('Synthetic protected document.\n')
    (public / 'uploads').mkdir()
    (public / 'uploads/probe.php').write_text('Synthetic forbidden upload.\n')
    port = free_port()
    def quoted(path): return '"' + str(path).replace('\\', '\\\\').replace('"', '\\"') + '"'
    config = directory / 'httpd.conf'
    lines = [f'ServerRoot {quoted(directory)}', f'PidFile {quoted(directory / "httpd.pid")}', f'Listen 127.0.0.1:{port}', 'ServerName localhost', f'ErrorLog {quoted(directory / "error.log")}', 'LogLevel warn']
    lines += [f'LoadModule {name} {quoted(module_dir / filename)}' for name, filename in modules]
    lines += [f'DocumentRoot {quoted(public)}', f'<Directory {quoted(public)}>', 'AllowOverride All', 'Options FollowSymLinks', 'Require all granted', '</Directory>']
    config.write_text('\n'.join(lines) + '\n')
    result = subprocess.run([binary, '-t', '-f', str(config)], capture_output=True, text=True)
    require(result.returncode == 0, 'Isolated Apache config failed: ' + result.stderr)
    with server([binary, '-X', '-f', str(config)], directory, port, directory / 'server.log') as http:
        status, headers, _ = http.request('/about')
        require(status == 301 and headers.get('Location') == CANONICAL + '/about', 'Apache did not enforce canonical HTTPS redirect.')
        require(headers.get('X-Content-Type-Options') == 'nosniff', 'Apache security headers did not load.')
        for path in ['/config.php', '/docs/probe.md', '/uploads/probe.php', '/uploads/probe.php/extra', '/uploads/probe.php.jpg']:
            require(http.request(path)[0] == 403, f'Apache did not protect {path}.')
    print('PASS Apache: real .htaccess parsed, HTTPS redirect/security headers/private-path denial verified.')
    print('LIMIT Apache smoke uses static synthetic files without TLS/PHP; production HTTPS clean-URL routing, PHP/MySQL and email need Hostinger staging checks.')


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--php', default='php', help='PHP CLI executable (default: php).')
    parser.add_argument('--apache', choices=['auto', 'required', 'skip'], default='auto')
    args = parser.parse_args()
    php = shutil.which(args.php)
    require(bool(php), 'PHP CLI is required.')
    with tempfile.TemporaryDirectory(prefix='sialsourcing-verify-') as temporary:
        temporary = Path(temporary)
        fixture = temporary / 'site'
        fixture.mkdir()
        port = free_port()
        files = make_fixture(fixture, php, port)
        with sqlite3.connect(fixture / '.preview/preview.sqlite') as database:
            require(database.execute('SELECT COUNT(*) FROM admin_users').fetchone()[0] == 0, 'Preview created an admin account.')
            require(database.execute('SELECT COUNT(*) FROM blog_posts').fetchone()[0] == 0, 'Preview unexpectedly contains stored articles.')
            command = [php, '-d', 'disable_functions=mail', '-d', 'output_buffering=0', '-S', f'127.0.0.1:{port}', 'scripts/preview-router.php']
            with server(command, fixture, port, temporary / 'php-server.log') as http:
                verify_routes(http, fixture, files)
                verify_contact(http, database)
                verify_blog(http, database)
        log = (temporary / 'php-server.log').read_text(errors='replace')
        require(not re.search(r'PHP (?:Warning|Fatal error|Parse error)|SialSourcing request failed:', log), 'PHP emitted a runtime error: ' + log[-2000:])
        apache_smoke(temporary / 'apache', files['.htaccess'], args.apache)
    print('PASS isolated verification complete. Temporary servers/data removed; no live requests, production data or email were used.')


if __name__ == '__main__':
    try:
        main()
    except (RuntimeError, OSError, subprocess.SubprocessError, sqlite3.Error, ValueError, ET.ParseError) as error:
        print(f'Verification failed: {error}', file=sys.stderr)
        sys.exit(1)
