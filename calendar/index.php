<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Calendar App</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<style>
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
      display: flex;
      flex-direction: column;
      align-items: center;
  }

  h1 {
      text-align: center;
      margin-bottom: 40px;
  }

  .navigation {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
  }

  .calendar {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 5px;
      margin: 20px;
      width: 100%;
      max-width: 800px;
  }

  .day {
      border: 1px solid #ddd;
      padding: 15px;
      text-align: center;
      cursor: pointer;
      background: white;
      transition: background 0.3s;
  }

  .day:hover {
      background: #eee;
  }

  .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
  }

  .modal-content {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      width: 300px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }

  .close {
      float: right;
      cursor: pointer;
      font-size: 1.2em;
  }

  form {
      display: flex;
      flex-direction: column;
      gap: 15px;
  }

  label {
      font-weight: bold;
      color: #555;
  }

  input[type="text"] {
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 16px;
  }

  button {
      background: #333;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 4px;
      cursor: pointer;
      transition: background 0.3s;
  }

  button:hover {
      background: #555;
  }

  #currentMonthYear {
      font-size: 1.2em;
      font-weight: bold;
      color: #333;
  }
</style>
<body>
  <h1>Calendar App</h1>
  
  <!-- Navigation for Year and Month -->
  <div class="navigation">
    <button id="prevYear">« Prev Year</button>
    <button id="prevMonth">« Prev Month</button>
    <span id="currentMonthYear"></span>
    <button id="nextMonth">Next Month »</button>
    <button id="nextYear">Next Year »</button>
  </div>
  
  <!-- Calendar Grid -->
  <div class="calendar" id="calendar"></div>

  <!-- Task Modal -->
  <div id="taskModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Add Task</h2>
      <form id="taskForm">
        <input type="hidden" id="selectedDate" name="date" />
        <label for="task">Task:</label>
        <input type="text" id="task" name="task" required />
        <button type="submit">Save Task</button>
      </form>
    </div>
  </div>

  <script src="script.js"></script>
</body>
</html>
