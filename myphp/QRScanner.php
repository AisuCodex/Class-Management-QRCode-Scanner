<?php
// Database configuration for table_db
$servername = "localhost";
$username = "root";
$password = "";
$dbname_table = "table_db"; // Database name where the tables exist
$dbname_masterlist = "masterlistDB"; // Database where the master list exists

// Create connections
$conn_table = new mysqli($servername, $username, $password, $dbname_table);
$conn_masterlist = new mysqli($servername, $username, $password, $dbname_masterlist);

// Check connection for table_db
if ($conn_table->connect_error) {
    die("Connection failed to table_db: " . $conn_table->connect_error);
}

// Check connection for masterlistDB
if ($conn_masterlist->connect_error) {
    die("Connection failed to masterlistDB: " . $conn_masterlist->connect_error);
}

// Handle POST request to insert scanned data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['qrcode'], $_POST['lrn'], $_POST['tableName'])) {
    $studentName = $_POST['qrcode'];
    $lrn = $_POST['lrn'];
    $tableName = $_POST['tableName'];

    // Step 1: Check if the scanned data exists in the master_list table
    $stmt_masterlist = $conn_masterlist->prepare("SELECT * FROM master_list WHERE studentname = ? AND lrn = ?");
    $stmt_masterlist->bind_param("ss", $studentName, $lrn);
    $stmt_masterlist->execute();
    $result = $stmt_masterlist->get_result();

    if ($result->num_rows > 0) {
        // Step 2: If a match is found, insert the data into the selected table in table_db
        $stmt_table = $conn_table->prepare("INSERT INTO $tableName (studentname, lrn, time_in) VALUES (?, ?, NOW())");
        $stmt_table->bind_param("ss", $studentName, $lrn);
        if ($stmt_table->execute()) {
            echo "Data inserted successfully!";
        } else {
            echo "Error: " . $stmt_table->error;
        }
        $stmt_table->close();
    } else {
        echo "Error: QR code data does not match any entry in the master list.";
    }

    $stmt_masterlist->close();
    exit;
}

// Fetch the list of tables in the table_db
$tables = [];
$result = $conn_table->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
}

$conn_table->close();
$conn_masterlist->close();
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

                    // Extract name and LRN using regex (assuming they are in the format "Name: <name>, LRN: <lrn>")
                    const nameMatch = scannedData.match(/Name:\s*([a-zA-Z\s]+)/);
                    const lrnMatch = scannedData.match(/LRN:\s*([\d]+)/);

                    const studentNameOnly = nameMatch ? nameMatch[1] : "";
                    const lrnOnly = lrnMatch ? lrnMatch[1] : "";

                    resultText.textContent = `Scanned: ${studentNameOnly}, LRN: ${lrnOnly}`;

                    // Send the data (studentName, lrn) to the backend
                    sendToBackend(studentNameOnly, lrnOnly);
                } else {
                    requestAnimationFrame(scanQRCode);
                }
            } else {
                requestAnimationFrame(scanQRCode);
            }
        }

        function sendToBackend(studentName, lrn) {
            // Get the selected table
            let selectedTable = document.getElementById('tableSelect').value;

            // Send the data using AJAX
            let xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    if (xhr.responseText.includes("Error")) {
                        alert("QR Code data doesn't match any entry in the master list.");
                    } else {
                        alert("QR Code data has been submitted successfully!");
                    }
                }
            };
            xhr.send('qrcode=' + encodeURIComponent(studentName) + '&lrn=' + encodeURIComponent(lrn) + '&tableName=' + encodeURIComponent(selectedTable));
        }
    </script>
</body>
</html>
