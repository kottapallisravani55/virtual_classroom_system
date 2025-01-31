<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

require '../database/db_config.php'; // Adjust path if necessary

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendance_date = $_POST['attendance_date'];
    $subject = $_POST['subject'];
    $teacher_id = $_SESSION['user_id']; // Assuming teacher's ID is stored in session
    $attendance = $_POST['attendance'];

    // Check if attendance already exists for this date
    $check_sql = "SELECT * FROM attendance WHERE attendance_date = ? AND subject = ? AND teacher_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ssi", $attendance_date, $subject, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Error: Attendance for this date already exists.";
        exit;
    }

    // Insert attendance records
    $insert_sql = "INSERT INTO attendance (student_id, teacher_id, subject, attendance_status, attendance_date, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert_sql);

    foreach ($attendance as $student_id => $status) {
        $stmt->bind_param("iisss", $student_id, $teacher_id, $subject, $status, $attendance_date);
        $stmt->execute();
    }

    // Set success message in the session
    $_SESSION['success_message'] = "Attendance for $attendance_date has been taken successfully.";
    header("Location: class_attendance.php");
    exit;
}
?>
