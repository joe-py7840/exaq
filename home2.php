<?php
session_start();
require_once('tcpdf.php');


if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION["username"];
$selectedSubjects = []; // Initialize an array to store selected subjects

// Retrieve the user's selected subjects from the database
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "exadocs";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT subject_1, subject_2 FROM user WHERE username = '$username' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if ($row["subject_1"]) {
        $selectedSubjects[] = $row["subject_1"];
    }
    if ($row["subject_2"]) {
        $selectedSubjects[] = $row["subject_2"];
    }
}
$selectedSubject = isset($_GET["subject"]) ? $_GET["subject"] : null;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Include DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <nav class="navbar navbar-expand navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="home.php"><?php echo $username; ?></a>
            <!-- ... (existing code) -->

            <ul class="navbar-nav ml-auto">
                <?php foreach ($selectedSubjects as $subjectCode) {
                    echo '<li class="nav-item">';
                    echo '<a class="nav-link" href="home.php?subject=' . urlencode($subjectCode) . '">' . $subjectCode . '</a>';
                    echo '</li>';
                } ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>

            <!-- ... (existing code) -->

        </div>
    </nav>

    <!-- ... (existing code) -->

<div class="container mt-4">
    <?php
    if ($selectedSubject) {
        // Create a new connection for subject content
        $conn_subject = new mysqli($servername, $username_db, $password_db, $dbname);

        if ($conn_subject->connect_error) {
            die("Connection failed: " . $conn_subject->connect_error);
        }

        // Fetch questions, answers, and marks from the subject table
        $sqlSubject = "SELECT question, answer, marks FROM $selectedSubject";
        $resultSubject = $conn_subject->query($sqlSubject);

        echo '<h3 class="mb-3">' . $selectedSubject . ' Questions</h3>';
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped">';
        echo '<thead class="table-dark"><tr><th>Question</th><th>Answer</th><th>Marks</th></tr></thead>';
        echo '<tbody>';

        // Display the subject content in a table
        while ($rowSubject = $resultSubject->fetch_assoc()) {
            $question = $rowSubject["question"];
            $answer = $rowSubject["answer"];
            $marks = $rowSubject["marks"];
            echo '<tr>';
            echo "<td>$question</td>";
            echo "<td>$answer</td>";
            echo "<td>$marks</td>";
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</div>';

        // Close the subject-specific database connection
        $conn_subject->close();
    } else {
        echo '<p class="text-center">Please select a subject from the navigation above.</p>';
    }
    ?>
</div>

<!-- ... (existing code) -->


    <!-- Include DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>

    <!-- Initialize DataTables -->
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('table').DataTable({
                "paging": true,
                "ordering": true,
                "info": true
            });
        });
    </script>
</body>
</html>
