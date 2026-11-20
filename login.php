<?php
require_once __DIR__ . '/config/conn.php';
require_once __DIR__ . '/includes/functions.php';
if (!empty($_SESSION['user_id'])) redirect_to(role_home());
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $stmt = mysqli_prepare($conn, 'SELECT id,full_name,email,password_hash,role,account_status FROM users WHERE email=? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        $error = 'Email or password is incorrect.';
    } elseif ($user['account_status'] !== 'active') {
        if ($user['role'] === 'supplier' && $user['account_status'] === 'pending') {
            $_SESSION['pending_supplier_id'] = (int)$user['id'];
            $error = 'Your supplier application is still waiting for EDNA FOODS management approval.';
        } else {
            $error = 'This account is currently suspended. Contact Perissos management.';
        }
    }
    else {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        unset($_SESSION['pending_supplier_id']);
        log_activity($conn, (int)$user['id'], 'Login', 'User logged in.');
        redirect_to(role_home($user['role']));
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Login | EDNA EVENTS</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body class="app-body"><div class="container auth-box"><a class="brand" href="index.html"><img class="brand-logo" src="assets/images/logo.jpg" alt="EDNA logo"><span class="brand-name">EDNA <strong>FOODS</strong></span></a><div class="app-card"><h1 class="page-title">Welcome back</h1><p class="page-subtitle">Log in to your customer, administrator or supplier account.</p>
<?php $flash=flash_message(); if (!empty($flash['message'])) { ?><div class="notice"><?php echo h($flash['message']); ?></div><?php } ?>
<?php if ($error) { ?><div class="notice error" role="alert"><?php echo h($error); ?></div><?php if (!empty($_SESSION['pending_supplier_id'])) { ?><p><a class="text-link" href="supplier_application_status.php">Check your supplier application status</a></p><?php } ?><?php } ?>
<form method="post" action="login.php"><input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>"><div class="form-group"><label for="email">Email address</label><input id="email" name="email" type="email" required autocomplete="username"></div><div class="form-group"><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password"></div><p><button class="app-button" type="submit">Log in</button></p></form>
<p>New customer or supplier? <a href="register.php">Create an account</a></p><p><a href="index1.html">Return to EDNA website</a></p></div></div></body></html>