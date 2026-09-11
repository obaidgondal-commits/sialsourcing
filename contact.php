<?php
require_once __DIR__ . '/config.php';
header('Cache-Control: no-store, private');
$pageTitle = 'Request a Pakistan Sourcing Quote';
$pageDesc = 'Send your product brief, quantity and destination. Discuss sourcing from Sialkot, Wazirabad and Faisalabad with SialSourcing.';
$canonicalPath = '/contact';
$subjects = ['Dental Instruments','Surgical Instruments','Custom Soccer Balls','Activewear & Sports Uniforms','Uniforms & Tactical Wear','Sports Goods','Leather Products','Cutlery & Kitchenware','Textiles & Home Linens','Multiple Products','Other'];
$regions = ['Not sure yet','Sialkot','Wazirabad','Faisalabad','Multiple regions'];
$preselect = '';
$productQuery = is_string($_GET['product'] ?? null) ? $_GET['product'] : '';
if ($productQuery === 'home-textiles') $preselect = 'Textiles & Home Linens';
elseif ($productQuery !== '') {
    $st = db()->prepare('SELECT title FROM products WHERE slug=? AND is_active=1');
    $st->execute([$productQuery]);
    $preselect = $st->fetchColumn() ?: '';
    if ($preselect && !in_array($preselect, $subjects, true)) $subjects[] = $preselect;
}
$regionQuery = is_string($_GET['region'] ?? null) && in_array($_GET['region'], $regions, true) ? $_GET['region'] : 'Not sure yet';
$values = ['name'=>'','email'=>'','company'=>'','phone'=>'','subject'=>$preselect ?: 'Other','region'=>$regionQuery,'country'=>'','quantity'=>'','deadline'=>'','message'=>''];
$error = '';
$success = !empty($_SESSION['rfq_received']);
unset($_SESSION['rfq_received']);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $success = false;
    foreach($values as $key => $value) {
        if (isset($_POST[$key]) && !is_string($_POST[$key])) $error = 'Please enter a valid text value in each field.';
        $values[$key] = is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
    }
    $limits = ['name'=>200,'email'=>200,'company'=>200,'phone'=>50,'subject'=>200,'region'=>50,'country'=>120,'quantity'=>150,'deadline'=>100,'message'=>4000];
    if (!$error && !csrf_ok()) $error = 'Your form session expired. Please submit again.';
    foreach($limits as $key=>$limit) if (strlen($values[$key]) > $limit) $error = 'One of your entries is too long. Please shorten it and try again.';
    if (!$error && (!$values['name'] || !$values['email'] || !$values['country'] || !$values['message'])) $error = 'Please complete your name, email, destination and product requirements.';
    if (!$error && (!filter_var($values['email'], FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $values['email'].$values['subject']))) $error = 'Please enter a valid email and product category.';
    if (!$error && !in_array($values['region'],$regions,true)) $error = 'Please select a sourcing region.';
    if (!$error && !empty($_POST['website'])) {
        $_SESSION['rfq_received'] = true;
        header('Location: /contact', true, 303); exit;
    }
    if (!$error && !rate_limit('rfq:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 5, 600)) $error = 'You have sent several requests. Please wait a few minutes before trying again.';
    if (!$error) {
        $message = "Destination: {$values['country']}\nRegion: {$values['region']}\nQuantity: {$values['quantity']}\nTarget date: {$values['deadline']}\n\n{$values['message']}";
        try {
            db()->prepare('INSERT INTO contact_submissions (name,email,company,phone,subject,message) VALUES (?,?,?,?,?,?)')->execute([$values['name'],$values['email'],$values['company'],$values['phone'],$values['subject'],$message]);
        } catch (Throwable $ex) {
            error_log('RFQ storage failed.');
            $error = 'We could not save your request. Please try again or email our team.';
        }
        if (!$error) {
            try {
                send_notification(setting('contact_email','info@sialsourcing.com'), 'New sourcing brief: '.$values['subject'], "Name: {$values['name']}\nCompany: {$values['company']}\nEmail: {$values['email']}\nPhone: {$values['phone']}\n\n$message", $values['email']);
            } catch (Throwable $ex) { error_log('RFQ saved; notification requires attention.'); }
            $_SESSION['rfq_received'] = true;
            header('Location: /contact', true, 303); exit;
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="rfq-layout"><div class="rfq-intro"><p class="eyebrow"><?= $success ? 'Your brief is saved' : 'Your next sourcing project' ?></p><h1><?= $success ? 'Thank you.<br>We have your brief.' : 'Start with<br>what you need.' ?></h1><p><?= $success ? 'The team will review your requirements and follow up using the contact details you provided.' : 'Whether you have a finished specification or an early idea, tell us about your product. We will review the brief and confirm the next steps with you.' ?></p><a class="text-link" href="mailto:<?= e(setting('contact_email','info@sialsourcing.com')) ?>"><?= e(setting('contact_email','info@sialsourcing.com')) ?></a><details class="rfq-help"><summary>What happens next?</summary><ol><li>We clarify your product and destination requirements.</li><li>We assess suitable manufacturing capabilities.</li><li>We agree the quotation and sampling steps.</li></ol></details><details class="rfq-help"><summary>Already have a tech pack?</summary><p>Mention it in your brief. We will arrange a suitable way to receive your drawings, references and files.</p><a class="text-link" href="/resources">Download a sourcing brief template</a></details></div>
<div>
<?php if($success): ?><div class="form-success" role="status"><p class="eyebrow">Brief received</p><h2>Thank you for the details.</h2><p>Your request has been saved for the SialSourcing team to review. We will use the contact details you provided to follow up.</p><a class="text-link" href="/products">Continue exploring products</a></div>
<?php else: ?><form class="rfq-form" method="post" action="/contact"><h2>Tell us about your project</h2><p class="field-note">Fields marked * are required.</p><?php if($error): ?><div class="form-alert" role="alert"><?= e($error) ?></div><?php endif; ?><?= csrf_field() ?><input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
<div class="form-fields-grid">
<?php foreach([['name','Your name *','text',200,'name'],['email','Business email *','email',200,'email'],['company','Company','text',200,'organization'],['phone','Phone / WhatsApp','tel',50,'tel']] as [$key,$label,$type,$max,$auto]): ?><div class="field"><label for="<?= $key ?>"><?= $label ?></label><input id="<?= $key ?>" name="<?= $key ?>" type="<?= $type ?>" maxlength="<?= $max ?>" autocomplete="<?= $auto ?>" value="<?= e($values[$key]) ?>" <?= in_array($key,['name','email']) ? 'required' : '' ?>></div><?php endforeach; ?>
</div>
<div class="field"><label for="subject">Product category</label><select id="subject" name="subject"><?php if($values['subject'] && !in_array($values['subject'],$subjects,true)) $subjects[]=$values['subject']; ?><?php foreach($subjects as $subject): ?><option <?= $values['subject']===$subject?'selected':'' ?>><?= e($subject) ?></option><?php endforeach; ?></select></div>
<div class="form-fields-grid"><div class="field"><label for="country">Destination country & city *</label><input id="country" name="country" required maxlength="120" autocomplete="off" placeholder="e.g. Germany, Hamburg" value="<?= e($values['country']) ?>"></div><div class="field"><label for="region">Sourcing region</label><select id="region" name="region"><?php foreach($regions as $region): ?><option <?= $values['region']===$region?'selected':'' ?>><?= e($region) ?></option><?php endforeach; ?></select></div><div class="field"><label for="quantity">Estimated quantity</label><input id="quantity" name="quantity" maxlength="150" placeholder="e.g. 500 units per design" value="<?= e($values['quantity']) ?>"></div><div class="field"><label for="deadline">Target delivery date</label><input id="deadline" name="deadline" maxlength="100" placeholder="e.g. January 2027 / flexible" value="<?= e($values['deadline']) ?>"></div></div>
<div class="field"><label for="message">Product requirements *</label><textarea id="message" name="message" required maxlength="4000" rows="6" placeholder="Describe the product, materials, sizes, branding, intended use and any market requirements. Please do not include payment details or confidential documents."><?= e($values['message']) ?></textarea></div>
<p class="field-note">Your details will be used to review and respond to this enquiry. Please include only information needed for your sourcing request.</p><button class="btn btn-gold" type="submit">Send sourcing brief <?= icon('arrow-right',18) ?></button>
</form><?php endif; ?>
</div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
