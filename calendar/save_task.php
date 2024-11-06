<?php
$host = 'localhost';
$dbname = 'calendar';
$user = 'root';
$password = '';

// Create a new database connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $task = $_POST['task'];

    $stmt = $conn->prepare("INSERT INTO tasks (date, task) VALUES (?, ?)");
    $stmt->bind_param("ss", $date, $task);

    if ($stmt->execute()) {
        echo "Task saved successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
