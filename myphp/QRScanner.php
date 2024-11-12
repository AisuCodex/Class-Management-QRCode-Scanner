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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['qrcode'], $_POST['lrn'], $_POST['registered_number'], $_POST['tableName'])) {
    $studentName = $_POST['qrcode'];
    $lrn = $_POST['lrn'];
    $registeredNumber = $_POST['registered_number'];
    $tableName = $_POST['tableName'];

    // Step 1: Check if the scanned data exists in the master_list table
    $stmt_masterlist = $conn_masterlist->prepare("SELECT * FROM master_list WHERE studentname = ? AND lrn = ? AND registered_number = ?");
    $stmt_masterlist->bind_param("sss", $studentName, $lrn, $registeredNumber);
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
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

/* Container Styles */
.container {
    background: rgba(255, 255, 255, 0.95);
    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
    width: 90%;
    max-width: 500px;
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.18);
}

/* Heading Styles */
h2 {
    color: #2d3436;
    margin-bottom: 1.5rem;
    font-size: 1.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Video Preview Styles */
#preview {
    width: 100%;
    border-radius: 15px;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    background: #000;
    transform: scaleX(-1);
}

/* Result Section Styles */
.result {
    background: #f8f9fa;
    padding: 1.2rem;
    border-radius: 12px;
    margin: 1.5rem 0;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
}

.result h3 {
    color: #2d3436;
    margin-bottom: 0.8rem;
    font-size: 1.1rem;
    font-weight: 500;
}

#scanResult {
    color: #636e72;
    font-size: 0.95rem;
    line-height: 1.4;
    word-break: break-word;
}

/* Button Styles */
button {
    background: #6c5ce7;
    color: white;
    border: none;
    padding: 0.8rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
    margin-bottom: 1.5rem;
}

button:hover {
    background: #5f4dd1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 92, 231, 0.2);
}

/* Dropdown Select Styles */
label {
    display: block;
    margin-bottom: 0.5rem;
    color: #2d3436;
    font-weight: 500;
}

select {
    width: 100%;
    padding: 0.8rem;
    border-radius: 8px;
    border: 2px solid #e0e0e0;
    background: white;
    color: #2d3436;
    font-size: 1rem;
    transition: all 0.3s ease;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%232d3436' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1em;
}

select:focus {
    outline: none;
    border-color: #6c5ce7;
    box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 1.5rem;
        width: 95%;
    }

    h2 {
        font-size: 1.5rem;
    }

    button {
        padding: 0.7rem 1.2rem;
        font-size: 0.95rem;
    }
}

/* Animation for Scanning Effect */
@keyframes scan {
    0% {
        transform: translateY(-100%);
    }
    100% {
        transform: translateY(100%);
    }
}

#preview::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: rgba(108, 92, 231, 0.5);
    animation: scan 2s linear infinite;
}

/* Alert Styles */
.alert {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    background: #00b894;
    color: white;
    box-shadow: 0 4px 12px rgba(0, 184, 148, 0.2);
    animation: slideIn 0.3s ease-out;
    z-index: 1000;
}

.alert.error {
    background: #d63031;
    box-shadow: 0 4px 12px rgba(214, 48, 49, 0.2);
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
    </style>
</head>
<body>
<div class="container">
    <h2>Scan QR Code</h2>

    <!-- Back Button -->
    <button onclick="window.location.href='addTable.php'" style="margin-bottom: 15px;">Back</button>

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

                    // Extract name, LRN, and registered number
                    const nameMatch = scannedData.match(/Name:\s*([a-zA-Z\s]+)/);
                    const lrnMatch = scannedData.match(/LRN:\s*([\d]+)/);
                    const regNumberMatch = scannedData.match(/Registered Number:\s*([\d]{6})/);

                    const studentNameOnly = nameMatch ? nameMatch[1] : "";
                    const lrnOnly = lrnMatch ? lrnMatch[1] : "";
                    const registeredNumberOnly = regNumberMatch ? regNumberMatch[1] : "";

                    resultText.textContent = `Scanned: ${studentNameOnly}, LRN: ${lrnOnly}, Registered Number: ${registeredNumberOnly}`;

                    // Send the data (studentName, lrn, registeredNumber) to the backend
                    sendToBackend(studentNameOnly, lrnOnly, registeredNumberOnly);
                } else {
                    requestAnimationFrame(scanQRCode);
                }
            } else {
                requestAnimationFrame(scanQRCode);
            }
        }

        function sendToBackend(studentName, lrn, registeredNumber) {
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
            xhr.send('qrcode=' + encodeURIComponent(studentName) + '&lrn=' + encodeURIComponent(lrn) + '&registered_number=' + encodeURIComponent(registeredNumber) + '&tableName=' + encodeURIComponent(selectedTable));
        }
    </script>
</body>
</html>
