<!DOCTYPE html>
<html>
<head>
    <title>Sign Up Page</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Include custom CSS for validation styles -->
    <style>
        .validation-icon {
            font-size: 20px;
            margin-right: 10px;
        }
        .valid-icon {
            color: green;
        }
        .invalid-icon {
            color: red;
        }
        .lnk a{
            text-decoration: none;
        }
    </style>
    <!-- Include custom JavaScript -->
    <script>
        function validatePassword() {
            const password = document.getElementById("signupPassword").value;
            const uppercase = /[A-Z]/.test(password);
            const lowercase = /[a-z]/.test(password);
            const number = /[0-9]/.test(password);
            const specialChar = /[^\w]/.test(password);
            const passwordLength = password.length >= 6;

            showValidationIcon("uppercaseIcon", uppercase);
            showValidationIcon("lowercaseIcon", lowercase);
            showValidationIcon("numberIcon", number);
            showValidationIcon("specialCharIcon", specialChar);
            showValidationIcon("passwordLengthIcon", passwordLength);
        }

        function showValidationIcon(iconId, isValid) {
            const iconElement = document.getElementById(iconId);
            if (isValid) {
                iconElement.className = "validation-icon valid-icon";
                iconElement.innerText = "✓";
            } else {
                iconElement.className = "validation-icon invalid-icon";
                iconElement.innerText = "✗";
            }
        }

        function validateSubjects() {
            const subject1 = document.getElementById("subject1").value;
            const subject2 = document.getElementById("subject2").value;
            const subjectValidation = document.getElementById("subjectValidation");

            if (subject1 === subject2) {
                subjectValidation.innerText = "Subjects cannot be the same";
            } else {
                subjectValidation.innerText = "";
            }
        }
    </script>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-center">Sign Up</h2>
                </div>
                <div class="card-body">
                <?php
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        // Check if passwords match
                        $signupPassword = $_POST["signupPassword"];
                        $signupConfirmPassword = $_POST["signupConfirmPassword"];

                        // Password validation checks
                        $uppercase = preg_match('@[A-Z]@', $signupPassword);
                        $lowercase = preg_match('@[a-z]@', $signupPassword);
                        $number = preg_match('@[0-9]@', $signupPassword);
                        $specialChar = preg_match('@[^\w]@', $signupPassword);
                        $passwordLength = strlen($signupPassword) >= 6;

                        if (!$uppercase || !$lowercase || !$number || !$specialChar || !$passwordLength) {
                            echo '<div class="alert alert-danger">Password must be at least 6 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.</div>';
                        } elseif ($signupPassword !== $signupConfirmPassword) {
                            echo '<div class="alert alert-danger">Passwords do not match.</div>';
                        } else {
                            // Database connection
                            $servername = "localhost";
                            $username = "root";
                            $password = ""; // No password (not recommended for production)
                            $dbname = "exadocs";

                            $conn = new mysqli($servername, $username, $password, $dbname);

                            // Check connection
                            if ($conn->connect_error) {
                                die("Connection failed: " . $conn->connect_error);
                            }

                            // Check if email is already registered
                            $signupEmail = $_POST["signupEmail"];
                            $emailCheckQuery = "SELECT * FROM user WHERE email = '$signupEmail'";
                            $result = $conn->query($emailCheckQuery);

                            if ($result->num_rows > 0) {
                                echo '<div class="alert alert-danger">Email is already registered.</div>';
                            } else {
                                // Prepare and bind the SQL statement
                                $stmt = $conn->prepare("INSERT INTO user (username, email, password, subject_1, subject_2) VALUES (?, ?, ?, ?, ?)");
                                $stmt->bind_param("sssss", $signupUsername, $signupEmail, $hashedPassword, $subject1, $subject2);

                                // Retrieve user input
                                $signupUsername = $_POST["signupUsername"];
                                $hashedPassword = password_hash($signupPassword, PASSWORD_DEFAULT); // Hash the password

                                // Process selected subjects
                                $subject1 = $_POST["subject1"];
                                $subject2 = $_POST["subject2"];
                                if ($subject1 === $subject2) {
                                    echo '<div class="alert alert-danger">Please select two different subjects.</div>';
                                } else {
                                    // Execute the statement
                                    if ($stmt->execute()) {
                                        echo '<div class="alert alert-success">User \'' . $signupUsername . '\' successfully registered with email \'' . $signupEmail . '\'!</div>';
                                        // Redirect to index.php after successful registration
                                        header("Location: index.php");
                                        exit;
                                    } else {
                                        echo '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
                                    }
                                }

                                // Close the statement and connection
                                $stmt->close();
                            }

                            $conn->close();
                        }
                    }
                    ?>

                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <div class="mb-3">
                            <label for="signupUsername" class="form-label">Username:</label>
                            <input type="text" class="form-control" id="signupUsername" name="signupUsername" required>
                        </div>

                        <div class="mb-3">
                            <label for="signupEmail" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="signupEmail" name="signupEmail" required>
                        </div>

                        <div class="mb-3">
                            <label for="signupPassword" class="form-label">Password:</label>
                            <input type="password" class="form-control" id="signupPassword" name="signupPassword" required onkeyup="validatePassword()">
                            <div class="password-validation">
                                <span class="validation-icon" id="uppercaseIcon">✗</span> Uppercase letter<br>
                                <span class="validation-icon" id="lowercaseIcon">✗</span> Lowercase letter<br>
                                <span class="validation-icon" id="numberIcon">✗</span> Number<br>
                                <span class="validation-icon" id="specialCharIcon">✗</span> Special character<br>
                                <span class="validation-icon" id="passwordLengthIcon">✗</span> At least 6 characters long
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="signupConfirmPassword" class="form-label">Confirm Password:</label>
                            <input type="password" class="form-control" id="signupConfirmPassword" name="signupConfirmPassword" required>
                        </div>

                        <div class="mb-3">
                        <div class="mb-3">
                            <label for="subject1" class="form-label">Select Subject 1:</label>
                            <select class="form-select" id="subject1" name="subject1" required>
                                <option value="" disabled selected>Select a subject</option>
                                <option value="Maths">Maths</option>
                                <option value="English">English</option>
                                <option value="Kiswahili">Kiswahili</option>
                                <option value="Physics">Physics</option>
                                <option value="Chemistry">Chemistry</option>
                                <option value="Biology">Biology</option>
                                <option value="Geography">Geography</option>
                                <option value="History">History</option>
                                <option value="CRE">CRE</option>
                                <option value="Computer Studies">Computer Studies</option>
                                <option value="Agriculture">Agriculture</option>
                                <option value="Business Studies">Business Studies</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="subject2" class="form-label">Select Subject 2:</label>
                            <select class="form-select" id="subject2" name="subject2" required>
                                <option value="" disabled selected>Select a subject</option>
                                <option value="Maths">Maths</option>
                                <option value="English">English</option>
                                <option value="Kiswahili">Kiswahili</option>
                                <option value="Physics">Physics</option>
                                <option value="Chemistry">Chemistry</option>
                                <option value="Biology">Biology</option>
                                <option value="Geography">Geography</option>
                                <option value="History">History</option>
                                <option value="CRE">CRE</option>
                                <option value="Computer Studies">Computer Studies</option>
                                <option value="Agriculture">Agriculture</option>
                                <option value="Business Studies">Business Studies</option>
                            </select>
                        </div>


                        <button type="submit" class="btn btn-primary">Sign Up</button><br><br>
                        <p class="lnk">Already have an account? <a href="index.php">Login</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
