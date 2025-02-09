<<<<<<< HEAD
<?php
session_start();
require '../database/db_config.php';

// Check if the user is a teacher
if ($_SESSION['role'] != 'teacher') {
    die("Access Denied");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'upload') {
        // Upload assignment logic
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $deadline = trim($_POST['deadline'] ?? '');
        $assignment_file = $_FILES['assignment_file'] ?? null;

        if (empty($title) || empty($description) || empty($deadline) || empty($assignment_file['name'])) {
            die("Please provide all required fields, including the file upload and deadline.");
        }

        $deadline_date = date('Y-m-d H:i:s', strtotime($deadline));
        if ($deadline_date <= date('Y-m-d H:i:s')) {
            die("The deadline must be a future date.");
        }

        $teacher_query = $conn->prepare("
            SELECT teachers.uid, teachers.subject 
            FROM teachers 
            JOIN users ON teachers.uid = users.id 
            WHERE users.id = ?
        ");
        $teacher_query->bind_param("i", $_SESSION['user_id']);
        $teacher_query->execute();
        $teacher_result = $teacher_query->get_result();

        if ($teacher_result->num_rows > 0) {
            $teacher = $teacher_result->fetch_assoc();
            $uid = $teacher['uid'];
            $subject = $teacher['subject'];

            $target_dir = "../uploads/assignments/";
            $file_name = basename($assignment_file['name']);
            $file_path = $target_dir . $file_name;

            if (move_uploaded_file($assignment_file['tmp_name'], $file_path)) {
                $insert_query = $conn->prepare("
                    INSERT INTO assignments (title, description, uid, subject, file_path, deadline) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $insert_query->bind_param("ssisss", $title, $description, $uid, $subject, $file_path, $deadline_date);
                if ($insert_query->execute()) {
                    header("Location: upload_assignment.php?status=success");
                    exit();
                } else {
                    echo "Failed to upload assignment.";
                }
            } else {
                echo "Failed to upload the file.";
            }
        } else {
            echo "Teacher information not found.";
        }
    } elseif ($action === 'edit') {
        // Edit assignment logic
        $assignment_id = intval($_POST['assignment_id']);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $deadline = trim($_POST['deadline'] ?? '');

        if (empty($title) || empty($description) || empty($deadline)) {
            die("Please provide all required fields for editing.");
        }

        $deadline_date = date('Y-m-d H:i:s', strtotime($deadline));
        if ($deadline_date <= date('Y-m-d H:i:s')) {
            die("The deadline must be a future date.");
        }

        $update_query = $conn->prepare("
            UPDATE assignments 
            SET title = ?, description = ?, deadline = ? 
            WHERE id = ? AND uid = ?
        ");
        $update_query->bind_param("sssii", $title, $description, $deadline_date, $assignment_id, $_SESSION['user_id']);
        if ($update_query->execute()) {
            header("Location: upload_assignment.php?status=edited");
            exit();
        } else {
            echo "Failed to update assignment.";
        }
    } elseif ($action === 'delete') {
        // Delete assignment logic
        $assignment_id = intval($_POST['assignment_id']);
        $delete_query = $conn->prepare("
            DELETE FROM assignments 
            WHERE id = ? AND uid = ?
        ");
        $delete_query->bind_param("ii", $assignment_id, $_SESSION['user_id']);
        if ($delete_query->execute()) {
            header("Location: upload_assignment.php?status=deleted");
            exit();
        } else {
            echo "Failed to delete assignment.";
        }
    }
}

// Handle success messages
if (isset($_GET['status'])) {
    $status = $_GET['status'];
    if ($status === 'success') {
        echo "<p>Assignment uploaded successfully.</p>";
    } elseif ($status === 'edited') {
        echo "<p>Assignment edited successfully.</p>";
    } elseif ($status === 'deleted') {
        echo "<p>Assignment deleted successfully.</p>";
    }
}

// Fetch assignments
$assignments_query = $conn->prepare("
    SELECT * FROM assignments 
    WHERE uid = ?
");
$assignments_query->bind_param("i", $_SESSION['user_id']);
$assignments_query->execute();
$assignments = $assignments_query->get_result();
?>
<h2>Manage Assignments</h2>

<!-- Upload Form -->
<form action="upload_assignment.php" method="POST" enctype="multipart/form-data" id="assignmentForm">
    <input type="hidden" name="action" value="upload">
    <label for="title">Title:</label>
    <input type="text" id="title" name="title" required>
    
    <label for="description">Description:</label>
    <textarea id="description" name="description" required></textarea>

    <label for="deadline">Deadline:</label>
    <input type="datetime-local" id="deadline" name="deadline" required>

    <label for="assignment_file">File:</label>
    <input type="file" id="assignment_file" name="assignment_file" required>

    <button type="submit">Upload Assignment</button>
</form>

<!-- Assignment List -->
<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Deadline</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $assignments->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= htmlspecialchars($row['deadline']) ?></td>
                <td>
                    <!-- Edit Form -->
                    <form action="upload_assignment.php" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="assignment_id" value="<?= $row['id'] ?>">
                        <a href="edit_assignment.php?id=<?= $row['id']; ?>">Edit</a>
                    </form>

                    <!-- Delete Form -->
                    <form action="upload_assignment.php" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="assignment_id" value="<?= $row['id'] ?>">
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this assignment?');">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
function populateEditForm(data) {
    const form = document.getElementById('assignmentForm');
    form.action.value = 'edit';
    form.assignment_id.value = data.id;
    document.getElementById('title').value = data.title;
    document.getElementById('description').value = data.description;
    document.getElementById('deadline').value = data.deadline.replace(' ', 'T');
}
</script>
=======
<?php
session_start();
require '../database/db_config.php';

// Check if the user is a teacher
if ($_SESSION['role'] != 'teacher') {
    die("Access Denied");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'upload') {
        // Upload assignment logic
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $deadline = trim($_POST['deadline'] ?? '');
        $assignment_file = $_FILES['assignment_file'] ?? null;

        if (empty($title) || empty($description) || empty($deadline) || empty($assignment_file['name'])) {
            die("Please provide all required fields, including the file upload and deadline.");
        }

        $deadline_date = date('Y-m-d H:i:s', strtotime($deadline));
        if ($deadline_date <= date('Y-m-d H:i:s')) {
            die("The deadline must be a future date.");
        }

        $teacher_query = $conn->prepare("
            SELECT teachers.uid, teachers.subject 
            FROM teachers 
            JOIN users ON teachers.uid = users.id 
            WHERE users.id = ?
        ");
        $teacher_query->bind_param("i", $_SESSION['user_id']);
        $teacher_query->execute();
        $teacher_result = $teacher_query->get_result();

        if ($teacher_result->num_rows > 0) {
            $teacher = $teacher_result->fetch_assoc();
            $uid = $teacher['uid'];
            $subject = $teacher['subject'];

            $target_dir = "../uploads/assignments/";
            $file_name = basename($assignment_file['name']);
            $file_path = $target_dir . $file_name;

            if (move_uploaded_file($assignment_file['tmp_name'], $file_path)) {
                $insert_query = $conn->prepare("
                    INSERT INTO assignments (title, description, uid, subject, file_path, deadline) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $insert_query->bind_param("ssisss", $title, $description, $uid, $subject, $file_path, $deadline_date);
                if ($insert_query->execute()) {
                    header("Location: upload_assignment.php?status=success");
                    exit();
                } else {
                    echo "Failed to upload assignment.";
                }
            } else {
                echo "Failed to upload the file.";
            }
        } else {
            echo "Teacher information not found.";
        }
    } elseif ($action === 'edit') {
        // Edit assignment logic
        $assignment_id = intval($_POST['assignment_id']);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $deadline = trim($_POST['deadline'] ?? '');

        if (empty($title) || empty($description) || empty($deadline)) {
            die("Please provide all required fields for editing.");
        }

        $deadline_date = date('Y-m-d H:i:s', strtotime($deadline));
        if ($deadline_date <= date('Y-m-d H:i:s')) {
            die("The deadline must be a future date.");
        }

        $update_query = $conn->prepare("
            UPDATE assignments 
            SET title = ?, description = ?, deadline = ? 
            WHERE id = ? AND uid = ?
        ");
        $update_query->bind_param("sssii", $title, $description, $deadline_date, $assignment_id, $_SESSION['user_id']);
        if ($update_query->execute()) {
            header("Location: upload_assignment.php?status=edited");
            exit();
        } else {
            echo "Failed to update assignment.";
        }
    } elseif ($action === 'delete') {
        // Delete assignment logic
        $assignment_id = intval($_POST['assignment_id']);
        $delete_query = $conn->prepare("
            DELETE FROM assignments 
            WHERE id = ? AND uid = ?
        ");
        $delete_query->bind_param("ii", $assignment_id, $_SESSION['user_id']);
        if ($delete_query->execute()) {
            header("Location: upload_assignment.php?status=deleted");
            exit();
        } else {
            echo "Failed to delete assignment.";
        }
    }
}

// Handle success messages
if (isset($_GET['status'])) {
    $status = $_GET['status'];
    if ($status === 'success') {
        echo "<p>Assignment uploaded successfully.</p>";
    } elseif ($status === 'edited') {
        echo "<p>Assignment edited successfully.</p>";
    } elseif ($status === 'deleted') {
        echo "<p>Assignment deleted successfully.</p>";
    }
}

// Fetch assignments
$assignments_query = $conn->prepare("
    SELECT * FROM assignments 
    WHERE uid = ?
");
$assignments_query->bind_param("i", $_SESSION['user_id']);
$assignments_query->execute();
$assignments = $assignments_query->get_result();
?>
<h2>Manage Assignments</h2>

<!-- Upload Form -->
<form action="upload_assignment.php" method="POST" enctype="multipart/form-data" id="assignmentForm">
    <input type="hidden" name="action" value="upload">
    <label for="title">Title:</label>
    <input type="text" id="title" name="title" required>
    
    <label for="description">Description:</label>
    <textarea id="description" name="description" required></textarea>

    <label for="deadline">Deadline:</label>
    <input type="datetime-local" id="deadline" name="deadline" required>

    <label for="assignment_file">File:</label>
    <input type="file" id="assignment_file" name="assignment_file" required>

    <button type="submit">Upload Assignment</button>
</form>

<!-- Assignment List -->
<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Deadline</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $assignments->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= htmlspecialchars($row['deadline']) ?></td>
                <td>
                    <!-- Edit Form -->
                    <form action="upload_assignment.php" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="assignment_id" value="<?= $row['id'] ?>">
                        <a href="edit_assignment.php?id=<?= $row['id']; ?>">Edit</a>
                    </form>

                    <!-- Delete Form -->
                    <form action="upload_assignment.php" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="assignment_id" value="<?= $row['id'] ?>">
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this assignment?');">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
function populateEditForm(data) {
    const form = document.getElementById('assignmentForm');
    form.action.value = 'edit';
    form.assignment_id.value = data.id;
    document.getElementById('title').value = data.title;
    document.getElementById('description').value = data.description;
    document.getElementById('deadline').value = data.deadline.replace(' ', 'T');
}
</script>
>>>>>>> ee7c9565c28e3f015817e1645a6e2d0b3b949065
