<?php
// Database connection
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
    
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update"])) {
        $subject = $_POST['subject'];
        $section = $_POST['section'];

        $stmt = $conn->prepare("UPDATE classes SET subject = ?, section = ? WHERE id = ?");
        $stmt->bind_param("ssi", $subject, $section, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $class = $result->fetch_assoc();
    } else {
        echo "<p>Class not found.</p>";
        exit;
    }
} else {
    echo "<p>Invalid class ID.</p>";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Class</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }
        body {
            background-color: #f5f5f5;
            color: #333;
            padding: 40px 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 500px;
            width: 100%;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
        }
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #f44336;
            color: white;
            border: none;
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            transition: opacity 0.3s ease;
        }
        .close-btn:hover {
            opacity: 0.9;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        label {
            font-weight: bold;
            color: #555;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        button[type="submit"] {
            background: #333;
            color: white;
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            border-radius: 4px;
            transition: opacity 0.3s ease;
            font-size: 16px;
        }
        button[type="submit"]:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <button class="close-btn" onclick="window.location.href='index.php'">X</button>
        <h2>Edit Class</h2>
        <form method="post">
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($class['subject']); ?>" required>
            <label for="section">Section:</label>
            <input type="text" id="section" name="section" value="<?php echo htmlspecialchars($class['section']); ?>" required>
            <button type="submit" name="update">Update</button>
        </form>
    </div>
</body>
</html>
