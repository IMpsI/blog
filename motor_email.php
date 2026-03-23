<?php
// Carrega os ficheiros do PHPMailer que acabámos de transferir
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Função para enviar e-mails de forma centralizada
 * * @param string $para_email O e-mail de destino (do leitor)
 * @param string $para_nome O nome do leitor
 * @param string $assunto O assunto do e-mail
 * @param string $mensagem_html O corpo do e-mail (aceita tags HTML)
 * @return bool Retorna true se enviou com sucesso, ou false em caso de erro
 */
function enviarEmail($para_email, $para_nome, $assunto, $mensagem_html)
{
    $mail = new PHPMailer(true);

    try {
        // Configurações do Servidor SMTP (Gmail)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;

        // COLOQUE AQUI O SEU GMAIL E A PALAVRA-PASSE DE APLICAÇÃO (16 LETRAS)
        $mail->Username   = 'oblogbotrec@gmail.com';
        $mail->Password   = 'oums jybe sapw goti';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Definir a codificação para evitar problemas com acentos (ç, ã, á)
        $mail->CharSet = 'UTF-8';

        // Remetente e Destinatário
        $mail->setFrom('oblogbotrec@gmail.com', 'Equipa do Blog');
        $mail->addAddress($para_email, $para_nome);

        // Conteúdo do E-mail
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $mensagem_html;

        // Texto alternativo caso o cliente de e-mail não suporte HTML
        $mail->AltBody = strip_tags($mensagem_html);

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Se quiser ver o erro exato no futuro, pode imprimir $mail->ErrorInfo
        return false;
    }
}
