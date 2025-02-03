<?php
// Include database connection
include '../database/db_config.php';

// Check if the user is logged in and is a teacher
session_start();
if ($_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit();
}

// Get filtered parameters
$filter_date = isset($_POST['filter_date']) ? $_POST['filter_date'] : null;
$subject = isset($_POST['subject']) ? $_POST['subject'] : null;

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=filtered_attendance_list.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Write the header row to the CSV file
fputcsv($output, ['Date', 'Student ID', 'Student Name', 'Status']);

// Build the query based on filters
$query = "SELECT a.date, a.student_id, s.username AS student_name, a.status 
          FROM attendance a
          INNER JOIN students s ON a.student_id = s.id
          WHERE a.subject = ?";

$params = [$subject];

if ($filter_date) {
    $query .= " AND a.date = ?";
    $params[] = $filter_date;
}

$stmt = $conn->prepare($query);
$stmt->bind_param(str_repeat("s", count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Write filtered records to the CSV file
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

// Close the output stream
fclose($output);
exit();
?>
