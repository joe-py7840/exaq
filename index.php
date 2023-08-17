<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "exadocs";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the form data
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Prepare the SQL statement
    $sql = "SELECT * FROM user WHERE username = ?";

    // Create a prepared statement
    $stmt = $conn->prepare($sql);

    // Bind the parameters with the form data
    $stmt->bind_param("s", $username);

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Check if a matching user is found
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $row["password"])) {
            // Start a session
            session_start();

            // Store the user information in the session
            $_SESSION["username"] = $row["username"];
            $_SESSION["email"] = $row["email"];

            // Redirect to the home page
            header("Location: home2.php");
            exit();
        }
    }

    // Display an error message if login fails
    $loginError = "Invalid username or password.";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script>
        function togglePasswordVisibility() {
            var passwordElement = document.getElementById("password");
            if (passwordElement.type === "password") {
                passwordElement.type = "text";
            } else {
                passwordElement.type = "password";
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility()">
                        <i class="bi bi-eye-fill"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <?php if (isset($loginError)) { ?>
            <div class="alert alert-danger mt-3"><?php echo $loginError; ?></div>
        <?php } ?>
        <div class="mt-3">
            <p>Don't have an account? <a href="signup.php">Sign up</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>