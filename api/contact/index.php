<?php
/**
 * Contact Submission API Endpoint for Portfolio Website
 * Handles contact emails securely and forwards them to amalskumardev@gmail.com
 */

// 1. CORS Configuration
$allowed_origins = [
    'https://amalskumar.dev', 
    'http://localhost:3000', 
    'http://localhost:3001',
    'http://localhost:5173'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $origin);
}
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Respond immediately to browser preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Reject any request method other than POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method Not Allowed"]);
    exit();
}

// 2. Load Configuration from .env File (without starting DB connection)
$base_dir = dirname(dirname(__DIR__));
$env_path = $base_dir . '/.env';
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        $line = trim($line);
        if (strpos($line, '=') !== false) {
            list($env_key, $env_val) = explode('=', $line, 2);
            $env_key = trim($env_key);
            $env_val = trim($env_val);
            // Remove optional surrounding quotes
            $env_val = trim($env_val, "\"'");
            if (!array_key_exists($env_key, $_SERVER) && !array_key_exists($env_key, $_ENV)) {
                putenv("{$env_key}={$env_val}");
                $_ENV[$env_key] = $env_val;
                $_SERVER[$env_key] = $env_val;
            }
        }
    }
}

// Get contact configurations with defaults
$contact_to = getenv('CONTACT_TO_EMAIL') ?: 'amalskumardev@gmail.com';
$contact_from = getenv('CONTACT_FROM_EMAIL') ?: 'noreply@amalskumar.dev';

// SMTP settings from environment
$smtp_settings = [
    'host'       => getenv('SMTP_HOST') ?: '',
    'port'       => getenv('SMTP_PORT') ?: '',
    'user'       => getenv('SMTP_USER') ?: '',
    'pass'       => getenv('SMTP_PASS') ?: '',
    'secure'     => getenv('SMTP_SECURE') ?: '', // 'tls', 'ssl', or ''
    'from_email' => $contact_from
];

// Helper to sanitize single line string values (removes CRLF to prevent header injection)
function sanitize_header_value($value) {
    if ($value === null) return '';
    return str_replace(["\r", "\n"], '', trim((string)$value));
}

// 3. Parse and Validate JSON Payload
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid JSON payload"]);
    exit();
}

// Extract inputs
$firstName = sanitize_header_value($data['firstName'] ?? '');
$lastName = sanitize_header_value($data['lastName'] ?? '');
$email = sanitize_header_value($data['email'] ?? '');
$message = $data['message'] ?? '';

// Extract metadata
$metadata = $data['metadata'] ?? [];
$meta_ip = sanitize_header_value($metadata['ip'] ?? '');
$meta_os = sanitize_header_value($metadata['os'] ?? '');
$meta_browser = sanitize_header_value($metadata['browser'] ?? '');
$meta_time = sanitize_header_value($metadata['time'] ?? '');

// Validate required fields
if (empty($firstName) || empty($email) || empty($message)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid email or missing fields"]);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid email or missing fields"]);
    exit();
}

// 4. Generate Visual HTML Email Template
$fullNameHtml = htmlspecialchars($firstName . ' ' . $lastName, ENT_QUOTES, 'UTF-8');
$emailHtml = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$messageHtml = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

$ipHtml = htmlspecialchars($meta_ip ?: 'Unknown', ENT_QUOTES, 'UTF-8');
$osHtml = htmlspecialchars($meta_os ?: 'Unknown', ENT_QUOTES, 'UTF-8');
$browserHtml = htmlspecialchars($meta_browser ?: 'Unknown', ENT_QUOTES, 'UTF-8');
$timeHtml = htmlspecialchars($meta_time ?: date('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8');

$emailBody = <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      background: #faf8fd;
      background-image: linear-gradient(180deg, #faf8fd 0%, #fff5f7 100%);
      margin: 0;
      padding: 0;
    }
    .email-container {
      max-width: 600px;
      margin: 40px auto;
      background-color: #ffffff;
      border-radius: 16px;
      box-shadow: 0 10px 40px rgba(244, 114, 182, 0.05);
      overflow: hidden;
      border: 1px solid #f3eaf2;
    }
    .email-header {
      background: linear-gradient(135deg, #fff0f3 0%, #f3e8ff 100%);
      padding: 45px 35px;
      text-align: center;
      border-bottom: 1px solid #fdf2f4;
    }
    .email-header h1 {
      margin: 0;
      font-size: 26px;
      font-weight: 800;
      letter-spacing: -0.5px;
      color: #581c87;
    }
    .email-header p {
      margin: 10px 0 0 0;
      font-size: 14px;
      color: #701a75;
      font-weight: 500;
    }
    .email-body {
      padding: 40px 35px;
      color: #1e1b4b;
    }
    .section-title {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: #8b5cf6;
      margin-bottom: 10px;
      margin-top: 28px;
    }
    .section-title:first-child {
      margin-top: 0;
    }
    .contact-info {
      background-color: #faf5ff;
      border-radius: 12px;
      padding: 22px;
      margin-bottom: 24px;
      border: 1px solid #f3e8ff;
    }
    .info-row {
      margin-bottom: 14px;
      border-bottom: 1px solid #f5f0fa;
      padding-bottom: 14px;
    }
    .info-row:last-child {
      margin-bottom: 0;
      border-bottom: none;
      padding-bottom: 0;
    }
    .info-label {
      font-weight: 700;
      font-size: 11px;
      color: #a855f7;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 5px;
    }
    .info-value {
      font-size: 15px;
      color: #1e1b4b;
      font-weight: 500;
    }
    .message-box {
      background: #fffafb;
      border-left: 4px solid #f472b6;
      border-top: 1px solid #fbcfe8;
      border-right: 1px solid #fbcfe8;
      border-bottom: 1px solid #fbcfe8;
      padding: 22px;
      margin-bottom: 24px;
      color: #4c1d95;
      font-size: 15px;
      line-height: 1.7;
      border-radius: 0 12px 12px 0;
    }
    .metadata-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 16px;
    }
    .metadata-table td {
      padding: 12px;
      font-size: 13px;
      border-bottom: 1px solid #f5f0fa;
    }
    .metadata-table tr:last-child td {
      border-bottom: none;
    }
    .metadata-label {
      font-weight: 600;
      color: #9ca3af;
      width: 140px;
    }
    .metadata-value {
      color: #4b5563;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }
    .email-footer {
      background-color: #fdfafc;
      color: #a855f7;
      text-align: center;
      padding: 24px;
      font-size: 12px;
      font-weight: 500;
      border-top: 1px solid #f5f0fa;
      border-bottom-left-radius: 16px;
      border-bottom-right-radius: 16px;
    }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="email-header">
      <h1>New Quest Proposal</h1>
      <p>A prospective client contacted you via your personal portfolio website.</p>
    </div>
    <div class="email-body">
      <div class="section-title">Sender Details</div>
      <div class="contact-info">
        <div class="info-row">
          <div class="info-label">Name</div>
          <div class="info-value">{$fullNameHtml}</div>
        </div>
        <div class="info-row">
          <div class="info-label">Email Address</div>
          <div class="info-value">
            <a href="mailto:{$emailHtml}" style="color: #8b5cf6; text-decoration: none; font-weight: 600; border-bottom: 1px dashed #8b5cf6;">{$emailHtml}</a>
          </div>
        </div>
      </div>

      <div class="section-title">Message Details</div>
      <div class="message-box">
        {$messageHtml}
      </div>

      <div class="section-title">Network & Client Metadata</div>
      <table class="metadata-table">
        <tr>
          <td class="metadata-label">IP Address</td>
          <td class="metadata-value">{$ipHtml}</td>
        </tr>
        <tr>
          <td class="metadata-label">Operating System</td>
          <td class="metadata-value">{$osHtml}</td>
        </tr>
        <tr>
          <td class="metadata-label">Browser Agent</td>
          <td class="metadata-value">{$browserHtml}</td>
        </tr>
        <tr>
          <td class="metadata-label">Submission Time</td>
          <td class="metadata-value">{$timeHtml}</td>
        </tr>
      </table>
    </div>
    <div class="email-footer">
      Generated automatically by Momentum API service.
    </div>
  </div>
</body>
</html>
HTML;

// 5. Send Email Logic
$subject = "Portfolio Contact: " . ($firstName . ' ' . $lastName);

// Headers
$headers = [
    'MIME-Version' => '1.0',
    'Content-Type' => 'text/html; charset=UTF-8',
    'From'         => $contact_from,
    'Reply-To'     => $email
];

/**
 * Socket-based SMTP mail sending implementation.
 */
function sendSmtpEmail($to, $subject, $body, $headers, $smtpSettings) {
    $host = $smtpSettings['host'];
    $port = intval($smtpSettings['port'] ?: 587);
    $user = $smtpSettings['user'] ?? '';
    $pass = $smtpSettings['pass'] ?? '';
    $secure = strtolower($smtpSettings['secure'] ?? ''); // 'ssl', 'tls', or ''

    $socketPrefix = ($secure === 'ssl') ? 'ssl://' : '';
    
    // Create socket connection with a 15 second timeout
    $socket = @stream_socket_client(
        $socketPrefix . $host . ':' . $port,
        $errno,
        $errstr,
        15,
        STREAM_CLIENT_CONNECT,
        stream_context_create()
    );

    if (!$socket) {
        throw new Exception("Could not connect to SMTP host $host:$port: $errstr ($errno)");
    }

    $readResponse = function($socket, $expectedCode) {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        $code = substr($response, 0, 3);
        if ($code !== (string)$expectedCode) {
            throw new Exception("SMTP Error: Expected $expectedCode, got: " . trim($response));
        }
        return $response;
    };

    try {
        $readResponse($socket, 220);
        
        $helloCmd = "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n";
        fwrite($socket, $helloCmd);
        $readResponse($socket, 250);

        // Upgrade connection to TLS if required
        if ($secure === 'tls') {
            fwrite($socket, "STARTTLS\r\n");
            $readResponse($socket, 220);
            
            $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                $cryptoMethod = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
            }
            
            if (!@stream_socket_enable_crypto($socket, true, $cryptoMethod)) {
                throw new Exception("Failed to start TLS encryption on SMTP socket.");
            }
            
            // Send EHLO again after upgrading to TLS
            fwrite($socket, $helloCmd);
            $readResponse($socket, 250);
        }

        // Perform authentication if credentials are provided
        if (!empty($user) && !empty($pass)) {
            fwrite($socket, "AUTH LOGIN\r\n");
            $readResponse($socket, 334);
            fwrite($socket, base64_encode($user) . "\r\n");
            $readResponse($socket, 334);
            fwrite($socket, base64_encode($pass) . "\r\n");
            $readResponse($socket, 235);
        }

        $fromEmail = $smtpSettings['from_email'] ?: ($user ?: 'noreply@amalskumar.dev');
        fwrite($socket, "MAIL FROM:<$fromEmail>\r\n");
        $readResponse($socket, 250);

        fwrite($socket, "RCPT TO:<$to>\r\n");
        $readResponse($socket, 250);

        fwrite($socket, "DATA\r\n");
        $readResponse($socket, 354);

        // Build the headers payload
        $emailData = '';
        foreach ($headers as $name => $value) {
            $emailData .= "$name: $value\r\n";
        }
        if (!isset($headers['Subject'])) {
            $emailData .= "Subject: $subject\r\n";
        }
        if (!isset($headers['To'])) {
            $emailData .= "To: $to\r\n";
        }
        
        $emailData .= "\r\n"; // Headers and Body separator

        // Normalize newlines and handle SMTP dot-stuffing
        $bodyNormalized = str_replace("\r\n", "\n", $body);
        $bodyLines = explode("\n", $bodyNormalized);
        foreach ($bodyLines as $line) {
            if (strpos($line, '.') === 0) {
                $line = '.' . $line; // Double the dot to escape it
            }
            $emailData .= $line . "\r\n";
        }
        
        $emailData .= ".\r\n"; // End of mail data delimiter

        fwrite($socket, $emailData);
        $readResponse($socket, 250);

        fwrite($socket, "QUIT\r\n");
        $readResponse($socket, 221);
    } finally {
        fclose($socket);
    }

    return true;
}

/**
 * Send email using Resend HTTP API
 */
function sendResendEmail($to, $subject, $body, $from, $apiKey) {
    $url = 'https://api.resend.com/emails';
    $payload = [
        'from' => $from,
        'to' => [$to],
        'subject' => $subject,
        'html' => $body
    ];

    $ch = curl_init($url);
    if (!$ch) {
        throw new Exception("Failed to initialize cURL for Resend API");
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        throw new Exception("Resend cURL Failure: " . $curlError);
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    }

    $err = json_decode($response, true);
    $errMsg = $err['message'] ?? (is_array($err) ? json_encode($err) : $response);
    throw new Exception("Resend API Error (HTTP $httpCode): " . $errMsg);
}

/**
 * Send email using SendGrid HTTP API
 */
function sendSendGridEmail($to, $subject, $body, $from, $apiKey) {
    $url = 'https://api.sendgrid.com/v3/mail/send';
    $payload = [
        'personalizations' => [[
            'to' => [['email' => $to]]
        ]],
        'from' => ['email' => $from],
        'subject' => $subject,
        'content' => [[
            'type' => 'text/html',
            'value' => $body
        ]]
    ];

    $ch = curl_init($url);
    if (!$ch) {
        throw new Exception("Failed to initialize cURL for SendGrid API");
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        throw new Exception("SendGrid cURL Failure: " . $curlError);
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    }

    $err = json_decode($response, true);
    $errMsg = $err['errors'][0]['message'] ?? (is_array($err) ? json_encode($err) : $response);
    throw new Exception("SendGrid API Error (HTTP $httpCode): " . $errMsg);
}

$resend_api_key = getenv('RESEND_API_KEY') ?: '';
$sendgrid_api_key = getenv('SENDGRID_API_KEY') ?: '';

try {
    if (!empty($resend_api_key)) {
        sendResendEmail($contact_to, $subject, $emailBody, $contact_from, $resend_api_key);
    } else if (!empty($sendgrid_api_key)) {
        sendSendGridEmail($contact_to, $subject, $emailBody, $contact_from, $sendgrid_api_key);
    } else if (!empty($smtp_settings['host'])) {
        sendSmtpEmail($contact_to, $subject, $emailBody, $headers, $smtp_settings);
    } else {
        // Fallback to native mail()
        $headersString = '';
        foreach ($headers as $k => $v) {
            $headersString .= "$k: $v\r\n";
        }
        $headersString = rtrim($headersString);
        
        if (!@mail($contact_to, $subject, $emailBody, $headersString)) {
            throw new Exception("Native PHP mail() function returned false.");
        }
    }

    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Email sent successfully"]);
} catch (Exception $e) {
    error_log("Contact API Mail Failure: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Unable to send email: " . $e->getMessage()]);
}
