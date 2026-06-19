<?php
session_start();
require_once __DIR__ . '/../database/db_connect.php';
require_once __DIR__ . '/../includes/auth_timeout.php';

header('Content-Type: application/json');

apex_enforce_login_timeout();

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->getConnection();

    // 1. Sanitize Inputs
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $bio        = trim($_POST['bio'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $gender     = trim($_POST['gender'] ?? '');
    $birthday   = trim($_POST['birthday'] ?? '');

    $street     = trim($_POST['street_address'] ?? '');
    $city       = trim($_POST['city'] ?? '');
    $postal     = trim($_POST['postal_code'] ?? '');

    if ($first_name !== '' && !preg_match('/^[A-Za-z ]+$/', $first_name)) {
        echo json_encode(['success' => false, 'message' => 'Please only use letters for First Name.']);
        exit;
    }
    if ($last_name !== '' && !preg_match('/^[A-Za-z ]+$/', $last_name)) {
        echo json_encode(['success' => false, 'message' => 'Please only use letters for Last Name.']);
        exit;
    }
    if ($phone !== '' && !preg_match('/^09\d{9}$/', $phone)) {
        echo json_encode(['success' => false, 'message' => 'Contact number must start with 09 and be exactly 11 digits.']);
        exit;
    }

    // 2. Handle Profile Picture Upload
    $image_path = $_SESSION['user']['profile_picture'] ?? null; // Default to existing

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExt = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExt, $allowedExts)) {
            $newFileName = 'profile_' . $user_id . '_' . time() . '.' . $fileExt;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destPath)) {
                $image_path = 'assets/images/profiles/' . $newFileName;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid image format.']);
            exit;
        }
    }

    try {
        $conn->begin_transaction();

        // 3. Update main users_tbl
        $stmt1 = $conn->prepare("UPDATE users_tbl SET first_name = ?, last_name = ?, email = ?, gender = ? WHERE user_id = ?");
        $stmt1->bind_param("ssssi", $first_name, $last_name, $email, $gender, $user_id);
        $stmt1->execute();

        // 4. Update users_profiles_tbl
        // Check if profile exists first (in case it wasn't created during registration)
        $checkProf = $conn->prepare("SELECT profile_id FROM users_profiles_tbl WHERE user_id = ?");
        $checkProf->bind_param("i", $user_id);
        $checkProf->execute();

        if ($checkProf->get_result()->num_rows > 0) {
            // Added birthday here
            $stmt2 = $conn->prepare("UPDATE users_profiles_tbl SET bio = ?, street_address = ?, city = ?, zip_code = ?, phone_number = ?, image_path = ?, birthday = ? WHERE user_id = ?");
            $stmt2->bind_param("sssssssi", $bio, $street, $city, $postal, $phone, $image_path, $birthday, $user_id);
        } else {
            // Added birthday here
            $stmt2 = $conn->prepare("INSERT INTO users_profiles_tbl (bio, street_address, city, zip_code, phone_number, image_path, birthday, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("sssssssi", $bio, $street, $city, $postal, $phone, $image_path, $birthday, $user_id);
        }
        $stmt2->execute();

        $conn->commit();

        // 5. Update Session Data so UI refreshes without re-login
        $_SESSION['user']['first_name'] = $first_name;
        $_SESSION['user']['last_name'] = $last_name;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['gender'] = $gender;
        $_SESSION['user']['bio'] = $bio;
        $_SESSION['user']['phone'] = $phone;
        $_SESSION['user']['street_address'] = $street;
        $_SESSION['user']['city'] = $city;
        $_SESSION['user']['postal_code'] = $postal;
        $_SESSION['user']['profile_picture'] = $image_path;
        $_SESSION['user']['birthday'] = $birthday;

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }

    $db->closeConnection();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
