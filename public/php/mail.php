<?php
// Simple error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/mail_errors.log');

// CORS headers - MUST be first
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

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
if (!isset($data['name']) || !isset($data['contact']) || !isset($data['subject']) || !isset($data['body'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

// Sanitize input
$name = htmlspecialchars(strip_tags($data['name']));
$contact = htmlspecialchars(strip_tags($data['contact']));
$subject = htmlspecialchars(strip_tags($data['subject']));
$body = htmlspecialchars(strip_tags($data['body']));

// Extract email for reply-to
$replyEmail = $contact;
if (strpos($contact, '|') !== false) {
    $parts = explode('|', $contact);
    $replyEmail = trim($parts[0]);
}

// ============================================
// ZOHO MAIL API CONFIGURATION
// ============================================
// Get your credentials from: https://api-console.zoho.in/
$ZOHO_ACCOUNT_ID = 'YOUR_ACCOUNT_ID_HERE';  // Replace with your Zoho account ID
$ZOHO_CLIENT_ID = 'YOUR_CLIENT_ID_HERE';     // Replace with your Client ID
$ZOHO_CLIENT_SECRET = 'YOUR_CLIENT_SECRET_HERE'; // Replace with your Client Secret
$ZOHO_REFRESH_TOKEN = 'YOUR_REFRESH_TOKEN_HERE'; // Replace with your Refresh Token

$FROM_EMAIL = 'eversure@rathigroup.com';
$FROM_NAME = 'Eversure Contact Form';
$TO_EMAIL = 'eversure@rathigroup.com';
$TO_NAME = 'Eversure Team';

// ============================================
// STEP 1: Get Access Token
// ============================================
function getZohoAccessToken($clientId, $clientSecret, $refreshToken) {
    $tokenUrl = "https://accounts.zoho.in/oauth/v2/token";
    
    $postData = http_build_query([
        'refresh_token' => $refreshToken,
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'grant_type' => 'refresh_token'
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("Zoho token error: " . $response);
        return null;
    }
    
    $result = json_decode($response, true);
    return isset($result['access_token']) ? $result['access_token'] : null;
}

// ============================================
// STEP 2: Send Email via Zoho API
// ============================================
function sendZohoEmail($accessToken, $accountId, $fromEmail, $fromName, $toEmail, $toName, $subject, $bodyText, $replyTo = null) {
    $apiUrl = "https://mail.zoho.in/api/accounts/{$accountId}/messages";
    
    // Prepare email data
    $emailData = [
        'fromAddress' => $fromEmail,
        'toAddress' => $toEmail,
        'subject' => $subject,
        'content' => $bodyText,
        'mailFormat' => 'plaintext'
    ];
    
    if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $emailData['replyTo'] = $replyTo;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Zoho-oauthtoken ' . $accessToken,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("Zoho send error: " . $response);
        return false;
    }
    
    return true;
}

// ============================================
// MAIN EXECUTION
// ============================================
try {
    // Get access token
    $accessToken = getZohoAccessToken($ZOHO_CLIENT_ID, $ZOHO_CLIENT_SECRET, $ZOHO_REFRESH_TOKEN);
    
    if (!$accessToken) {
        throw new Exception('Failed to get Zoho access token');
    }
    
    // Prepare email body
    $emailBody = "New Contact Form Submission\n\n";
    $emailBody .= "Name: {$name}\n";
    $emailBody .= "Contact: {$contact}\n\n";
    $emailBody .= "Message:\n{$body}\n\n";
    $emailBody .= "---\n";
    $emailBody .= "Sent from: Contact Form\n";
    $emailBody .= "Time: " . date('Y-m-d H:i:s');
    
    // Send email
    $sent = sendZohoEmail(
        $accessToken,
        $ZOHO_ACCOUNT_ID,
        $FROM_EMAIL,
        $FROM_NAME,
        $TO_EMAIL,
        $TO_NAME,
        $subject,
        $emailBody,
        $replyEmail
    );
    
    if ($sent) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Email sent successfully'
        ]);
    } else {
        throw new Exception('Failed to send email');
    }
    
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to send email. Please try again later.'
    ]);
}
?>