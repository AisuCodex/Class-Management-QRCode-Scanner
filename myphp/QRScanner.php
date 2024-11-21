<?php
// Start session to store the selected table
session_start();

// Database configuration for u193875898_table_db
$servername = "localhost";
$username = "root";
$password = "";
$dbname_table = "u193875898_table_db"; // Database name where the tables exist

// Create connection to u193875898_table_db
$conn_table = new mysqli($servername, $username, $password, $dbname_table);

// Check connection for u193875898_table_db
if ($conn_table->connect_error) {
    die("Connection failed to u193875898_table_db: " . $conn_table->connect_error);
}

// Handle POST request to insert scanned data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['qrcode'], $_POST['lrn'], $_POST['registered_number'], $_POST['tableName'])) {
    $studentName = $_POST['qrcode'];
    $lrn = $_POST['lrn'];
    $registeredNumber = $_POST['registered_number'];
    $tableName = $_POST['tableName'];

    // Save the selected table in session
    $_SESSION['selectedTable'] = $tableName;

    // Ensure tableName is valid by checking against available tables
    $tables = [];
    $result = $conn_table->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }

    if (!in_array($tableName, $tables)) {
        echo "Error: Selected table does not exist.";
        exit;
    }

    // Step 1: Check if the registered number exists and if status and time_in are empty
    $stmt_table_check = $conn_table->prepare("SELECT * FROM $tableName WHERE registered_number = ?");
    $stmt_table_check->bind_param("s", $registeredNumber);
    $stmt_table_check->execute();
    $result_check = $stmt_table_check->get_result();

    if ($result_check->num_rows > 0) {
        // Fetch the row to check current status and time_in values
        $row = $result_check->fetch_assoc();
        $currentStatus = $row['status'];
        $currentTimeIn = $row['time_in'];

        // Only proceed if status or time_in is missing
        if (empty($currentStatus) || empty($currentTimeIn)) {
            // Retrieve deadline time from the selected table
            $stmt_deadline = $conn_table->prepare("SELECT deadline FROM $tableName LIMIT 1");
            $stmt_deadline->execute();
            $result_deadline = $stmt_deadline->get_result();
            $row_deadline = $result_deadline->fetch_assoc();
            $deadlineTime = $row_deadline ? $row_deadline['deadline'] : null;

            // Convert deadline and current time to timestamps for comparison
            $currentTime = date("H:i:s");
            $currentTimeStamp = strtotime($currentTime);
            $deadlineTimeStamp = strtotime($deadlineTime);

            // Determine status based on comparison
            $status = ($deadlineTime && $currentTimeStamp <= $deadlineTimeStamp) ? "on time" : "late";

            // Update only the status and time_in fields if they are currently blank
            $stmt_table_update = $conn_table->prepare("UPDATE $tableName SET status = IF(status IS NULL, ?, status), time_in = IF(time_in IS NULL, NOW(), time_in) WHERE registered_number = ?");
            $stmt_table_update->bind_param("ss", $status, $registeredNumber);

            if ($stmt_table_update->execute()) {
                echo "Status and time_in updated successfully!";
            } else {
                echo "Error: " . $stmt_table_update->error;
            }

            $stmt_table_update->close();
        } else {
            echo "No update needed: Status and time_in are already set.";
        }

        $stmt_deadline->close();
    } else {
        echo "Error: QR code data does not match any entry in the selected table.";
    }

    $stmt_table_check->close();
    exit;
}

// Fetch the list of tables in the u193875898_table_db
$tables = [];
$result = $conn_table->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
}

// Get the selected table from the session
$selectedTable = isset($_SESSION['selectedTable']) ? $_SESSION['selectedTable'] : "";

$conn_table->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner</title>
    <link rel="stylesheet" href="../css/QRScanner.css">
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
            <option value="<?php echo htmlspecialchars($table); ?>" 
                <?php echo ($table === $selectedTable) ? "selected" : ""; ?>>
                <?php echo htmlspecialchars($table); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.js"></script>
<script>
    let video = document.getElementById('preview');
    let resultText = document.getElementById('scanResult');

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
                const scannedData = code.data;

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
    async function sendToBackend(studentName, lrn, registeredNumber) {
    let selectedTable = document.getElementById('tableSelect').value;
    try {
        let response = await fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `qrcode=${encodeURIComponent(studentName)}&lrn=${encodeURIComponent(lrn)}&registered_number=${encodeURIComponent(registeredNumber)}&tableName=${encodeURIComponent(selectedTable)}`
        });

        let result = await response.text();
        if (result.includes("Error")) {
            // Display an alert for denied QR code and refresh the page after user clicks "OK"
            alert("QR code denied.");
            location.reload(); // Refresh the page
        } else {
            // Display success message and refresh the page
            alert("QR Code data has been successfully processed!");
            location.reload(); // Reload the page to allow the next scan
        }
    } catch (error) {
        console.error('Error during the request:', error);
        resultText.textContent = "An error occurred, please try again.";
    }
}

</script>
</body>
</html>
