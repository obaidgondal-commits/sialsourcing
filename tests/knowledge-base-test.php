<?php
declare(strict_types=1);
require __DIR__ . '/../includes/knowledge-base.php';
$checks = 0;
function kb_check(bool $value, string $label): void {
    global $checks;
    if (!$value) throw new RuntimeException($label);
    $checks++;
}
putenv('SIAL_STAGING=0');
kb_check(knowledge_articles() === [], 'Drafts excluded from production');
kb_check(knowledge_articles(true) === [], 'Caller cannot override trusted staging configuration');
kb_check(!knowledge_available(), 'No draft links in production resources');
putenv('SIAL_STAGING=1');
$articles = knowledge_articles(true);
kb_check(count($articles) === 3, 'Three staging drafts');
kb_check(knowledge_articles() === [], 'Sitemap excludes drafts even on staging');
foreach ($articles as $slug => $article) {
    kb_check(preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug) === 1, 'Valid route');
    kb_check($article['status'] === 'draft' && $article['reviewer'] === '' && $article['reviewed_on'] === '', 'No fabricated approval');
    kb_check(count($article['sections']) >= 3 && $article['sources'] !== [], 'Substantive sections and sources');
    foreach ($article['sections'] as $section) {
        foreach ($section['sources'] ?? [] as $id) kb_check(isset($article['sources'][$id]), 'Every citation resolves');
    }
    foreach ($article['sources'] as $source) kb_check(str_starts_with($source['url'], 'https://') && filter_var($source['url'], FILTER_VALIDATE_URL) !== false, 'HTTPS source URL');
    foreach ($article['related'] as $link) kb_check(preg_match('#^/[a-z0-9/-]+$#D', $link['path']) === 1, 'Internal related links');
}
kb_check(count(knowledge_search($articles, 'football', 'Sports Goods')) === 1, 'Search and industry combined');
kb_check(knowledge_search($articles, 'football', 'Surgical & Dental') === [], 'Cross-industry filter excludes article');
kb_check(knowledge_search($articles, 'not-a-real-term-xyz', '') === [], 'No false search results');
echo "$checks knowledge-base checks passed.\n";
