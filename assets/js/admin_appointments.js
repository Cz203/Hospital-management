/**
 * Admin Appointments Management - JavaScript
 * Quản lý lịch hẹn cho Admin
 */

let viewModal;

// Khởi tạo khi document ready
document.addEventListener("DOMContentLoaded", function () {
  // Khởi tạo Bootstrap modal
  const modalElement = document.getElementById("viewModal");
  if (modalElement) {
    viewModal = new bootstrap.Modal(modalElement);
  }
});

/**
 * Xem chi tiết lịch hẹn
 */
function viewAppointment(appointmentId) {
  // Show modal with loading
  viewModal.show();

  // Fetch appointment details
  fetch(`?action=get_appointment_detail&id=${appointmentId}`)
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        const appointment = result.data;
        displayAppointmentDetails(appointment);
      } else {
        document.getElementById("modalContent").innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        ${result.message || "Không thể tải thông tin lịch hẹn!"}
                    </div>
                `;
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      document.getElementById("modalContent").innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Có lỗi xảy ra khi tải thông tin lịch hẹn!
                </div>
            `;
    });
}

/**
 * Hiển thị chi tiết lịch hẹn trong modal
 */
function displayAppointmentDetails(appointment) {
  const statusBadge = getStatusBadge(appointment.trang_thai);
  const typeBadge = getTypeBadge(appointment.loai_lich);

  const html = `
        <div class="appointment-details">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Mã lịch hẹn</h6>
                    <h5><span class="badge bg-secondary">#${
                      appointment.id
                    }</span></h5>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="text-muted mb-2">Trạng thái</h6>
                    <h5>${statusBadge}</h5>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="info-group">
                        <i class="fas fa-user-injured text-primary me-2"></i>
                        <strong>Bệnh nhân:</strong>
                        <p class="mb-0">${
                          appointment.ten_benh_nhan || "N/A"
                        }</p>
                        <small class="text-muted">${
                          appointment.so_dien_thoai || ""
                        }</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-group">
                        <i class="fas fa-user-md text-success me-2"></i>
                        <strong>Bác sĩ:</strong>
                        <p class="mb-0">${appointment.ten_bac_si || "N/A"}</p>
                        <small class="text-muted">${
                          appointment.chuyen_khoa || ""
                        }</small>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="info-group">
                        <i class="fas fa-calendar-day text-info me-2"></i>
                        <strong>Ngày hẹn:</strong>
                        <p class="mb-0">${formatDate(appointment.ngay_hen)}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-group">
                        <i class="fas fa-clock text-warning me-2"></i>
                        <strong>Giờ hẹn:</strong>
                        <p class="mb-0">${formatTime(appointment.gio_hen)}</p>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="info-group">
                        <i class="fas fa-clipboard-list text-secondary me-2"></i>
                        <strong>Loại lịch:</strong>
                        <p class="mb-0">${typeBadge}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-group">
                        <i class="fas fa-calendar-plus text-muted me-2"></i>
                        <strong>Ngày tạo:</strong>
                        <p class="mb-0">${formatDateTime(
                          appointment.ngay_tao
                        )}</p>
                    </div>
                </div>
            </div>

            ${
              appointment.ly_do
                ? `
            <div class="row mb-3">
                <div class="col-12">
                    <div class="info-group">
                        <i class="fas fa-notes-medical text-danger me-2"></i>
                        <strong>Lý do khám:</strong>
                        <p class="mb-0">${appointment.ly_do}</p>
                    </div>
                </div>
            </div>
            `
                : ""
            }

            ${
              appointment.ghi_chu
                ? `
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Ghi chú:</strong> ${appointment.ghi_chu}
                    </div>
                </div>
            </div>
            `
                : ""
            }

            ${
              appointment.dia_chi_kham && appointment.loai_lich === "Tại nhà"
                ? `
            <div class="row mb-3">
                <div class="col-12">
                    <div class="info-group">
                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                        <strong>Địa chỉ khám:</strong>
                        <p class="mb-0">${appointment.dia_chi_kham}</p>
                    </div>
                </div>
            </div>
            `
                : ""
            }

            ${
              appointment.link_tu_van && appointment.loai_lich === "Tư vấn"
                ? `
            <div class="row mb-3">
                <div class="col-12">
                    <div class="info-group">
                        <i class="fas fa-video text-primary me-2"></i>
                        <strong>Link tư vấn:</strong>
                        <p class="mb-0"><a href="${appointment.link_tu_van}" target="_blank" class="btn btn-sm btn-primary">
                            <i class="fas fa-external-link-alt me-1"></i>Tham gia cuộc họp
                        </a></p>
                    </div>
                </div>
            </div>
            `
                : ""
            }
        </div>
    `;

  document.getElementById("modalContent").innerHTML = html;
}

/**
 * Hủy lịch hẹn
 */
function cancelAppointment(appointmentId) {
  // Show confirmation dialog
  if (
    !confirm(
      "Bạn có chắc chắn muốn hủy lịch hẹn này?\nHành động này không thể hoàn tác!"
    )
  ) {
    return;
  }

  // Ask for reason
  const reason = prompt("Lý do hủy lịch hẹn:");
  if (!reason) {
    return;
  }

  // Send request
  fetch("?action=admin_cancel_appointment", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      appointment_id: appointmentId,
      reason: reason,
    }),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        showNotification("success", result.message);
        // Reload page after 1 second
        setTimeout(() => {
          location.reload();
        }, 1000);
      } else {
        showNotification(
          "error",
          result.message || "Có lỗi xảy ra khi hủy lịch hẹn!"
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("error", "Có lỗi xảy ra khi hủy lịch hẹn!");
    });
}

/**
 * Làm mới dữ liệu
 */
function refreshData() {
  location.reload();
}

/**
 * Xuất dữ liệu Excel (placeholder)
 */
function exportAppointments() {
  showNotification("info", "Tính năng xuất Excel đang được phát triển!");
}

/**
 * Get status badge HTML
 */
function getStatusBadge(status) {
  const statusMap = {
    "Chờ xác nhận": "warning",
    "Đã xác nhận": "success",
    "Đang khám": "info",
    "Hoàn thành": "secondary",
    "Đã hủy": "danger",
  };
  const className = statusMap[status] || "secondary";
  return `<span class="badge bg-${className}">${status}</span>`;
}

/**
 * Get type badge HTML
 */
function getTypeBadge(type) {
  const typeMap = {
    "Trực tiếp": "primary",
    "Tư vấn": "info",
    "Tại nhà": "warning",
  };
  const className = typeMap[type] || "secondary";
  return `<span class="badge bg-${className}">${type}</span>`;
}

/**
 * Format date (YYYY-MM-DD to DD/MM/YYYY)
 */
function formatDate(dateString) {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}

/**
 * Format time (HH:MM:SS to HH:MM)
 */
function formatTime(timeString) {
  if (!timeString) return "N/A";
  return timeString.substring(0, 5);
}

/**
 * Format datetime
 */
function formatDateTime(datetimeString) {
  if (!datetimeString) return "N/A";
  const date = new Date(datetimeString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, "0");
  const minutes = String(date.getMinutes()).padStart(2, "0");
  return `${day}/${month}/${year} ${hours}:${minutes}`;
}

/**
 * Hiển thị thông báo
 */
function showNotification(type, message) {
  // Tạo element thông báo
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

  // Thêm vào body
  document.body.appendChild(alertDiv);

  // Tự động ẩn sau 5 giây
  setTimeout(() => {
    alertDiv.remove();
  }, 5000);
}
