// Appointment Booking JavaScript
document.addEventListener("DOMContentLoaded", function () {
  const doctorIdInput = document.getElementById("doctorId");
  const appointmentDate = document.getElementById("appointmentDate");
  const appointmentTime = document.getElementById("appointmentTime");
  const selectedDateDisplay = document.getElementById("selectedDateDisplay");
  const selectedTimeDisplay = document.getElementById("selectedTimeDisplay");
  const submitBtn = document.getElementById("submitBtn");
  // Initialize loading overlay - create it immediately and append to body
  function initLoadingOverlay() {
    let overlay = document.getElementById("loadingOverlay");

    if (!overlay) {
      // Create overlay if it doesn't exist
      overlay = document.createElement("div");
      overlay.id = "loadingOverlay";
      overlay.className = "loading-overlay";
      overlay.style.display = "none";
      overlay.innerHTML = `
        <div class="loading-content">
          <div class="spinner-border" role="status" aria-label="Loading"></div>
          <p>Đang xử lý yêu cầu...</p>
        </div>
      `;
    }

    // Always ensure overlay is in body (top level)
    if (overlay.parentElement !== document.body) {
      document.body.appendChild(overlay);
    }

    return overlay;
  }

  // Initialize overlay immediately
  const loadingOverlay = initLoadingOverlay();

  // Quick booking elements
  const quickBookingCard = document.getElementById("quickBookingCard");
  const quickDays = document.getElementById("quickDays");
  const qbPrev = document.getElementById("qbPrev");
  const qbNext = document.getElementById("qbNext");
  const quickTimeSlots = document.getElementById("quickTimeSlots");
  const slotCountLabel = document.getElementById("slotCountLabel");
  // Không dùng tuần nữa, chỉ hiển thị dải ngày liên tục từ hôm nay
  // Giảm số ngày để tránh gọi quá nhiều request -> nhanh hơn
  const MAX_QUICK_DAYS = 14; // số ngày tối đa hiển thị từ hôm nay

  // Initialize form
  function initializeForm() {
    if (doctorIdInput.value) {
      const quickBookingSection = document.getElementById(
        "quickBookingSection"
      );
      if (quickBookingSection) {
        quickBookingSection.style.display = "block";
      }
      renderQuickDays();
    }
    updateSubmitButton();
  }

  async function renderQuickDays() {
    if (!quickDays) return;
    // Setup layout: 1 hàng trượt ngang
    quickDays.innerHTML =
      "<div class='text-center text-muted py-3'><i class='icofont-spinner icofont-spin mr-2'></i>Đang tải...</div>";
    quickDays.style.display = "flex";
    quickDays.style.overflowX = "auto";
    quickDays.style.whiteSpace = "nowrap";
    quickDays.style.gap = "8px";
    quickDays.style.paddingBottom = "6px";

    // Bắt đầu từ hôm nay, hiển thị liên tục MAX_QUICK_DAYS ngày
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Collect all days to check (from today forward)
    const daysToCheck = [];
    for (let i = 0; i < MAX_QUICK_DAYS; i++) {
      const d = new Date(today);
      d.setDate(today.getDate() + i);
      const yyyy = d.getFullYear();
      const mm = String(d.getMonth() + 1).padStart(2, "0");
      const dd = String(d.getDate()).padStart(2, "0");
      const ymd = `${yyyy}-${mm}-${dd}`;
      daysToCheck.push({ date: d, ymd: ymd });
    }

    // Fetch slots count for all days in parallel
    const slotsPromises = daysToCheck.map((day) =>
      fetchSlotsCount(day.ymd).then((count) => ({ ...day, count }))
    );

    const daysWithSlots = await Promise.all(slotsPromises);

    // Clear loading message
    quickDays.innerHTML = "";

    // Only render days with available slots (count > 0)
    daysWithSlots.forEach(({ date, ymd, count }) => {
      if (count > 0) {
        const d = date;
        const dow = d.toLocaleDateString("vi-VN", { weekday: "short" });
        const month = d.toLocaleDateString("vi-VN", { month: "short" });
        const dd = String(d.getDate()).padStart(2, "0");

        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "quick-day-btn";
        btn.style.borderRadius = "12px";
        btn.style.display = "inline-flex";
        btn.style.flexDirection = "column";
        btn.style.alignItems = "center";
        btn.style.justifyContent = "center";
        btn.style.minWidth = "80px";
        btn.style.marginRight = "8px";
        btn.innerHTML = `
          <span class="day-name">${dow}</span>
          <span class="day-number">${dd}</span>
          <span class="day-month">${month}</span>
          <small style="display: block; margin-top: 4px; font-size: 0.7rem; opacity: 0.7;">${count} giờ</small>
        `;
        btn.dataset.date = ymd;
        btn.dataset.has = "1";

        // Check if today
        const todayCheck = new Date();
        todayCheck.setHours(0, 0, 0, 0);
        const compareDate = new Date(d);
        compareDate.setHours(0, 0, 0, 0);
        if (compareDate.getTime() === todayCheck.getTime()) {
          btn.classList.add("today");
        }

        btn.addEventListener("click", () => {
          setSelectedDate(ymd);
          loadTimeSlots(ymd);
          highlightSelectedDay(ymd);
        });

        quickDays.appendChild(btn);
      }
    });

    // Không còn dùng điều hướng tuần, ẩn/disable nút prev/next nếu có
    if (qbPrev) {
      qbPrev.disabled = true;
      qbPrev.style.display = "none";
      qbPrev.onclick = null;
    }
    if (qbNext) {
      qbNext.disabled = true;
      qbNext.style.display = "none";
      qbNext.onclick = null;
    }
  }

  function setSelectedDate(ymd) {
    appointmentDate.value = ymd;
    if (selectedDateDisplay) selectedDateDisplay.value = formatVn(ymd);
  }

  function setSelectedTime(display, time) {
    appointmentTime.value = time;
    if (selectedTimeDisplay) selectedTimeDisplay.value = display;
    updateSubmitButton();
  }

  function highlightSelectedDay(ymd) {
    quickDays.querySelectorAll("button").forEach((b) => {
      if (b.dataset.date === ymd) b.classList.add("selected");
      else b.classList.remove("selected");
    });
  }

  function fetchSlotsCount(ymd) {
    const doctorId = doctorIdInput.value;
    if (!doctorId) return Promise.resolve(0);
    return fetch("./get_available_time_slots", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `doctor_id=${doctorId}&date=${ymd}`,
    })
      .then((r) => r.json())
      .then((data) => {
        const slots =
          data && data.success && Array.isArray(data.data) ? data.data : [];
        // Chỉ đếm các slot không bị disabled
        const availableSlots = slots.filter((slot) => !slot.disabled);
        return availableSlots.length;
      })
      .catch(() => 0);
  }

  function loadTimeSlots(date) {
    const doctorId = doctorIdInput.value;
    if (!date || !doctorId) {
      quickTimeSlots.innerHTML = "";
      slotCountLabel.textContent = "(0 khung giờ)";
      return;
    }
    showLoading();
    fetch("./get_available_time_slots", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `doctor_id=${doctorId}&date=${date}`,
    })
      .then((response) => response.json())
      .then((data) => {
        const slots =
          data && data.success && Array.isArray(data.data) ? data.data : [];
        renderQuickTimeSlots(slots, date);
      })
      .catch(() => {
        renderQuickTimeSlots([], date);
      })
      .finally(() => {
        hideLoading();
      });
  }

  function renderQuickTimeSlots(timeSlots, date) {
    quickTimeSlots.innerHTML = "";

    // Lọc bỏ các slot bị disabled (chỉ hiển thị các slot có thể đặt)
    const availableSlots = timeSlots.filter((slot) => !slot.disabled);

    slotCountLabel.textContent = `(${availableSlots.length} khung giờ)`;
    if (availableSlots.length === 0) {
      const span = document.createElement("span");
      span.className = "text-muted";
      span.textContent = "Không có khung giờ khả dụng";
      quickTimeSlots.appendChild(span);
      return;
    }

    const order = ["Ca sáng", "Ca chiều", "Ca tối"];
    const icons = {
      "Ca sáng": "icofont-sun text-warning",
      "Ca chiều": "icofont-sun-alt text-info",
      "Ca tối": "icofont-moon text-primary",
    };
    const groups = new Map();
    availableSlots.forEach((s) => {
      const key = s.shift || "Khác";
      if (!groups.has(key)) groups.set(key, []);
      groups.get(key).push(s);
    });
    const keys = Array.from(groups.keys()).sort(
      (a, b) => order.indexOf(a) - order.indexOf(b)
    );

    keys.forEach((k) => {
      const header = document.createElement("div");
      header.className = "shift-header";
      header.style.cssText =
        "display: flex; align-items: center; margin-bottom: 0.75rem; margin-top: 1rem; font-weight: 600; color: #223a66;";
      const i = document.createElement("i");
      i.className = `${icons[k] || "icofont-clock-time text-secondary"} mr-2`;
      i.style.fontSize = "1.1rem";
      const strong = document.createElement("strong");
      strong.textContent = k;
      header.appendChild(i);
      header.appendChild(strong);
      quickTimeSlots.appendChild(header);

      // Layout theo cột dọc (grid columns)
      const container = document.createElement("div");
      container.className = "time-slots-columns";
      container.style.cssText =
        "display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px; margin-bottom: 1rem;";

      groups.get(k).forEach((slot) => {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "time-slot-btn";
        btn.textContent = slot.display;
        btn.style.cssText =
          "padding: 14px 16px; border: 2px solid #e9ecef; background: #ffffff; border-radius: 12px; text-align: center; cursor: pointer; transition: all 0.3s ease; font-weight: 500; color: #223a66; font-size: 0.95rem; white-space: nowrap;";

        btn.addEventListener("mouseenter", () => {
          if (!btn.classList.contains("selected")) {
            btn.style.borderColor = "#223a66";
            btn.style.background = "#f8f9fa";
            btn.style.transform = "translateY(-2px)";
            btn.style.boxShadow = "0 4px 10px rgba(0, 0, 0, 0.1)";
          }
        });

        btn.addEventListener("mouseleave", () => {
          if (!btn.classList.contains("selected")) {
            btn.style.borderColor = "#e9ecef";
            btn.style.background = "#ffffff";
            btn.style.transform = "translateY(0)";
            btn.style.boxShadow = "none";
          }
        });

        btn.addEventListener("click", () => {
          setSelectedDate(date);
          setSelectedTime(`${slot.display}`, slot.time);
          // Remove selected from all buttons
          quickTimeSlots.querySelectorAll(".time-slot-btn").forEach((b) => {
            b.classList.remove("selected");
            b.style.background = "#ffffff";
            b.style.borderColor = "#e9ecef";
            b.style.color = "#223a66";
          });
          // Add selected to clicked button
          btn.classList.add("selected");
          btn.style.background = "#223a66";
          btn.style.borderColor = "#223a66";
          btn.style.color = "#ffffff";

          // Show selected info display
          const selectedInfoDisplay = document.getElementById(
            "selectedInfoDisplay"
          );
          if (selectedInfoDisplay) {
            selectedInfoDisplay.style.display = "block";
          }
        });

        container.appendChild(btn);
      });
      quickTimeSlots.appendChild(container);
    });
  }

  function formatVn(ymd) {
    const d = new Date(ymd);
    const dd = String(d.getDate()).padStart(2, "0");
    const mm = String(d.getMonth() + 1).padStart(2, "0");
    const yyyy = d.getFullYear();
    const dow = d.toLocaleDateString("vi-VN", { weekday: "short" });
    return `${dow}, ${dd}-${mm}-${yyyy}`;
  }

  // Submit overlay
  document
    .getElementById("appointmentForm")
    .addEventListener("submit", function () {
      if (!submitBtn.disabled) showLoading();
    });

  function updateSubmitButton() {
    submitBtn.disabled = !(
      doctorIdInput.value &&
      appointmentDate.value &&
      appointmentTime.value
    );
  }

  function showLoading() {
    // Ensure overlay exists and is in body
    let overlay = document.getElementById("loadingOverlay");
    if (!overlay) {
      overlay = initLoadingOverlay();
    }

    // Ensure overlay is in body (top level) and is the last child
    if (overlay.parentElement !== document.body) {
      document.body.appendChild(overlay);
    } else {
      // Move to end of body to ensure it's on top
      document.body.appendChild(overlay);
    }

    // Show overlay with inline styles to ensure it's always on top and centered
    overlay.style.cssText = `
      display: flex !important;
      position: fixed !important;
      z-index: 9999999 !important;
      top: 0 !important;
      left: 0 !important;
      right: 0 !important;
      bottom: 0 !important;
      width: 100vw !important;
      height: 100vh !important;
      min-width: 100vw !important;
      min-height: 100vh !important;
      background: rgba(0, 0, 0, 0.8) !important;
      backdrop-filter: blur(8px) !important;
      -webkit-backdrop-filter: blur(8px) !important;
      margin: 0 !important;
      padding: 0 !important;
      border: none !important;
      align-items: center !important;
      justify-content: center !important;
    `;
  }
  function hideLoading() {
    const overlay = document.getElementById("loadingOverlay");
    if (overlay) {
      overlay.style.display = "none";
    }
  }
  function showError(message) {
    console.error(message);
  }

  // Init
  initializeForm();
});
