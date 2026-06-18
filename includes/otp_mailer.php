<?php

const APEX_SMTP_HOST = 'smtp.gmail.com';
const APEX_SMTP_PORT = 587;
const APEX_SMTP_USERNAME = 'apex26gear@gmail.com';
const APEX_SMTP_FROM_NAME = 'ApeX Gear';
const APEX_SMTP_PASSWORD = 'cvdj pjug mxou roqp';

function sendRegistrationOtpEmail(string $recipientEmail, string $recipientName, string $otp): void
{
    $smtpPassword = getenv('APEX_SMTP_PASSWORD') ?: APEX_SMTP_PASSWORD;
    if (!$smtpPassword) {
        throw new RuntimeException('Email service is not configured. Set the APEX_SMTP_PASSWORD environment variable or constant.');
    }

    $logoPath = dirname(__DIR__) . '/assets/images/Apex_Mail.png';
    if (!is_file($logoPath)) {
        throw new RuntimeException('The ApeX Gear email logo could not be found.');
    }

    $safeName = htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8');
    $spacedOtp = substr($otp, 0, 3) . ' ' . substr($otp, 3);
    $boundary = 'apex_' . bin2hex(random_bytes(12));
    $logoCid = 'apex-logo';
    $subject = 'Your ApeX Gear verification code';

    $html = '<!doctype html><html><body style="margin:0;background:#f2f0ef;font-family:Arial,sans-serif;color:#102033;">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f2f0ef;padding:32px 12px;"><tr><td align="center">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:630px;background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 12px 35px rgba(12,31,52,.10);">'
        . '<tr><td style="height:7px;background:#00c2ff;"></td></tr>'
        . '<tr><td style="padding:34px 42px 10px;"><img src="cid:' . $logoCid . '" width="76" alt="ApeX Gear" style="display:block;width:76px;height:auto;"></td></tr>'
        . '<tr><td style="padding:16px 42px 36px;"><h1 style="margin:0 0 14px;font-size:27px;line-height:1.25;color:#071828;">Verify your email</h1>'
        . '<p style="margin:0 0 18px;font-size:16px;line-height:1.6;color:#506174;">Hi ' . $safeName . ', use this one-time code to finish creating your ApeX Gear account:</p>'
        . '<div style="margin:26px 0;text-align:center;"><span style="display:inline-block;padding:18px 28px;border-radius:14px;background:#eafaff;border:1px solid #bcefff;color:#071828;font-size:34px;font-weight:700;letter-spacing:7px;">' . $spacedOtp . '</span></div>'
        . '<p style="margin:0;font-size:15px;line-height:1.65;color:#506174;">This code expires in <strong style="color:#071828;">10 minutes</strong> and can only be used once. Never share it with anyone.</p>'
        . '<hr style="border:0;border-top:1px solid #e3eaf1;margin:30px 0 24px;">'
        . '<p style="margin:0;font-size:13px;line-height:1.6;color:#7b8998;">If you did not request this code, you can safely ignore this email.<br>ApeX Gear Account Security</p>'
        . '</td></tr></table></td></tr></table></body></html>';

    $plain = "Hi {$recipientName},\r\n\r\nYour ApeX Gear verification code is {$otp}.\r\n"
        . "It expires in 10 minutes and can only be used once.\r\n\r\n"
        . "If you did not request this code, you can ignore this email.";

    $headers = [
        'From: ' . APEX_SMTP_FROM_NAME . ' <' . APEX_SMTP_USERNAME . '>',
        'To: <' . $recipientEmail . '>',
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: multipart/related; boundary="' . $boundary . '"',
        'Date: ' . date(DATE_RFC2822),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@apexgear.local>',
    ];

    $message = implode("\r\n", $headers) . "\r\n\r\n"
        . '--' . $boundary . "\r\n"
        . "Content-Type: multipart/alternative; boundary=\"{$boundary}_alt\"\r\n\r\n"
        . '--' . $boundary . "_alt\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n"
        . quoted_printable_encode($plain) . "\r\n"
        . '--' . $boundary . "_alt\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n"
        . quoted_printable_encode($html) . "\r\n"
        . '--' . $boundary . "_alt--\r\n"
        . '--' . $boundary . "\r\n"
        . "Content-Type: image/png; name=\"ApeX Logo.png\"\r\nContent-Transfer-Encoding: base64\r\n"
        . "Content-ID: <{$logoCid}>\r\nContent-Disposition: inline; filename=\"ApeX Logo.png\"\r\n\r\n"
        . chunk_split(base64_encode((string) file_get_contents($logoPath))) . "\r\n"
        . '--' . $boundary . "--\r\n";

    smtpSend($recipientEmail, $message, $smtpPassword);
}

function sendContactMessageEmail(string $senderName, string $senderEmail, string $contactSubject, string $contactMessage): void
{
    $smtpPassword = getenv('APEX_SMTP_PASSWORD') ?: APEX_SMTP_PASSWORD;
    if (!$smtpPassword) {
        throw new RuntimeException('Email service is not configured. Set the APEX_SMTP_PASSWORD environment variable or constant.');
    }

    $logoPath = dirname(__DIR__) . '/assets/images/Apex_Mail.png';
    if (!is_file($logoPath)) {
        throw new RuntimeException('The ApeX Gear email logo could not be found.');
    }

    $recipientEmail = APEX_SMTP_USERNAME;
    $safeName = htmlspecialchars($senderName, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($senderEmail, ENT_QUOTES, 'UTF-8');
    $safeSubject = htmlspecialchars($contactSubject, ENT_QUOTES, 'UTF-8');
    $safeMessage = nl2br(htmlspecialchars($contactMessage, ENT_QUOTES, 'UTF-8'));
    $submittedAt = date('M d, Y h:i A');
    $boundary = 'apex_' . bin2hex(random_bytes(12));
    $logoCid = 'apex-logo';
    $subject = 'ApeX Gear Contact Form: ' . trim(preg_replace('/[\r\n]+/', ' ', $contactSubject));

    $html = '<!doctype html><html><body style="margin:0;background:#f2f0ef;font-family:Arial,sans-serif;color:#102033;">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f2f0ef;padding:32px 12px;"><tr><td align="center">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:630px;background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 12px 35px rgba(12,31,52,.10);">'
        . '<tr><td style="height:7px;background:#00c2ff;"></td></tr>'
        . '<tr><td style="padding:34px 42px 10px;"><img src="cid:' . $logoCid . '" width="76" alt="ApeX Gear" style="display:block;width:76px;height:auto;"></td></tr>'
        . '<tr><td style="padding:16px 42px 36px;"><h1 style="margin:0 0 14px;font-size:27px;line-height:1.25;color:#071828;">New contact message</h1>'
        . '<p style="margin:0 0 22px;font-size:16px;line-height:1.6;color:#506174;">A customer submitted a message through the ApeX Gear contact form.</p>'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:separate;border-spacing:0 10px;">'
        . '<tr><td style="width:130px;color:#7b8998;font-size:13px;font-weight:700;text-transform:uppercase;">Full Name</td><td style="color:#071828;font-size:15px;font-weight:700;">' . $safeName . '</td></tr>'
        . '<tr><td style="width:130px;color:#7b8998;font-size:13px;font-weight:700;text-transform:uppercase;">Email</td><td style="color:#071828;font-size:15px;"><a href="mailto:' . $safeEmail . '" style="color:#0b2fa8;text-decoration:none;">' . $safeEmail . '</a></td></tr>'
        . '<tr><td style="width:130px;color:#7b8998;font-size:13px;font-weight:700;text-transform:uppercase;">Subject</td><td style="color:#071828;font-size:15px;font-weight:700;">' . $safeSubject . '</td></tr>'
        . '<tr><td style="width:130px;color:#7b8998;font-size:13px;font-weight:700;text-transform:uppercase;">Sent</td><td style="color:#071828;font-size:15px;">' . $submittedAt . '</td></tr>'
        . '</table>'
        . '<div style="margin:24px 0 0;padding:20px 22px;border-radius:14px;background:#eafaff;border:1px solid #bcefff;color:#071828;font-size:15px;line-height:1.7;">' . $safeMessage . '</div>'
        . '<hr style="border:0;border-top:1px solid #e3eaf1;margin:30px 0 24px;">'
        . '<p style="margin:0;font-size:13px;line-height:1.6;color:#7b8998;">Reply directly to this email to respond to the customer.<br>ApeX Gear Contact Form</p>'
        . '</td></tr></table></td></tr></table></body></html>';

    $plain = "New ApeX Gear contact message\r\n\r\n"
        . "Full Name: {$senderName}\r\n"
        . "Email: {$senderEmail}\r\n"
        . "Subject: {$contactSubject}\r\n"
        . "Sent: {$submittedAt}\r\n\r\n"
        . "Message:\r\n{$contactMessage}\r\n";

    $headers = [
        'From: ' . APEX_SMTP_FROM_NAME . ' <' . APEX_SMTP_USERNAME . '>',
        'To: <' . $recipientEmail . '>',
        'Reply-To: ' . mailHeaderPhrase($senderName) . ' <' . $senderEmail . '>',
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: multipart/related; boundary="' . $boundary . '"',
        'Date: ' . date(DATE_RFC2822),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@apexgear.local>',
    ];

    $message = implode("\r\n", $headers) . "\r\n\r\n"
        . '--' . $boundary . "\r\n"
        . "Content-Type: multipart/alternative; boundary=\"{$boundary}_alt\"\r\n\r\n"
        . '--' . $boundary . "_alt\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n"
        . quoted_printable_encode($plain) . "\r\n"
        . '--' . $boundary . "_alt\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n"
        . quoted_printable_encode($html) . "\r\n"
        . '--' . $boundary . "_alt--\r\n"
        . '--' . $boundary . "\r\n"
        . "Content-Type: image/png; name=\"ApeX Logo.png\"\r\nContent-Transfer-Encoding: base64\r\n"
        . "Content-ID: <{$logoCid}>\r\nContent-Disposition: inline; filename=\"ApeX Logo.png\"\r\n\r\n"
        . chunk_split(base64_encode((string) file_get_contents($logoPath))) . "\r\n"
        . '--' . $boundary . "--\r\n";

    smtpSend($recipientEmail, $message, $smtpPassword);
}

function mailHeaderPhrase(string $value): string
{
    $value = trim(preg_replace('/[\r\n]+/', ' ', $value));
    if ($value === '') {
        return 'ApeX Gear Customer';
    }

    return '"' . addcslashes($value, '"\\') . '"';
}

function smtpSend(string $recipientEmail, string $message, string $smtpPassword): void
{
    $socket = @stream_socket_client(
        'tcp://' . APEX_SMTP_HOST . ':' . APEX_SMTP_PORT,
        $errorNumber,
        $errorMessage,
        20,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        throw new RuntimeException('Could not connect to the email server: ' . $errorMessage);
    }

    stream_set_timeout($socket, 20);

    try {
        smtpExpect($socket, [220]);
        smtpCommand($socket, 'EHLO apexgear.local', [250]);
        smtpCommand($socket, 'STARTTLS', [220]);

        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new RuntimeException('Could not establish a secure email connection.');
        }

        smtpCommand($socket, 'EHLO apexgear.local', [250]);
        smtpCommand($socket, 'AUTH LOGIN', [334]);
        smtpCommand($socket, base64_encode(APEX_SMTP_USERNAME), [334]);
        smtpCommand($socket, base64_encode($smtpPassword), [235]);
        smtpCommand($socket, 'MAIL FROM:<' . APEX_SMTP_USERNAME . '>', [250]);
        smtpCommand($socket, 'RCPT TO:<' . $recipientEmail . '>', [250, 251]);
        smtpCommand($socket, 'DATA', [354]);

        $escapedMessage = preg_replace('/(?m)^\./', '..', $message);
        fwrite($socket, $escapedMessage . "\r\n.\r\n");
        smtpExpect($socket, [250]);
        smtpCommand($socket, 'QUIT', [221]);
    } finally {
        fclose($socket);
    }
}

function smtpCommand($socket, string $command, array $expectedCodes): void
{
    fwrite($socket, $command . "\r\n");
    smtpExpect($socket, $expectedCodes);
}

function smtpExpect($socket, array $expectedCodes): void
{
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (strlen($line) < 4 || $line[3] === ' ') {
            break;
        }
    }

    $code = (int) substr($response, 0, 3);
    if (!in_array($code, $expectedCodes, true)) {
        throw new RuntimeException('Email server rejected the request (SMTP ' . $code . ').');
    }
}
