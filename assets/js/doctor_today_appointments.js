/**
 * Doctor Today Appointments - JavaScript
 * Quản lý lịch hẹn hôm nay của bác sĩ
 */

/**
 * Thay đổi ngày xem
 */
function changeDate(date) {
  window.location.href = `?action=doctor_today_appointments&date=${date}`;
}

/**
 * Làm mới dữ liệu
 */
function refreshData() {
  location.reload();
}

/**
 * Xác nhận lịch hẹn
 */
function confirmAppointment(appointmentId) {
  if (!confirm("Bạn có chắc chắn muốn xác nhận lịch hẹn này?")) {
    return;
  }

  // Update status to "Đã xác nhận"
  updateAppointmentStatus(appointmentId, "Đã xác nhận", "Bác sĩ đã xác nhận");
}

/**
 * Hủy/Từ chối lịch hẹn
 */
function cancelAppointment(appointmentId) {
  const reason = prompt("Lý do từ chối lịch hẹn:");
  if (!reason) {
    return;
  }

  // Update status to "Đã hủy"
  updateAppointmentStatus(appointmentId, "Đã hủy", reason);
}

/**
 * Bắt đầu khám bệnh
 */
function startExamination(appointmentId) {
  if (confirm("Bạn có muốn bắt đầu khám bệnh cho lịch hẹn này?")) {
    // Redirect to examination page
    window.location.href = `?action=start_examination&appointment_id=${appointmentId}`;
  }
}

/**
 * Tiếp tục khám
 */
function continueExamination(appointmentId) {
  // Redirect to examination page
  window.location.href = `?action=start_examination&appointment_id=${appointmentId}`;
}

/**
 * Xem chi tiết lịch hẹn
 */
function viewDetails(appointmentId) {
  // Redirect to appointment detail or examination page
  window.location.href = `?action=start_examination&appointment_id=${appointmentId}`;
}

/**
 * Cập nhật trạng thái lịch hẹn
 */
function updateAppointmentStatus(appointmentId, status, note) {
  // Show loading
  showNotification("info", "Đang cập nhật...");

  // Create form data
  const formData = new FormData();
  formData.append("appointment_id", appointmentId);
  formData.append("status", status);
  formData.append("note", note);

  // Send request
  fetch("?action=update_appointment_status", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (response.redirected) {
        window.location.href = response.url;
        return null;
      }
      return response.text();
    })
    .then((data) => {
      if (data) {
        showNotification("success", "Cập nhật thành công!");
        // Reload after 1 second
        setTimeout(() => {
          location.reload();
        }, 1000);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("error", "Có lỗi xảy ra khi cập nhật trạng thái!");
    });
}

/**
 * Hiển thị thông báo
 */
function showNotification(type, message) {
  // Remove existing notifications
  const existingAlerts = document.querySelectorAll(".alert.position-fixed");
  existingAlerts.forEach((alert) => alert.remove());

  // Create new notification
  const alertDiv = document.createElement("div");
  alertDiv.className = `alert alert-${
    type === "success" ? "success" : type === "info" ? "info" : "danger"
  } alert-dismissible fade show position-fixed`;
  alertDiv.style.cssText =
    "top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);";
  alertDiv.innerHTML = `
        <i class="fas fa-${
          type === "success"
            ? "check-circle"
            : type === "info"
            ? "info-circle"
            : "exclamation-circle"
        } me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

  // Add to body
  document.body.appendChild(alertDiv);

  // Auto hide after 5 seconds
  setTimeout(() => {
    alertDiv.remove();
  }, 5000);
}

// Initialize date picker to today if not set
document.addEventListener("DOMContentLoaded", function () {
  const dateSelector = document.getElementById("dateSelector");
  if (dateSelector && !dateSelector.value) {
    const today = new Date().toISOString().split("T")[0];
    dateSelector.value = today;
  }
});
