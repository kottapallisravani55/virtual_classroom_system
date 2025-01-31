<?php
include '../database/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_POST['teacher_id'];
    $student_id = $_POST['student_id'];

    // Generate a random chat code
    $chat_code = substr(md5(uniqid(mt_rand(), true)), 0, 8);

    // Check if teacher exists
    $check_teacher_sql = "SELECT COUNT(*) FROM teachers WHERE uid = ?";
    $stmt = $conn->prepare($check_teacher_sql);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $stmt->bind_result($teacher_exists);
    $stmt->fetch();

    if ($teacher_exists == 0) {
        echo json_encode(["success" => false, "error" => "Invalid teacher ID."]);
        exit();
    }

    // Check if student exists
    $check_student_sql = "SELECT COUNT(*) FROM students WHERE id = ?";
    $stmt = $conn->prepare($check_student_sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->bind_result($student_exists);
    $stmt->fetch();

    if ($student_exists == 0) {
        echo json_encode(["success" => false, "error" => "Invalid student ID."]);
        exit();
    }

    // Insert new chat code into the database
    $stmt = $conn->prepare("INSERT INTO chats (chat_code, teacher_id, student_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $chat_code, $teacher_id, $student_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "chat_code" => $chat_code]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
}
?>
