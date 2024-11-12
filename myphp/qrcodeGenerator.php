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
<style>
    /* Reset default styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.container {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    width: 90%;
    max-width: 500px;
}

h2 {
    color: #2c3e50;
    text-align: center;
    margin-bottom: 2rem;
    font-size: 1.8rem;
    font-weight: 600;
}

form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

label {
    color: #34495e;
    font-size: 0.9rem;
    margin-bottom: 0.3rem;
}

input, select {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

input:focus, select:focus {
    outline: none;
    border-color: #3498db;
}

button {
    background: #3498db;
    color: white;
    border: none;
    padding: 1rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
    transition: background 0.3s ease;
    margin-top: 1rem;
}

button:hover {
    background: #2980b9;
}

.qr-code {
    margin-top: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

canvas {
    background: white;
    padding: 1rem;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.download-btn {
    display: none;
    text-decoration: none;
    background: #2ecc71;
    color: white;
    padding: 0.8rem 1.5rem;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: background 0.3s ease;
}

.download-btn:hover {
    background: #27ae60;
}

/* Responsive Design */
@media (max-width: 600px) {
    .container {
        width: 95%;
        padding: 1.5rem;
    }

    h2 {
        font-size: 1.5rem;
    }

    button, .download-btn {
        width: 100%;
        text-align: center;
    }
}
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
