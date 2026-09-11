<?php
/** Structured data for the public sourcing guides. */
function sourcing_guide_schema(string $name, string $description, string $path, array $breadcrumbs): void
{
    $origin = rtrim(defined('CANONICAL_URL') ? CANONICAL_URL : 'https://sialsourcing.com', '/');
    $items = [];
    foreach ($breadcrumbs as $index => $breadcrumb) {
        $items[] = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $breadcrumb[0],
            'item' => $origin . $breadcrumb[1],
        ];
    }
    $data = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'CollectionPage',
                '@id' => $origin . $path . '#webpage',
                'url' => $origin . $path,
                'name' => $name,
                'description' => $description,
                'inLanguage' => 'en',
                'breadcrumb' => ['@id' => $origin . $path . '#breadcrumb'],
                'publisher' => ['@type' => 'Organization', 'name' => 'SialSourcing', 'url' => $origin],
            ],
            [
                '@type' => 'BreadcrumbList',
                '@id' => $origin . $path . '#breadcrumb',
                'itemListElement' => $items,
            ],
        ],
    ];
    echo '<script type="application/ld+json">' . json_encode(
        $data,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
    ) . '</script>';
}
