<?php
session_start(); // Ensure session is started

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require '../database/db_config.php'; // Database connection

$user_id = $_SESSION['user_id'];

// Fetch user details from the 'users' table
$sql_user = "SELECT username, email, role, password FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows == 1) {
    $user = $result_user->fetch_assoc();
} else {
    echo "User not found.";
    exit;
}

// Define default avatar
$default_avatar = "default-avatar.png";
$avatar_path = "../assets/avatars/$user_id-avatar.jpg"; // Path to user's custom avatar
$current_avatar = file_exists($avatar_path) ? "$user_id-avatar.jpg" : $default_avatar;

// List of predefined avatars
$predefined_avatars = ["avatar1.jpg", "avatar2.jpg", "avatar3.jpg", "avatar4.jpg", "avatar5.jpg"];

// Handle avatar upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle uploaded file
    if (isset($_FILES['upload_photo']) && $_FILES['upload_photo']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = "../assets/avatars/";
        $uploaded_file = $upload_dir . $user_id . "-avatar.jpg";

        // Move the uploaded file to the avatars directory
        if (move_uploaded_file($_FILES['upload_photo']['tmp_name'], $uploaded_file)) {
            $current_avatar = "$user_id-avatar.jpg";
            echo "<script>alert('Profile photo updated successfully!');</script>";
        } else {
            echo "<script>alert('Failed to upload photo. Please try again.');</script>";
        }
    }

    // Handle selected avatar
    if (isset($_POST['selected_avatar'])) {
        $selected_avatar = $_POST['selected_avatar'];

        // Copy the selected avatar to a unique file for the user
        $selected_avatar_path = "../assets/avatars/$selected_avatar";
        $user_avatar_path = "../assets/avatars/$user_id-avatar.jpg";

        if (copy($selected_avatar_path, $user_avatar_path)) {
            $current_avatar = "$user_id-avatar.jpg";
            echo "<script>alert('Avatar updated successfully!');</script>";
        } else {
            echo "<script>alert('Failed to update avatar.');</script>";
        }
    }

    // Handle password update
    if (isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify the current password
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                $sql_update_password = "UPDATE users SET password = ? WHERE id = ?";
                $stmt_update_password = $conn->prepare($sql_update_password);
                $stmt_update_password->bind_param("si", $hashed_password, $user_id);

                if ($stmt_update_password->execute()) {
                    echo "<script>alert('Password updated successfully!');</script>";
                } else {
                    echo "<script>alert('Failed to update password. Please try again.');</script>";
                }
            } else {
                echo "<script>alert('New password and confirmation do not match!');</script>";
            }
        } else {
            echo "<script>alert('Current password is incorrect!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
                body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: rgba(0, 0, 0, 0.1);
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #333;
        }
        .profile-photo-container {
            position: relative;
            display: inline-block;
        }
        .profile-photo {
            margin: 20px auto;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #007bff;
            cursor: pointer;
        }
        .upload-icon {
            position: absolute;
            bottom: 0;
            right: 0;
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            cursor: pointer;
        }
        .profile-info {
            font-size: 16px;
            line-height: 1.6;
            text-align: left;
            margin-top: 20px;
        }
        .profile-info strong {
            color: #007bff;
        }
        .avatars {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }
        .avatars img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            padding: 5px;
        }
        .avatars img:hover {
            border-color: #007bff;
        }
        .avatars img.selected {
            border-color: #007bff;
        }
        .form-group {
            margin: 10px 0;
            text-align: left;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .save-btn{
            padding: 10px 20px; 
            background-color: #007bff; 
            color: white;
             border: none; 
             border-radius: 10px; 
             cursor: pointer;
        }
        .save-btn:hover {
            background-color: #0056b3;
        }
        .password-section {
            display: none;
            margin-top: 20px;
        }
    

    </style>
     
   
</head>
<body>
    <div class="container">
        
        <!-- Current Profile Photo -->
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="profile-photo-container">
                <img src="../assets/avatars/<?php echo htmlspecialchars($current_avatar); ?>" alt="Profile Photo" class="profile-photo" onclick="document.getElementById('upload_photo').click();">
                <div class="upload-icon" onclick="document.getElementById('upload_photo').click();">+</div>
            </div>
            <input type="file" name="upload_photo" id="upload_photo" style="display: none;" onchange="this.form.submit();">
        </form>

        <!-- User information -->
        <div class="profile-info">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
        </div>

        <!-- Avatar Selection -->
        <form method="POST" action="">
            <h3>Select an Avatar</h3>
            <div class="avatars">
                <?php foreach ($predefined_avatars as $avatar): ?>
                    <label>
                        <input 
                            type="radio" 
                            name="selected_avatar" 
                            value="<?php echo $avatar; ?>" 
                            style="display:none;" 
                            onclick="highlightAvatar(this)"
                            <?php echo $current_avatar === $avatar ? 'checked' : ''; ?>
                        >
                        <img 
                            src="../assets/avatars/<?php echo $avatar; ?>" 
                            alt="<?php echo $avatar; ?>" 
                            class="avatar-img <?php echo $current_avatar === $avatar ? 'selected' : ''; ?>"
                        >
                    </label>
                <?php endforeach; ?>
            </div>
            <button type="submit"  class="save-btn">
                Save
            </button>
        </form>
        <button class="btn" onclick="togglePasswordSection()">Change Password</button>
        
        <div class="password-section" id="password-section">
            <form method="POST" action="">
                <div class="form-group">
                    <input type="password" name="current_password" placeholder="Current Password" required>
                </div>
                <div class="form-group">
                    <input type="password" name="new_password" placeholder="New Password" required>
                </div>
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                </div>
                <button type="submit" class="btn">Update Password</button>
            </form>
        </div>        
    </div>

    <script>
        function highlightAvatar(inputElement) {
            const allAvatars = document.querySelectorAll('.avatar-img');
            allAvatars.forEach(avatar => avatar.classList.remove('selected'));

            // Highlight the selected avatar
            inputElement.nextElementSibling.classList.add('selected');
        }
        function togglePasswordSection() {
            const section = document.getElementById('password-section');
            section.style.display = section.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
