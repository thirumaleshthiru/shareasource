<?php
session_start();
include 'config.php';

if (!isset($_SESSION['otp'])) {
    // If no OTP in session, redirect to registration
    header("Location: register.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = $_POST['otp'];

    if (time() > $_SESSION['otp_expiration']) {
        $message = "OTP has expired. Please try registering again.";
        unset($_SESSION['otp'], $_SESSION['otp_expiration'], $_SESSION['username'], $_SESSION['password'], $_SESSION['email']);
    } elseif ($entered_otp == $_SESSION['otp']) {
        // OTP verified
        $username = $_SESSION['username'];
        $password = $_SESSION['password'];
        $email = $_SESSION['email'];

        // Determine role
        $role = ($username === 'admin') ? 'admin' : 'user';

        // Insert user into database
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            die('MySQL prepare error: ' . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("ssss", $username, $password, $email, $role);

        if ($stmt->execute()) {
            // Registration successful
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['role'] = $role;
            $_SESSION['username'] = $username;
            unset($_SESSION['otp'], $_SESSION['otp_expiration'], $_SESSION['password'], $_SESSION['email']);
            header("Location: login.php");
            exit();
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "Invalid OTP. Please try again.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #EEEDEB;
            color: #2F3645;
            margin: 0;
            padding: 0;
        }
        .verify-container {
            max-width: 400px;
            width: 100%;
            margin: 50px auto;
            padding: 20px;
            background-color: #E6B9A6;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
        }
        input[type="text"] {
            padding: 10px;
            border: 1px solid #939185;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #2F3645;
            color: #EEEDEB;
            cursor: pointer;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            color: #EEEDEB;
            background-color: #FF6B6B;
        }
    </style>
    <link rel="stylesheet" href="style.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="verify-container">
        <h2>Verify OTP</h2>
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="verify_otp.php" method="POST">
            <div>
                <label for="otp">OTP:</label>
                <input type="text" id="otp" name="otp" required>
            </div>
            <br>
            <button type="submit">Verify</button>
        </form>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
