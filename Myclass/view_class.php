<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myclassdb";

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
    // Retrieve form data
    $subject = $_POST['subject'];
    $section = $_POST['section'];

    // Insert data into the table
    $stmt = $conn->prepare("INSERT INTO classes (subject, section) VALUES (?, ?)");
    $stmt->bind_param("ss", $subject, $section);
    $stmt->execute();
    $stmt->close();
}

// Fetch all classes from the database
$result = $conn->query("SELECT * FROM classes ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Class</title>
    <style>
        /* Styling */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 50px;
        }
        .btn, .section-button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
        .form-container {
            margin-top: 20px;
            display: none; /* Initially hidden */
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
        }
        .form-group input {
            padding: 8px;
            width: 100%;
            max-width: 300px;
        }
        .section-list {
            margin-top: 30px;
            width: 80%;
            max-width: 400px;
            text-align: left;
        }
        .section-item {
            padding: 10px;
            margin-bottom: 5px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }
        .section-button {
            display: inline-block;
            margin-top: 10px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            text-align: center;
        }
        .section-button:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function toggleForm() {
            var formContainer = document.getElementById('form-container');
            formContainer.style.display = formContainer.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>

    <button class="btn" onclick="toggleForm()">Add Class</button>

    <div class="form-container" id="form-container">
        <form method="post" action="">
            <div class="form-group">
                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject" required>
            </div>
            <div class="form-group">
                <label for="section">Section:</label>
                <input type="text" id="section" name="section" required>
            </div>
            <button type="submit" name="submit" class="btn">Submit</button>
        </form>
    </div>

    <div class="section-list">
        <h2>Classes</h2>
        <?php
        // Display all classes from the database
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="section-item">';
                echo "<strong>Subject:</strong> " . htmlspecialchars($row['subject']) . "<br>";
                echo "<strong>Section:</strong> " . htmlspecialchars($row['section']) . "<br>";

                // Buttons to View, Edit, and Delete
                echo '<a href="view_class.php?id=' . $row['id'] . '" class="section-button">View Details</a>';
                echo '<a href="edit_class.php?id=' . $row['id'] . '" class="section-button">Edit</a>';
                echo '<a href="delete_class.php?id=' . $row['id'] . '" class="section-button" onclick="return confirm(\'Are you sure you want to delete this class?\')">Delete</a>';
                
                echo '</div>';
            }
        } else {
            echo "<p>No classes added yet.</p>";
        }
        ?>
    </div>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
