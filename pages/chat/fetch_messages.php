<<<<<<< HEAD
<?php
include '../database/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $chat_code = $_GET['chat_code'];

    // Fetch messages for the chat code
    $stmt = $conn->prepare("SELECT users.username, chats.message, chats.sent_at 
                            FROM chats 
                            JOIN users ON chats.sender_id = users.id 
                            WHERE chat_code = ? ORDER BY sent_at ASC");
    $stmt->bind_param("s", $chat_code);
    $stmt->execute();

    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($messages);
}
?>
=======
<?php
include '../database/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $chat_code = $_GET['chat_code'];

    // Fetch messages for the chat code
    $stmt = $conn->prepare("SELECT users.username, chats.message, chats.sent_at 
                            FROM chats 
                            JOIN users ON chats.sender_id = users.id 
                            WHERE chat_code = ? ORDER BY sent_at ASC");
    $stmt->bind_param("s", $chat_code);
    $stmt->execute();

    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($messages);
}
?>
>>>>>>> ee7c9565c28e3f015817e1645a6e2d0b3b949065
