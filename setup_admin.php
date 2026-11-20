<?php
require_once __DIR__ . '/config/conn.php';
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['user_id'])) {
    redirect_to(role_home());
}

$count = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='admin'");
$hasAdmin = (int)mysqli_fetch_assoc($count)['total'] > 0;
$error = '';

if (!$hasAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $name = post_text('full_name', 120);
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = post_text('phone', 30);
    $password = $_POST['password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '') {
        $error = 'Complete all fields correctly.';
    } elseif (strlen($password) < 12) {
        $error = 'Use at least 12 characters for the first administrator password.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, phone, password_hash, role, account_status) VALUES (?, ?, ?, ?, 'admin', 'active')");
        mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $phone, $hash);
        if (mysqli_stmt_execute($stmt)) {
            $adminId = (int)mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $adminId;
            $_SESSION['full_name'] = $name;
            $_SESSION['role'] = 'admin';
            log_activity($conn, $adminId, 'Admin setup', 'Initial administrator created.');
            flash_message('Administrator account created successfully. You are now in the administrator dashboard. For security, delete setup_admin.php from the project folder now.');
            redirect_to('admin/dashboard.php');
        } else {
            $error = 'Could not create administrator. Check whether the email is already used.';
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>First Administrator Setup | EDNA FOODS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="app-body">
<div class="container auth-box">
    <div class="app-card">
        <a class="brand" href="index.html"><img class="brand-logo" src="assets/images/edna-logo.png" alt="EDNA FOODS logo"><span class="brand-name">EDNA <strong>FOODS</strong></span></a>
        <h1 class="page-title">First administrator setup</h1>
        <?php if ($hasAdmin) { ?>
            <div class="notice">An administrator already exists. Administrator accounts are not created through public registration.</div>
            <p>Log in using an existing administrator account. If you need another administrator, ask an existing administrator to open User Accounts.</p>
            <a class="app-button" href="login.php">Go to administrator login</a>
            <p class="form-help">For security, delete setup_admin.php after the first administrator has been created.</p>
        <?php } else { ?>
            <p class="page-subtitle">Create the first EDNA FOODS administrator. This one-time setup requires a password of at least 12 characters.</p>
            <?php if ($error !== '') { ?><div class="notice error" role="alert"><?php echo h($error); ?></div><?php } ?>
            <form method="post" action="setup_admin.php">
                <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
                <div class="form-group"><label for="full_name">Full name</label><input id="full_name" name="full_name" maxlength="120" required></div>
                <div class="form-group"><label for="email">Email address</label><input id="email" name="email" type="email" maxlength="150" required></div>
                <div class="form-group"><label for="phone">Phone number</label><input id="phone" name="phone" type="tel" maxlength="30" required></div>
                <div class="form-group"><label for="password">Password (minimum 12 characters)</label><input id="password" name="password" type="password" minlength="12" required></div>
                <button class="app-button" type="submit">Create administrator and open dashboard</button>
            </form>
        <?php } ?>
    </div>
</div>
</body>
</html>