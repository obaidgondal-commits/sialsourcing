<?php
/** Approved, exact-variant photographs. The private manifest never reaches templates. */

function instrument_media_text(mixed $value, int $limit, bool $required = false): ?string
{
    if (!is_string($value) || strlen($value) > $limit || !preg_match('//u', $value) ||
        preg_match('/[\x00-\x1F\x7F<>]/', $value) || ($required && trim($value) === '')) {
        return null;
    }
    return $value;
}

/** Return SKU => list of public image records for products actually present in this family. */
function instrument_media_for_family(array $family, bool $firstOnly = false): array
{
    $code = $family['code'] ?? null;
    if (!is_string($code) || !preg_match('/^SS-(DEN|ORT|SUR|PLA|VET)-[A-Z0-9]{2,8}-[0-9]{4}$/D', $code) ||
        !is_array($family['products'] ?? null)) {
        return [];
    }
    $skus = [];
    foreach ($family['products'] as $product) {
        $sku = is_array($product) ? ($product['sku'] ?? null) : null;
        if (is_string($sku) && strlen($sku) <= 40 && preg_match('/^[A-Z0-9]+(?:-[A-Z0-9]+)*$/D', $sku)) {
            $skus[$sku] = true;
        }
    }
    $configured = getenv('SIAL_INSTRUMENT_MEDIA_FILE');
    if (!$skus || !is_string($configured) || $configured === '' || $configured[0] !== '/') {
        return [];
    }

    try {
        $path = realpath($configured);
        if ($path === false || !is_file($path) || filesize($path) > 2_000_000) {
            return [];
        }
        foreach ([dirname(__DIR__), $_SERVER['DOCUMENT_ROOT'] ?? ''] as $root) {
            $root = $root !== '' ? realpath($root) : false;
            if ($root !== false && ($path === $root || str_starts_with($path, rtrim($root, '/') . '/'))) {
                return [];
            }
        }
        $contents = @file_get_contents($path, false, null, 0, 2_000_001);
        if (!is_string($contents) || strlen($contents) > 2_000_000) {
            return [];
        }
        $manifest = json_decode($contents, true, 32, JSON_THROW_ON_ERROR);
        if (!is_array($manifest) || ($manifest['schema_version'] ?? null) !== 1 ||
            !is_array($manifest['images'] ?? null) || !array_is_list($manifest['images']) ||
            count($manifest['images']) > 5000) {
            return [];
        }
        $publicRoot = realpath(dirname(__DIR__));
        $imageRoot = realpath(dirname(__DIR__) . '/uploads/instruments');
        if ($publicRoot === false || $imageRoot === false || !is_dir($imageRoot) ||
            !str_starts_with($imageRoot, $publicRoot . '/') || !function_exists('imagecreatefromstring')) {
            return [];
        }
        $images = [];
        $seen = [];
        foreach ($manifest['images'] as $item) {
            if (!is_array($item) || ($item['approved'] ?? null) !== true ||
                ($item['family_code'] ?? null) !== $code || !is_string($item['sku'] ?? null) ||
                !isset($skus[$item['sku']]) || !is_string($item['file'] ?? null) ||
                !preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{0,179}\.(jpg|png|webp)$/D', $item['file'], $extension) ||
                !is_string($item['sha256'] ?? null) || !preg_match('/^[a-f0-9]{64}$/D', $item['sha256'])) {
                continue;
            }
            $key = $item['sku'] . '/' . $item['file'];
            if (isset($seen[$key])) {
                continue;
            }
            $rights = $item['rights'] ?? null;
            if (!is_array($rights) || !in_array($rights['basis'] ?? null, ['own', 'supplier-authorized'], true) ||
                instrument_media_text($rights['evidence_ref'] ?? null, 1000, true) === null ||
                instrument_media_text($rights['checked_by'] ?? null, 255, true) === null ||
                !is_string($rights['checked_on'] ?? null)) {
                continue;
            }
            $checked = DateTimeImmutable::createFromFormat('!Y-m-d', $rights['checked_on']);
            if ($checked === false || $checked->format('Y-m-d') !== $rights['checked_on']) {
                continue;
            }
            $alt = instrument_media_text($item['alt'] ?? null, 300, true);
            $caption = instrument_media_text($item['caption'] ?? '', 1000);
            $credit = instrument_media_text($item['credit'] ?? '', 300);
            $view = $item['view'] ?? null;
            if ($alt === null || $caption === null || $credit === null ||
                !in_array($view, ['front', 'side', 'working-end', 'handle', 'scale', 'detail', 'packaging'], true)) {
                continue;
            }
            $file = realpath($imageRoot . '/' . $item['file']);
            if ($file === false || !str_starts_with($file, $imageRoot . '/') || !is_file($file) ||
                filesize($file) > 15_000_000) {
                continue;
            }
            // Inspect and hash the same bounded bytes; never approve a replacement under an old name.
            $bytes = @file_get_contents($file, false, null, 0, 15_000_001);
            if (!is_string($bytes) || strlen($bytes) > 15_000_000 ||
                !hash_equals($item['sha256'], hash('sha256', $bytes))) {
                continue;
            }
            $size = @getimagesizefromstring($bytes);
            $type = ['jpg' => IMAGETYPE_JPEG, 'png' => IMAGETYPE_PNG, 'webp' => IMAGETYPE_WEBP][$extension[1]];
            if ($size === false || $size[2] !== $type || $size[0] < 1 || $size[1] < 1 ||
                $size[0] > 12000 || $size[1] > 12000 || $size[0] * $size[1] > 12_000_000) {
                continue;
            }
            // GD decoding rejects truncated or fabricated raster headers. Missing GD hides all images.
            $decoded = @imagecreatefromstring($bytes);
            if ($decoded === false) {
                continue;
            }
            unset($decoded);
            $seen[$key] = true;
            $images[$item['sku']][] = [
                'path' => '/uploads/instruments/' . $item['file'] . '?v=' . substr($item['sha256'], 0, 16),
                'alt' => $alt,
                'caption' => $caption,
                'credit' => $credit,
                'view' => $view,
                'width' => $size[0],
                'height' => $size[1],
            ];
            if ($firstOnly) {
                break;
            }
        }
        return $images;
    } catch (Throwable $error) {
        // Never disclose paths, supplier records or malformed private-manifest contents.
        return [];
    }
}

/** Exact SKU lookup; no fallback to another variant's photograph. Escape public text when rendering. */
function instrument_media_for_product(array $family, array $product): array
{
    $sku = $product['sku'] ?? null;
    if (!is_string($sku)) {
        return [];
    }
    return instrument_media_for_family($family)[$sku] ?? [];
}
