<?php
// get_subject_content.php

$subject = $_POST['subject'];

$conn_subject = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn_subject->connect_error) {
    die("Connection failed: " . $conn_subject->connect_error);
}

$sqlSubject = "SELECT question, answer, marks FROM $subject";
$resultSubject = $conn_subject->query($sqlSubject);

$content = [];

while ($rowSubject = $resultSubject->fetch_assoc()) {
    $content[] = $rowSubject;
}

$conn_subject->close();

echo json_encode($content);
?>
