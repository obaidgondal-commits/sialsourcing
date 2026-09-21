#!/usr/bin/env python3
"""Run the real contact controller with a temporary CMS boundary stub.

No database or email service is contacted. PHP's mail() is disabled so any
accidental staging notification makes the test fail.
"""
import json
from pathlib import Path
import shutil
import subprocess
import tempfile

ROOT = Path(__file__).resolve().parents[1]
SKU = 'SS-DEN-EXT-0010-01'
FAMILY = 'SS-DEN-EXT-0010'
with tempfile.TemporaryDirectory(prefix='sial-contact-contract-') as temporary:
    directory = Path(temporary)
    site = directory / 'site'
    (site / 'includes').mkdir(parents=True)
    shutil.copy2(ROOT / 'contact.php', site / 'contact.php')
    shutil.copy2(ROOT / 'includes/instrument-data.php', site / 'includes/instrument-data.php')
    (site / 'includes/header.php').write_text('<?php /* CMS layout boundary */ ?>')
    (site / 'includes/footer.php').write_text('<?php /* CMS layout boundary */ ?>')
    (site / 'config.php').write_text('''<?php
    define('SITE_URL', 'http://staging.invalid');
    function e($v) { return htmlspecialchars(is_scalar($v) ? (string)$v : '', ENT_QUOTES, 'UTF-8'); }
    function setting($key, $fallback='') { return $fallback; }
    function icon(...$args) { return ''; }
    function csrf_ok() { return ($_POST['csrf'] ?? '') === 'token'; }
    function csrf_field() { return '<input name="csrf" value="token">'; }
    class TestStatement {
        public function __construct(private string $sql) {}
        public function execute($args) { if (str_starts_with($this->sql, 'INSERT INTO contact_submissions')) $GLOBALS['saved'][]=$args; }
        public function fetchColumn() { return 'Dental Instruments'; }
    }
    class TestDatabase { public function prepare($sql) { return new TestStatement($sql); } }
    function db() { return new TestDatabase(); }
    ''')
    data_file = directory / 'private.json'
    fixture = {'schema_version': 1, 'mode': 'staging-preview', 'generated_at': '2026-09-19', 'families': [{
        'code': FAMILY, 'slug': 'test-forceps', 'name': 'Test forceps', 'discipline': 'Dental Instruments',
        'group': 'Extraction', 'pattern': '', 'description': '', 'products': [{
            'sku': SKU, 'description': 'Example variant', 'material': 'Stainless steel', 'attributes': [], 'review_status': 'pending'}]}]}
    data_file.write_text(json.dumps(fixture))
    driver = directory / 'driver.php'
    driver.write_text('''<?php
    $input=json_decode($argv[1],true);
    $_SERVER['REQUEST_METHOD']=$input['method'];
    $_SERVER['DOCUMENT_ROOT']=$argv[2];
    $_GET=$input['get']; $_POST=$input['post'];
    putenv('SIAL_STAGING=1'); putenv('SIAL_CATALOGUE_FILE='.$argv[3]);
    $GLOBALS['saved']=[];
    ob_start(); require $argv[2].'/contact.php'; $html=ob_get_clean();
    echo json_encode(['status'=>http_response_code() ?: 200,'saved'=>$GLOBALS['saved'],'html'=>$html],JSON_THROW_ON_ERROR);
    ''')
    def request(method='GET', get=None, post=None):
        result = subprocess.run(['php', '-d', 'disable_functions=mail', str(driver),
            json.dumps({'method': method, 'get': get or {}, 'post': post or {}}), str(site), str(data_file)],
            text=True, capture_output=True, check=True)
        return json.loads(result.stdout)
    checks = 0
    def check(value, message):
        global checks
        if not value: raise AssertionError(message)
        checks += 1
    valid_get = {'product': 'surgical-instruments', 'family': FAMILY, 'skus': [SKU]}
    result = request(get=valid_get)
    check('Surgical Instruments' in result['html'] and '<option selected>Surgical Instruments</option>' in result['html'], 'Dental variants must map to the existing surgical subject.')
    check(SKU in result['html'] and 'Selected catalogue references:' in result['html'] and 'name="skus[]"' in result['html'], 'The existing message form must carry validated selections.')
    check(result['saved'] == [], 'Prefilling must not create an enquiry.')
    result = request(get={**valid_get, 'skus': ['SS-DEN-EXT-0020-01']})
    check(result['status'] == 422 and result['saved'] == [] and 'name="skus[]"' not in result['html'], 'Invalid GET selection must fail without populating a fake SKU.')
    post = {'csrf': 'token', 'name': 'Test buyer', 'email': 'buyer@example.invalid', 'company': '', 'phone': '', 'subject': 'Other', 'message': 'Please quote 20 units.', 'family': FAMILY, 'skus': [SKU]}
    result = request('POST', post=post)
    check(len(result['saved']) == 1 and result['saved'][0][4] == 'Surgical Instruments', 'A valid selection must save to the existing CMS inbox and correct subject.')
    check(SKU in result['saved'][0][5] and 'Please quote 20 units.' in result['saved'][0][5], 'Saved enquiry must retain validated references and buyer requirements.')
    check('No email was sent.' in result['html'], 'Staging must succeed with mail() disabled.')
    result = request('POST', post={**post, 'skus': ['SS-DEN-EXT-0020-01']})
    check(result['status'] == 422 and result['saved'] == [], 'Tampered POST reference must not write to the inbox.')
    result = request('POST', post={**post, 'csrf': 'wrong'})
    check(result['saved'] == [], 'Existing CMS CSRF validation must still reject invalid tokens.')
    generic = {k: v for k, v in post.items() if k not in ('family', 'skus')}
    generic['subject'] = 'Custom Soccer Balls'
    result = request('POST', post=generic)
    check(result['saved'][0][4:] == ['Custom Soccer Balls', 'Please quote 20 units.'], 'Ordinary contact enquiries must keep their original behavior.')
    fixture['mode'] = 'published'
    data_file.write_text(json.dumps(fixture))
    result = request('POST', post=post)
    check(result['status'] == 422 and result['saved'] == [], 'Pending products in a published snapshot cannot be requested as catalogue selections.')
    print(f'{checks} contact integration checks passed; no database or mail service used.')
