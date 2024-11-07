<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generator</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.4.4/build/qrcode.min.js"></script>
    <style>
        /* Global Styles */
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

        .add-btn, .submit-btn, .download-btn {
            background: none;
            border: 2px solid #333;
            padding: 10px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .add-btn:hover, .submit-btn:hover, .download-btn:hover {
            background: #333;
            color: #fff;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

        .submit-btn, .download-btn {
            background: #333;
            color: white;
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            border-radius: 4px;
            transition: opacity 0.3s ease;
            margin-top: 20px;
        }

        #qrcode {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        .download-btn {
            display: none; /* Hide initially */
            text-decoration: none;
        }

        .qr-code{
          display: flex;
          justify-content: center;
          align-items: center;
          flex-direction: column;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Generate QR Code</h2>
        </div>
        <div class="form-container">
            <form id="qrForm">
                <div class="form-group">
                    <label for="status">Status:</label>
                    <input type="text" id="status" name="status" required>
                </div>
                <div class="form-group">
                    <label for="id">ID:</label>
                    <input type="text" id="id" name="id" required>
                </div>
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="lrn">LRN:</label>
                    <input type="text" id="lrn" name="lrn" required>
                </div>
                <div class="form-group">
                    <label for="timeIn">Time In:</label>
                    <input type="text" id="timeIn" name="timeIn" required>
                </div>
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <input type="text" id="gender" name="gender" required>
                </div>
                <div class="form-group">
                    <label for="enrolled">Enrolled:</label>
                    <input type="text" id="enrolled" name="enrolled" required>
                </div>
                <button type="button" class="submit-btn" onclick="generateQRCode()">Generate QR Code</button>
            </form>
            <div class="qr-code">
              <canvas id="qrcode"></canvas>
               <a id="downloadBtn" class="download-btn" download="qrcode.png">Download QR Code</a>
            </div>
        </div>
    </div>

    <script>
        function generateQRCode() {
            const status = document.getElementById("status").value;
            const id = document.getElementById("id").value;
            const name = document.getElementById("name").value;
            const lrn = document.getElementById("lrn").value;
            const timeIn = document.getElementById("timeIn").value;
            const gender = document.getElementById("gender").value;
            const enrolled = document.getElementById("enrolled").value;
            
            if (status && id && name && lrn && timeIn && gender && enrolled) {
                const qrData = `Status: ${status}\nID: ${id}\nName: ${name}\nLRN: ${lrn}\nTime In: ${timeIn}\nGender: ${gender}\nEnrolled: ${enrolled}`;
                const canvas = document.getElementById("qrcode");
                
                QRCode.toCanvas(canvas, qrData, { width: 200 }, function (error) {
                    if (error) console.error(error);
                    else {
                        const dataUrl = canvas.toDataURL("image/png");
                        const downloadBtn = document.getElementById("downloadBtn");
                        downloadBtn.href = dataUrl;
                        downloadBtn.style.display = "inline-block"; // Show the download button
                    }
                });
            } else {
                alert("Please fill in all fields.");
            }
        }
    </script>
</body>
</html>
        