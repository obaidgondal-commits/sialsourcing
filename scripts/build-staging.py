#!/usr/bin/env python3
"""Package the complete existing website for isolated, password-protected staging."""
import argparse
import json
from pathlib import Path
import re
import shutil
import subprocess
import zipfile

ROOT = Path(__file__).resolve().parents[1]

def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--catalogue', required=True, type=Path)
    parser.add_argument('--auth-config', required=True, type=Path, help='Existing private preview config; reuses its password hash.')
    parser.add_argument('--output', required=True, type=Path)
    parser.add_argument('--origin', required=True)
    parser.add_argument('--media-manifest', type=Path, help='Optional private approved-photo manifest; never stored in public_html.')
    args = parser.parse_args()
    if not re.fullmatch(r'https://[a-z0-9][a-z0-9.-]+', args.origin):
        parser.error('Provide an HTTPS staging origin without a trailing slash.')
    destination = args.output.resolve()
    if destination.exists() or destination.is_relative_to(ROOT):
        parser.error('Use a NEW output directory outside the website source root.')
    catalogue = json.loads(args.catalogue.read_text())
    if catalogue.get('schema_version') != 1 or catalogue.get('mode') not in ('published', 'staging-preview'):
        parser.error('Use a valid public-field catalogue export.')
    if args.media_manifest:
        if args.media_manifest.resolve().is_relative_to(ROOT) or args.media_manifest.stat().st_size > 2_000_000:
            parser.error('Media manifest must be a private file outside the website source root, at most 2 MB.')
        media = json.loads(args.media_manifest.read_text())
        if not isinstance(media, dict) or media.get('schema_version') != 1 or not isinstance(media.get('images'), list):
            parser.error('Use a schema version 1 image manifest.')
    auth = args.auth_config.read_text()
    hashed = re.search(r"'CATALOGUE_STAGE_PASSWORD_HASH'\s*=>\s*'([^']+)'", auth)
    user = re.search(r"'CATALOGUE_STAGE_USER'\s*=>\s*'([^']+)'", auth)
    if not hashed or not user or not re.fullmatch(r'[a-zA-Z0-9_-]+', user[1]):
        parser.error('Existing staging authentication configuration is required.')
    public = destination / 'public_html'
    private = destination / 'full-site-private'
    public.mkdir(parents=True)
    private.mkdir(mode=0o700)
    (private / 'sessions').mkdir(mode=0o700)
    # Explicit web-runtime allowlist excludes database exports, development files,
    # credentials, pilot source material, tests, old ZIPs and Git metadata.
    for source in ROOT.glob('*.php'):
        if source.name != 'config.php':
            shutil.copy2(source, public / source.name)
    for folder in ('assets', 'includes', 'uploads', 'admin'):
        shutil.copytree(ROOT / folder, public / folder, ignore=shutil.ignore_patterns('.gitkeep'))
    for filename in ('.htaccess', 'favicon.png', 'favicon.webp', 'og-image.jpg', 'sialsourcing-icon.webp', 'sialsourcing-logo.png', 'llms.txt'):
        shutil.copy2(ROOT / filename, public / filename)
    subprocess.run(['python3', str(ROOT / 'scripts/setup-preview.py'), '--output', str(private / 'cms.sqlite'), '--site-url', args.origin], check=True)
    shutil.copy2(args.catalogue, private / 'catalogue.json')
    (private / 'catalogue.json').chmod(0o600)
    if args.media_manifest:
        shutil.copy2(args.media_manifest, private / 'instrument-media.json')
        (private / 'instrument-media.json').chmod(0o600)
    (private / '.htaccess').write_text('Require all denied\n')
    (public / 'robots.txt').write_text('User-agent: *\nDisallow: /\n')
    original = (public / '.htaccess').read_text()
    canonical = r'''RewriteCond %{HTTPS} off [OR]
RewriteCond %{HTTP_HOST} ^www\. [NC]
RewriteRule ^(.*)$ https://sialsourcing.com/$1 [R=301,L]'''
    if canonical not in original:
        raise ValueError('Canonical redirect changed; inspect before packaging staging.')
    original = original.replace(canonical, '''# Staging: reject plaintext requests before any authentication challenge.
RewriteCond %{HTTPS} !=on
RewriteRule ^ - [F,L]''')
    original = original.replace('RewriteEngine On', '''RewriteEngine On
RewriteRule ^(?:admin|includes|data|database|scripts|tests|docs)(?:/|$) - [F,L]
RewriteRule ^catalogue(?:/.*)?$ /surgical-instruments [R=302,L]''', 1)
    original += '''
<IfModule mod_headers.c>
  Header always set X-Robots-Tag "noindex, nofollow, noarchive"
  Header always set Cache-Control "no-store, private"
  Header always set Content-Security-Policy "script-src 'self' 'unsafe-inline'; connect-src 'self'"
</IfModule>
'''
    (public / '.htaccess').write_text(original)
    (destination / 'full-site-config.php').write_text("<?php\nreturn " + "[\n" +
        "'origin' => '" + args.origin + "',\n'user' => '" + user[1] + "',\n'password_hash' => '" + hashed[1] + "',\n];\n")
    (destination / 'full-site-config.php').chmod(0o600)
    (public / 'config.php').write_text('''<?php
// Dedicated staging only. Private paths never point at the live CMS.
$stage = require dirname(__DIR__) . '/full-site-config.php';
header('X-Robots-Tag: noindex, nofollow, noarchive');
header('Cache-Control: no-store, private');
if (PHP_SAPI !== 'cli' && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) {
    http_response_code(403); exit('HTTPS is required.');
}
$user = $_SERVER['PHP_AUTH_USER'] ?? '';
$password = $_SERVER['PHP_AUTH_PW'] ?? '';
$authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if ($user === '' && str_starts_with($authorization, 'Basic ')) {
    $decoded = base64_decode(substr($authorization, 6), true);
    if (is_string($decoded) && str_contains($decoded, ':')) [$user, $password] = explode(':', $decoded, 2);
}
if (!hash_equals($stage['user'], $user) || !password_verify($password, $stage['password_hash'])) {
    header('WWW-Authenticate: Basic realm="SialSourcing private preview", charset="UTF-8"');
    http_response_code(401); exit('This preview requires its review login.');
}
putenv('SIAL_STAGING=1');
putenv('SIAL_SITE_URL=' . $stage['origin']);
putenv('SIAL_CMS_FILE=' . dirname(__DIR__) . '/full-site-private/cms.sqlite');
putenv('SIAL_SESSION_DIR=' . dirname(__DIR__) . '/full-site-private/sessions');
putenv('SIAL_CATALOGUE_FILE=' . dirname(__DIR__) . '/full-site-private/catalogue.json');
if (is_file(dirname(__DIR__) . '/full-site-private/instrument-media.json')) {
    putenv('SIAL_INSTRUMENT_MEDIA_FILE=' . dirname(__DIR__) . '/full-site-private/instrument-media.json');
}
require __DIR__ . '/config.example.php';
''')
    (destination / '.htaccess').write_text('<Files "full-site-config.php">\nRequire all denied\n</Files>\n')
    with zipfile.ZipFile(destination.with_suffix('.zip'), 'x', zipfile.ZIP_DEFLATED) as archive:
        for path in sorted(destination.rglob('*')):
            if path.is_file():
                archive.write(path, path.relative_to(destination))
    print(f'Complete-site staging archive: {destination.with_suffix(".zip")}')
    print('Deploy ONLY at the dedicated staging site root, above public_html. Existing login is retained.')

if __name__ == '__main__':
    main()
