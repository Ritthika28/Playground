/*script for add another time slot button*/
        document.addEventListener("DOMContentLoaded", function () {
        let timeSlotIndex = document.querySelectorAll('.time-slot').length;

        function addTimeSlot(containerId) {
            const container = document.getElementById(containerId);
            const slotDiv = document.createElement("div");
            slotDiv.className = "time-slot mb-2";

            slotDiv.innerHTML = `
                <label>Booking Date:</label>
                <input type="Date" name="time_slots[${timeSlotIndex}][booking_date]" required>
                <input type="hidden" name="time_slots[${timeSlotIndex}][slot_id]" value="">
                <label>Start Time:</label>
                <input type="time" name="time_slots[${timeSlotIndex}][start_time]" required>
                <label>End Time:</label>
                <input type="time" name="time_slots[${timeSlotIndex}][end_time]" required>
                <button type="button" class="btn btn-danger btn-sm remove-slot">Remove</button>
            `;

            container.appendChild(slotDiv);
            timeSlotIndex++;

            slotDiv.querySelector(".remove-slot").addEventListener("click", function () {
                container.removeChild(slotDiv);
            });
        }

        document.getElementById("add-slot-button")?.addEventListener("click", function () {
            addTimeSlot("add-time-slots");
        });

        document.getElementById("edit-slot-button")?.addEventListener("click", function () {
            addTimeSlot("edit-time-slots");
        });
    });


const toggleBtn = document.querySelector('.toggle-sidebar-btn');
        const sidebar = document.querySelector('.sidebar');
        const content = document.querySelector('.content');
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');
        });

/* Set the width of the sidebar to 250px and the left margin of the page content to 250px */
        function openNav() {
          document.getElementById("mySidebar").style.width = "250px";
          document.getElementById("main").style.marginLeft = "250px";
        }

        /* Set the width of the sidebar to 0 and the left margin of the page content to 0 */
        function closeNav() {
          document.getElementById("mySidebar").style.width = "0";
          document.getElementById("main").style.marginLeft = "0";
        }