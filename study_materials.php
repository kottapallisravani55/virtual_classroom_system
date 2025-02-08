<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$subject = isset($_SESSION['subject']) ? $_SESSION['subject'] : null; // Check if the user is a teacher and has a subject

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Materials - Virtual Classroom</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .dashboard-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2, h3 {
            color: #333;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        ul li {
            margin-bottom: 10px;
        }
        ul li a {
            text-decoration: none;
            color: #007bff;
        }
        ul li a:hover {
            text-decoration: underline;
        }
        .btn {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #a71d2a;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            border-radius: 5px;
            color: green;
        }
    </style>
    <div id="google_translate_element"></div>
</head>
<body>
    <div class="dashboard-container">
        <h2>Study Materials</h2>
        <p>Welcome, <?php echo htmlspecialchars($username); ?>!</p>

        <!-- Display success or error messages -->
        <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='message'>" . htmlspecialchars($_SESSION['message']) . "</div>";
            unset($_SESSION['message']);
        }
        ?>

        <h3>Available Study Materials:</h3>
        <ul>
            <?php
            include '../database/db_config.php';

            // For students: Fetch materials subject-wise
            if ($role == 'student') {
                // Fetch all available subjects and study materials for the student
                $query = "SELECT DISTINCT subject FROM study_materials";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $subject_name = $row['subject'];
                        echo "<li><h4>" . htmlspecialchars($subject_name) . "</h4><ul>";

                        // Fetch study materials for this subject
                        $material_query = "SELECT * FROM study_materials WHERE subject = '$subject_name'";
                        $material_result = $conn->query($material_query);
                        while ($material_row = $material_result->fetch_assoc()) {
                            $file_name = htmlspecialchars($material_row['file_name']);
                            $upload_date = $material_row['upload_date'];  // Timestamp of upload
                            echo "<li><a href='../assets/uploads/$file_name' download>$file_name</a> - <small>Uploaded on: $upload_date</small></li>";
                        }
                        echo "</ul></li>";
                    }
                } else {
                    echo "<li>No subjects available.</li>";
                }
            } 
            // For teachers: Fetch their own materials based on subject
            else if ($role == 'teacher') {
                if ($subject) {
                    // Fetch study materials for the teacher's subject
                    $query = "SELECT * FROM study_materials WHERE subject = '$subject'";
                    $result = $conn->query($query);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $file_name = htmlspecialchars($row['file_name']);
                            $upload_date = $row['upload_date'];  // Timestamp of upload
                            echo "<li><a href='../assets/uploads/$file_name' download>$file_name</a> - <small>Uploaded on: $upload_date</small> 
                                    <form action='delete_material.php' method='POST' style='display:inline;'>
                                        <input type='hidden' name='file_id' value='{$row['id']}'>
                                        <input type='hidden' name='file_name' value='$file_name'>
                                        <button type='submit' class='btn btn-danger'>Delete</button>
                                    </form>
                                  </li>";
                        }
                    } else {
                        echo "<li>No study materials for your subject yet.</li>";
                    }
                } else {
                    echo "<li>No subject assigned to you.</li>";
                }
            }
            ?>
        </ul>

        <?php if ($_SESSION['role'] == 'teacher'): ?>
            <h3>Upload New Material</h3>
            <form action="../assets/uploads/upload_material.php" method="POST" enctype="multipart/form-data">
                <label for="material_file">Select file:</label>
                <input type="file" name="material_file" id="material_file" required>
                <button type="submit" class="btn">Upload</button>
            </form>
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
