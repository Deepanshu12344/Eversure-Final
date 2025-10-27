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
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Validate required fields
$requiredFields = ['firstName', 'lastName', 'post', 'email', 'contactNo', 'address'];
$errors = [];

foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        $errors[$field] = ucfirst($field) . ' is required';
    }
}

// Validate email format
if (isset($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address';
}

// Validate resume file
if (!isset($_FILES['resume']) || $_FILES['resume']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors['resume'] = 'Resume is required';
} elseif ($_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
    $errors['resume'] = 'Error uploading file';
} else {
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $fileType = $_FILES['resume']['type'];
    $fileSize = $_FILES['resume']['size'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($fileType, $allowedTypes)) {
        $errors['resume'] = 'Please upload a PDF or Word document';
    } elseif ($fileSize > $maxSize) {
        $errors['resume'] = 'File size must be less than 5MB';
    }
}

// Return validation errors if any
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Please correct the errors in the form',
        'errors' => $errors
    ]);
    exit();
}

// Sanitize input data
$firstName = htmlspecialchars(strip_tags(trim($_POST['firstName'])));
$lastName = htmlspecialchars(strip_tags(trim($_POST['lastName'])));
$post = htmlspecialchars(strip_tags(trim($_POST['post'])));
$email = htmlspecialchars(strip_tags(trim($_POST['email'])));
$contactNo = htmlspecialchars(strip_tags(trim($_POST['contactNo'])));
$address = htmlspecialchars(strip_tags(trim($_POST['address'])));

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if autoload exists
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'PHPMailer not installed. Please contact administrator.'
    ]);
    exit();
}

require __DIR__ . '/vendor/autoload.php';

try {
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'deepanshu123sharma4@gmail.com'; // YOUR Gmail address
    $mail->Password   = 'jkff uqzc rfdz gipt'; // Replace with your 16-char app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    
    // Recipients
    $mail->setFrom('deep1209anshu@gmail.com', 'Career Application - Eversure');
    $mail->addAddress('deepanshu123sharma4@gmail.com', 'Deepanshu Sharma');
    $mail->addReplyTo($email, "$firstName $lastName");
    
    // Attach resume file
    $resumeFile = $_FILES['resume']['tmp_name'];
    $resumeName = $_FILES['resume']['name'];
    $mail->addAttachment($resumeFile, $resumeName);
    
    // Email content
    $mail->isHTML(true);
    $mail->Subject = "New Career Application: $post - $firstName $lastName";
    
    $mail->Body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #309ed9; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #309ed9; }
            .value { margin-left: 10px; }
            .footer { margin-top: 20px; padding: 15px; background-color: #e9e9e9; text-align: center; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Career Application Received</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>Name:</span>
                    <span class='value'>$firstName $lastName</span>
                </div>
                <div class='field'>
                    <span class='label'>Position Applied For:</span>
                    <span class='value'>$post</span>
                </div>
                <div class='field'>
                    <span class='label'>Email:</span>
                    <span class='value'>$email</span>
                </div>
                <div class='field'>
                    <span class='label'>Contact Number:</span>
                    <span class='value'>$contactNo</span>
                </div>
                <div class='field'>
                    <span class='label'>Address:</span>
                    <span class='value'>$address</span>
                </div>
                <div class='field'>
                    <span class='label'>Resume:</span>
                    <span class='value'>Attached ($resumeName)</span>
                </div>
            </div>
            <div class='footer'>
                <p>This application was submitted from the Eversure Career page on " . date('Y-m-d H:i:s') . "</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Plain text version for email clients that don't support HTML
    $mail->AltBody = "New Career Application Received\n\n" .
                     "Name: $firstName $lastName\n" .
                     "Position Applied For: $post\n" .
                     "Email: $email\n" .
                     "Contact Number: $contactNo\n" .
                     "Address: $address\n" .
                     "Resume: Attached ($resumeName)\n\n" .
                     "Submitted on: " . date('Y-m-d H:i:s');
    
    $mail->send();
    
    http_response_code(200);
    echo json_encode([
        'success' => true, 
        'message' => 'Application submitted successfully! We will review your application and get back to you soon.'
    ]);
    
} catch (Exception $e) {
    error_log("Career Mail Error: " . $mail->ErrorInfo);
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => "Failed to submit application. Please try again or contact us directly.",
        'error' => $mail->ErrorInfo
    ]);
}
?>