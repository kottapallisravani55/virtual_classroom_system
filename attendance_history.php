<?php
session_start();

// Ensure the user is logged in as a teacher
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

require '../database/db_config.php'; // Adjust path if necessary

// Get the teacher's ID from the session
$teacher_id = $_SESSION['user_id']; // Assuming teacher ID is stored in the session

// Check if teacher_id is set in session
if (empty($teacher_id)) {
    echo "No teacher ID found in session!";
    exit;
}

// Fetch unique attendance dates for filtering
$date_query = "SELECT DISTINCT attendance_date FROM attendance WHERE teacher_id = ? ORDER BY attendance_date DESC";
$date_stmt = $conn->prepare($date_query);
$date_stmt->bind_param("i", $teacher_id);
$date_stmt->execute();
$date_result = $date_stmt->get_result();

// Check if there are any attendance dates available
if ($date_result->num_rows > 0) {
    // Fetch all dates
    $dates = $date_result->fetch_all(MYSQLI_ASSOC);
} else {
    $dates = [];
}

// Handle filters
$selected_date = $_GET['date'] ?? '';
$search_id = $_GET['search_id'] ?? '';

$where_clauses = ["a.teacher_id = ?"];
$params = [$teacher_id];
$param_types = "i";

// Apply filter by selected date if set
if (!empty($selected_date)) {
    $where_clauses[] = "a.attendance_date = ?";
    $params[] = $selected_date;
    $param_types .= "s";
}

// Apply filter by student ID if set
if (!empty($search_id)) {
    $where_clauses[] = "a.student_id = ?";
    $params[] = $search_id;
    $param_types .= "i";
}

// Combine the where clauses
$where_sql = implode(" AND ", $where_clauses);

// Query to fetch attendance records
$query = "SELECT a.id, a.student_id, s.username AS student_name, a.attendance_status, a.attendance_date, a.subject
          FROM attendance a
          JOIN students s ON a.student_id = s.id
          WHERE $where_sql
          ORDER BY a.attendance_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$attendance_records = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        button {
            margin: 10px 0;
        }
    </style>
    <script>
        function printAttendance() {
            const printContents = document.getElementById('attendanceTable').outerHTML;
            const originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }
    </script>
    <div id="google_translate_element"></div>
</head>
<body>
    <h1>Attendance History</h1>

    <!-- Filters -->
    <form method="GET" action="">
        <label for="date">Filter by Date:</label>
        <select name="date" id="date">
            <option value="">All Dates</option>
            <?php foreach ($dates as $date): ?>
                <option value="<?php echo $date['attendance_date']; ?>" <?php echo $selected_date === $date['attendance_date'] ? 'selected' : ''; ?>>
                    <?php echo $date['attendance_date']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="search_id">Search by Student ID:</label>
        <input type="text" name="search_id" id="search_id" value="<?php echo htmlspecialchars($search_id); ?>" placeholder="Enter Student ID">

        <button type="submit">Filter</button>
        <button type="button" onclick="printAttendance()">Print Attendance</button>
    </form>

    <!-- Attendance records table -->
    <div id="attendanceTable">
        <table>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Attendance Date</th>
                <th>Subject</th>
                <th>Attendance Status</th>
                <th>Actions</th>
            </tr>
            <?php if (count($attendance_records) > 0): ?>
                <?php foreach ($attendance_records as $record): ?>
                    <tr>
                        <td><?php echo $record['student_id']; ?></td>
                        <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                        <td><?php echo $record['attendance_date']; ?></td>
                        <td><?php echo htmlspecialchars($record['subject']); ?></td>
                        <td><?php echo $record['attendance_status']; ?></td>
                        <td>
                            <a href="edit_attendance.php?id=<?php echo $record['id']; ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No attendance records found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
    <script src="script.js"></script>
      <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement(
                {pageLanguage: 'en'},
                'google_translate_element'
            );
        } 
  </script>
  <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

</body>
</html>
