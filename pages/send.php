<?php

use Infobip\Configuration;
use Infobip\Api\SmsApi;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use Infobip\Model\SmsAdvancedTextualRequest;
use Twilio\Rest\Client;

require __DIR__ . "/vendor/autoload.php";

// Database connection
$host = 'localhost'; // Replace with your DB host
$user = 'root'; // Replace with your DB username
$password = ''; // Replace with your DB password
$database = 'virtual_classroom'; // Replace with your DB name

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch form data
$number = $_POST["number"];
$message = $_POST["message"];
$chat_code = $_POST["chat_code"] ?? 'default_chat_code';
$sender_id = $_POST["sender_id"] ?? 1; // Default to 1 if not set
$provider = $_POST["provider"];

// Check if the chat_code exists in the database for the current teacher/student pair
$sql_check_chat_code = "SELECT * FROM chats WHERE chat_code = ?";
$stmt_check_chat_code = $conn->prepare($sql_check_chat_code);
$stmt_check_chat_code->bind_param("s", $chat_code);
$stmt_check_chat_code->execute();
$result_check_chat_code = $stmt_check_chat_code->get_result();

if ($result_check_chat_code->num_rows == 0) {
    die("No chat code found for this teacher and student pair.");
}

// Store the message in the database
$sql = "INSERT INTO messages (chat_code, sender_id, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sis", $chat_code, $sender_id, $message);

if (!$stmt->execute()) {
    die("Failed to save message: " . $stmt->error);
}

// Send message using provider
if ($provider === "infobip") {
    try {
         $base_url = "https://1gye1x.api.infobip.com";
    $api_key = "74de2b8e1df950e62cc79dd5d4ad8c34-84b5fa7b-915b-4f61-a5e6-23b1a48afb76";

        $configuration = new Configuration(host: $base_url, apiKey: $api_key);
        $api = new SmsApi(config: $configuration);

        $destination = new SmsDestination(to: $number);

        $messageObj = new SmsTextualMessage(
            destinations: [$destination],
            text: $message,
            from: "daveh"
        );

        $request = new SmsAdvancedTextualRequest(messages: [$messageObj]);
        $response = $api->sendSmsMessage($request);
    } catch (Exception $e) {
        echo "Infobip Error: " . $e->getMessage();
    }
} else {  
    try {
        $account_id = "your_account_sid";
        $auth_token = "your_auth_token";

        $client = new Client($account_id, $auth_token);
        $twilio_number = "+your_twilio_phone_number";

        $client->messages->create(
            $number,
            [
                "from" => $twilio_number,
                "body" => $message
            ]
        );
    } catch (Exception $e) {
        echo "Twilio Error: " . $e->getMessage();
    }
}

echo "Message sent and stored in the database."; 
?>
