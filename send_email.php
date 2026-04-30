<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail(string $toEmail, string $toName, string $subject, string $message): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = 2;
$mail->Debugoutput = 'html';
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // CHANGE THESE
        $mail->Username = 'ashleyzichawo@gmail.com';
$mail->Password = 'abcd efgh ijkl mnop';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('ashleyzichawo@gmail.com', 'HIT Hostel Maintenance Portal');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message);

        return $mail->send();

    } catch (Exception $e) {
    echo "Email failed: " . $mail->ErrorInfo;
    error_log("Email failed: " . $mail->ErrorInfo);
    return false;
}
}