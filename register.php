<?php
require_once __DIR__ . '/config/conn.php';
require_once __DIR__ . '/includes/functions.php';

// A user who is already signed in should return to their own dashboard.
if (!empty($_SESSION['user_id'])) {
    redirect_to(role_home());
}

$error = '';
$selectedRole = $_POST['account_type'] ?? 'customer';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $name = post_text('full_name', 120);
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = post_text('phone', 30);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = $_POST['account_type'] ?? 'customer';
    $business = post_text('business_name', 150);
    $category = post_text('supplier_category', 120);
    $location = post_text('supplier_location', 180);

    // Public registration is only for customers and suppliers.
    // Administrator accounts are created by the first-admin setup or an existing admin.
    if (!in_array($role, ['customer', 'supplier'], true)) {
        $role = 'customer';
        $selectedRole = 'customer';
    }

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '') {
        $error = 'Enter your full name, a valid email address and phone number.';
    } elseif (strlen($password) < 8) {
        $error = 'Your password must contain at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'The passwords do not match.';
    } elseif ($role === 'supplier' && ($business === '' || $category === '' || $location === '')) {
        $error = 'Supplier accounts need a business name, supply category and business location.';
    } else {
        $check = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($check, 's', $email);
        mysqli_stmt_execute($check);
        $exists = mysqli_stmt_get_result($check);
        $emailExists = mysqli_num_rows($exists) > 0;
        mysqli_stmt_close($check);

        if ($emailExists) {
            $error = 'That email address is already registered. Please log in instead.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $accountStatus = $role === 'supplier' ? 'pending' : 'active';

            mysqli_begin_transaction($conn);
            try {
                $stmt = mysqli_prepare($conn, 'INSERT INTO users (full_name, email, phone, password_hash, role, account_status) VALUES (?, ?, ?, ?, ?, ?)');
                mysqli_stmt_bind_param($stmt, 'ssssss', $name, $email, $phone, $hash, $role, $accountStatus);
                mysqli_stmt_execute($stmt);
                $userId = (int)mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);

                if ($role === 'supplier') {
                    $profile = mysqli_prepare($conn, 'INSERT INTO supplier_profiles (user_id, business_name, supplier_category, location) VALUES (?, ?, ?, ?)');
                    mysqli_stmt_bind_param($profile, 'isss', $userId, $business, $category, $location);
                    mysqli_stmt_execute($profile);
                    mysqli_stmt_close($profile);
                }

                log_activity($conn, $userId, 'Registration', ucfirst($role) . ' account registered.');
                mysqli_commit($conn);

                if ($role === 'supplier') {
                    // Suppliers cannot enter the supplier dashboard until approved.
                    $admins = mysqli_query($conn, "SELECT id FROM users WHERE role='admin' AND account_status='active'");
                    while ($a = mysqli_fetch_assoc($admins)) {
                        notify_user($conn, (int)$a['id'], 'New supplier application', $business . ' has applied to join the supplier network.', 'admin/suppliers.php');
                    }
                    $_SESSION['pending_supplier_id'] = $userId;
                    flash_message('Your supplier application has been submitted. It must be approved by EDNA FOODS management before you can access the supplier dashboard.');
                    redirect_to('supplier_application_status.php');
                }

                // Customers are active immediately, so sign them in and open their dashboard.
                session_regenerate_id(true);
                $_SESSION['user_id'] = $userId;
                $_SESSION['full_name'] = $name;
                $_SESSION['role'] = 'customer';
                unset($_SESSION['pending_supplier_id']);
                flash_message('Welcome to EDNA FOODS. Your account is ready. You can now submit your event request.');
                redirect_to('customer/dashboard.php');

            } catch (Throwable $e) {
                mysqli_rollback($conn);
                $error = 'Registration could not be completed. Please check your details and try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create an Account | EDNA FOODS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="app-body">
<div class="container auth-box">
    <a class="brand" href="index.html">
        <img class="brand-logo" src="assets/images/logo.jpg" alt="EDNA FOODS logo">
        <span class="brand-name">EDNA <strong>FOODS</strong></span>
    </a>

    <div class="app-card">
        <h1 class="page-title">Create an account</h1>
        <p class="page-subtitle">Choose whether you are registering as a customer or applying to become a supplier.</p>

        <?php if ($error !== '') { ?>
            <div class="notice error" role="alert"><?php echo h($error); ?></div>
        <?php } ?>

        <form method="post" action="register.php" id="register-form">
            <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">

            <div class="form-group">
                <label for="account_type">Account type</label>
                <select id="account_type" name="account_type">
                    <option value="customer" <?php echo $selectedRole === 'customer' ? 'selected' : ''; ?>>Customer</option>
                    <option value="supplier" <?php echo $selectedRole === 'supplier' ? 'selected' : ''; ?>>Supplier (requires approval)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="full_name">Full name / Contact person</label>
                <input id="full_name" name="full_name" required maxlength="120" value="<?php echo h($_POST['full_name'] ?? ''); ?>">
            </div>

            <div class="form-grid-app">
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" required maxlength="150" autocomplete="email" value="<?php echo h($_POST['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone number</label>
                    <input id="phone" name="phone" type="tel" required maxlength="30" autocomplete="tel" value="<?php echo h($_POST['phone'] ?? ''); ?>">
                </div>
            </div>

            <div id="supplier-fields" class="supplier-fields" <?php echo $selectedRole === 'supplier' ? '' : 'hidden'; ?>>
                <h2>Supplier business details</h2>
                <div class="form-group">
                    <label for="business_name">Business name</label>
                    <input id="business_name" name="business_name" maxlength="150" value="<?php echo h($_POST['business_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="supplier_category">What do you supply?</label>
                    <select id="supplier_category" name="supplier_category">
                        <option value="">Select category</option>
                        <?php foreach (['Rice and cereals','Chicken and poultry','Beef and goat meat','Fish','Matooke and other food','Fruits and mangoes','Vegetables','Cooking oil and groceries','Drinks','Equipment and rentals','Transport','Other'] as $cat) { ?>
                            <option value="<?php echo h($cat); ?>" <?php echo (($_POST['supplier_category'] ?? '') === $cat) ? 'selected' : ''; ?>><?php echo h($cat); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="supplier_location">Business location</label>
                    <input id="supplier_location" name="supplier_location" maxlength="180" value="<?php echo h($_POST['supplier_location'] ?? ''); ?>">
                </div>
                <p class="form-help">Supplier applications are reviewed by EDNA FOODS management. You can access your supplier dashboard after approval.</p>
            </div>

            <div class="form-grid-app">
                <div class="form-group">
                    <label for="password">Password (minimum 8 characters)</label>
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm password</label>
                    <input id="confirm_password" name="confirm_password" type="password" required minlength="8" autocomplete="new-password">
                </div>
            </div>

            <button class="app-button" type="submit">Create account</button>
        </form>

        <div class="account-help">
            <p>Already registered? <a href="login.php">Log in here</a>.</p>
            <p>EDNA FOODS administrators are created securely by the first administrator or an existing administrator. Administrator accounts cannot be created through public registration.</p>
            <a href="index1.html">Return to the EDNA FOODS website</a>
        </div>
    </div>
</div>
<script>
const accountType = document.getElementById('account_type');
const supplierFields = document.getElementById('supplier-fields');
const supplierRequiredFields = ['business_name', 'supplier_category', 'supplier_location'];

function updateSupplierFields() {
    const isSupplier = accountType.value === 'supplier';
    supplierFields.hidden = !isSupplier;
    supplierRequiredFields.forEach((id) => {
        document.getElementById(id).required = isSupplier;
    });
}
accountType.addEventListener('change', updateSupplierFields);
updateSupplierFields();
</script>
</body>
</html>