<?php
/** Run with PHP + GD. Uses an isolated temporary site; never creates product photography. */
$temporary = sys_get_temp_dir() . '/sial-media-test-' . bin2hex(random_bytes(6));
$site = $temporary . '/site';
$previousManifest = getenv('SIAL_INSTRUMENT_MEDIA_FILE');
$previousDocumentRoot = $_SERVER['DOCUMENT_ROOT'] ?? null;
$checks = 0;

function media_check(bool $condition, string $description): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($description);
    }
    $checks++;
}

function media_cleanup(string $path): void
{
    if (is_dir($path) && !is_link($path)) {
        foreach (scandir($path) as $entry) {
            if ($entry !== '.' && $entry !== '..') {
                media_cleanup($path . '/' . $entry);
            }
        }
        rmdir($path);
    } else {
        unlink($path);
    }
}

try {
    if (!function_exists('imagecreatetruecolor')) {
        throw new RuntimeException('The media tests require PHP GD, as does approved-photo rendering.');
    }
    mkdir($site . '/includes', 0700, true);
    mkdir($site . '/uploads/instruments', 0700, true);
    copy(dirname(__DIR__) . '/includes/instrument-media.php', $site . '/includes/instrument-media.php');
    require $site . '/includes/instrument-media.php';
    $_SERVER['DOCUMENT_ROOT'] = $site;
    $imagePath = $site . '/uploads/instruments/test-front.png';
    $fixture = imagecreatetruecolor(2, 3);
    imagepng($fixture, $imagePath);
    $originalBytes = file_get_contents($imagePath);
    $family = ['code' => 'SS-DEN-EXT-0010', 'products' => [
        ['sku' => 'SS-DEN-EXT-0010-01'], ['sku' => 'SS-DEN-EXT-0010-02'],
    ]];
    $sku = $family['products'][0]['sku'];
    $item = [
        'family_code' => $family['code'], 'sku' => $sku, 'approved' => true,
        'file' => 'test-front.png', 'sha256' => hash('sha256', $originalBytes),
        'alt' => 'Test fixture front view', 'caption' => 'Test fixture only', 'credit' => '', 'view' => 'front',
        'rights' => ['basis' => 'own', 'evidence_ref' => 'PRIVATE-EVIDENCE-CANARY',
            'checked_by' => 'PRIVATE-REVIEWER-CANARY', 'checked_on' => '2026-09-01'],
        'supplier_name' => 'PRIVATE-SUPPLIER-CANARY',
    ];
    $manifestPath = $temporary . '/media.json';
    $save = function (array $items) use ($manifestPath): void {
        file_put_contents($manifestPath, json_encode(['schema_version' => 1, 'images' => $items], JSON_THROW_ON_ERROR));
        putenv('SIAL_INSTRUMENT_MEDIA_FILE=' . $manifestPath);
    };
    putenv('SIAL_INSTRUMENT_MEDIA_FILE');
    media_check(instrument_media_for_family($family) === [], 'Unset manifest must show no photographs.');
    $save([]);
    media_check(instrument_media_for_family($family) === [], 'An empty manifest must show no photographs.');
    $save([$item]);
    $result = instrument_media_for_family($family);
    media_check(array_keys($result) === [$sku] && count($result[$sku]) === 1, 'Approved exact-variant image is returned keyed by SKU.');
    media_check($result[$sku][0] === ['path' => '/uploads/instruments/test-front.png?v=' . substr($item['sha256'], 0, 16),
        'alt' => $item['alt'], 'caption' => $item['caption'], 'credit' => '', 'view' => 'front', 'width' => 2, 'height' => 3],
        'Return only the public allowlist, including measured image dimensions.');
    media_check(!str_contains(json_encode($result), 'PRIVATE-'), 'Private evidence, reviewers and source names must not leak.');
    media_check(instrument_media_for_product($family, $family['products'][0]) === $result[$sku], 'Product lookup returns the same exact variant.');
    media_check(instrument_media_for_product($family, $family['products'][1]) === [], 'A variant without a photo must not inherit another variant’s photo.');

    foreach ([['approved' => false], ['approved' => 'true'], ['sku' => 'UNKNOWN-SKU'], ['sku' => null],
        ['family_code' => 'SS-DEN-EXT-0011'], ['rights' => []],
        ['rights' => array_replace($item['rights'], ['basis' => 'downloaded'])],
        ['rights' => array_replace($item['rights'], ['checked_on' => '2026-02-30'])],
        ['rights' => array_replace($item['rights'], ['evidence_ref' => ''])],
        ['alt' => '<script>test</script>'], ['view' => 'PRIVATE-SOURCE'], ['sha256' => str_repeat('0', 64)],
    ] as $change) {
        $save([array_replace($item, $change)]);
        media_check(instrument_media_for_family($family) === [], 'Reject unapproved, misidentified or incompletely reviewed image metadata.');
    }
    foreach (['https://example.com/image.png', '/uploads/instruments/test-front.png', '../test-front.png',
        '%2e%2e-test.png', '.hidden.png', 'test.php.png', 'test.svg', 'missing.png'] as $filename) {
        $save([array_replace($item, ['file' => $filename])]);
        media_check(instrument_media_for_family($family) === [], 'Reject remote, unsafe, unsupported or missing image paths.');
    }
    copy($imagePath, $site . '/uploads/instruments/disguised.jpg');
    $save([array_replace($item, ['file' => 'disguised.jpg'])]);
    media_check(instrument_media_for_family($family) === [], 'Raster type must match its file extension.');
    imagejpeg($fixture, $site . '/uploads/instruments/test.jpg');
    $save([array_replace($item, ['file' => 'test.jpg', 'sha256' => hash_file('sha256', $site . '/uploads/instruments/test.jpg')])]);
    media_check(count(instrument_media_for_product($family, $family['products'][0])) === 1, 'A valid approved JPEG is accepted.');
    imagewebp($fixture, $site . '/uploads/instruments/test.webp');
    $save([array_replace($item, ['file' => 'test.webp', 'sha256' => hash_file('sha256', $site . '/uploads/instruments/test.webp'),
        'rights' => array_replace($item['rights'], ['basis' => 'supplier-authorized'])])]);
    media_check(count(instrument_media_for_product($family, $family['products'][0])) === 1, 'A valid supplier-authorized WebP is accepted.');

    $save([$item]);
    $replacement = imagecreatetruecolor(4, 5);
    imagepng($replacement, $imagePath);
    media_check(instrument_media_for_family($family) === [], 'Replacing image bytes invalidates the previous approval hash.');
    $replacementHash = hash_file('sha256', $imagePath);
    $save([array_replace($item, ['sha256' => $replacementHash])]);
    $reapproved = instrument_media_for_product($family, $family['products'][0]);
    media_check($reapproved[0]['path'] !== $result[$sku][0]['path'] &&
        $reapproved[0]['path'] === '/uploads/instruments/test-front.png?v=' . substr($replacementHash, 0, 16),
        'Reapproved replacement bytes change the public URL to bypass the prior long-lived image cache.');
    $broken = substr($originalBytes, 0, 33);
    file_put_contents($imagePath, $broken);
    $save([array_replace($item, ['sha256' => hash('sha256', $broken)])]);
    media_check(instrument_media_for_family($family) === [], 'A raster header without a decodable image is rejected even with its correct hash.');
    file_put_contents($imagePath, $originalBytes);
    $save([$item, $item]);
    media_check(count(instrument_media_for_product($family, $family['products'][0])) === 1, 'Duplicate records do not repeat the same image.');
    $save([array_replace($item, ['approved' => false]), $item]);
    media_check(count(instrument_media_for_product($family, $family['products'][0])) === 1, 'A rejected entry does not make an independently approved image disappear.');
    $secondVariant = array_replace($item, ['sku' => $family['products'][1]['sku'], 'file' => 'test.jpg',
        'sha256' => hash_file('sha256', $site . '/uploads/instruments/test.jpg')]);
    $save([$item, $secondVariant]);
    media_check(count(instrument_media_for_family($family)) === 2 &&
        instrument_media_for_family($family, true) === [$sku => $result[$sku]],
        'Representative-only lookup returns the first approved photo while the full gallery retains all variants.');

    $save([$item]);
    copy($manifestPath, $site . '/manifest.json');
    putenv('SIAL_INSTRUMENT_MEDIA_FILE=' . $site . '/manifest.json');
    media_check(instrument_media_for_family($family) === [], 'A manifest inside the website is forbidden.');
    mkdir($temporary . '/other-public');
    copy($manifestPath, $temporary . '/other-public/manifest.json');
    $_SERVER['DOCUMENT_ROOT'] = $temporary . '/other-public';
    putenv('SIAL_INSTRUMENT_MEDIA_FILE=' . $temporary . '/other-public/manifest.json');
    media_check(instrument_media_for_family($family) === [], 'The configured document root is also protected.');
    $_SERVER['DOCUMENT_ROOT'] = $site;
    putenv('SIAL_INSTRUMENT_MEDIA_FILE=relative.json');
    media_check(instrument_media_for_family($family) === [], 'Relative manifest paths are forbidden.');
    symlink($site . '/manifest.json', $temporary . '/linked-manifest.json');
    putenv('SIAL_INSTRUMENT_MEDIA_FILE=' . $temporary . '/linked-manifest.json');
    media_check(instrument_media_for_family($family) === [], 'A manifest symlink cannot bypass the private-path rule.');
    $save([$item]);
    rename($imagePath, $temporary . '/private-image.png');
    symlink($temporary . '/private-image.png', $imagePath);
    media_check(instrument_media_for_family($family) === [], 'A photo symlink cannot escape the upload directory.');
    unlink($imagePath);
    rename($temporary . '/private-image.png', $imagePath);
    file_put_contents($manifestPath, '{broken');
    media_check(instrument_media_for_family($family) === [], 'Malformed JSON fails closed.');
    file_put_contents($manifestPath, json_encode(['schema_version' => 2, 'images' => [$item]]));
    media_check(instrument_media_for_family($family) === [], 'Unknown manifest versions fail closed.');
    echo "Instrument media: {$checks} checks passed.\n";
} finally {
    $previousManifest === false ? putenv('SIAL_INSTRUMENT_MEDIA_FILE') : putenv('SIAL_INSTRUMENT_MEDIA_FILE=' . $previousManifest);
    if ($previousDocumentRoot === null) {
        unset($_SERVER['DOCUMENT_ROOT']);
    } else {
        $_SERVER['DOCUMENT_ROOT'] = $previousDocumentRoot;
    }
    if (is_dir($temporary)) {
        media_cleanup($temporary);
    }
}
