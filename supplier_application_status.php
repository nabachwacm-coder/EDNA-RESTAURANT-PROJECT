<?php
require_once __DIR__ . '/config/conn.php';
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['user_id'])) {
    redirect_to(role_home());
}

$supplier = null;
if (!empty($_SESSION['pending_supplier_id'])) {
    $supplierId = (int)$_SESSION['pending_supplier_id'];
    $stmt = mysqli_prepare($conn, "SELECT u.full_name, u.email, u.account_status, p.business_name, p.supplier_category FROM users u LEFT JOIN supplier_profiles p ON p.user_id=u.id WHERE u.id=? AND u.role='supplier' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $supplierId);
    mysqli_stmt_execute($stmt);
    $supplier = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}
$flash = flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Supplier Application Status | EDNA FOODS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="app-body">
<div class="container auth-box">
    <a class="brand" href="index.html">
        <img class="brand-logo" src="assets/images/logo.jpg" alt="EDNA FOODS logo">
        <span class="brand-name">EDNA <strong>FOODS</strong></span>
    </a>
    <section class="app-card status-card">
        <p class="eyebrow">SUPPLIER NETWORK</p>
        <h1 class="page-title">Supplier application status</h1>
        <?php if ($flash && !empty($flash['message'])) { ?>
            <div class="notice"><?php echo h($flash['message']); ?></div>
        <?php } ?>

        <?php if (!$supplier) { ?>
            <div class="notice error">We could not find a supplier application linked to this browser session. You can register again or log in if you already have an account.</div>
            <div class="action-links"><a class="app-button" href="register.php">Register</a><a class="app-button light" href="login.php">Log in</a></div>
        <?php } elseif ($supplier['account_status'] === 'pending') { ?>
            <div class="status-banner pending"><strong>Application received — awaiting approval</strong><span>Your supplier application has been saved. EDNA FOODS management will review your details.</span></div>
            <dl class="detail-list">
                <div><dt>Contact person</dt><dd><?php echo h($supplier['full_name']); ?></dd></div>
                <div><dt>Business</dt><dd><?php echo h($supplier['business_name']); ?></dd></div>
                <div><dt>Supply category</dt><dd><?php echo h($supplier['supplier_category']); ?></dd></div>
                <div><dt>Email</dt><dd><?php echo h($supplier['email']); ?></dd></div>
            </dl>
            <p class="form-help">You cannot open the supplier dashboard until your account has been approved. Return to this page after EDNA FOODS contacts you, or try logging in later.</p>
            <div class="action-links"><a class="app-button" href="supplier_application_status.php">Refresh application status</a><a class="app-button light" href="login.php">Go to login</a></div>
        <?php } elseif ($supplier['account_status'] === 'active') { ?>
            <div class="status-banner approved"><strong>Your supplier account has been approved!</strong><span>You can now log in. After login, you will be taken to your supplier dashboard.</span></div>
            <a class="app-button" href="login.php">Log in to supplier dashboard</a>
        <?php } else { ?>
            <div class="status-banner suspended"><strong>Your supplier account is not active.</strong><span>Please contact EDNA FOODS management for assistance.</span></div>
            <a class="app-button light" href="index.html">Return to website</a>
        <?php } ?>
    </section>
</div>
</body>
</html>