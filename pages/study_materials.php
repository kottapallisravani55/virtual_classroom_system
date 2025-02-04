<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$subject = isset($_SESSION['subject']) ? $_SESSION['subject'] : null; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Materials - Virtual Classroom</title>
    <style>
        .dashboard-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            margin-top: -20px;
        }
        .subjects-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }
        .subject-block {
            flex: 1;
            min-width: 250px;
            padding: 20px;
            border-radius: 10px;
            color: white;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease; /* Smooth transition */
            height: 230px; /* Adjusted to make the block closer to square */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .subject-block.clicked {
            height: auto; /* Allow clicked block to expand */
            padding-bottom: 25px;
        }

        .subject-block h3 {
            font-size: 2rem; /* Increased size of subject name by default */
            transition: all 0.3s ease;
            margin: 0;
            position: relative;
            bottom: 0;
        }

        .subject-block.clicked h3 {
            font-size: 2.5rem; /* Increase font size for clicked subject */
            bottom: 20px; /* Move subject name to the top of the block */
        }

        .materials-list {
            list-style-type: none;
            padding: 10px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            display: none; /* Initially hide the document list */
        }

        .materials-list.show {
            display: block; /* Show documents when the block is clicked */
        }

        .btn {
            margin-top: 10px;
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-danger {
            background-color: #dc3545;
        }
    </style>
    <script>
        function toggleDocuments(subjectId) {
            const documentList = document.getElementById(subjectId);
            const subjectBlock = document.getElementById("block_" + subjectId);

            // Toggle visibility of the document list
            documentList.classList.toggle("show");

            // Toggle size of the clicked block
            subjectBlock.classList.toggle("clicked");

            // Collapse all other blocks
            const allSubjectBlocks = document.querySelectorAll('.subject-block');
            allSubjectBlocks.forEach(block => {
                if (block.id !== "block_" + subjectId) {
                    block.classList.remove("clicked");
                    block.querySelector('.materials-list').classList.remove('show');
                }
            });
        }
    </script>
</head>
<body>
    <div class="dashboard-container">
        <h1>Study Materials</h1>
        <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='message'>" . htmlspecialchars($_SESSION['message']) . "</div>";
            unset($_SESSION['message']);
        }
        ?>
        
        <div class="subjects-container">
        <?php
        include '../database/db_config.php';
        
        $subject_colors = ["#007bff", "#28a745", "#ffc107", "#dc3545", "#17a2b8", "#6f42c1"];
        $color_index = 0;
        
        if ($role == 'student') {
            $query = "SELECT DISTINCT subject FROM study_materials";
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $subject_name = $row['subject'];
                    $color = $subject_colors[$color_index % count($subject_colors)];
                    $color_index++;
                    $subjectId = "subject_" . $subject_name;
                    echo "<div class='subject-block' id='block_$subjectId' style='background-color: $color;' onclick='toggleDocuments(\"$subjectId\")'>";
                    echo "<h3>" . htmlspecialchars($subject_name) . "</h3>";
                    echo "<ul class='materials-list' id='$subjectId'>";
                    
                    $material_query = "SELECT * FROM study_materials WHERE subject = '$subject_name'";
                    $material_result = $conn->query($material_query);
                    while ($material_row = $material_result->fetch_assoc()) {
                        $file_name = htmlspecialchars($material_row['file_name']);
                        $upload_date = $material_row['upload_date'];
                        echo "<li><a href='../assets/uploads/$file_name' download>$file_name</a> - <small>Uploaded on: $upload_date</small></li>";
                    }
                    echo "</ul></div>";
                }
            } else {
                echo "<p>No subjects available.</p>";
            }
        } elseif ($role == 'teacher' && $subject) {
            $color = $subject_colors[$color_index % count($subject_colors)];
            $subjectId = "subject_" . $subject;
            echo "<div class='subject-block' id='block_$subjectId' style='background-color: $color;' onclick='toggleDocuments(\"$subjectId\")'>";
            echo "<h3>" . htmlspecialchars($subject) . "</h3>";
            echo "<ul class='materials-list' id='$subjectId'>";
            
            $query = "SELECT * FROM study_materials WHERE subject = '$subject'";
            $result = $conn->query($query);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $file_name = htmlspecialchars($row['file_name']);
                    $upload_date = $row['upload_date'];
                    echo "<li><a href='../assets/uploads/$file_name' download>$file_name</a> - <small>Uploaded on: $upload_date</small> ";
                    echo "<form action='delete_material.php' method='POST' style='display:inline;'>";
                    echo "<input type='hidden' name='file_id' value='{$row['id']}'>";
                    echo "<input type='hidden' name='file_name' value='$file_name'>";
                    echo "<button type='submit' class='btn btn-danger'>Delete</button>";
                    echo "</form></li>";
                }
            } else {
                echo "<li>No study materials for your subject yet.</li>";
            }
            echo "</ul></div>";
        }
        ?>
        </div>

        <?php if ($role == 'teacher'): ?>
            <h3>Upload New Material</h3>
            <form action="../assets/uploads/upload_material.php" method="POST" enctype="multipart/form-data">
                <label for="material_file">Select file:</label>
                <input type="file" name="material_file" id="material_file" required>
                <button type="submit" class="btn">Upload</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>