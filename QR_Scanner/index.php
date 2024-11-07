<?php
// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "qrcode_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['qrcode'])) {
    $qrcode = $_POST['qrcode'];

    // Insert QR code data into the database
    $sql = "INSERT INTO scanned_qrcodes (data) VALUES ('$qrcode')";

    if ($conn->query($sql) === TRUE) {
        echo "New QR code scanned successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    echo "No QR code data received";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner</title>
    <link rel="stylesheet" href="style.css">
</head>

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
}

.result {
    margin-top: 20px;
}

#submit {
    padding: 10px 20px;
    background-color: #28a745;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

#submit:hover {
    background-color: #218838;
}

</style>
<body>
    <div class="container">
        <h2>Scan QR Code</h2>
        <video id="preview" width="100%" height="auto" style="border: 1px solid #000;"></video>
        <div id="result" class="result">
            <h3>Scanned QR Code:</h3>
            <p id="scanResult">Waiting for QR code...</p>
        </div>
        <button id="submit" onclick="sendToBackend()">Submit to Backend</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.js"></script>
    <script>
      let video = document.getElementById('preview');
let resultText = document.getElementById('scanResult');
let submitButton = document.getElementById('submit');
let scannedData = '';

// Access camera and start video stream
navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
    .then(function (stream) {
        video.srcObject = stream;
        video.setAttribute('playsinline', true); // Required for iPhone
        video.play();
        requestAnimationFrame(scanQRCode); // Start scanning
    })
    .catch(function (error) {
        alert('Error accessing the camera: ' + error);
    });

// Scan QR code
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
            resultText.textContent = `Scanned: ${scannedData}`;
            submitButton.style.display = 'block'; // Show the submit button
        } else {
            requestAnimationFrame(scanQRCode);
        }
    } else {
        requestAnimationFrame(scanQRCode);
    }
}

// Send data to backend (PHP)
function sendToBackend() {
    let xhr = new XMLHttpRequest();
    xhr.open('POST', 'save_qrcode.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            alert('QR Code data saved to the server.');
        }
    };
    xhr.send('qrcode=' + encodeURIComponent(scannedData));
}

    </script>
</body>
</html>
