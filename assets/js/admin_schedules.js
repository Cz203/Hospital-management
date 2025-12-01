/**
 * Admin Schedule Management - JavaScript
 * Quản lý lịch làm việc bác sĩ cho Admin
 */

let scheduleModal;
let isEditMode = false;

// Khởi tạo khi document ready
document.addEventListener("DOMContentLoaded", function () {
  // Khởi tạo Bootstrap modal
  const modalElement = document.getElementById("scheduleModal");
  if (modalElement) {
    scheduleModal = new bootstrap.Modal(modalElement);
  }

  // Auto-fill giờ bắt đầu / kết thúc theo loại ca (áp dụng cho cả thêm & sửa)
  const loaiCaSelect = document.getElementById("loaiCa");
  const gioBatDauInput = document.getElementById("gioBatDau");
  const gioKetThucInput = document.getElementById("gioKetThuc");

  if (loaiCaSelect && gioBatDauInput && gioKetThucInput) {
    loaiCaSelect.addEventListener("change", function () {
      switch (this.value) {
        case "Ca sáng":
          // Ca sáng: 07:00 - 11:30
          gioBatDauInput.value = "07:00";
          gioKetThucInput.value = "11:30";
          break;
        case "Ca chiều":
          // Ca chiều: 13:00 - 21:00
          gioBatDauInput.value = "13:00";
          gioKetThucInput.value = "21:00";
          break;
        default:
          // Không set gì nếu không chọn ca hợp lệ
          break;
      }
    });
  }
});

/**
 * Hiển thị modal thêm lịch làm việc mới (từ nút bên cạnh bác sĩ)
 */
function showAddScheduleModal(doctorId, dayOfWeek) {
  isEditMode = false;

  // Reset form
  document.getElementById("scheduleForm").reset();
  document.getElementById("scheduleId").value = "";
  document.getElementById("doctorIdHidden").value = doctorId;

  // Ẩn dropdown chọn bác sĩ và set giá trị
  document.getElementById("doctorSelectGroup").style.display = "none";
  document.getElementById("doctorSelect").value = doctorId;
  document.getElementById("doctorSelect").removeAttribute("required");

  // Set thứ trong tuần nếu có
  if (dayOfWeek) {
    document.getElementById("thuTrongTuan").value = dayOfWeek;
  }

  // Ẩn trường trạng thái khi thêm mới
  document.getElementById("trangThaiGroup").style.display = "none";

  // Thay đổi tiêu đề modal
  document.getElementById("scheduleModalLabel").textContent =
    "Thêm lịch làm việc";

  // Hiển thị modal
  scheduleModal.show();
}

/**
 * Hiển thị modal thêm lịch với dropdown chọn bác sĩ (từ nút header)
 */
function showAddScheduleModalWithDoctorSelect() {
  isEditMode = false;

  // Reset form
  document.getElementById("scheduleForm").reset();
  document.getElementById("scheduleId").value = "";
  document.getElementById("doctorIdHidden").value = "";

  // Hiển thị dropdown chọn bác sĩ
  document.getElementById("doctorSelectGroup").style.display = "block";
  document.getElementById("doctorSelect").value = "";
  document.getElementById("doctorSelect").setAttribute("required", "required");

  // Ẩn trường trạng thái khi thêm mới
  document.getElementById("trangThaiGroup").style.display = "none";

  // Thay đổi tiêu đề modal
  document.getElementById("scheduleModalLabel").textContent =
    "Thêm lịch làm việc";

  // Hiển thị modal
  scheduleModal.show();
}

/**
 * Hiển thị modal sửa lịch làm việc
 */
function showEditScheduleModal(scheduleId, doctorId) {
  isEditMode = true;

  // Thay đổi tiêu đề modal
  document.getElementById("scheduleModalLabel").textContent =
    "Sửa lịch làm việc";

  // Ẩn dropdown chọn bác sĩ khi sửa
  document.getElementById("doctorSelectGroup").style.display = "none";
  document.getElementById("doctorSelect").removeAttribute("required");

  // Hiển thị trường trạng thái khi sửa
  document.getElementById("trangThaiGroup").style.display = "block";

  // Hiển thị modal trước khi load dữ liệu
  scheduleModal.show();

  // Load dữ liệu lịch làm việc
  fetch(
    `?action=admin_get_schedule_info&schedule_id=${scheduleId}&doctor_id=${doctorId}`
  )
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        const schedule = result.data;

        // Điền dữ liệu vào form
        document.getElementById("scheduleId").value = schedule.id;
        document.getElementById("doctorIdHidden").value = doctorId;
        document.getElementById("doctorSelect").value = doctorId;
        document.getElementById("thuTrongTuan").value = schedule.thu_trong_tuan;
        document.getElementById("gioBatDau").value = schedule.gio_bat_dau;
        document.getElementById("gioKetThuc").value = schedule.gio_ket_thuc;
        document.getElementById("loaiCa").value = schedule.loai_ca;
        document.getElementById("ghiChu").value = schedule.ghi_chu || "";
        document.getElementById("trangThai").value =
          schedule.trang_thai || "active";
      } else {
        showNotification(
          "error",
          result.message || "Không thể tải thông tin lịch làm việc!"
        );
        scheduleModal.hide();
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification(
        "error",
        "Có lỗi xảy ra khi tải thông tin lịch làm việc!"
      );
      scheduleModal.hide();
    });
}

/**
 * Lưu lịch làm việc (thêm mới hoặc cập nhật)
 */
function saveSchedule() {
  const form = document.getElementById("scheduleForm");

  // Validate form
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  // Lấy doctor_id từ dropdown (nếu hiển thị) hoặc từ hidden field
  let doctorId;
  const doctorSelectGroup = document.getElementById("doctorSelectGroup");
  if (doctorSelectGroup.style.display !== "none") {
    // Nếu dropdown hiển thị, lấy từ dropdown
    doctorId = document.getElementById("doctorSelect").value;
  } else {
    // Nếu dropdown ẩn, lấy từ hidden field
    doctorId = document.getElementById("doctorIdHidden").value;
  }

  // Lấy dữ liệu từ form
  const formData = {
    schedule_id: document.getElementById("scheduleId").value,
    doctor_id: doctorId,
    thu_trong_tuan: document.getElementById("thuTrongTuan").value,
    gio_bat_dau: document.getElementById("gioBatDau").value,
    gio_ket_thuc: document.getElementById("gioKetThuc").value,
    loai_ca: document.getElementById("loaiCa").value,
    ghi_chu: document.getElementById("ghiChu").value,
    trang_thai: document.getElementById("trangThai").value || "active",
  };

  // Xác định action (thêm mới hay cập nhật)
  const action = isEditMode ? "admin_update_schedule" : "admin_add_schedule";

  // Disable nút lưu để tránh click nhiều lần
  const saveBtn = event.target;
  const originalText = saveBtn.innerHTML;
  saveBtn.disabled = true;
  saveBtn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Đang lưu...';

  // Gửi request
  fetch(`?action=${action}`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(formData),
  })
    .then((response) => response.json())
    .then((result) => {
      saveBtn.disabled = false;
      saveBtn.innerHTML = originalText;

      if (result.success) {
        showNotification("success", result.message);
        scheduleModal.hide();

        // Reload trang sau 1 giây để cập nhật dữ liệu
        setTimeout(() => {
          location.reload();
        }, 1000);
      } else {
        showNotification("error", result.message || "Có lỗi xảy ra!");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      saveBtn.disabled = false;
      saveBtn.innerHTML = originalText;
      showNotification("error", "Có lỗi xảy ra khi lưu lịch làm việc!");
    });
}

/**
 * Xóa lịch làm việc
 */
function deleteSchedule(scheduleId, doctorId) {
  // Xác nhận trước khi xóa
  if (
    !confirm(
      "Bạn có chắc chắn muốn xóa lịch làm việc này?\nHành động này không thể hoàn tác!"
    )
  ) {
    return;
  }

  // Gửi request xóa
  fetch("?action=admin_delete_schedule", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      schedule_id: scheduleId,
      doctor_id: doctorId,
    }),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        showNotification("success", result.message);

        // Reload trang sau 1 giây
        setTimeout(() => {
          location.reload();
        }, 1000);
      } else {
        showNotification(
          "error",
          result.message || "Có lỗi xảy ra khi xóa lịch làm việc!"
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("error", "Có lỗi xảy ra khi xóa lịch làm việc!");
    });
}

/**
 * Làm mới dữ liệu
 */
function refreshData() {
  location.reload();
}

/**
 * Lọc theo bác sĩ - LOGIC SỬA LẠI
 */
function filterByDoctor() {
  const doctorFilter = document.getElementById("doctorFilter");
  const selectedDoctorId = doctorFilter.value;
  const filterStatus = document.getElementById("filterStatus");

  // Lấy tất cả các day-card
  const allDayCards = document.querySelectorAll(".day-card");

  if (selectedDoctorId === "") {
    // Hiển thị tất cả
    allDayCards.forEach((dayCard) => {
      const doctorSchedules = dayCard.querySelectorAll(".doctor-schedule");
      doctorSchedules.forEach((schedule) => {
        schedule.style.display = "block";
      });

      // Xóa empty state nếu có lịch
      const dayBody = dayCard.querySelector(".day-body");
      const emptyState = dayBody.querySelector(".empty-state");
      if (doctorSchedules.length > 0 && emptyState) {
        emptyState.remove();
      } else if (doctorSchedules.length === 0 && !emptyState) {
        dayBody.innerHTML =
          '<div class="empty-state"><i class="fas fa-calendar-times"></i><p class="mb-0">Không có bác sĩ trực</p></div>';
      }
    });
    filterStatus.textContent = "Hiển thị tất cả bác sĩ";
  } else {
    // Chỉ hiển thị bác sĩ được chọn
    allDayCards.forEach((dayCard) => {
      const doctorSchedules = dayCard.querySelectorAll(".doctor-schedule");
      let hasVisibleSchedule = false;

      doctorSchedules.forEach((schedule) => {
        const doctorId = schedule.getAttribute("data-doctor-id");
        if (doctorId === selectedDoctorId) {
          schedule.style.display = "block";
          hasVisibleSchedule = true;
        } else {
          schedule.style.display = "none";
        }
      });

      // Cập nhật empty state
      const dayBody = dayCard.querySelector(".day-body");
      const emptyState = dayBody.querySelector(".empty-state");

      if (!hasVisibleSchedule && !emptyState) {
        // Thêm empty state nếu không có lịch hiển thị
        dayBody.innerHTML =
          '<div class="empty-state"><i class="fas fa-calendar-times"></i><p class="mb-0">Không có ca trực</p></div>';
      } else if (hasVisibleSchedule && emptyState) {
        // Xóa empty state nếu có lịch hiển thị
        emptyState.remove();
      }
    });

    const selectedOption = doctorFilter.options[doctorFilter.selectedIndex];
    filterStatus.textContent = `Hiển thị: ${selectedOption.text}`;
  }
}

/**
 * Hiển thị thông báo
 */
function showNotification(type, message) {
  // Tạo element thông báo
  const alertDiv = document.createElement("div");
  alertDiv.className = `alert alert-${
    type === "success" ? "success" : "danger"
  } alert-dismissible fade show position-fixed`;
  alertDiv.style.cssText =
    "top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);";
  alertDiv.innerHTML = `
        <i class="fas fa-${
          type === "success" ? "check-circle" : "exclamation-circle"
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
