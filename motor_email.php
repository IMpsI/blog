<?php
declare(strict_types=1);

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail(string $recipient, string $name, string $subject, string $html): bool
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = envValue('BLOG_SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = envValue('BLOG_SMTP_USER');
        $mail->Password = envValue('BLOG_SMTP_PASS');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) envValue('BLOG_SMTP_PORT', '587');
        $mail->CharSet = 'UTF-8';
        $mail->setFrom(envValue('BLOG_SMTP_FROM', $mail->Username), APP_NAME);
        $mail->addAddress($recipient, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->AltBody = strip_tags($html);
        $mail->send();
        return true;
    } catch (Exception $exception) {
        error_log('Falha no envio de e-mail: ' . $exception->getMessage());
        return false;
    }
}
