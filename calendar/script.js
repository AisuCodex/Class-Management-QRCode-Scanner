document.addEventListener('DOMContentLoaded', () => {
  const calendar = document.getElementById('calendar');
  const currentMonthYear = document.getElementById('currentMonthYear');
  const taskModal = document.getElementById('taskModal');
  const closeModal = document.querySelector('.close');
  const taskForm = document.getElementById('taskForm');
  const selectedDateInput = document.getElementById('selectedDate');

  const prevMonthBtn = document.getElementById('prevMonth');
  const nextMonthBtn = document.getElementById('nextMonth');
  const prevYearBtn = document.getElementById('prevYear');
  const nextYearBtn = document.getElementById('nextYear');

  let currentDate = new Date();

  // Load tasks from localStorage or initialize empty object
  const tasks = JSON.parse(localStorage.getItem('tasks')) || {};

  // Function to generate the calendar
  function generateCalendar(date) {
    const year = date.getFullYear();
    const month = date.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);

    currentMonthYear.textContent = `${firstDay.toLocaleString('default', {
      month: 'long',
    })} ${year}`;
    calendar.innerHTML = '';

    const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    daysOfWeek.forEach((day) => {
      const dayHeader = document.createElement('div');
      dayHeader.className = 'day header';
      dayHeader.textContent = day;
      calendar.appendChild(dayHeader);
    });

    // Fill in empty days at the start of the month
    for (let i = 0; i < firstDay.getDay(); i++) {
      const emptyDay = document.createElement('div');
      emptyDay.className = 'day empty';
      calendar.appendChild(emptyDay);
    }

    // Fill in days of the month
    for (let i = 1; i <= lastDay.getDate(); i++) {
      const day = document.createElement('div');
      day.className = 'day';
      day.textContent = i;

      const formattedDate = `${year}-${month + 1}-${i}`;
      if (tasks[formattedDate]) {
        const taskList = document.createElement('ul');
        taskList.className = 'task-list';

        tasks[formattedDate].forEach((task, index) => {
          const taskItem = document.createElement('li');
          taskItem.textContent = task;

          // Add remove button for each task
          const removeBtn = document.createElement('button');
          removeBtn.textContent = 'Remove';
          removeBtn.className = 'remove-btn';
          removeBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevent opening modal on click
            removeTask(formattedDate, index);
          });

          taskItem.appendChild(removeBtn);
          taskList.appendChild(taskItem);
        });

        day.appendChild(taskList);
      }

      day.addEventListener('click', () => openTaskModal(year, month, i));
      calendar.appendChild(day);
    }
  }

  // Function to open the task modal
  function openTaskModal(year, month, day) {
    selectedDateInput.value = `${year}-${month + 1}-${day}`;
    taskModal.style.display = 'flex';
  }

  // Function to close the task modal
  closeModal.addEventListener('click', () => {
    taskModal.style.display = 'none';
  });

  // Save task to localStorage and update calendar
  taskForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(taskForm);
    const taskDate = formData.get('date');
    const taskText = formData.get('task');

    if (!tasks[taskDate]) {
      tasks[taskDate] = [];
    }
    tasks[taskDate].push(taskText);

    // Save tasks to localStorage
    localStorage.setItem('tasks', JSON.stringify(tasks));

    // Close modal and reset form
    taskModal.style.display = 'none';
    taskForm.reset();
    generateCalendar(currentDate);
  });

  // Function to remove a task
  function removeTask(date, index) {
    if (tasks[date]) {
      tasks[date].splice(index, 1);
      if (tasks[date].length === 0) {
        delete tasks[date]; // Remove date key if no tasks left
      }

      // Save updated tasks to localStorage
      localStorage.setItem('tasks', JSON.stringify(tasks));

      generateCalendar(currentDate);
    }
  }

  // Close modal if clicking outside of it
  window.onclick = (event) => {
    if (event.target == taskModal) {
      taskModal.style.display = 'none';
    }
  };

  // Navigation buttons to change month and year
  prevMonthBtn.addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() - 1);
    generateCalendar(currentDate);
  });

  nextMonthBtn.addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    generateCalendar(currentDate);
  });

  prevYearBtn.addEventListener('click', () => {
    currentDate.setFullYear(currentDate.getFullYear() - 1);
    generateCalendar(currentDate);
  });

  nextYearBtn.addEventListener('click', () => {
    currentDate.setFullYear(currentDate.getFullYear() + 1);
    generateCalendar(currentDate);
  });

  // Initial calendar generation
  generateCalendar(currentDate);
});
