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

    // Redirect to the same page to prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all classes from the database
$result = $conn->query("SELECT * FROM classes ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes</title>
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
}

.container {
    max-width: 800px;
    margin: 0 auto;
}

.header {
    text-align: center;
    margin-bottom: 40px;
}

.add-btn {
    background: none;
    border: 2px solid #333;
    padding: 10px 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.add-btn:hover {
    background: #333;
    color: #fff;
}

.form-container {
    background: white;
    padding: 30px;
    border-radius: 10px;
    margin: 20px 0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    display: none;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #555;
}

.form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.submit-btn {
    background: #333;
    color: white;
    border: none;
    padding: 12px 24px;
    cursor: pointer;
    border-radius: 4px;
    transition: opacity 0.3s ease;
}

.submit-btn:hover {
    opacity: 0.9;
}

.classes-grid {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
}

.class-card {
    width: 400px;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.class-info {
    margin-bottom: 15px;
}

.class-info p {
    margin: 5px 0;
    color: #666;
}

.class-actions {
    display: flex;
    gap: 10px;
}

.action-btn {
    flex: 1;
    padding: 8px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: opacity 0.3s ease;
}

.edit-btn {
    background: #4CAF50;
    color: white;
}

.delete-btn {
    background: #f44336;
    color: white;
}

.proceed-btn {
    background: #2196F3;
    color: white;
}

.action-btn:hover {
    opacity: 0.9;
}

    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Classes</h1>
            <button class="add-btn" onclick="toggleForm()">Add New Class</button>
        </div>
        <div class="form-container" id="form-container">
            <form method="post" action="">
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="section">Section</label>
                    <input type="text" id="section" name="section" required>
                </div>
                <button type="submit" name="submit" class="submit-btn">Add Class</button>
            </form>
        </div>
        <div class="classes-grid">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="class-card">';
                    echo '<div class="class-info">';
                    echo '<p><strong>Subject:</strong> ' . htmlspecialchars($row['subject']) . '</p>';
                    echo '<p><strong>Section:</strong> ' . htmlspecialchars($row['section']) . '</p>';
                    echo '</div>';
                    echo '<div class="class-actions">';
                    echo '<button onclick="window.location.href=\'edit_class.php?id=' . $row['id'] . '\'" class="action-btn edit-btn">Edit</button>';
                    echo '<button onclick="if(confirm(\'Are you sure?\')) window.location.href=\'delete_class.php?id=' . $row['id'] . '\'" class="action-btn delete-btn">Delete</button>';
                    echo '<button onclick="window.location.href=\'proceed.php?id=' . $row['id'] . '\'" class="action-btn proceed-btn">Proceed</button>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo "<p style='text-align: center; color: #666;'>No classes added yet.</p>";
            }
            ?>
        </div>
    </div>
    <script>
        function toggleForm() {
            const form = document.getElementById('form-container');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
