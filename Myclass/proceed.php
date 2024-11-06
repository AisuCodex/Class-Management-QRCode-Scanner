<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner</title>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f5f5f5;
            font-family: 'Segoe UI', sans-serif;
            color: #333;
        }
        #reader {
            width: 300px;
            border: 2px solid #333;
            border-radius: 8px;
            padding: 20px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        #result {
            margin-top: 20px;
            font-size: 1.2em;
            text-align: center;
            color: #4CAF50;
        }
    </style>
    <!-- Add the html5-qrcode library -->
    <script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>
</head>
<body>

<div id="reader"></div>
<div id="result">Scan a QR Code</div>

<script>
    function onScanSuccess(decodedText, decodedResult) {
        // Handle the scanned code as per your needs
        document.getElementById('result').innerText = `Scanned Code: ${decodedText}`;
        // You can redirect or send data to the server here
    }

    function onScanFailure(error) {
        // Handle the scan failure (if needed)
        console.warn(`Scan failure: ${error}`);
    }

    let html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", { fps: 10, qrbox: 250 });
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
</script>

</body>
</html>
