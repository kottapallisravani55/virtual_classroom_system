<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../database/db_config.php';

$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Classes - Virtual Classroom</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <div id="google_translate_element"></div>
</head>
<body>
    <div class="dashboard-container">
        <h2>Live Classes</h2>
        <p>Welcome, <?php echo htmlspecialchars($username); ?>!</p>

        <h3>Upcoming Live Classes:</h3>
        <ul>
            <?php
            if ($role === 'student') {
                // Fetch all subjects assigned to teachers
                $subject_query = "SELECT subject, teacher_id FROM teacher_subjects";
                $subject_result = $conn->query($subject_query);

                if ($subject_result->num_rows > 0) {
                    while ($subject_row = $subject_result->fetch_assoc()) {
                        $subject = htmlspecialchars($subject_row['subject']);
                        $teacher_id = $subject_row['teacher_id'];

                        echo "<li><strong>$subject</strong><ul>";

                        // Fetch live classes based on the teacher ID and matching class_name with the subject
                        $class_query = "SELECT * FROM live_classes WHERE teacher_id = ? AND class_name = ?";
                        $class_stmt = $conn->prepare($class_query);
                        $class_stmt->bind_param("is", $teacher_id, $subject);
                        $class_stmt->execute();
                        $class_result = $class_stmt->get_result();

                        if ($class_result->num_rows > 0) {
                            while ($class_row = $class_result->fetch_assoc()) {
                                $class_name = htmlspecialchars($class_row['class_name']);
                                $class_date = date("F j, Y, g:i a", strtotime($class_row['class_date']));
                                $description = htmlspecialchars($class_row['description']);
                                $class_link = htmlspecialchars($class_row['class_link']);
                                $video_path = htmlspecialchars($class_row['video_path'] ?? '');

                                echo "<li>
                                    <strong>$class_name</strong><br>
                                    <em>$class_date</em><br>
                                    <p>$description</p>
                                    <a href='$class_link' target='_blank'>Join Class</a><br>";
                                
                                if (!empty($video_path)) {
                                    echo "<a href='../uploads/videos/$video_path' target='_blank'>View Recording</a>";
                                }

                                echo "</li>";
                            }
                        } else {
                            echo "<li>No classes available for this subject.</li>";
                        }
                        echo "</ul></li>";
                    }
                } else {
                    echo "<li>No subjects available.</li>";
                }
            } elseif ($role === 'teacher') {
                // Fetch live classes created by the logged-in teacher
                $teacher_id = $_SESSION['user_id'];
                $class_query = "SELECT * FROM live_classes WHERE teacher_id = ?";
                $class_stmt = $conn->prepare($class_query);
                $class_stmt->bind_param("i", $teacher_id);
                $class_stmt->execute();
                $class_result = $class_stmt->get_result();

                if ($class_result->num_rows > 0) {
                    while ($class_row = $class_result->fetch_assoc()) {
                        $class_name = htmlspecialchars($class_row['class_name']);
                        $class_date = date("F j, Y, g:i a", strtotime($class_row['class_date']));
                        $description = htmlspecialchars($class_row['description']);
                        $class_link = htmlspecialchars($class_row['class_link']);
                        $video_path = htmlspecialchars($class_row['video_path'] ?? '');

                        echo "<li>
                            <strong>$class_name</strong><br>
                            <em>$class_date</em><br>
                            <p>$description</p>
                            <a href='$class_link' target='_blank'>Join Class</a><br>";

                        if (!empty($video_path)) {
                            echo "<a href='../uploads/videos/$video_path' target='_blank'>View Recording</a>";
                        }

                        echo "<form method='POST' action='delete_live_class.php' style='display:inline;'>
                                <input type='hidden' name='class_id' value='{$class_row['id']}'>
                                <button type='submit' class='btn btn-danger'>Delete</button>
                              </form>
                              <a href='edit_live_class.php?class_id={$class_row['id']}' class='btn'>Edit</a>
                              <form method='POST' action='upload_video.php' enctype='multipart/form-data' style='display:inline;'>
                                <input type='hidden' name='class_id' value='{$class_row['id']}'>
                                <input type='file' name='class_video' required>
                                <button type='submit' class='btn'>Upload Video</button>
                              </form>";

                        echo "</li>";
                    }
                } else {
                    echo "<li>No live classes created yet.</li>";
                }
            }
            ?>
        </ul>

        <?php if ($role === 'teacher'): ?>
            <a href="create_live_class.php" class="btn">Schedule a New Class</a>
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
