<?php
ob_start();
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require './vendor/autoload.php'; // Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate JSON input
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data']);
        exit;
    }

    // Validate required fields
    $requiredFields = ['email', 'fullName', 'message'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => "$field is required"]);
            exit;
        }
    }

    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit;
    }

    // Sanitize input
    $email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
    $fullName = htmlspecialchars($input['fullName']);
    $message = htmlspecialchars($input['message']);

    try {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tunesflix.movie@gmail.com';
        $mail->Password = 'vstmuygbtfbksqow';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        // Recipients
        $mail->setFrom('jctech333@gmail.com', 'Portfolio Contact');
        $mail->addAddress('jctech333@gmail.com'); // Your receiving address
        $mail->addReplyTo($email, $fullName);

        // Load and populate HTML template
        $htmlTemplate = file_get_contents("./Contact.html");
        $htmlTemplate = str_replace(
            ['{{fullName}}', '{{email}}', '{{message}}', '{{date}}'],
            [$fullName, $email, nl2br($message), date('F j, Y \a\t g:i a')],
            $htmlTemplate
        );

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New message from $fullName";
        $mail->Body = $htmlTemplate;
        $mail->AltBody = "Name: $fullName\nEmail: $email\nMessage:\n$message";

        $mail->send();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Thank you! Your message has been sent.'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Message could not be sent.',
            'error' => $mail->ErrorInfo
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}