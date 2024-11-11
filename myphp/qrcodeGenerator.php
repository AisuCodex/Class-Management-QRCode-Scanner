<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "masterlistDB";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle POST request to insert full student information with registered number
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['studentname'], $_POST['lrn'], $_POST['gender'], $_POST['registered_number'])) {
    $studentname = $_POST['studentname'];
    $lrn = $_POST['lrn'];
    $gender = $_POST['gender'];
    $registered_number = $_POST['registered_number'];

    // Insert all student information including registered number into the master_list table
    $stmt = $conn->prepare("INSERT INTO master_list (studentname, lrn, gender, registered_number) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $studentname, $lrn, $gender, $registered_number);

    if ($stmt->execute()) {
        echo "Student information with registered number inserted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    exit; // Exit to prevent HTML from loading after AJAX request
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generator</title>
    <link rel="stylesheet" href="css/QRCodeGenerator.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.4.4/build/qrcode.min.js"></script>
</head>
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

            // Generate a 6-digit registered number
            const registeredNumber = Math.floor(100000 + Math.random() * 900000);

            // Log data being used to generate QR code
            console.log(`Generating QR with Name: ${studentname}, LRN: ${lrn}, Gender: ${gender}, Registered Number: ${registeredNumber}`);

            // Create QR code data with student information
            const qrData = `Name: ${studentname}, LRN: ${lrn}, Gender: ${gender}, Registered Number: ${registeredNumber}`;
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

            // Save student information to the master_list table including the registered number
            saveStudentInfo(studentname, lrn, gender, registeredNumber);
        }

        function saveStudentInfo(studentname, lrn, gender, registeredNumber) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "", true); // Posting to the same file
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            // Log data being sent to the server
            console.log(`Sending student info: studentname=${studentname}&lrn=${lrn}&gender=${gender}&registered_number=${registeredNumber}`);

            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    console.log("Student information with registered number saved successfully");
                }
            };

            // Send the form data including the registered number
            xhr.send(`studentname=${studentname}&lrn=${lrn}&gender=${gender}&registered_number=${registeredNumber}`);
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>
