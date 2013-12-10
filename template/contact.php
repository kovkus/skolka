<?php
$mailTo = 'your_email_here';
$name = htmlspecialchars($_POST['cform_name']);
$mailFrom = htmlspecialchars($_POST['cform_email']);
$subject = 'Message from your website';
$message_text = htmlspecialchars($_POST['cform_message']);

$message =  'From: '.$name.'; Email: '.$mailFrom.' ; Message: '.$message_text;

mail($mailTo, $subject, $message);
?>