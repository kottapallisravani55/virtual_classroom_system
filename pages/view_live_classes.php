<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_class_id'])) {
    include '../database/db_config.php';
    $class_id = $_POST['delete_class_id'];

    // Only allow teachers to delete their own classes
    if ($_SESSION['role'] == 'teacher') {
        $query = "DELETE FROM live_classes WHERE id = ? AND teacher_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $class_id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            echo "Class deleted successfully!";
        } else {
            echo "Failed to delete the class: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Classes - Virtual Classroom</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <div class="dashboard-container">
        <h2>Live Classes</h2>
        <p>Welcome, <?php echo htmlspecialchars($username); ?>!</p>

        <h3>Upcoming Live Classes:</h3>
        <ul>
            <?php
            include '../database/db_config.php';

            // Fetch all live classes
            $query = ($_SESSION['role'] == 'teacher') 
                ? "SELECT * FROM live_classes WHERE teacher_id = ? ORDER BY class_date ASC"
                : "SELECT * FROM live_classes ORDER BY class_date ASC";

            $stmt = $conn->prepare($query);

            // Bind teacher ID for teachers
            if ($_SESSION['role'] == 'teacher') {
                $stmt->bind_param("i", $_SESSION['user_id']);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $class_id = $row['id'];
                    $class_name = htmlspecialchars($row['class_name']);
                    $description = htmlspecialchars($row['description']);
                    $class_date = date("F j, Y, g:i a", strtotime($row['class_date']));
                    $class_link = htmlspecialchars($row['class_link']);
                    $video_path = htmlspecialchars($row['video_path'] ?? '');

                    echo "<li>
                            <strong>$class_name</strong><br>
                            <em>$class_date</em><br>
                            <p>$description</p>
                            <a href='$class_link' target='_blank'>Join Class</a><br>";

                    if (!empty($video_path)) {
                        echo "<a href='../uploads/videos/$video_path' target='_blank'>View Recording</a><br>";
                    }

                    // Allow deletion and video upload only for teachers
                    if ($_SESSION['role'] == 'teacher') {
                        echo "<form method='POST' action='view_live_classes.php' style='display:inline;'>
                                <input type='hidden' name='delete_class_id' value='$class_id'>
                                <button type='submit' class='btn btn-danger'>Delete</button>
                              </form>
                              <form method='POST' action='upload_video.php' enctype='multipart/form-data' style='display:inline;'>
                                <input type='hidden' name='class_id' value='$class_id'>
                                <input type='file' name='class_video' required>
                                <button type='submit' class='btn'>Upload Video</button>
                              </form>";
                    }

                    echo "</li>";
                }
            } else {
                echo "<li>No upcoming classes available.</li>";
            }
            ?>
        </ul>

        <?php if ($_SESSION['role'] == 'teacher'): ?>
            <a href="create_live_class.php" class="btn">Schedule a New Class</a>
        <?php endif; ?>
    </div>
</body>
</html>
