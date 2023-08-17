<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    // User is not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Get the logged-in user's username
$username = $_SESSION["username"];

// Retrieve the user's subjects from the database
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "exadocs";

// Create a connection
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare the SQL statement to retrieve the user's indicated subjects
$sql = "SELECT subject_1, subject_2 FROM user WHERE username = '$username' LIMIT 1";
$result = $conn->query($sql);

// Check if a matching user is found
if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $subject1 = $row["subject_1"];
    $subject2 = $row["subject_2"];

    // List of available subjects
    $availableSubjects = [
        "maths" => "Maths",
        "english" => "English",
        "kiswahili" => "Kiswahili",
        "physics" => "Physics",
        "chemistry" => "Chemistry",
        "biology" => "Biology",
        "geography" => "Geography",
        "history" => "History",
        "cre" => "CRE",
        "computer_studies" => "Computer Studies",
        "agriculture" => "Agriculture",
        "business_studies" => "Business Studies"
    ];
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Exadocs</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Include DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>


</head>
<body>

<!-- Navbar with logout button -->
<nav class="navbar navbar-expand navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="home.php"><?php echo $username; ?></a>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <div class="mb-4">
    <button type="button" class="btn btn-primary" id="printButton">Print</button>
        <h3 class="mb-3">Select a Subject:</h3>
        <form action="" method="post">
            <select name="selectedSubject" class="form-select" onchange="this.form.submit()">
                <option value="" disabled selected>Select a subject</option>
                <?php
                foreach ($availableSubjects as $subjectCode => $subjectName) {
                    if ($subject1 === $subjectName || $subject2 === $subjectName) {
                        echo '<option value="' . $subjectCode . '">' . $subjectName . '</option>';
                    }
                }
                ?>
            </select>
        </form>
    </div>

    <?php
    if (isset($_POST['selectedSubject'])) {
        $selectedSubject = $_POST['selectedSubject'];
        if (array_key_exists($selectedSubject, $availableSubjects)) {
            $selectedSubjectName = $availableSubjects[$selectedSubject];
            echo '<h3 class="mb-3">' . $selectedSubjectName .'</h3>';
            
            // Create a new connection for subject content
            $conn_subject = new mysqli($servername, $username_db, $password_db, $dbname);

            // Example: Fetch questions, answers, and marks from the subject table
            $sqlSubject = "SELECT question, answer, marks FROM $selectedSubject";
            $resultSubject = $conn_subject->query($sqlSubject);

           // Display the subject content in a table
echo '<div class="table-responsive">';
echo '<table class="table table-striped" id="subjectTable">';
echo '<thead class="table-dark"><tr><th>Question</th><th>Answer</th><th>Marks</th><th>Select</th></tr></thead>';
echo '<tbody>';

// Process the result and display the content
while ($rowSubject = $resultSubject->fetch_assoc()) {
    $question = $rowSubject["question"];
    $answer = $rowSubject["answer"];
    $marks = $rowSubject["marks"];
    echo '<tr>';
    echo "<td>$question</td>";
    echo "<td>$answer</td>";
    echo "<td>$marks</td>";
    echo '<td><button class="select-button btn btn-primary" data-selected="false" data-question="' . htmlspecialchars($question) . '">Select</button></td>'; // Button in each row
    echo '</tr>';
}

echo '</tbody></table>';
echo '</div>';


            
            // Close the subject-specific database connection
            $conn_subject->close();
        } else {
            echo '<p class="text-danger">Invalid subject selection.</p>';
        }
    }
    ?>
</div>


<!-- Include DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>

<!-- Initialize DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>

<script>
    $(document).ready(function() {
    // Initialize DataTables
    $('#subjectTable').DataTable({
        "paging": true,
        "ordering": true,
        "info": true
    });

    // Handle "Select" button click
    $('.select-button').click(function() {
        var button = $(this);
        var isSelected = button.data('selected');

        if (isSelected) {
            // Deselect the subject
            button.removeClass('btn-danger');
            button.addClass('btn-primary');
            button.text('Select');
        } else {
            // Select the subject
            button.removeClass('btn-primary');
            button.addClass('btn-danger');
            button.text('Selected');
        }

        // Toggle selection state
        button.data('selected', !isSelected);
    });

    // Handle "Print" button click
    $('#printButton').click(function() {
        var selectedQuestions = [];

        // Loop through each selected row and gather content
        $('.select-button[data-selected="true"]').each(function() {
            var question = $(this).data('question');
            var answer = $(this).closest('tr').find('td:eq(1)').text();
            var marks = $(this).closest('tr').find('td:eq(2)').text();

            selectedQuestions.push({
                question: question,
                answer: answer,
                marks: marks
            });
        });

        // Generate the PDF using jsPDF
        if (selectedQuestions.length > 0) {
            var doc = new jsPDF();
            var yPos = 10;

            selectedQuestions.forEach(function(q) {
                doc.setFontSize(12);
                doc.text(10, yPos, 'Question: ' + q.question);
                doc.text(10, yPos + 10, 'Answer: ' + q.answer);
                doc.text(10, yPos + 20, 'Marks: ' + q.marks);
                yPos += 30;

                if (yPos >= doc.internal.pageSize.height - 10) {
                    doc.addPage();
                    yPos = 10;
                }
            });

            // Save or open the PDF
            doc.save('selected_questions.pdf');
        }
    });
});


</script>



</body>
</html>
