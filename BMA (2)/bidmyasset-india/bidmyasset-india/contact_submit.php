<?php
require_once 'config.php';
require_once 'includes/functions.php';

// This deployment only ever serves one country, so use that directly
// rather than trusting whatever was submitted in the form — a malicious
// or malformed request could otherwise write a stray row for the wrong
// country into this site's database.
$country = get_country();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name    = trim($_POST['name'] ?? '');
$company = trim($_POST['company'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

$valid = $name !== '' && $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL);

$ok = false;
if ($valid) {
    $created = PocketBase::create('contact_submissions', [
        'country' => $country,
        'name'    => $name,
        'company' => $company,
        'phone'   => $phone,
        'email'   => $email,
        'message' => $message,
    ]);
    $ok = $created !== null;
}

header('Location: index.php?sent=' . ($ok ? '1' : '0') . '#contact');
exit;
