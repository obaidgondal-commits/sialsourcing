<?php
declare(strict_types=1);
require_once __DIR__ . '/instrument-data.php';

function knowledge_articles(bool $includeDrafts = false): array
{
    $articles = require __DIR__ . '/knowledge-content.php';
    return array_filter($articles, static function (array $article) use ($includeDrafts): bool {
        if ($includeDrafts && instrument_is_staging()) return true;
        return ($article['status'] ?? '') === 'published'
            && trim($article['reviewer'] ?? '') !== ''
            && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $article['reviewed_on'] ?? '') === 1;
    });
}

function knowledge_available(): bool
{
    return knowledge_articles(instrument_is_staging()) !== [];
}

function knowledge_search(array $articles, string $query, string $industry): array
{
    return array_filter($articles, static function (array $article) use ($query, $industry): bool {
        if ($industry !== '' && ($article['industry'] ?? '') !== $industry) return false;
        $haystack = implode(' ', [$article['title'], $article['summary'], $article['industry'], implode(' ', $article['keywords'] ?? [])]);
        foreach ($article['sections'] as $section) {
            $haystack .= ' ' . $section['title'] . ' ' . implode(' ', $section['paragraphs'] ?? []) . ' ' . implode(' ', $section['bullets'] ?? []);
        }
        return $query === '' || stripos($haystack, $query) !== false;
    });
}
