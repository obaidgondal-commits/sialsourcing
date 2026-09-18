<?php
require dirname(__DIR__) . '/includes/instrument-data.php';

$checks = 0;
function check(bool $condition, string $message): void {
    global $checks;
    if (!$condition) throw new RuntimeException($message);
    $checks++;
}
function rejected(callable $fn): bool {
    try { $fn(); } catch (InvalidArgumentException $error) { return true; }
    return false;
}
$path = tempnam(sys_get_temp_dir(), 'sial-instruments-');
$oldPath = getenv('SIAL_CATALOGUE_FILE');
$oldStage = getenv('SIAL_STAGING');
$oldRoot = $_SERVER['DOCUMENT_ROOT'] ?? null;
$fixture = [
    'schema_version' => 1, 'mode' => 'staging-preview', 'generated_at' => '2026-09-19T00:00:00Z',
    'families' => [[
        'code' => 'SS-DEN-EXT-0010', 'slug' => 'test-forceps', 'name' => 'Test forceps',
        'discipline' => 'Dental Instruments', 'group' => 'Extraction', 'pattern' => '', 'description' => '',
        'manufacturer' => 'PRIVATE-CANARY',
        'products' => [
            ['sku' => 'SS-DEN-EXT-0010-01', 'description' => 'Pending example', 'material' => 'Stainless steel',
             'attributes' => [['code' => 'side', 'label' => 'Side', 'value' => 'Either', 'unit' => null, 'supplier' => 'PRIVATE-CANARY']],
             'review_status' => 'pending', 'source_document' => 'PRIVATE-CANARY'],
            ['sku' => 'SS-DEN-EXT-0010-02', 'description' => 'Reviewed example', 'material' => 'Stainless steel',
             'attributes' => [], 'review_status' => 'verified'],
        ],
    ], [
        'code' => 'SS-DEN-EXT-0020', 'slug' => 'other-forceps', 'name' => 'Other forceps',
        'discipline' => 'Dental Instruments', 'group' => 'Extraction', 'pattern' => '', 'description' => '',
        'products' => [['sku' => 'SS-DEN-EXT-0020-01', 'description' => 'Other family', 'material' => '', 'attributes' => [], 'review_status' => 'verified']],
    ]],
];
try {
    putenv('SIAL_CATALOGUE_FILE');
    check(instrument_catalogue()['families'] === [], 'No path should leave the existing site usable.');
    putenv('SIAL_CATALOGUE_FILE=' . $path);
    file_put_contents($path, json_encode($fixture));
    putenv('SIAL_STAGING=0');
    check(instrument_catalogue()['families'] === [], 'Production must reject preview snapshots.');
    putenv('SIAL_STAGING=1');
    $data = instrument_catalogue();
    check(count($data['families']) === 2 && count($data['families'][0]['products']) === 2, 'Staging should load available preview facts.');
    check($data['families'][0]['url'] === '/dental-instruments/extraction/test-forceps', 'Family routes must be safe and root-relative.');
    check(!str_contains(json_encode($data), 'PRIVATE-CANARY'), 'Only explicitly public fields may leave the data adapter.');
    $selection = instrument_selected_products('SS-DEN-EXT-0010', ['SS-DEN-EXT-0010-01', 'SS-DEN-EXT-0010-01'], $data);
    check(count($selection['skus']) === 1, 'Repeated selections should be deduplicated.');
    check(str_contains(instrument_enquiry_prefix($selection), 'SS-DEN-EXT-0010-01'), 'Enquiries need the exact validated catalogue reference.');
    check(rejected(fn () => instrument_selected_products('SS-DEN-EXT-0010', ['UNKNOWN'], $data)), 'Unknown reference must be rejected.');
    check(rejected(fn () => instrument_selected_products('SS-DEN-EXT-0010', ['SS-DEN-EXT-0020-01'], $data)), 'Reference from another family must be rejected.');
    check(rejected(fn () => instrument_selected_products('SS-DEN-EXT-0010', [], $data)), 'Empty selection must be rejected.');
    check(rejected(fn () => instrument_selected_products('SS-DEN-EXT-0010', 'SS-DEN-EXT-0010-01', $data)), 'Scalar selection must be rejected.');
    check(rejected(fn () => instrument_selected_products('SS-DEN-EXT-0010', [['nested']], $data)), 'Nested selection must be rejected.');
    check(rejected(fn () => instrument_selected_products('test-forceps', ['SS-DEN-EXT-0010-01'], $data)), 'Contact must validate exact family code, not a loose alias.');
    $fixture['mode'] = 'published';
    file_put_contents($path, json_encode($fixture));
    putenv('SIAL_STAGING=0');
    $published = instrument_catalogue();
    check(array_column($published['families'][0]['products'], 'sku') === ['SS-DEN-EXT-0010-02'], 'Published pages must exclude pending products.');
    check(rejected(fn () => instrument_selected_products('SS-DEN-EXT-0010', ['SS-DEN-EXT-0010-01'], $published)), 'Pending references cannot be selected in production.');
    putenv('SIAL_STAGING=1');
    check(count(instrument_catalogue()['families'][0]['products']) === 1, 'Published mode keeps its approval gate even in staging.');
    $fixture['families'][0]['slug'] = '../private';
    file_put_contents($path, json_encode($fixture));
    check(instrument_catalogue()['families'] === [], 'Unsafe routes must fail closed.');
    $fixture['families'][0]['slug'] = 'test-forceps';
    file_put_contents($path, json_encode($fixture));
    $_SERVER['DOCUMENT_ROOT'] = dirname($path);
    check(instrument_catalogue()['families'] === [], 'Snapshots under a public document root must be rejected.');
    echo "$checks instrument data checks passed.\n";
} finally {
    unlink($path);
    putenv($oldPath === false ? 'SIAL_CATALOGUE_FILE' : 'SIAL_CATALOGUE_FILE=' . $oldPath);
    putenv($oldStage === false ? 'SIAL_STAGING' : 'SIAL_STAGING=' . $oldStage);
    if ($oldRoot === null) unset($_SERVER['DOCUMENT_ROOT']); else $_SERVER['DOCUMENT_ROOT'] = $oldRoot;
}
