document.addEventListener("DOMContentLoaded", () => {
    // Role is dynamically set by PHP
    // const role is already passed to JS from PHP

    // Toggle calendar visibility
    function toggleCalendar() {
        const calendarPopup = document.getElementById("calendarPopup");
        calendarPopup.style.display = calendarPopup.style.display === "block" ? "none" : "block";
    }

    // Event data storage (using localStorage for persistence)
    const eventList = JSON.parse(localStorage.getItem("eventList")) || {};

    // Save events to localStorage
    function saveEvents() {
        localStorage.setItem("eventList", JSON.stringify(eventList));
    }

    // Current month and year
    let currentDate = new Date();

    // Generate calendar dynamically
    function generateCalendar() {
        const calendarGrid = document.querySelector(".calendar-grid");
        const monthYearDisplay = document.getElementById("monthYearDisplay");
        const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1).getDay();
        const daysInMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0).getDate();

        // Update month and year display
        const monthNames = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        monthYearDisplay.textContent = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;

        calendarGrid.innerHTML = ""; // Clear the existing grid

        // Add day names
        const dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
        dayNames.forEach((day) => {
            const dayNameCell = document.createElement("div");
            dayNameCell.textContent = day;
            dayNameCell.classList.add("day-name");
            calendarGrid.appendChild(dayNameCell);
        });

        // Add blank days for alignment
        for (let i = 0; i < firstDay; i++) {
            const blankDay = document.createElement("div");
            blankDay.classList.add("blank-day");
            calendarGrid.appendChild(blankDay);
        }

        // Populate calendar with days
        for (let day = 1; day <= daysInMonth; day++) {
            const dateKey = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
            const dayCell = document.createElement("div");
            dayCell.textContent = day;
            dayCell.classList.add("day-cell");

            // Highlight today's date
            if (dateKey === new Date().toISOString().split("T")[0]) {
                dayCell.classList.add("current-day");
            }

            // Show event indicator if events exist for the date
            if (eventList[dateKey] && eventList[dateKey].length > 0) {
                const eventIndicator = document.createElement("span");
                eventIndicator.classList.add("event-indicator");
                eventIndicator.textContent = `${eventList[dateKey].length} event(s)`;
                dayCell.appendChild(eventIndicator);
            }

            // Click to view or manage events based on role
            dayCell.addEventListener("click", () => {
                if (role === "teacher") {
                    manageEvents(dateKey);
                } else {
                    viewEvents(dateKey);
                }
            });

            calendarGrid.appendChild(dayCell);
        }
    }

    // Manage events (add, edit, delete) for a specific date
    function manageEvents(dateKey) {
        const events = eventList[dateKey] || [];
        const eventOptions = events.map((event, index) => `${index + 1}. ${event}`).join("\n");

        const action = prompt(
            `Manage Events for ${dateKey} (Teacher Actions):\n\n${eventOptions || "No events found"}\n\n` +
            "Choose an action:\n1. Add Event\n2. Edit Event\n3. Delete Event\n4. Cancel"
        );

        if (action === "1") {
            const newEvent = prompt("Enter the event description:");
            if (newEvent) {
                if (!eventList[dateKey]) eventList[dateKey] = [];
                eventList[dateKey].push(newEvent);
                saveEvents();
                alert(`Event added for ${dateKey}.`);
            }
        } else if (action === "2") {
            if (events.length === 0) {
                alert("No events to edit.");
                return;
            }
            const eventToEdit = prompt(
                `Select the event number to edit:\n${eventOptions}`
            );
            const indexToEdit = parseInt(eventToEdit) - 1;
            if (indexToEdit >= 0 && indexToEdit < events.length) {
                const updatedEvent = prompt(
                    `Editing Event: \"${events[indexToEdit]}\"\nEnter the new description:`
                );
                if (updatedEvent) {
                    eventList[dateKey][indexToEdit] = updatedEvent;
                    saveEvents();
                    alert(`Event updated for ${dateKey}.`);
                }
            } else {
                alert("Invalid selection.");
            }
        } else if (action === "3") {
            if (events.length === 0) {
                alert("No events to delete.");
                return;
            }
            const eventToDelete = prompt(
                `Select the event number to delete:\n${eventOptions}`
            );
            const indexToDelete = parseInt(eventToDelete) - 1;
            if (indexToDelete >= 0 && indexToDelete < events.length) {
                eventList[dateKey].splice(indexToDelete, 1);
                if (eventList[dateKey].length === 0) {
                    delete eventList[dateKey];
                }
                saveEvents();
                alert(`Event deleted for ${dateKey}.`);
            } else {
                alert("Invalid selection.");
            }
        }
        generateCalendar(); // Refresh calendar after changes
    }

    // View events for students
    function viewEvents(dateKey) {
        const events = eventList[dateKey] || [];
        alert(`Events for ${dateKey}:\n\n${events.join("\n") || "No events found."}`);
    }

    // Navigate to previous month
    function previousMonth() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        generateCalendar();
    }

    // Navigate to next month
    function nextMonth() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        generateCalendar();
    }

    // Attach event listeners to buttons
    document.getElementById("prevMonth").addEventListener("click", previousMonth);
    document.getElementById("nextMonth").addEventListener("click", nextMonth);

    // Initialize calendar
    generateCalendar();
    window.toggleCalendar = toggleCalendar; // Expose globally for onclick in HTML
});
