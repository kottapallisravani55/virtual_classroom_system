<?php
session_start();
require '../database/db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Access Denied.");
}

$student_id = $_SESSION['user_id'];

// Fetch all assignments grouped by subject
$result = $conn->query("SELECT subject, GROUP_CONCAT(id, '|', title, '|', file_path, '|', uploaded_at, '|', deadline SEPARATOR ';') AS assignments 
                        FROM assignments 
                        GROUP BY subject");

?>

<h1>Assignments by Subject</h1>

<?php while ($row = $result->fetch_assoc()): 
    $subject = $row['subject'];
    $assignments = explode(';', $row['assignments']); // Split assignments by `;`
?>
    <h2><?php echo htmlspecialchars($subject); ?></h2>
    <table border="1">
        <tr>
            <th>Title</th>
            <th>Uploaded At</th>
            <th>Assignment File</th>
            <th>Submission</th>
        </tr>
        <?php foreach ($assignments as $assignment_data): 
            list($assignment_id, $title, $file_path, $uploaded_at, $deadline) = explode('|', $assignment_data);
            $deadline_date = date('Y-m-d', strtotime($deadline));
            $current_day = date('Y-m-d');

            // Skip assignments with deadlines in the past
            if ($deadline_date < $current_day) {
                continue;
            }

            // Check if the student has already submitted for this assignment
            $submission_query = $conn->prepare("SELECT id, file_path FROM submissions WHERE assignment_id = ? AND student_id = ?");
            $submission_query->bind_param("ii", $assignment_id, $student_id);
            $submission_query->execute();
            $submission_result = $submission_query->get_result();
            $submission = $submission_result->fetch_assoc();
        ?>
        <tr>
            <td><?php echo htmlspecialchars($title); ?></td>
            <td><?php echo htmlspecialchars($uploaded_at); ?></td>
            <td>
                <a href="<?php echo htmlspecialchars($file_path); ?>" target="_self">View Assignment</a>
            </td>
            <td>
                <?php if ($submission): ?>
                    <p>Submitted File: <a href="<?php echo htmlspecialchars($submission['file_path']); ?>" target="_self">View</a></p>
                    <form action="delete_submission.php" method="POST">
                        <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                        <button type="submit">Delete Submission</button>
                    </form>
                <?php else: ?>
                    <form action="submit_assignment.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">
                        <input type="file" name="assignment_file" required>
                        <button type="submit">Submit</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endwhile; ?>
