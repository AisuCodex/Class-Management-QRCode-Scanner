<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "table_db"; // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle POST request to insert scanned data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['qrcode'], $_POST['tableName'])) {
    $scannedData = $_POST['qrcode'];
    $tableName = $_POST['tableName'];

    // Prepare and execute the insert query
    $stmt = $conn->prepare("INSERT INTO $tableName (studentname, time_in) VALUES (?, NOW())");
    $stmt->bind_param("s", $scannedData);
    if ($stmt->execute()) {
        echo "Data inserted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    exit;
}

// Fetch the list of tables
$tables = [];
$result = $conn->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            text-align: center;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 20px;
        }
        #preview {
            width: 100%;
            border: 2px solid #333;
            border-radius: 8px;
            margin-bottom: 20px;
            transform: scaleX(-1); /* Flip video horizontally */
        }
        .result {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Scan QR Code</h2>
        <video id="preview" width="100%" height="auto" style="border: 1px solid #000;"></video>
        <div id="result" class="result">
            <h3>Scanned QR Code:</h3>
            <p id="scanResult">Waiting for QR code...</p>
        </div>

        <!-- Dropdown to select the table -->
        <label for="tableSelect">Select Table:</label>
        <select id="tableSelect">
            <?php foreach ($tables as $table): ?>
                <option value="<?php echo htmlspecialchars($table); ?>"><?php echo htmlspecialchars($table); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.js"></script>
    <script>
        let video = document.getElementById('preview');
        let resultText = document.getElementById('scanResult');
        let scannedData = '';

        // Start the camera
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
            .then(function (stream) {
                video.srcObject = stream;
                video.setAttribute('playsinline', true);
                video.play();
                requestAnimationFrame(scanQRCode);
            })
            .catch(function (error) {
                alert('Error accessing the camera: ' + error);
            });

        function scanQRCode() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                let canvas = document.createElement('canvas');
                let context = canvas.getContext('2d');
                canvas.height = video.videoHeight;
                canvas.width = video.videoWidth;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                let imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                let code = jsQR(imageData.data, canvas.width, canvas.height);

                if (code) {
                    scannedData = code.data;

                    // Extract only the student name if prefixed by "Name: "
                    const nameMatch = scannedData.match(/Name:\s*([a-zA-Z\s]+)/);
                    const studentNameOnly = nameMatch ? nameMatch[1] : scannedData;

                    resultText.textContent = `Scanned: ${studentNameOnly}`;

                    // Automatically send data to the backend
                    sendToBackend(studentNameOnly);
                } else {
                    requestAnimationFrame(scanQRCode);
                }
            } else {
                requestAnimationFrame(scanQRCode);
            }
        }

        function sendToBackend(studentName) {
            // Get the selected table
            let selectedTable = document.getElementById('tableSelect').value;

            // Send the data using AJAX
            let xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert("QR Code data has been submitted successfully!");
                }
            };
            xhr.send('qrcode=' + encodeURIComponent(studentName) + '&tableName=' + encodeURIComponent(selectedTable));
        }
    </script>
</body>
</html>
