<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myclassdb";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit;
} else {
    echo "<p>Invalid class ID.</p>";
}

$conn->close();
?>
