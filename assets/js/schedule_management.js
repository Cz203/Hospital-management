document.addEventListener("DOMContentLoaded", function () {
  // Auto-fill time based on shift type (chỉ còn Ca sáng, Ca chiều)
  document.getElementById("loai_ca").addEventListener("change", function () {
    const startTime = document.getElementById("gio_bat_dau");
    const endTime = document.getElementById("gio_ket_thuc");

    switch (this.value) {
      case "Ca sáng":
        // Ca sáng: 07:00 - 11:30
        startTime.value = "07:00";
        endTime.value = "11:30";
        break;
      case "Ca chiều":
        // Ca chiều: 13:00 - 21:00
        startTime.value = "13:00";
        endTime.value = "21:00";
        break;
      // Đã bỏ ca tối
      // Bỏ ca đêm
    }
  });

  // Edit schedule functionality (date-specific)
  document.querySelectorAll(".edit-schedule").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();

      // Quy định: Chỉ cho chỉnh sửa vào Thứ 2 và CHỈ áp dụng cho NGÀY thuộc TUẦN SAU
      const now = new Date();
      // Dùng timezone VN từ server-side mặc định, JS client lấy local; nếu client khác timezone có thể lệch.
      // Đơn giản: coi theo client, 0=Sun..6=Sat
      const dow = now.getDay();
      const isMon = dow === 1;
      if (!isMon) {
        showToast("Chỉ được thay đổi ca trực vào Thứ 2.");
        return;
      }
      const dateYmd = this.getAttribute("data-date");
      if (!dateYmd) {
        showToast("Thiếu ngày áp dụng.");
        return;
      }
      const dateObj = new Date(dateYmd + "T00:00:00");
      dateObj.setHours(0, 0, 0, 0);
      const mondayThisWeek = new Date(now);
      const dowNorm = dow === 0 ? 7 : dow; // 1..7
      mondayThisWeek.setDate(now.getDate() - (dowNorm - 1));
      mondayThisWeek.setHours(0, 0, 0, 0);
      const nextWeekStart = new Date(mondayThisWeek);
      nextWeekStart.setDate(mondayThisWeek.getDate() + 7);
      nextWeekStart.setHours(0, 0, 0, 0);
      const nextWeekEnd = new Date(nextWeekStart);
      nextWeekEnd.setDate(nextWeekStart.getDate() + 6);
      nextWeekEnd.setHours(23, 59, 59, 999);
      if (dateObj < nextWeekStart || dateObj > nextWeekEnd) {
        showToast("Chỉ được chỉnh sửa ca trực cho các ngày thuộc TUẦN SAU.");
        return;
      }
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
            showToast("Không thể tải thông tin ca trực: " + data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showToast("Có lỗi xảy ra khi tải thông tin ca trực");
        });
    });
  });

  // Prompt reason when cancelling a specific date
  document
    .querySelectorAll('form[action="./doctor_cancel_schedule_for_date"] button')
    .forEach((btn) => {
      btn.addEventListener("click", function (e) {
        const now = new Date();
        const dow = now.getDay();
        const isMonCancel = dow === 1;
        if (!isMonCancel) {
          e.preventDefault();
          showToast("Chỉ được xóa ca trực vào Thứ 2.");
          return false;
        }
        // Chỉ cho phép HỦY ngày thuộc TUẦN SAU
        const form = this.closest("form");
        const dateInput = form.querySelector('input[name="date"]');
        const dateYmd = dateInput ? dateInput.value : "";
        if (!dateYmd) {
          e.preventDefault();
          showToast("Thiếu ngày áp dụng.");
          return false;
        }
        const dateObj = new Date(dateYmd + "T00:00:00");
        dateObj.setHours(0, 0, 0, 0);
        const dowNorm = dow === 0 ? 7 : dow;
        const mondayThisWeek = new Date(now);
        mondayThisWeek.setDate(now.getDate() - (dowNorm - 1));
        mondayThisWeek.setHours(0, 0, 0, 0);
        const nextWeekStart = new Date(mondayThisWeek);
        nextWeekStart.setDate(mondayThisWeek.getDate() + 7);
        nextWeekStart.setHours(0, 0, 0, 0);
        const nextWeekEnd = new Date(nextWeekStart);
        nextWeekEnd.setDate(nextWeekStart.getDate() + 6);
        nextWeekEnd.setHours(23, 59, 59, 999);
        if (dateObj < nextWeekStart || dateObj > nextWeekEnd) {
          e.preventDefault();
          showToast("Chỉ được hủy ca trực cho các ngày thuộc TUẦN SAU.");
          return false;
        }
        // Mở modal nhập lý do
        e.preventDefault();
        const modalEl = document.getElementById("cancelReasonModal");
        const confirmBtn = document.getElementById("confirmCancelBtn");
        const reasonInputEl = document.getElementById("cancelReasonInput");
        const dateLabel = document.getElementById("cancelDateLabel");
        dateLabel.textContent = dateYmd;
        reasonInputEl.value = "";
        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
        confirmBtn.onclick = () => {
          const val = reasonInputEl.value.trim();
          if (!val) {
            showToast("Vui lòng nhập lý do hợp lệ.");
            return;
          }
          let hiddenReason = form.querySelector('input[name="reason"]');
          if (!hiddenReason) {
            hiddenReason = document.createElement("input");
            hiddenReason.type = "hidden";
            hiddenReason.name = "reason";
            form.appendChild(hiddenReason);
          }
          hiddenReason.value = val;
          bsModal.hide();
          form.submit();
        };
      });
    });

  // Auto-fill time for edit modal (chỉ còn Ca sáng, Ca chiều)
  document
    .getElementById("edit_loai_ca")
    .addEventListener("change", function () {
      const startTime = document.getElementById("edit_gio_bat_dau");
      const endTime = document.getElementById("edit_gio_ket_thuc");

      switch (this.value) {
        case "Ca sáng":
          // Ca sáng: 07:00 - 11:30
          startTime.value = "07:00";
          endTime.value = "11:30";
          break;
        case "Ca chiều":
          // Ca chiều: 13:00 - 21:00
          startTime.value = "13:00";
          endTime.value = "21:00";
          break;
        // Đã bỏ ca tối
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

  function showToast(message, type = "danger") {
    // Hiển thị thông báo inline trên giao diện thay vì toast/popup
    const bar = document.getElementById("messageBar");
    if (!bar) return;
    bar.style.display = "block";
    const cls =
      type === "success"
        ? "alert alert-success"
        : type === "warning"
        ? "alert alert-warning"
        : "alert alert-danger";
    bar.className = cls;
    bar.textContent = message;
    // Tự ẩn sau 3 giây
    setTimeout(() => {
      if (bar) {
        bar.style.display = "none";
        bar.className = "";
        bar.textContent = "";
      }
    }, 3000);
  }
});
