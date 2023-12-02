<?php
session_start();

// Database connection parameters
$host = "localhost";
$port = "5432";
$dbname = "your_database_name";
$user = "your_username";
$password = "your_password";

// Connect to PostgreSQL
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

// Function to sanitize user input
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST["username"]);
    $password = sanitize_input($_POST["password"]);

    // Query the database for the user
    $query = "SELECT * FROM users WHERE username = $1 AND password = $2";
    $result = pg_query_params($conn, $query, array($username, $password));

    if ($row = pg_fetch_assoc($result)) {
        // Authentication successful
        $_SESSION["username"] = $row["username"];
        header("Location: dashboard.php"); // Redirect to the dashboard or another authenticated page
        exit();
    } else {
        // Authentication failed
        $error_message = "Invalid username or password";
    }
}

// Process registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $username = sanitize_input($_POST["username"]);
    $password = password_hash(sanitize_input($_POST["password"]), PASSWORD_BCRYPT);

    // Check if the username is already taken
    $check_username_query = "SELECT * FROM users WHERE username = $1";
    $check_username_result = pg_query_params($conn, $check_username_query, array($username));

    if (pg_num_rows($check_username_result) > 0) {
        $error_message = "Username already taken. Please choose another username.";
    } else {
        // Insert the new user into the database
        $insert_user_query = "INSERT INTO users (username, password) VALUES ($1, $2)";
        $insert_user_result = pg_query_params($conn, $insert_user_query, array($username, $password));

        if ($insert_user_result) {
            // Registration successful
            $_SESSION["username"] = $username;
            header("Location: dashboard.php"); // Redirect to the dashboard or another authenticated page
            exit();
        } else {
            // Registration failed
            $error_message = "Registration failed. Please try again.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login and Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 300px;
            margin: 100px auto;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
        }
        button {
            padding: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Login</h2>
    <?php if (isset($error_message)) : ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" name="login">Login</button>
    </form>

    <hr>

    <h2>Register</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="reg_username">Username:</label>
        <input type="text" id="reg_username" name="username" required>

        <label for="reg_password">Password:</label>
        <input type="password" id="reg_password" name="password" required>

        <button type="submit" name="register">Register</button>
    </form>
</div>

</body>
</html>