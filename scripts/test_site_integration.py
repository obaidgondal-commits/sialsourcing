#!/usr/bin/env python3
"""Read-only HTTP checks for the instrument extension inside the original site.

Start the original snapshot on :8879 and the integrated site on :8878. This test
uses GET only: it never submits a contact form, writes an enquiry or sends mail.
Unrelated HTML is compared exactly after only base URL, CSRF value and stylesheet
mtime normalization. Browser checks are still required for layout and JavaScript.
"""
from __future__ import annotations

import argparse
from html.parser import HTMLParser
import http.cookiejar
import json
import re
import sys
from urllib.error import HTTPError, URLError
from urllib.parse import quote, urlencode, urljoin, urlparse
from urllib.request import HTTPCookieProcessor, HTTPRedirectHandler, ProxyHandler, Request, build_opener


UNCHANGED_PAGES = [
    '/', '/products', '/custom-soccer-balls', '/promotional-soccer-balls',
    '/activewear-sports-uniforms', '/uniforms-tactical-wear',
    '/security-guard-uniforms', '/medical-scrubs', '/sports-goods',
    '/leather-goods', '/cutlery', '/solutions', '/lab-qc', '/about', '/team',
    '/contact', '/blog', '/resources', '/faq', '/qc-inspection-request',
    '/for-manufacturers',
]


class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None


class Page(HTMLParser):
    def __init__(self, html):
        super().__init__(convert_charrefs=True)
        self.links = []
        self.stylesheets = []
        self.items = []
        self.inputs = []
        self.forms = []
        self.h1 = ''
        self.message = ''
        self.subject = ''
        self.canonical = ''
        self.noindex = False
        self._h1 = False
        self._message = False
        self._subject_select = False
        self._selected_option = False
        self.feed(html)

    def handle_starttag(self, tag, attributes):
        attrs = dict(attributes)
        if tag == 'a':
            self.links.append(attrs.get('href', ''))
        if tag == 'link' and attrs.get('rel') == 'stylesheet':
            self.stylesheets.append(attrs.get('href', ''))
        if tag == 'link' and attrs.get('rel') == 'canonical':
            self.canonical = attrs.get('href', '')
        if tag == 'meta' and attrs.get('name') == 'robots':
            self.noindex = 'noindex' in attrs.get('content', '')
        if 'data-instrument-item' in attrs:
            self.items.append(attrs)
        if tag == 'input':
            self.inputs.append(attrs)
        if tag == 'form':
            self.forms.append(attrs)
        if tag == 'h1':
            self._h1 = True
        if tag == 'textarea' and attrs.get('name') == 'message':
            self._message = True
        if tag == 'select' and attrs.get('name') == 'subject':
            self._subject_select = True
        if tag == 'option':
            self._selected_option = self._subject_select and 'selected' in attrs

    def handle_endtag(self, tag):
        if tag == 'h1':
            self._h1 = False
        if tag == 'textarea':
            self._message = False
        if tag == 'select':
            self._subject_select = False
        if tag == 'option':
            self._selected_option = False

    def handle_data(self, text):
        if self._h1:
            self.h1 += text
        if self._message:
            self.message += text
        if self._selected_option:
            self.subject += text

    def values(self, name, kind=None):
        return [item.get('value', '') for item in self.inputs
                if item.get('name') == name and (kind is None or item.get('type') == kind)]


def normalized(html, base):
    html = html.replace(base.rstrip('/'), '<SITE>')
    html = html.replace(quote(base.rstrip('/'), safe=''), '<SITE>')

    def input_tag(match):
        tag = match.group(0)
        if re.search(r'\bname=["\'](?:_?csrf(?:_token)?|csrf_token)["\']', tag, re.I):
            return re.sub(r'(\bvalue=)(["\']).*?\2', r'\1"<CSRF>"', tag, flags=re.I | re.S)
        return tag

    html = re.sub(r'<input\b[^>]*>', input_tag, html, flags=re.I)
    html = re.sub(r'(/assets/css/[^"\'<>?]+\.css\?v=)\d+', r'\1<MTIME>', html)
    return html


def original_nav(html):
    found = re.search(r'<nav\b[^>]*\bid="mainNav"[^>]*>.*?</nav>', html, re.S)
    return found.group(0) if found else ''


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--baseline-url', default='http://127.0.0.1:8879')
    parser.add_argument('--integrated-url', default='http://127.0.0.1:8878')
    parser.add_argument('--expected-families', type=int, default=42)
    parser.add_argument('--expected-variants', type=int, default=344)
    parser.add_argument('--quiet', action='store_true', help='Print only failures and the final counts')
    args = parser.parse_args()
    for value in (args.baseline_url, args.integrated_url):
        url = urlparse(value)
        if url.scheme not in ('http', 'https') or url.username or url.password or url.path not in ('', '/'):
            parser.error('Use HTTP(S) origins without credentials or path.')
        if url.hostname not in ('localhost', '127.0.0.1', '::1'):
            parser.error('This comparison script is restricted to local preview servers.')
    clients = {base: build_opener(ProxyHandler({}), HTTPCookieProcessor(http.cookiejar.CookieJar()), NoRedirect())
               for base in (args.baseline_url, args.integrated_url)}
    passed = 0
    failures = []

    def check(condition, description):
        nonlocal passed
        if condition:
            passed += 1
            if not args.quiet:
                print('PASS: ' + description)
        else:
            failures.append(description)
            print('FAIL: ' + description)
        return condition

    def get(base, path):
        request = Request(urljoin(base + '/', path), headers={'User-Agent': 'SialSourcing-integration-check/1.0'})
        try:
            response = clients[base].open(request, timeout=15)
        except HTTPError as error:
            response = error
        with response:
            return response.code, response.headers, response.read().decode('utf-8', errors='replace')

    def normalize_body(html):
        for base in (args.baseline_url, args.integrated_url):
            html = normalized(html, base)
        return html

    def compare_page(path):
        a_status, _, a_body = get(args.baseline_url, path)
        b_status, _, b_body = get(args.integrated_url, path)
        a_html, b_html = normalize_body(a_body), normalize_body(b_body)
        good = check(a_status == b_status == 200 and a_html == b_html, 'Original HTML preserved for ' + path)
        if not good and a_status == b_status == 200:
            a_lines, b_lines = a_html.splitlines(), b_html.splitlines()
            mismatch = next((i for i, (a, b) in enumerate(zip(a_lines, b_lines)) if a != b), min(len(a_lines), len(b_lines)))
            print('  First difference at normalized line ' + str(mismatch + 1)
                  + '; baseline/integrated line counts ' + str(len(a_lines)) + '/' + str(len(b_lines)))
        return a_body, b_body

    try:
        bodies = {}
        for path in UNCHANGED_PAGES:
            bodies[path] = compare_page(path)
        blog_links = {urlparse(link).path for link in Page(bodies['/blog'][0]).links
                      if re.fullmatch(r'/blog/[a-zA-Z0-9-]+', urlparse(link).path)}
        for path in sorted(blog_links):
            compare_page(path)
        for path in ('/assets/css/style.css', '/assets/js/main.js'):
            a_status, _, a_content = get(args.baseline_url, path)
            b_status, _, b_content = get(args.integrated_url, path)
            check(a_status == b_status == 200 and a_content == b_content, 'Original asset unchanged: ' + path)
        original_navigation = normalize_body(original_nav(bodies['/'][0]))
        check(bool(original_navigation), 'Baseline contains the existing main navigation')
        for path in ('/surgical-instruments', '/dental-instruments'):
            status, _, body = get(args.integrated_url, path)
            check(status == 200 and 'Browse Instruments in Detail' in body, 'Existing medical page adds detail browsing: ' + path)
            check(normalize_body(original_nav(body)) == original_navigation,
                  'Original navigation retained on ' + path)
            check('/dental-instruments/extraction' in body and '344 variants' in body,
                  'Medical overview links to the complete extraction pilot: ' + path)
            baseline_status, _, original = get(args.baseline_url, path)
            for section in ('pp-hero', 'pp-content', 'pp-specs', 'pp-faq', 'pp-cta'):
                expression = r'<section class="' + section + r'">.*?</section>'
                original_section = re.search(expression, original, re.S)
                integrated_section = re.search(expression, body, re.S)
                same = ((original_section is None and integrated_section is None and section == 'pp-faq')
                        or (original_section is not None and integrated_section is not None
                            and normalize_body(original_section.group(0)) == normalize_body(integrated_section.group(0))))
                check(baseline_status == 200 and same, 'Original ' + section + ' preserved on ' + path)
            original_footer = re.search(r'<footer>.*?</footer>', original, re.S)
            integrated_footer = re.search(r'<footer>.*?</footer>', body, re.S)
            check(original_footer is not None and integrated_footer is not None
                  and normalize_body(original_footer.group(0)) == normalize_body(integrated_footer.group(0)),
                  'Original footer preserved on ' + path)

        group_path = '/dental-instruments/extraction'
        status, headers, group_body = get(args.integrated_url, group_path)
        group_page = Page(group_body)
        if not check(status == 200 and len(group_page.items) == args.expected_families,
                     'Extraction group renders all ' + str(args.expected_families) + ' families'):
            raise AssertionError('Cannot continue family-route checks without the complete group.')
        check(group_page.noindex and 'noindex' in headers.get('X-Robots-Tag', ''), 'Pending group is noindex in metadata and headers')
        check(normalize_body(original_nav(group_body)) == original_navigation, 'Group page uses the original full site navigation')
        check(any(urlparse(url).path == '/assets/css/style.css' for url in group_page.stylesheets), 'Group page uses the original shared stylesheet')
        family_paths = sorted({urlparse(link).path for link in group_page.links
                               if re.fullmatch(r'/dental-instruments/extraction/[a-z0-9-]+', urlparse(link).path)})
        check(len(family_paths) == args.expected_families, 'Group exposes exactly ' + str(args.expected_families) + ' distinct family routes')
        variants = set()
        first_family = None
        other_family = None
        for path in family_paths:
            status, headers, body = get(args.integrated_url, path)
            page = Page(body)
            skus = page.values('skus[]', 'checkbox')
            family_codes = page.values('family', 'hidden')
            good = status == 200 and bool(skus) and len(skus) == len(set(skus)) and len(family_codes) == 1
            check(good, 'Family route renders selectable variants: ' + path.rsplit('/', 1)[-1])
            if not good:
                continue
            check(page.noindex and 'noindex' in headers.get('X-Robots-Tag', '')
                  and normalize_body(original_nav(body)) == original_navigation,
                  'Family preserves original navigation and pending noindex: ' + family_codes[0])
            check(not variants.intersection(skus), 'Family references are unique across the catalogue: ' + family_codes[0])
            variants.update(skus)
            if first_family is None and len(skus) >= 3:
                first_family = (path, family_codes[0], skus, page)
            elif first_family is not None and family_codes[0] != first_family[1]:
                other_family = (path, family_codes[0], skus, page)
        check(len(variants) == args.expected_variants, 'All family routes expose exactly ' + str(args.expected_variants) + ' unique pilot variants')
        for path in ('/dental-instruments/extraction/unknown-family', '/dental-instruments/unknown-group',
                     '/instrument-catalogue.php?discipline=dental-instruments&group=extraction'):
            status, _, _ = get(args.integrated_url, path)
            check(status == 404, 'Unknown or direct controller route is rejected: ' + path.split('?', 1)[0])
        if first_family is None or other_family is None:
            raise AssertionError('Suitable families are unavailable for selection tests.')
        path, family_code, skus, page = first_family
        override = urlencode({'discipline': 'surgical-instruments', 'group': 'unknown-group', 'family': 'unknown-family', 'q': skus[0]})
        status, _, body = get(args.integrated_url, path + '?' + override)
        overridden = Page(body)
        check(status == 200 and overridden.values('family', 'hidden') == [family_code]
              and overridden.values('skus[]', 'checkbox') == skus,
              'Query parameters cannot override the route identity')
        check(sum('hidden' not in item for item in overridden.items) == 1
              and overridden.values('q') == [skus[0]], 'Search query survives routing and filters the family on the server')
        first_attributes = json.loads(page.items[0].get('data-attributes', '{}'))
        if first_attributes:
            code, value = next(iter(first_attributes.items()))
            status, _, body = get(args.integrated_url, path + '?' + urlencode({'filter[' + code + ']': value}))
            filtered = Page(body)
            expected = sum(json.loads(item.get('data-attributes', '{}')).get(code) == value for item in page.items)
            check(status == 200 and sum('hidden' not in item for item in filtered.items) == expected,
                  'Attribute filter survives routing and matches the recorded values')
        selected = [skus[0], skus[-1]]
        contact_query = {'product': 'surgical-instruments', 'family': family_code, 'skus[]': selected}
        status, _, body = get(args.integrated_url, '/contact?' + urlencode(contact_query, doseq=True))
        contact = Page(body)
        check(status == 200 and contact.subject.strip() == 'Surgical Instruments', 'Selected dental references use the existing Surgical Instruments enquiry subject')
        check(contact.values('family', 'hidden') == [family_code] and contact.values('skus[]', 'hidden') == selected,
              'Existing contact form retains exactly the selected references')
        check(all(sku in contact.message for sku in selected) and all(sku not in contact.message for sku in skus[1:-1]),
              'Existing message contains the selected subset, not the whole family')
        check(normalize_body(original_nav(body)) == original_navigation
              and any(form.get('method', '').lower() == 'post' and form.get('action') == '/contact' for form in contact.forms),
              'Selection continues in the original contact page and form')
        for label, bad_query in (
            ('cross-family reference', {**contact_query, 'skus[]': [other_family[2][0]]}),
            ('unknown family', {**contact_query, 'family': 'SS-DEN-EXT-9999'}),
            ('malformed selection', {'product': 'surgical-instruments', 'family': family_code, 'skus[unexpected]': skus[0]}),
        ):
            status, _, body = get(args.integrated_url, '/contact?' + urlencode(bad_query, doseq=True))
            invalid = Page(body)
            check(status == 422 and invalid.values('skus[]', 'hidden') == [], 'Contact rejects ' + label + ' without retaining an invalid selection')
        print(f'{passed} checks passed; {len(failures)} failed. Only GET requests were used; no enquiry was submitted.')
        return 1 if failures else 0
    except (AssertionError, OSError, URLError, ValueError) as error:
        detail = str(error) if isinstance(error, AssertionError) else type(error).__name__
        print('STOP: ' + detail, file=sys.stderr)
        print(f'{passed} checks passed; {len(failures)} failed before the stop. No enquiry was submitted.')
        return 1


if __name__ == '__main__':
    raise SystemExit(main())
