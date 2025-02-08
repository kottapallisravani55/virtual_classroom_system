<?php
session_start();
include '../database/db_config.php';

// Check if the user is logged in and is a student
if ($_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

// Get the student's email from the session
$student_email = $_SESSION['email'];

// Fetch the student details (to get student ID)
$query = "SELECT id, username FROM students WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_email);
$stmt->execute();
$result = $stmt->get_result();
$student_data = $result->fetch_assoc();
$stmt->close();

// Ensure the student exists in the system
if (!$student_data) {
    die("Error: Unable to fetch student data. Please contact admin.");
}

$student_id = $student_data['id'];
$student_name = $student_data['username'];

// Handle date filter and sorting
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : null;
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'date'; // Default sorting by date
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC'; // Default order: descending

// Prepare the SQL query to get the student's attendance data
$query = "SELECT a.date, a.subject, a.status 
          FROM attendance a
          WHERE a.student_id = ?";

if ($filter_date) {
    $query .= " AND a.date = ?";
}

$query .= " ORDER BY $sort_by $order";

$stmt = $conn->prepare($query);
if ($filter_date) {
    $stmt->bind_param("ss", $student_id, $filter_date);
} else {
    $stmt->bind_param("s", $student_id);
}
$stmt->execute();
$result = $stmt->get_result();
$attendance_records = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        form input, form select, form button {
            padding: 10px;
            font-size: 14px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        form button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        form button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .no-data {
            text-align: center;
            margin: 20px 0;
            color: red;
        }
    </style>
    <div id="google_translate_element"></div>
</head>
<body>
    <div class="container">
        <h2>Attendance Records</h2>
        <p><strong>Student:</strong> <?php echo htmlspecialchars($student_name); ?></p>

        <!-- Filter and Sorting Form -->
        <form method="GET" action="student_attendance.php">
            <div>
                <label for="filter_date">Filter by Date:</label>
                <input type="date" name="filter_date" id="filter_date" value="<?php echo htmlspecialchars($filter_date); ?>">
            </div>
            <div>
                <label for="sort_by">Sort by:</label>
                <select name="sort_by" id="sort_by">
                    <option value="date" <?php echo $sort_by === 'date' ? 'selected' : ''; ?>>Date</option>
                    <option value="subject" <?php echo $sort_by === 'subject' ? 'selected' : ''; ?>>Subject</option>
                    <option value="status" <?php echo $sort_by === 'status' ? 'selected' : ''; ?>>Status</option>
                </select>
                <select name="order" id="order">
                    <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                    <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                </select>
            </div>
            <button type="submit">Apply</button>
        </form>

        <?php if (empty($attendance_records)): ?>
            <p class="no-data">No attendance records found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance_records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['date']); ?></td>
                            <td><?php echo htmlspecialchars($record['subject']); ?></td>
                            <td><?php echo htmlspecialchars($record['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
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
