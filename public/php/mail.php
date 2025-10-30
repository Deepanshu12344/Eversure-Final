<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Set headers for CORS and JSON response
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Get JSON data from request body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
if (!isset($data['name']) || !isset($data['contact']) || !isset($data['subject']) || !isset($data['body'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

// Sanitize input data
$name = htmlspecialchars(strip_tags($data['name']));
$contact = htmlspecialchars(strip_tags($data['contact']));
$subject = htmlspecialchars(strip_tags($data['subject']));
$body = htmlspecialchars(strip_tags($data['body']));

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if autoload exists
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'PHPMailer not installed. Run: composer require phpmailer/phpmailer']);
    exit();
}

require __DIR__ . '/vendor/autoload.php';

try {
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.zoho.in';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'eversure@rathigroup.com'; // YOUR Gmail address
    $mail->Password   = '755MysJDwhMd'; // Replace with your 16-char app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    
    // For debugging (remove in production)
    // $mail->SMTPDebug = 2;
    
    // Recipients
    $mail->setFrom('eversure@rathigroup.com', 'Contact Form - Eversure');
    $mail->addAddress('eversure@rathigroup.com', 'Eversure');
    
    // Extract email from contact field for reply-to (contact contains: email | Phone: +91...)
    $emailMatch = [];
    $replyEmail = $contact;
    
    // If contact contains pipe character, extract just the email part
    if (strpos($contact, '|') !== false) {
        $parts = explode('|', $contact);
        $replyEmail = trim($parts[0]);
    }
    
    // Validate email format before adding reply-to
    if (filter_var($replyEmail, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($replyEmail, $name);
    }
    
    // Content
    $mail->isHTML(false);
    $mail->Subject = $subject;
    $mail->Body    = "New Contact Form Submission\n\n" .
                     "Name: $name\n" .
                     "Contact: $contact\n\n" .
                     "Message:\n$body\n\n" .
                     "---\n" .
                     "Sent from: Contact Form\n" .
                     "Time: " . date('Y-m-d H:i:s');
    
    $mail->send();
    
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
    
} catch (Exception $e) {
    error_log("Mail Error: " . $mail->ErrorInfo);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => "Failed to send email: {$mail->ErrorInfo}"]);
}
?>