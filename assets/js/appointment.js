// Appointment Booking JavaScript
document.addEventListener("DOMContentLoaded", function () {
  const doctorIdInput = document.getElementById("doctorId");
  const appointmentDate = document.getElementById("appointmentDate");
  const appointmentTime = document.getElementById("appointmentTime");
  const submitBtn = document.getElementById("submitBtn");
  const loadingOverlay = document.getElementById("loadingOverlay");

  // Event listeners
  appointmentDate.addEventListener("change", loadTimeSlots);

  // Initialize form
  function initializeForm() {
    // Enable date input if doctor is selected
    if (doctorIdInput.value) {
      appointmentDate.disabled = false;
    }
    updateSubmitButton();
  }

  // Load time slots for selected date
  function loadTimeSlots() {
    const date = appointmentDate.value;
    const doctorId = doctorIdInput.value;

    if (!date || !doctorId) {
      appointmentTime.innerHTML = '<option value="">Chọn ngày trước</option>';
      appointmentTime.disabled = true;
      updateSubmitButton();
      return;
    }

    showLoading();

    fetch("./get_available_time_slots", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `doctor_id=${doctorId}&date=${date}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          displayTimeSlots(data.data, doctorId, date);
        } else {
          showError(data.message || "Không thể tải khung giờ");
          appointmentTime.innerHTML =
            '<option value="">Không có khung giờ trống</option>';
          appointmentTime.disabled = true;
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showError("Có lỗi xảy ra khi tải khung giờ");
        appointmentTime.innerHTML = '<option value="">Có lỗi xảy ra</option>';
        appointmentTime.disabled = true;
      })
      .finally(() => {
        hideLoading();
        updateSubmitButton();
      });
  }

  // Display time slots
  function displayTimeSlots(timeSlots, doctorId, date) {
    if (timeSlots.length === 0) {
      appointmentTime.innerHTML =
        '<option value="">Không có khung giờ trống</option>';
      appointmentTime.disabled = true;
      return;
    }

    appointmentTime.innerHTML = '<option value="">Chọn giờ khám</option>';
    // Kiểm tra conflict từng slot để disable nếu đã được đặt
    const checks = timeSlots.map((slot) => {
      return fetch("./check_conflict", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `doctor_id=${doctorId}&date=${date}&time=${slot.time}`,
      })
        .then((r) => r.json())
        .then((resp) => ({ slot, conflict: resp.conflict }))
        .catch(() => ({ slot, conflict: false }));
    });

    Promise.all(checks).then((results) => {
      const optionsHtml = results
        .map(({ slot, conflict }) => {
          const disabled = conflict ? "disabled" : "";
          const note = conflict ? " - (đã được đặt)" : "";
          return `<option value="${slot.time}" ${disabled}>${slot.display} (${slot.shift})${note}</option>`;
        })
        .join("");
      appointmentTime.innerHTML =
        '<option value="">Chọn giờ khám</option>' + optionsHtml;
      appointmentTime.disabled = false;
    });

    appointmentTime.disabled = false;
  }

  // Update submit button state
  function updateSubmitButton() {
    const hasDoctor = doctorIdInput.value;
    const hasDate = appointmentDate.value;
    const hasTime = appointmentTime.value;

    submitBtn.disabled = !(hasDoctor && hasDate && hasTime);
  }

  // Event listener for time selection
  appointmentTime.addEventListener("change", updateSubmitButton);

  // Form submission
  document
    .getElementById("appointmentForm")
    .addEventListener("submit", function (e) {
      if (!submitBtn.disabled) {
        showLoading();
      }
    });

  // Utility functions
  function showLoading() {
    loadingOverlay.style.display = "flex";
  }

  function hideLoading() {
    loadingOverlay.style.display = "none";
  }

  function showError(message) {
    // You can implement a toast notification here
    console.error(message);
  }

  // Initialize form on page load
  initializeForm();
});
