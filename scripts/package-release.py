#!/usr/bin/env python3
"""Build a reviewed file-only release. Never copy config, uploads, DBs or Git data."""
from __future__ import annotations

import argparse
import hashlib
import json
from pathlib import Path
import shutil
import subprocess
import sys
import tempfile
import zipfile

ROOT = Path(__file__).resolve().parent.parent
PUBLIC_PHP = """
404.php about.php activewear-sports-uniforms.php blog.php blog-single.php
contact.php custom-soccer-balls.php cutlery.php dental-instruments.php
faisalabad-sourcing.php faq.php for-manufacturers.php home-textiles.php index.php
lab-qc.php leather-goods.php medical-scrubs.php product-template.php products.php
promotional-soccer-balls.php qc-inspection-request.php resources.php
security-guard-uniforms.php sialkot-sourcing.php sitemap.php solutions.php
sourcing-regions.php sports-goods.php surgical-instruments.php team.php
uniforms-tactical-wear.php wazirabad-sourcing.php
admin/blog.php admin/dashboard.php admin/faq.php admin/inbox.php admin/index.php
admin/lab.php admin/logout.php admin/manufacturers.php admin/product-pages.php
admin/products.php admin/settings.php admin/solutions.php admin/team.php
admin/includes/auth.php admin/includes/header.php
includes/footer.php includes/header.php includes/icons.php includes/region-content.php
""".split()
PUBLIC_FILES = """
.htaccess robots.txt llms.txt favicon.png favicon.webp og-image.jpg
sialsourcing-icon.webp sialsourcing-logo.png
assets/docs/US-Import-Documents-Checklist.pdf
assets/docs/Sample-QC-Inspection-Report.pdf
assets/docs/SialSourcing-Company-Profile.pdf
assets/docs/RFQ-Sourcing-Brief-Template.pdf
assets/docs/QC-Inspection-Request-Form.pdf
assets/docs/Apparel-Uniform-Tech-Pack-Template.pdf
""".split()
# New routes require a deliberate addition here. These two policy pages are
# optional while being written and, when present, are explicitly publishable.
OPTIONAL_PHP = ['privacy.php', 'terms.php']
ASSET_RULES = {
    'assets/css': {'.css'}, 'assets/js': {'.js'},
    'assets/images': {'.png', '.jpg', '.jpeg', '.webp', '.svg', '.avif'},
    'assets/fonts': {'.woff', '.woff2'},
}
FORBIDDEN_PARTS = {'.git', '.github', '.preview', 'docs', 'database', 'scripts', 'work', 'outputs', 'uploads', 'backups'}


def fail(message: str) -> None:
    raise RuntimeError(message)


def checked_file(relative: str) -> Path:
    path = ROOT / relative
    if not path.is_file():
        fail(f'Missing required public file: {relative}')
    cursor = path
    while cursor != ROOT:
        if cursor.is_symlink():
            fail(f'Symlinks are not permitted in release inputs: {relative}')
        cursor = cursor.parent
    if not path.resolve().is_relative_to(ROOT):
        fail(f'Path escapes repository: {relative}')
    return path


def collect() -> dict[str, bytes]:
    names = set(PUBLIC_PHP + PUBLIC_FILES)
    names.update(name for name in OPTIONAL_PHP if (ROOT / name).exists())
    for folder, allowed in ASSET_RULES.items():
        directory = ROOT / folder
        if not directory.exists():
            continue
        if directory.is_symlink():
            fail(f'Asset directory cannot be a symlink: {folder}')
        for path in directory.rglob('*'):
            relative = path.relative_to(ROOT).as_posix()
            if path.is_symlink():
                fail(f'Asset symlink rejected: {relative}')
            if path.is_dir():
                continue
            if any(part.startswith('.') for part in path.relative_to(ROOT).parts):
                fail(f'Hidden asset rejected: {relative}')
            if path.suffix.lower() not in allowed:
                fail(f'Unapproved asset type: {relative}')
            names.add(relative)
    for directory in [ROOT, ROOT / 'admin', ROOT / 'admin/includes', ROOT / 'includes']:
        for path in directory.glob('*.php'):
            name = path.relative_to(ROOT).as_posix()
            if name not in names and name not in {'config.php', 'config.example.php'}:
                fail(f'New PHP file needs explicit release allowlist review: {name}')
    result = {}
    for name in sorted(names):
        parts = Path(name).parts
        # assets/docs is an explicit collection of public downloads, unlike docs/.
        if (set(parts) & FORBIDDEN_PARTS and not name.startswith('assets/docs/')) or \
                any(part.startswith('.env') for part in parts) or \
                Path(name).name in {'config.php', 'config.example.php'} or '..' in parts:
            fail(f'Forbidden release path: {name}')
        result[name] = checked_file(name).read_bytes()
    return result


def lint(php: str, files: dict[str, bytes]) -> str:
    executable = shutil.which(php)
    if not executable:
        fail('PHP CLI is required; syntax validation cannot be skipped.')
    version = subprocess.run([executable, '-r', 'echo PHP_VERSION;'], check=True, capture_output=True, text=True).stdout
    if tuple(map(int, version.split('.')[:2])) < (8, 2):
        fail('PHP 8.2 or newer is required.')
    names = [name for name in files if name.endswith('.php')]
    names += ['config.example.php', 'scripts/setup-preview.php', 'scripts/preview-router.php']
    for name in names:
        code = files[name] if name in files else checked_file(name).read_bytes()
        # Lint the captured bytes that will enter the ZIP, not a file that could
        # change during a concurrent editor save.
        result = subprocess.run([executable, '-l'], input=code, capture_output=True)
        if result.returncode:
            fail(f'PHP syntax validation failed: {name}\n{result.stdout.decode(errors="replace")}{result.stderr.decode(errors="replace")}')
    return version


def git_value(arguments: list[str]) -> str | None:
    if not shutil.which('git'):
        return None
    result = subprocess.run(['git', '-C', str(ROOT), *arguments], capture_output=True, text=True)
    return result.stdout.strip() if result.returncode == 0 else None


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--output-dir', type=Path, default=ROOT / '.preview/releases')
    parser.add_argument('--check-only', action='store_true')
    parser.add_argument('--php', default='php')
    args = parser.parse_args()
    files = collect()
    php_version = lint(args.php, files)
    hashes = {name: hashlib.sha256(data).hexdigest() for name, data in files.items()}
    content_id = hashlib.sha256(json.dumps(hashes, sort_keys=True).encode()).hexdigest()[:16]
    print(f'Validated {len(files)} allowlisted files using PHP {php_version}; content {content_id}.')
    if args.check_only:
        return
    destination = args.output_dir.resolve()
    # Build artifacts never belong in a directory that this package publishes.
    if destination == ROOT or any(destination.is_relative_to(ROOT / folder) for folder in ['admin', 'includes', 'assets']):
        fail('Choose a separate output directory, such as .preview/releases.')
    destination.mkdir(parents=True, exist_ok=True)
    source_commit = git_value(['rev-parse', 'HEAD'])
    source_status = git_value(['status', '--porcelain'])
    is_dirty = source_status is None or bool(source_status)
    revision = (source_commit[:8] if source_commit else 'unversioned') + ('-draft' if is_dirty else '')
    stem = f'sialsourcing-release-{content_id}-{revision}'
    zip_path = destination / f'{stem}.zip'
    manifest_path = destination / f'{stem}.manifest.json'
    with tempfile.TemporaryDirectory(prefix='sialsourcing-package-') as temporary:
        temporary_zip = Path(temporary) / 'release.zip'
        with zipfile.ZipFile(temporary_zip, 'w', compression=zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
            for name, data in files.items():
                info = zipfile.ZipInfo(name, date_time=(2026, 1, 1, 0, 0, 0))
                info.compress_type = zipfile.ZIP_DEFLATED
                info.create_system = 3
                info.external_attr = 0o100644 << 16
                archive.writestr(info, data)
        with zipfile.ZipFile(temporary_zip) as archive:
            if set(archive.namelist()) != set(files) or archive.testzip() is not None:
                fail('Release ZIP verification failed.')
            for name, digest in hashes.items():
                if hashlib.sha256(archive.read(name)).hexdigest() != digest:
                    fail(f'Archive content mismatch: {name}')
        zip_bytes = temporary_zip.read_bytes()
    zip_digest = hashlib.sha256(zip_bytes).hexdigest()
    if zip_path.exists() and hashlib.sha256(zip_path.read_bytes()).hexdigest() != zip_digest:
        fail('Existing release archive has different contents; refusing to overwrite it.')
    if not zip_path.exists():
        with zip_path.open('xb') as output:
            output.write(zip_bytes)
    manifest = {
        'format': 1, 'package': zip_path.name, 'sha256': zip_digest,
        'file_count': len(files), 'content_id': content_id,
        'source_commit': source_commit,
        'source_has_uncommitted_changes': is_dirty,
        'php_lint_version': php_version,
        'deployment_performed': False,
        'manual_setup_required': 'Reconcile the server-only config/runtime, existing MySQL schema and uploads before deployment; see docs/HOSTINGER-DEPLOYMENT.md in the repository.',
        'files_sha256': hashes,
    }
    # Keep an existing content-addressed manifest intact too.
    if not manifest_path.exists():
        with manifest_path.open('x', encoding='utf-8') as output:
            json.dump(manifest, output, indent=2, ensure_ascii=False)
            output.write('\n')
    print(zip_path)
    print(manifest_path)
    print('No configuration, uploads, database, preview data or deployment scripts were packaged. No server was modified.')


if __name__ == '__main__':
    try:
        main()
    except (RuntimeError, OSError, subprocess.SubprocessError) as error:
        print(f'Release blocked: {error}', file=sys.stderr)
        sys.exit(1)
