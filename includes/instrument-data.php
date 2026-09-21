<?php
/** Public instrument facts only; the existing CMS keeps its own database. */

function instrument_is_staging(): bool
{
    $value = defined('SIAL_STAGING') ? constant('SIAL_STAGING') : getenv('SIAL_STAGING');
    return $value === true || $value === 1 || (is_string($value) && in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true));
}

function instrument_text(mixed $value, int $limit = 5000): string
{
    if (!is_string($value) || strlen($value) > $limit || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
        throw new UnexpectedValueException('Invalid instrument text.');
    }
    return $value;
}

function instrument_catalogue(): array
{
    $empty = ['mode' => 'published', 'families' => []];
    $configured = getenv('SIAL_CATALOGUE_FILE');
    if ($configured === false || $configured === '') {
        return $empty;
    }
    try {
        $path = realpath($configured);
        if ($configured[0] !== '/' || $path === false || !is_file($path) || filesize($path) > 20_000_000) {
            throw new UnexpectedValueException('Invalid private catalogue path.');
        }
        foreach ([dirname(__DIR__), $_SERVER['DOCUMENT_ROOT'] ?? ''] as $root) {
            $root = $root !== '' ? realpath($root) : false;
            if ($root !== false && ($path === $root || str_starts_with($path, rtrim($root, '/') . '/'))) {
                throw new UnexpectedValueException('Catalogue must be outside the public document root.');
            }
        }
        $contents = @file_get_contents($path);
        if (!is_string($contents)) {
            throw new UnexpectedValueException('Private catalogue is unreadable.');
        }
        $data = json_decode($contents, true, 64, JSON_THROW_ON_ERROR);
        if (!is_array($data) || ($data['schema_version'] ?? null) !== 1 ||
            !in_array($data['mode'] ?? null, ['published', 'staging-preview'], true) ||
            !is_array($data['families'] ?? null) || !array_is_list($data['families'])) {
            throw new UnexpectedValueException('Invalid catalogue format.');
        }
        if ($data['mode'] === 'staging-preview' && !instrument_is_staging()) {
            return $empty;
        }
        $families = [];
        $seenFamilies = $seenPaths = $seenSkus = [];
        foreach ($data['families'] as $rawFamily) {
            if (!is_array($rawFamily) || !is_string($rawFamily['code'] ?? null) ||
                !preg_match('/^SS-(DEN|ORT|SUR|PLA|VET)-([A-Z0-9]{2,8})-[0-9]{4}$/D', $rawFamily['code'], $identity) ||
                !is_string($rawFamily['slug'] ?? null) ||
                !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $rawFamily['slug']) ||
                strlen($rawFamily['slug']) > 120 ||
                !is_array($rawFamily['products'] ?? null) || !array_is_list($rawFamily['products'])) {
                throw new UnexpectedValueException('Invalid catalogue family.');
            }
            $family = [];
            foreach (['code', 'slug', 'name', 'discipline', 'group', 'pattern', 'description'] as $field) {
                $family[$field] = instrument_text($rawFamily[$field] ?? '', $field === 'description' ? 10000 : 255);
            }
            if ($family['name'] === '' || $family['group'] === '') {
                throw new UnexpectedValueException('Missing family identity.');
            }
            $family['base_path'] = in_array($identity[1], ['DEN', 'ORT'], true) ? 'dental-instruments' : 'surgical-instruments';
            $family['group_slug'] = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($family['group'])), '-');
            if ($family['group_slug'] === '') {
                throw new UnexpectedValueException('Invalid instrument group.');
            }
            $family['group_url'] = '/' . $family['base_path'] . '/' . $family['group_slug'];
            $family['url'] = $family['group_url'] . '/' . $family['slug'];
            if (isset($seenFamilies[$family['code']]) || isset($seenPaths[$family['url']])) {
                throw new UnexpectedValueException('Duplicate family identity.');
            }
            $seenFamilies[$family['code']] = $seenPaths[$family['url']] = true;
            $family['products'] = [];
            foreach ($rawFamily['products'] as $rawProduct) {
                if (!is_array($rawProduct) || !is_string($rawProduct['sku'] ?? null) ||
                    !preg_match('/^[A-Z0-9]+(?:-[A-Z0-9]+)*$/D', $rawProduct['sku']) ||
                    strlen($rawProduct['sku']) > 40 || isset($seenSkus[$rawProduct['sku']])) {
                    throw new UnexpectedValueException('Invalid or duplicate instrument reference.');
                }
                $seenSkus[$rawProduct['sku']] = true;
                $status = $rawProduct['review_status'] ?? '';
                if (!in_array($status, ['verified', 'pending'], true)) {
                    throw new UnexpectedValueException('Invalid instrument review status.');
                }
                if ($data['mode'] === 'published' && $status !== 'verified') {
                    continue;
                }
                $product = ['sku' => $rawProduct['sku'], 'review_status' => $status];
                foreach (['description', 'material'] as $field) {
                    $product[$field] = instrument_text($rawProduct[$field] ?? '', $field === 'description' ? 1000 : 120);
                }
                if (!is_array($rawProduct['attributes'] ?? null) || !array_is_list($rawProduct['attributes'])) {
                    throw new UnexpectedValueException('Invalid instrument attributes.');
                }
                $product['attributes'] = [];
                $attributeCodes = [];
                foreach ($rawProduct['attributes'] as $rawAttribute) {
                    if (!is_array($rawAttribute) || !is_string($rawAttribute['code'] ?? null) ||
                        !preg_match('/^[a-z][a-z0-9_]*$/D', $rawAttribute['code']) ||
                        isset($attributeCodes[$rawAttribute['code']])) {
                        throw new UnexpectedValueException('Invalid instrument attribute identity.');
                    }
                    $attributeCodes[$rawAttribute['code']] = true;
                    $attribute = ['code' => instrument_text($rawAttribute['code'], 40)];
                    foreach (['label', 'value', 'unit'] as $field) {
                        $value = $rawAttribute[$field] ?? '';
                        if (is_int($value) || is_float($value)) {
                            $value = (string) $value;
                        }
                        $attribute[$field] = instrument_text($value, 1000);
                    }
                    $product['attributes'][] = $attribute;
                }
                $family['products'][] = $product;
            }
            if ($family['products'] !== []) {
                $families[] = $family;
            }
        }
        return ['mode' => $data['mode'], 'families' => $families];
    } catch (Throwable $error) {
        // Never expose private paths, file contents, or credentials to visitors.
        error_log('Instrument catalogue unavailable: invalid private snapshot.');
        return $empty;
    }
}

function instrument_family(string $codeOrSlug, ?array $catalogue = null): ?array
{
    foreach (($catalogue ?? instrument_catalogue())['families'] as $family) {
        if ($family['code'] === $codeOrSlug || $family['slug'] === $codeOrSlug) {
            return $family;
        }
    }
    return null;
}

function instrument_selected_products(mixed $familyCode, mixed $skus, ?array $catalogue = null): array
{
    if (!is_string($familyCode) || !is_array($skus) || !array_is_list($skus) || count($skus) < 1 || count($skus) > 100) {
        throw new InvalidArgumentException('Choose one or more instrument variants from a single family.');
    }
    $family = instrument_family($familyCode, $catalogue);
    if ($family === null || $family['code'] !== $familyCode) {
        throw new InvalidArgumentException('The selected instrument family is unavailable.');
    }
    $available = array_column($family['products'], null, 'sku');
    $selected = [];
    foreach ($skus as $sku) {
        if (!is_string($sku) || !isset($available[$sku])) {
            throw new InvalidArgumentException('A selected instrument reference does not belong to this family. Please select the variants again.');
        }
        $selected[$sku] = $available[$sku];
    }
    return ['family' => $family, 'products' => array_values($selected), 'skus' => array_keys($selected)];
}

function instrument_enquiry_prefix(array $selection): string
{
    $lines = ['Instrument enquiry', 'Family: ' . $selection['family']['name'] . ' (' . $selection['family']['code'] . ')', 'Selected catalogue references:'];
    foreach ($selection['products'] as $product) {
        $lines[] = '- ' . $product['sku'] . ' — ' . $product['description'];
    }
    return implode("\n", $lines) . "\n\n";
}
