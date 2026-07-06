<?php
header('Content-Type: application/json');

// Prevent direct access via GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Get the raw POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    // Fallback to $_POST if JSON decoding fails
    $input = $_POST;
}

// Sanitize inputs
$fullname = isset($input['fullname']) ? strip_tags(trim($input['fullname'])) : '';
$phone = isset($input['phone']) ? strip_tags(trim($input['phone'])) : '';
$email = isset($input['email']) ? filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL) : '';
$service = isset($input['service']) ? strip_tags(trim($input['service'])) : '';
$details = isset($input['details']) ? strip_tags(trim($input['details'])) : '';

// Validation
if (empty($fullname) || empty($phone) || empty($service)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address format.']);
    exit;
}

// Email parameters
$to = 'info@ashvathyprinters.com';
$subject = 'New Quote Request from ' . $fullname;

// Construct HTML message
$message = '
<html>
<head>
  <title>New Quote Request</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f7f9fc; padding: 20px;">
  <div style="max-width: 600px; margin: 0 auto; padding: 30px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);">
    <h2 style="color: #1565C0; border-bottom: 2px solid #1565C0; padding-bottom: 10px; margin-top: 0;">New Quote Request Details</h2>
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
      <tr>
        <td style="padding: 10px; font-weight: bold; width: 30%; border-bottom: 1px solid #edf2f7; color: #4a5568;">Full Name:</td>
        <td style="padding: 10px; border-bottom: 1px solid #edf2f7; color: #2d3748;">' . htmlspecialchars($fullname) . '</td>
      </tr>
      <tr>
        <td style="padding: 10px; font-weight: bold; border-bottom: 1px solid #edf2f7; color: #4a5568;">Phone Number:</td>
        <td style="padding: 10px; border-bottom: 1px solid #edf2f7; color: #2d3748;">' . htmlspecialchars($phone) . '</td>
      </tr>
      <tr>
        <td style="padding: 10px; font-weight: bold; border-bottom: 1px solid #edf2f7; color: #4a5568;">Email Address:</td>
        <td style="padding: 10px; border-bottom: 1px solid #edf2f7; color: #2d3748;">' . ($email ? htmlspecialchars($email) : '<em style="color: #a0aec0;">Not Provided</em>') . '</td>
      </tr>
      <tr>
        <td style="padding: 10px; font-weight: bold; border-bottom: 1px solid #edf2f7; color: #4a5568;">Service Required:</td>
        <td style="padding: 10px; border-bottom: 1px solid #edf2f7; color: #2d3748;">' . htmlspecialchars($service) . '</td>
      </tr>
      <tr>
        <td style="padding: 10px; font-weight: bold; vertical-align: top; color: #4a5568;">Project Details:</td>
        <td style="padding: 10px; white-space: pre-line; color: #2d3748;">' . (!empty($details) ? htmlspecialchars($details) : '<em style="color: #a0aec0;">None provided</em>') . '</td>
      </tr>
    </table>
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #edf2f7; font-size: 11px; color: #718096; line-height: 1.5;">
      <p style="margin: 0 0 5px 0;">This email was automatically generated from the Ashvathy Art Printers contact form.</p>
      <p style="margin: 0;"><strong>Email User Analytics ID:</strong> df129569-0dfa-46ca-a406-4eda3a1b716f</p>
    </div>
  </div>
</body>
</html>
';

// Setup email headers
$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/html; charset=UTF-8';
$headers[] = 'From: Ashvathy Art Printers Form <info@ashvathyprinters.com>';
if (!empty($email)) {
    $headers[] = 'Reply-To: ' . $email;
}
$headers[] = 'X-Email-User-Analytics-ID: df129569-0dfa-46ca-a406-4eda3a1b716f';

// Send email
if (mail($to, $subject, $message, implode("\r\n", $headers))) {
    echo json_encode(['success' => true, 'message' => 'Your request has been sent successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send email. Server mail configuration issue.']);
}
?>
