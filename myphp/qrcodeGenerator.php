<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "u193875898_masterlistdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get available tables from the database
$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

// Handle POST request to insert full student information with registered number and section
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['studentname'], $_POST['lrn'], $_POST['gender'], $_POST['registered_number'], $_POST['section'], $_POST['table'])) {
    $studentname = $_POST['studentname'];
    $lrn = $_POST['lrn'];
    $gender = $_POST['gender'];
    $registered_number = $_POST['registered_number'];
    $section = $_POST['section'];
    $table = $_POST['table'];

    // Prepare the SQL statement with error checking
    $stmt = $conn->prepare("INSERT INTO `$table` (studentname, lrn, gender, registered_number, section) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters and execute
    $stmt->bind_param("sssss", $studentname, $lrn, $gender, $registered_number, $section);

    if ($stmt->execute()) {
        echo "Student information with section inserted successfully!";
    } else {
        echo "Error executing statement: " . $stmt->error;
    }

    $stmt->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generator</title>
    <link rel="stylesheet" href="../css/qrcodeGenerator.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.4.4/build/qrcode.min.js"></script>
</head>
<style>

</style>
<body>
    <div class="container">
        <h2>Generate QR Code for Student</h2>

        <!-- Form to enter student information -->
        <form id="qrForm" method="POST" onsubmit="event.preventDefault(); generateQRCode();">
            <label for="studentname">Name:</label>
            <input type="text" id="studentname" name="studentname" required>

            <label for="lrn">LRN:</label>
            <input type="text" id="lrn" name="lrn" required>

            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>

            <label for="section">Section:</label>
            <input type="text" id="section" name="section" required>

            <label for="table">Select Table:</label>
            <select id="table" name="table" required>
                <option value="">Select Table</option>
                <?php foreach ($tables as $table): ?>
                    <option value="<?php echo $table; ?>"><?php echo $table; ?></option>
                <?php endforeach; ?>
            </select>

            <button type="button" onclick="generateQRCode()">Generate QR Code</button>
        </form>

        <!-- QR code display and download button -->
        <div class="qr-code">
            <canvas id="qrcode"></canvas>
            <a id="downloadBtn" class="download-btn" download="student_qrcode.png">Download QR Code</a>
        </div>
    </div>

    <script>
        function generateQRCode() {
            // Get form values
            const studentname = document.getElementById("studentname").value;
            const lrn = document.getElementById("lrn").value;
            const gender = document.getElementById("gender").value;
            const section = document.getElementById("section").value;
            const table = document.getElementById("table").value;

            if (!table) {
                alert("Please select a table.");
                return;
            }

            // Generate a 6-digit registered number
            const registeredNumber = Math.floor(100000 + Math.random() * 900000);

            // Create QR code data with student information
            const qrData = `Name: ${studentname}, LRN: ${lrn}, Gender: ${gender}, Section: ${section}, Registered Number: ${registeredNumber}`;
            const canvas = document.getElementById("qrcode");

            // Generate the QR code
            QRCode.toCanvas(canvas, qrData, { width: 200 }, function (error) {
                if (error) {
                    console.error(error);
                } else {
                    const dataUrl = canvas.toDataURL("image/png");
                    document.getElementById("downloadBtn").href = dataUrl;
                    document.getElementById("downloadBtn").style.display = "inline-block";
                }
            });

            // Save student information to the selected table including the registered number and section
            saveStudentInfo(studentname, lrn, gender, registeredNumber, section, table);
        }

        function saveStudentInfo(studentname, lrn, gender, registeredNumber, section, table) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "", true); // Posting to the same file
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    console.log(xhr.responseText); // Display PHP response for debugging
                    alert(xhr.responseText); // Optional: alert message to confirm data saved
                }
            };

            // Encode data to avoid issues
            const data = `studentname=${encodeURIComponent(studentname)}&lrn=${encodeURIComponent(lrn)}&gender=${encodeURIComponent(gender)}&registered_number=${encodeURIComponent(registeredNumber)}&section=${encodeURIComponent(section)}&table=${encodeURIComponent(table)}`;
            xhr.send(data);
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
