document.addEventListener("DOMContentLoaded", function () {
  // Hiển thị ngày giờ real-time
  function updateDateTime() {
    const now = new Date();
    const options = {
      year: "numeric",
      month: "2-digit",
      day: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: false,
    };
    document.getElementById("current-datetime").textContent =
      now.toLocaleString("vi-VN", options);
  }

  // Cập nhật ngày giờ mỗi giây
  updateDateTime();
  setInterval(updateDateTime, 1000);
});

// Filter by doctor
function filterByDoctor() {
  const doctorId = document.getElementById("doctorFilter").value;
  const dayCards = document.querySelectorAll(".day-card");
  const filterStatus = document.getElementById("filterStatus");
  const selectedOption =
    document.getElementById("doctorFilter").selectedOptions[0];

  // Cập nhật trạng thái filter
  if (doctorId === "") {
    filterStatus.textContent = "Hiển thị tất cả bác sĩ";
  } else {
    filterStatus.textContent = `Đang lọc: ${selectedOption.textContent}`;
  }

  dayCards.forEach((card) => {
    if (doctorId === "") {
      // Hiển thị tất cả
      card.style.display = "block";
      const doctorSchedules = card.querySelectorAll(".doctor-schedule");
      doctorSchedules.forEach((schedule) => {
        schedule.style.display = "block";
      });
    } else {
      const doctorSchedules = card.querySelectorAll(".doctor-schedule");
      let hasDoctor = false;

      doctorSchedules.forEach((schedule) => {
        const doctorDataId = schedule.getAttribute("data-doctor-id");
        if (doctorDataId === doctorId) {
          schedule.style.display = "block";
          hasDoctor = true;
        } else {
          schedule.style.display = "none";
        }
      });

      // Nếu không có bác sĩ nào trong ngày này, ẩn cả card
      if (hasDoctor) {
        card.style.display = "block";
      } else {
        // Kiểm tra xem có empty state không
        const emptyState = card.querySelector(".empty-state");
        if (emptyState) {
          card.style.display = "none";
        } else {
          card.style.display = "block";
        }
      }
    }
  });
}

// Refresh data
function refreshData() {
  location.reload();
}
