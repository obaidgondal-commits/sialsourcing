<?php
$pageTitle     = 'Request a QC Inspection in Pakistan — AQL 2.5 Pre-Shipment Inspection Service';
$pageDesc      = 'Third-party quality inspection at Pakistani factories: pre-production checks, in-line inspection, AQL 2.5 pre-shipment inspection with photographic report before you pay or ship.';
$canonicalPath = '/qc-inspection-request';
require_once __DIR__ . '/includes/header.php';

$services = ['Pre-shipment inspection (AQL 2.5)', 'In-line (during production) inspection', 'Pre-production check', 'Container loading supervision', 'Factory audit', 'Not sure — advise me'];

$success = false;
$error   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $factory = trim($_POST['factory'] ?? '');
    $product = trim($_POST['product'] ?? '');
    $service = trim($_POST['service'] ?? '');
    $details = trim($_POST['details'] ?? '');

    if (!empty($_POST['website'])) {
        $success = true; // honeypot
    } elseif (!csrf_ok()) {
        $error = 'Your session expired — please resubmit the form.';
    } elseif (!$name || !$email || !$factory || !$product) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($details) > 5000) {
        $error = 'Details are too long — please shorten and resubmit.';
    } else {
        $message = "QC INSPECTION REQUEST\n\nService: $service\nFactory (name & city): $factory\nProduct & order: $product\n\nDetails:\n$details";
        db()->prepare("INSERT INTO contact_submissions (name,email,company,phone,subject,message) VALUES (?,?,?,?,?,?)")
            ->execute([$name,$email,$company,$phone,'QC Inspection Request',$message]);
        $to       = setting('contact_email', 'info@sialsourcing.com');
        $safeMail = str_replace(["\r", "\n"], '', $email);
        @mail($to, 'New QC Inspection Request', "From: $name <$safeMail>\nCompany: $company\nPhone: $phone\n\n$message",
              "From: SialSourcing Website <no-reply@sialsourcing.com>\r\nReply-To: $safeMail");
        $success = true;
    }
}
?>

<div style="padding-top:100px;min-height:100vh;background:var(--cream);">
  <div class="container contact-grid">

    <div class="reveal-left">
      <div class="section-label">Third-Party QC</div>
      <h1 style="font-size:clamp(1.9rem,3.5vw,2.7rem);margin-bottom:1rem;">Inspect Before You Pay or Ship</h1>
      <p style="margin-bottom:1.5rem;">Already found a factory in Pakistan yourself? Our inspectors check your order on the ground — so you know what's in the cartons before your money or goods move.</p>
      <ul style="list-style:none;display:flex;flex-direction:column;gap:0.8rem;margin-bottom:2rem;">
        <?php foreach ([
          'AQL 2.5 statistical sampling with full photographic report',
          'Measurements, materials, and workmanship checked against your spec',
          'Clear release / hold recommendation from our QC manager',
          'Reports delivered within 24 hours of inspection',
        ] as $pt): ?>
        <li style="display:flex;gap:0.6rem;align-items:flex-start;font-size:0.92rem;color:var(--text);">
          <span style="color:var(--gold);flex-shrink:0;margin-top:2px;"><?= icon('check-circle', 17) ?></span> <?= $pt ?>
        </li>
        <?php endforeach; ?>
      </ul>
      <p style="font-size:0.85rem;color:var(--muted);">Want the paper version for your records? <a href="/assets/docs/QC-Inspection-Request-Form.pdf" style="color:var(--gold);font-weight:600;" download>Download the printable form</a> — or see <a href="/lab-qc" style="color:var(--gold);font-weight:600;">how our QC process works</a>.</p>
    </div>

    <div class="reveal-right">
      <?php if ($success): ?>
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:var(--radius);padding:2rem;text-align:center;">
          <div style="color:#16a34a;margin-bottom:1rem;"><?= icon('check-circle', 44) ?></div>
          <h3 style="color:#166534;margin-bottom:0.5rem;">Request Received!</h3>
          <p style="color:#16a34a;">Our QC team will confirm availability and a quote within 24 hours.</p>
        </div>
      <?php else: ?>
        <div style="background:var(--white);border-radius:var(--radius);padding:2.5rem;box-shadow:var(--shadow-md);border:1px solid var(--cream-dark);">
          <?php if ($error): ?>
            <div style="background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;border-radius:8px;padding:0.75rem 1rem;margin-bottom:1.25rem;font-size:0.88rem;"><?= e($error) ?></div>
          <?php endif; ?>
          <form method="post" action="/qc-inspection-request">
            <?= csrf_field() ?>
            <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
            <div class="form-fields-grid">
              <div class="field"><label class="field-label">Your Name *</label><input class="field-input" type="text" name="name" required maxlength="200" value="<?= e($_POST['name'] ?? '') ?>"></div>
              <div class="field"><label class="field-label">Email *</label><input class="field-input" type="email" name="email" required maxlength="200" value="<?= e($_POST['email'] ?? '') ?>"></div>
              <div class="field"><label class="field-label">Company</label><input class="field-input" type="text" name="company" maxlength="200" value="<?= e($_POST['company'] ?? '') ?>"></div>
              <div class="field"><label class="field-label">Phone / WhatsApp</label><input class="field-input" type="text" name="phone" maxlength="50" value="<?= e($_POST['phone'] ?? '') ?>"></div>
            </div>
            <div class="field" style="margin-bottom:1rem;"><label class="field-label">Inspection Needed</label>
              <select class="field-input" name="service" style="background:#fff;">
                <?php foreach ($services as $s): ?><option <?= ($_POST['service'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="field" style="margin-bottom:1rem;"><label class="field-label">Factory Name &amp; City (Pakistan) *</label><input class="field-input" type="text" name="factory" required maxlength="300" placeholder="e.g. ABC Industries, Sialkot" value="<?= e($_POST['factory'] ?? '') ?>"></div>
            <div class="field" style="margin-bottom:1rem;"><label class="field-label">Product &amp; Order Size *</label><input class="field-input" type="text" name="product" required maxlength="300" placeholder="e.g. 5,000 size-5 soccer balls, PO #1042" value="<?= e($_POST['product'] ?? '') ?>"></div>
            <div class="field" style="margin-bottom:1.5rem;"><label class="field-label">Dates, Specs &amp; Concerns</label><textarea class="field-input" name="details" rows="4" maxlength="5000" placeholder="Requested inspection dates, approved sample availability, specific concerns..."><?= e($_POST['details'] ?? '') ?></textarea></div>
            <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;">Request Inspection <?= icon('send', 17) ?></button>
          </form>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
