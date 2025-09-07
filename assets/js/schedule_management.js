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

  // Auto-fill time based on shift type
  document.getElementById("loai_ca").addEventListener("change", function () {
    const startTime = document.getElementById("gio_bat_dau");
    const endTime = document.getElementById("gio_ket_thuc");

    switch (this.value) {
      case "Ca sáng":
        startTime.value = "06:00";
        endTime.value = "12:00";
        break;
      case "Ca chiều":
        startTime.value = "12:00";
        endTime.value = "18:00";
        break;
      case "Ca tối":
        startTime.value = "18:00";
        endTime.value = "23:59";
        break;
      // Bỏ ca đêm
    }
  });

  // Edit schedule functionality
  document.querySelectorAll(".edit-schedule").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      const scheduleId = this.getAttribute("data-schedule-id");

      // Fetch schedule data
      fetch("./doctor_get_schedule_info", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "schedule_id=" + scheduleId,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            const schedule = data.data;
            document.getElementById("edit_schedule_id").value = schedule.id;
            document.getElementById("edit_thu_trong_tuan").value =
              schedule.thu_trong_tuan;
            document.getElementById("edit_loai_ca").value = schedule.loai_ca;
            document.getElementById("edit_gio_bat_dau").value =
              schedule.gio_bat_dau;
            document.getElementById("edit_gio_ket_thuc").value =
              schedule.gio_ket_thuc;
            document.getElementById("edit_trang_thai").value =
              schedule.trang_thai;
            document.getElementById("edit_ghi_chu").value =
              schedule.ghi_chu || "";

            // Show modal
            new bootstrap.Modal(
              document.getElementById("editScheduleModal")
            ).show();
          } else {
            alert("Không thể tải thông tin ca trực: " + data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Có lỗi xảy ra khi tải thông tin ca trực");
        });
    });
  });

  // Auto-fill time for edit modal
  document
    .getElementById("edit_loai_ca")
    .addEventListener("change", function () {
      const startTime = document.getElementById("edit_gio_bat_dau");
      const endTime = document.getElementById("edit_gio_ket_thuc");

      switch (this.value) {
        case "Ca sáng":
          startTime.value = "06:00";
          endTime.value = "12:00";
          break;
        case "Ca chiều":
          startTime.value = "12:00";
          endTime.value = "18:00";
          break;
        case "Ca tối":
          startTime.value = "18:00";
          endTime.value = "23:59";
          break;
        // Bỏ ca đêm
      }
    });
});
