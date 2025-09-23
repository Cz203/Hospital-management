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

  // Bỏ ràng buộc đăng ký theo ngày trong tháng (client-side)

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

  // Edit schedule functionality (date-specific)
  document.querySelectorAll(".edit-schedule").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();

      // Quy định: Chỉ cho chỉnh sửa vào Thứ 3 hoặc Thứ 4
      const dow = new Date().getDay(); // 0=Sun..6=Sat
      const isTueOrWed = dow === 2 || dow === 3;
      if (!isTueOrWed) {
        alert("Chỉ được thay đổi ca trực vào Thứ 3 hoặc Thứ 4.");
        return;
      }
      const scheduleId = this.getAttribute("data-schedule-id");
      const dateYmd = this.getAttribute("data-date");

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
            // giữ lại date cho submit
            let hiddenDate = document.getElementById("edit_date_specific");
            if (!hiddenDate) {
              hiddenDate = document.createElement("input");
              hiddenDate.type = "hidden";
              hiddenDate.name = "date";
              hiddenDate.id = "edit_date_specific";
              document
                .querySelector("#editScheduleModal form")
                .appendChild(hiddenDate);
            }
            hiddenDate.value = dateYmd || "";
            document.getElementById("edit_loai_ca").value = schedule.loai_ca;
            document.getElementById("edit_gio_bat_dau").value =
              schedule.gio_bat_dau;
            document.getElementById("edit_gio_ket_thuc").value =
              schedule.gio_ket_thuc;
            document.getElementById("edit_trang_thai").value =
              schedule.trang_thai;
            document.getElementById("edit_ghi_chu").value =
              schedule.ghi_chu || "";

            // Bắt buộc ghi lý do khi chỉnh sửa
            const noteEl = document.getElementById("edit_ghi_chu");
            if (noteEl) {
              noteEl.setAttribute("required", "required");
              noteEl.setAttribute(
                "placeholder",
                "Nhập lý do chính đáng cho việc thay đổi ca trực (bắt buộc)"
              );
            }

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

  // Prompt reason when cancelling a specific date
  document
    .querySelectorAll('form[action="./doctor_cancel_schedule_for_date"] button')
    .forEach((btn) => {
      btn.addEventListener("click", function (e) {
        const dow = new Date().getDay();
        const isTueOrWed = dow === 2 || dow === 3;
        if (!isTueOrWed) {
          e.preventDefault();
          alert("Chỉ được xóa ca trực vào Thứ 3 hoặc Thứ 4.");
          return false;
        }
        const form = this.closest("form");
        let reasonInput = form.querySelector('input[name="reason"]');
        if (!reasonInput) {
          reasonInput = document.createElement("input");
          reasonInput.type = "hidden";
          reasonInput.name = "reason";
          form.appendChild(reasonInput);
        }
        const reason = window.prompt("Nhập lý do hủy ca cho ngày này:");
        if (!reason || !reason.trim()) {
          e.preventDefault();
          alert("Vui lòng nhập lý do hợp lệ.");
          return false;
        }
        reasonInput.value = reason.trim();
        return true;
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

  // Week date picker: jump across weeks by setting ?from=YYYY-MM-DD
  const weekPicker = document.getElementById("weekDatePicker");
  if (weekPicker) {
    weekPicker.addEventListener("change", function () {
      const from = this.value;
      if (!from) return;
      // Khi chọn ngày mới thì reset offset tuần về 0 để tránh nhảy sai tuần
      window.location.href = `./doctor_schedule_management?from=${encodeURIComponent(
        from
      )}&w=0`;
    });
  }
});
