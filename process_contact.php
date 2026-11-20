<?php
require_once __DIR__ . '/config/conn.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: contact.html'); exit; }
$name=trim($_POST['full_name']??''); $email=trim($_POST['email']??''); $phone=trim($_POST['phone']??''); $subject=trim($_POST['subject']??''); $message=trim($_POST['message']??''); $website=trim($_POST['website']??'');
if ($website !== '') exit('Enquiry not accepted.');
if ($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || $subject==='' || mb_strlen($message)<10) exit('Please return to the contact page and complete the form correctly.');
$stmt=mysqli_prepare($conn,'INSERT INTO contact_messages(full_name,email,phone,subject,message) VALUES(?,?,?,?,?)');
mysqli_stmt_bind_param($stmt,'sssss',$name,$email,$phone,$subject,$message);
if(mysqli_stmt_execute($stmt)) { header('Location: contact_success.php'); exit; }
http_response_code(500); echo 'Your message could not be saved. Please try again.';
?>