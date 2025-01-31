<?php
session_start();
include('../database/db_config.php');

// Ensure the user is logged in as a teacher
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit();
}

// Retrieve the teacher's ID from the session
$teacher_id = $_SESSION['user_id']; // Teacher's user ID stored in session
$teacher_subject = $_SESSION['subject']; // Teacher's subject stored in session

// Check if the attendance data and date are provided
if (!isset($_POST['attendance_date']) || !isset($_POST['attendance'])) {
    die("Error: Attendance data or date is missing.");
}

$attendance_date = $_POST['attendance_date']; // The date of attendance
$attendance = $_POST['attendance']; // The attendance data (student_id => status)

// Update attendance for each student
foreach ($attendance as $student_id => $status) {
    $attendance_status = ($status === 'Present') ? 'Present' : 'Absent';

    // Check if attendance already exists for the given student and date
    $sql = "SELECT * FROM attendance WHERE teacher_id = ? AND subject = ? AND student_id = ? AND attendance_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $teacher_id, $teacher_subject, $student_id, $attendance_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Attendance already exists, update it
        $update_sql = "UPDATE attendance SET attendance_status = ? WHERE teacher_id = ? AND subject = ? AND student_id = ? AND attendance_date = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssi", $attendance_status, $teacher_subject, $teacher_id, $student_id, $attendance_date);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // If attendance doesn't exist, insert a new record
        $insert_sql = "INSERT INTO attendance (student_id, teacher_id, subject, attendance_status, attendance_date) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iisss", $student_id, $teacher_id, $teacher_subject, $attendance_status, $attendance_date);
        $insert_stmt->execute();
        $insert_stmt->close();
    }

    $stmt->close();
}

$conn->close();

// Redirect to attendance history page after updating
header("Location: attendance_history.php?attendance_date=" . $attendance_date);
exit();
?>
