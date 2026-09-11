<?php
/** Create a new, sanitized, local-only preview. Never imports or modifies a live database. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli' || !in_array('--local', $argv, true)) {
    http_response_code(403);
    exit("Usage: php scripts/setup-preview.php --local\n");
}
if (!extension_loaded('pdo_sqlite')) exit("Enable PHP pdo_sqlite first.\n");
umask(0077);
$root = dirname(__DIR__);
$private = $root . '/.preview';
$config = $root . '/config.php';
if (is_file($config) && !str_contains(file_get_contents($config), "define('SIAL_LOCAL_PREVIEW_CONFIG', true)")) {
    fwrite(STDERR, "Existing config.php is not the generated preview configuration; nothing was changed. Use a separate checkout for preview.\n");
    exit(1);
}
if (!is_dir($private) && !mkdir($private, 0700, true)) exit("Cannot create preview directory.\n");
$database = $private . '/preview.sqlite';
if (!is_file($database)) {
    $pdo = new PDO('sqlite:' . $database, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $schema = file_get_contents($root . '/database/schema.sql');
    // Only the checked-in fresh-install schema is translated; no live SQL is read.
    $schema = str_replace(
        ['INTEGER PRIMARY KEY AUTO_INCREMENT', ' ON UPDATE CURRENT_TIMESTAMP', ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'],
        ['INTEGER PRIMARY KEY AUTOINCREMENT', '', ''],
        $schema
    );
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->beginTransaction();
    try {
        $pdo->exec($schema);
        $insert = static function (string $table, array $row) use ($pdo): void {
            $columns = implode(',', array_keys($row));
            $marks = implode(',', array_fill(0, count($row), '?'));
            $pdo->prepare("INSERT INTO $table ($columns) VALUES ($marks)")->execute(array_values($row));
        };
        foreach ([
            'site_name' => 'SialSourcing',
            'site_tagline' => 'Product sourcing across Sialkot, Wazirabad and Faisalabad, Pakistan.',
            'contact_email' => 'info@sialsourcing.com',
            'contact_phone' => '',
            'contact_address' => 'Sialkot, Pakistan',
        ] as $key => $value) $insert('settings', ['setting_key' => $key, 'setting_value' => $value]);

        $categories = [
            ['dental-instruments', 'Dental Instruments', 'Sialkot', 'Discuss dental instrument patterns, intended use, finish and destination-market documentation before requesting samples.', 'tool', 10],
            ['surgical-instruments', 'Surgical Instruments', 'Sialkot', 'Build a sourcing brief around instrument specifications, material, finish, traceability and buyer-specific documentation.', 'activity', 20],
            ['sports-goods', 'Sports Goods', 'Sialkot', 'Explore sports equipment and accessories with construction, performance, branding and packing defined in your brief.', 'target', 30],
            ['activewear-sports-uniforms', 'Activewear & Sports Uniforms', 'Sialkot and Faisalabad', 'Specify performance fabrics, garment measurements, artwork, colour references and testing requirements for your teamwear or activewear project.', 'shirt', 40],
            ['uniforms-tactical-wear', 'Uniforms & Workwear', 'Sialkot and Faisalabad', 'Source uniforms and workwear to a clear specification covering fabric, fit, construction, trims and destination requirements.', 'shield', 50],
            ['leather-goods', 'Leather Goods', 'Sialkot', 'Define leather type, construction, hardware, finish and packaging for your leather goods sourcing project.', 'briefcase', 60],
            ['cutlery', 'Cutlery & Kitchenware', 'Wazirabad', 'Discuss blades, cutlery and kitchen tools with steel grade, hardness, handle material, finish and food-contact requirements specified.', 'tool', 70],
            ['custom-soccer-balls', 'Custom Soccer Balls', 'Sialkot', 'Specify ball size, panel construction, cover material, bladder, artwork and intended use before sampling.', 'target', 80],
            ['promotional-soccer-balls', 'Promotional Soccer Balls', 'Sialkot', 'Plan branded promotional balls with artwork, colour references, size, packaging and campaign dates.', 'target', 110],
            ['medical-scrubs', 'Medical Scrubs', 'Faisalabad and Sialkot', 'Define scrub fabric, fit, size grading, colours and care requirements with a garment specification.', 'shirt', 120],
            ['security-guard-uniforms', 'Security Guard Uniforms', 'Sialkot and Faisalabad', 'Prepare uniform specifications for fabric, fit, insignia, trims and the conditions in which the garments will be worn.', 'shield', 130],
        ];
        foreach ($categories as [$slug, $title, $city, $description, $icon, $order]) {
            $insert('products', [
                'title' => $title, 'slug' => $slug, 'description' => $description,
                'details' => '<p>' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</p><p>Share your specifications, target quantity and delivery market so the sourcing scope can be reviewed. Product availability, minimum quantities and lead times require supplier confirmation.</p>',
                'hero_headline' => $title . ' sourcing from Pakistan', 'meta_desc' => $description,
                'icon' => $icon, 'sort_order' => $order, 'certifications' => '',
                'moq' => 'Confirmed after specification review', 'lead_time' => 'Confirmed with the selected supplier',
                'materials' => 'Specify the material, grade and finish in your sourcing brief.',
            ]);
            $productId = (int) $pdo->lastInsertId();
            $insert('product_faqs', [
                'product_id' => $productId, 'question' => 'What should I include in an RFQ for ' . strtolower($title) . '?',
                'answer' => 'Include specifications or reference drawings, target quantity, delivery country, branding and packaging requirements, and any required documentation. Samples and commercial terms are confirmed against the final brief.',
            ]);
        }
        foreach ([
            ['Supplier research', 'A sourcing brief helps narrow the supplier search to the capabilities your product requires.', 'search'],
            ['Sampling & specification', 'Agree on the materials, dimensions, artwork and acceptance criteria before approving a sample.', 'file-text'],
            ['Quality coordination', 'Set inspection checkpoints and reporting requirements around your product and order.', 'check-circle'],
            ['Export coordination', 'Identify packing, documentation and freight requirements for your destination.', 'truck'],
        ] as $i => [$title, $description, $icon]) $insert('solutions', [
            'title' => $title, 'short_desc' => $description, 'description' => $description, 'icon' => $icon, 'sort_order' => $i,
        ]);
        foreach ([
            ['Which cities do you source from?', 'The sourcing focus covers Sialkot, Wazirabad and Faisalabad. Share the product specification so the relevant manufacturing capabilities can be reviewed.'],
            ['What is the minimum order quantity?', 'Minimum quantities vary by product, materials, customisation and supplier. They are confirmed with a quotation after reviewing the brief.'],
            ['Can you help with custom branding?', 'Include artwork, colour references and packing requirements in your brief. Feasibility and costs need confirmation for the selected product and supplier.'],
            ['How are quality requirements agreed?', 'Document specifications, sample approval and inspection acceptance criteria before production. Any testing or certification needs must be agreed for the product and destination.'],
            ['What information is needed for a quote?', 'Provide product details, target quantity, destination, packaging and required timing. Drawings and reference photos help define a comparable specification.'],
        ] as $i => [$question, $answer]) $insert('faqs', ['question' => $question, 'answer' => $answer, 'sort_order' => $i]);
        $insert('lab_tests', [
            'title' => 'Specification review', 'category' => 'QC Process', 'description' => 'Agree which dimensions, materials, workmanship and sampling checks are appropriate for the order.',
            'standards' => 'Buyer requirements; testing scope to be confirmed',
        ]);
        // Functional placeholder, not a claim about an identified employee.
        $insert('team_members', [
            'name' => 'Sourcing coordination', 'role' => 'Local preview placeholder',
            'bio' => 'Replace this demonstration entry with an approved team profile before launch.', 'location' => 'Pakistan',
        ]);
        $pdo->commit();
    } catch (Throwable $error) {
        $pdo->rollBack();
        $pdo = null;
        unlink($database);
        throw $error;
    }
    chmod($database, 0600);
    echo "Created sanitized local database. No admin account was created.\n";
} else {
    echo "Existing preview database preserved.\n";
}
if (!is_file($config)) {
    $contents = <<<'PHP'
<?php
// Generated by scripts/setup-preview.php. LOCAL ONLY. Never upload this file.
define('SIAL_LOCAL_PREVIEW_CONFIG', true);
putenv('SIAL_ENV=local');
putenv('SIAL_SITE_URL=http://127.0.0.1:8765');
putenv('SIAL_CANONICAL_URL=https://sialsourcing.com');
putenv('SIAL_DB_DRIVER=sqlite');
putenv('SIAL_PRIVATE_PATH=' . __DIR__ . '/.preview');
putenv('SIAL_DB_PATH=' . __DIR__ . '/.preview/preview.sqlite');
putenv('SIAL_MAIL_ENABLED=0');
require __DIR__ . '/config.example.php';
PHP;
    file_put_contents($config, $contents . "\n", LOCK_EX);
    chmod($config, 0600);
}
echo "Preview: php -d disable_functions=mail -S 127.0.0.1:8765 scripts/preview-router.php\n";
