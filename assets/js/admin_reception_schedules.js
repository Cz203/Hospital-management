/**
 * Admin Reception Schedule Management - JavaScript
 * Quản lý lịch làm việc lễ tân cho Admin
 */

let receptionScheduleModal;
let receptionIsEditMode = false;

// Khởi tạo khi document ready
document.addEventListener("DOMContentLoaded", function () {
  // Khởi tạo Bootstrap modal
  const modalElement = document.getElementById("receptionScheduleModal");
  if (modalElement) {
    receptionScheduleModal = new bootstrap.Modal(modalElement);
  }

  // Auto-fill giờ bắt đầu / kết thúc theo loại ca
  const loaiCaSelect = document.getElementById("receptionLoaiCa");
  const gioBatDauInput = document.getElementById("receptionGioBatDau");
  const gioKetThucInput = document.getElementById("receptionGioKetThuc");

  if (loaiCaSelect && gioBatDauInput && gioKetThucInput) {
    loaiCaSelect.addEventListener("change", function () {
      switch (this.value) {
        case "Ca sáng":
          gioBatDauInput.value = "07:00";
          gioKetThucInput.value = "11:30";
          break;
        case "Ca chiều":
          gioBatDauInput.value = "13:00";
          gioKetThucInput.value = "21:00";
          break;
        default:
          break;
      }
    });
  }
});

/**
 * Hiển thị modal thêm lịch làm việc mới (từ nút bên cạnh lễ tân)
 */
function showAddReceptionScheduleModal(receptionId, dayOfWeek) {
  receptionIsEditMode = false;

  const form = document.getElementById("receptionScheduleForm");
  form.reset();
  document.getElementById("receptionScheduleId").value = "";
  document.getElementById("receptionIdHidden").value = receptionId;

  const selectGroup = document.getElementById("receptionSelectGroup");
  const select = document.getElementById("receptionSelect");
  selectGroup.style.display = "none";
  select.value = receptionId;
  select.removeAttribute("required");

  if (dayOfWeek) {
    document.getElementById("receptionThuTrongTuan").value = dayOfWeek;
  }

  document.getElementById("receptionTrangThaiGroup").style.display = "none";
  document.getElementById("receptionScheduleModalLabel").textContent =
    "Thêm lịch làm việc lễ tân";

  receptionScheduleModal.show();
}

/**
 * Hiển thị modal thêm lịch với dropdown chọn lễ tân (từ nút header)
 */
function showAddReceptionScheduleModalWithSelect() {
  receptionIsEditMode = false;

  const form = document.getElementById("receptionScheduleForm");
  form.reset();
  document.getElementById("receptionScheduleId").value = "";
  document.getElementById("receptionIdHidden").value = "";

  const selectGroup = document.getElementById("receptionSelectGroup");
  const select = document.getElementById("receptionSelect");
  selectGroup.style.display = "block";
  select.value = "";
  select.setAttribute("required", "required");

  document.getElementById("receptionTrangThaiGroup").style.display = "none";
  document.getElementById("receptionScheduleModalLabel").textContent =
    "Thêm lịch làm việc lễ tân";

  receptionScheduleModal.show();
}

/**
 * Hiển thị modal sửa lịch làm việc lễ tân
 */
function showEditReceptionScheduleModal(scheduleId, receptionId) {
  receptionIsEditMode = true;

  document.getElementById("receptionScheduleModalLabel").textContent =
    "Sửa lịch làm việc lễ tân";

  const selectGroup = document.getElementById("receptionSelectGroup");
  const select = document.getElementById("receptionSelect");
  selectGroup.style.display = "none";
  select.removeAttribute("required");

  document.getElementById("receptionTrangThaiGroup").style.display = "block";

  receptionScheduleModal.show();

  fetch(
    `?action=admin_get_reception_schedule_info&schedule_id=${scheduleId}&reception_id=${receptionId}`
  )
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        const schedule = result.data;

        document.getElementById("receptionScheduleId").value = schedule.id;
        document.getElementById("receptionIdHidden").value = receptionId;
        select.value = receptionId;
        document.getElementById("receptionThuTrongTuan").value =
          schedule.thu_trong_tuan;
        document.getElementById("receptionGioBatDau").value =
          schedule.gio_bat_dau;
        document.getElementById("receptionGioKetThuc").value =
          schedule.gio_ket_thuc;
        document.getElementById("receptionLoaiCa").value = schedule.loai_ca;
        document.getElementById("receptionGhiChu").value =
          schedule.ghi_chu || "";
        document.getElementById("receptionTrangThai").value =
          schedule.trang_thai || "active";
      } else {
        showReceptionNotification(
          "error",
          result.message || "Không thể tải thông tin lịch làm việc!"
        );
        receptionScheduleModal.hide();
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showReceptionNotification(
        "error",
        "Có lỗi xảy ra khi tải thông tin lịch làm việc!"
      );
      receptionScheduleModal.hide();
    });
}

/**
 * Lưu lịch làm việc lễ tân (thêm mới hoặc cập nhật)
 */
function saveReceptionSchedule() {
  const form = document.getElementById("receptionScheduleForm");
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  let receptionId;
  const selectGroup = document.getElementById("receptionSelectGroup");
  if (selectGroup.style.display !== "none") {
    receptionId = document.getElementById("receptionSelect").value;
  } else {
    receptionId = document.getElementById("receptionIdHidden").value;
  }

  const formData = {
    schedule_id: document.getElementById("receptionScheduleId").value,
    reception_id: receptionId,
    thu_trong_tuan: document.getElementById("receptionThuTrongTuan").value,
    gio_bat_dau: document.getElementById("receptionGioBatDau").value,
    gio_ket_thuc: document.getElementById("receptionGioKetThuc").value,
    loai_ca: document.getElementById("receptionLoaiCa").value,
    ghi_chu: document.getElementById("receptionGhiChu").value,
    trang_thai: document.getElementById("receptionTrangThai").value || "active",
  };

  const action = receptionIsEditMode
    ? "admin_update_reception_schedule"
    : "admin_add_reception_schedule";

  const saveBtn = event.target;
  const originalText = saveBtn.innerHTML;
  saveBtn.disabled = true;
  saveBtn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"></span>Đang lưu...';

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
        showReceptionNotification("success", result.message);
        receptionScheduleModal.hide();
        setTimeout(() => {
          location.reload();
        }, 800);
      } else {
        showReceptionNotification(
          "error",
          result.message || "Có lỗi xảy ra khi lưu lịch làm việc!"
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      saveBtn.disabled = false;
      saveBtn.innerHTML = originalText;
      showReceptionNotification(
        "error",
        "Có lỗi xảy ra khi lưu lịch làm việc!"
      );
    });
}

/**
 * Xóa lịch làm việc lễ tân
 */
function deleteReceptionSchedule(scheduleId, receptionId) {
  if (
    !confirm(
      "Bạn có chắc chắn muốn xóa lịch làm việc này?\nHành động này không thể hoàn tác!"
    )
  ) {
    return;
  }

  fetch("?action=admin_delete_reception_schedule", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      schedule_id: scheduleId,
      reception_id: receptionId,
    }),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        showReceptionNotification("success", result.message);
        setTimeout(() => {
          location.reload();
        }, 800);
      } else {
        showReceptionNotification(
          "error",
          result.message || "Có lỗi xảy ra khi xóa lịch làm việc!"
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showReceptionNotification(
        "error",
        "Có lỗi xảy ra khi xóa lịch làm việc!"
      );
    });
}

/**
 * Làm mới dữ liệu
 */
function refreshReceptionData() {
  location.reload();
}

/**
 * Thông báo nhỏ trên màn hình
 */
function showReceptionNotification(type, message) {
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

  document.body.appendChild(alertDiv);

  setTimeout(() => {
    alertDiv.remove();
  }, 4000);
}
