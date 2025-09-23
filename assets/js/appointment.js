// Appointment Booking JavaScript
document.addEventListener("DOMContentLoaded", function () {
  const doctorIdInput = document.getElementById("doctorId");
  const appointmentDate = document.getElementById("appointmentDate");
  const appointmentTime = document.getElementById("appointmentTime");
  const selectedDateDisplay = document.getElementById("selectedDateDisplay");
  const selectedTimeDisplay = document.getElementById("selectedTimeDisplay");
  const submitBtn = document.getElementById("submitBtn");
  const loadingOverlay = document.getElementById("loadingOverlay");

  // Quick booking elements
  const quickBookingCard = document.getElementById("quickBookingCard");
  const quickDays = document.getElementById("quickDays");
  const qbPrev = document.getElementById("qbPrev");
  const qbNext = document.getElementById("qbNext");
  const quickTimeSlots = document.getElementById("quickTimeSlots");
  const slotCountLabel = document.getElementById("slotCountLabel");
  let qbStartOffset = 0; // days from today for first day shown
  const QB_WINDOW = 7;

  // Initialize form
  function initializeForm() {
    if (doctorIdInput.value && quickBookingCard) {
      quickBookingCard.style.display = "block";
      renderQuickDays();
    }
    updateSubmitButton();
  }

  function renderQuickDays() {
    if (!quickDays) return;
    quickDays.innerHTML = "";
    const today = new Date();
    for (let i = 0; i < QB_WINDOW; i++) {
      const d = new Date(today);
      d.setDate(today.getDate() + qbStartOffset + i);
      const yyyy = d.getFullYear();
      const mm = String(d.getMonth() + 1).padStart(2, "0");
      const dd = String(d.getDate()).padStart(2, "0");
      const ymd = `${yyyy}-${mm}-${dd}`;
      const dow = d.toLocaleDateString("vi-VN", { weekday: "short" });
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "btn btn-outline-primary text-start";
      btn.style.minWidth = "120px";
      btn.innerHTML = `<div class="small text-muted">${dow}</div><div class="fw-bold">${dd}-${mm}</div><div class="small" data-count-for="${ymd}">...</div>`;
      btn.dataset.date = ymd;
      btn.addEventListener("click", () => {
        setSelectedDate(ymd);
        loadTimeSlots(ymd);
        highlightSelectedDay(ymd);
      });
      quickDays.appendChild(btn);
      fetchSlotsCount(ymd);
    }
    qbPrev.onclick = () => {
      qbStartOffset = Math.max(0, qbStartOffset - QB_WINDOW);
      renderQuickDays();
    };
    qbNext.onclick = () => {
      qbStartOffset += QB_WINDOW;
      renderQuickDays();
    };
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
      if (b.dataset.date === ymd) b.classList.add("active");
      else b.classList.remove("active");
    });
  }

  function fetchSlotsCount(ymd) {
    const doctorId = doctorIdInput.value;
    if (!doctorId) return;
    fetch("./get_available_time_slots", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `doctor_id=${doctorId}&date=${ymd}`,
    })
      .then((r) => r.json())
      .then((data) => {
        const count =
          data && data.success && Array.isArray(data.data)
            ? data.data.length
            : 0;
        const small = quickDays.querySelector(`[data-count-for="${ymd}"]`);
        if (small) small.textContent = `${count} khung giờ`;
        const btn = Array.from(quickDays.querySelectorAll("button")).find(
          (b) => b.dataset.date === ymd
        );
        if (btn) {
          if (count === 0) {
            btn.classList.add("d-none");
            btn.dataset.has = "0";
          } else {
            btn.classList.remove("d-none");
            btn.dataset.has = "1";
          }
        }
      })
      .catch(() => {
        const small = quickDays.querySelector(`[data-count-for="${ymd}"]`);
        if (small) small.textContent = `0 khung giờ`;
        const btn = Array.from(quickDays.querySelectorAll("button")).find(
          (b) => b.dataset.date === ymd
        );
        if (btn) {
          btn.classList.add("d-none");
          btn.dataset.has = "0";
        }
      });
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
    slotCountLabel.textContent = `(${timeSlots.length} khung giờ)`;
    if (timeSlots.length === 0) {
      const span = document.createElement("span");
      span.className = "text-muted";
      span.textContent = "Không có khung giờ";
      quickTimeSlots.appendChild(span);
      return;
    }
    const order = ["Ca sáng", "Ca chiều", "Ca tối"];
    const icons = {
      "Ca sáng": "fa-sun text-warning",
      "Ca chiều": "fa-cloud-sun text-info",
      "Ca tối": "fa-moon text-primary",
    };
    const groups = new Map();
    timeSlots.forEach((s) => {
      const key = s.shift || "Khác";
      if (!groups.has(key)) groups.set(key, []);
      groups.get(key).push(s);
    });
    const keys = Array.from(groups.keys()).sort(
      (a, b) => order.indexOf(a) - order.indexOf(b)
    );
    keys.forEach((k) => {
      const header = document.createElement("div");
      header.className = "d-flex align-items-center w-100 mt-2 mb-1";
      const i = document.createElement("i");
      i.className = `fas ${icons[k] || "fa-clock text-secondary"} me-2`;
      const strong = document.createElement("strong");
      strong.textContent = k;
      header.appendChild(i);
      header.appendChild(strong);
      quickTimeSlots.appendChild(header);

      const container = document.createElement("div");
      container.className = "d-flex flex-wrap";
      container.style.gap = "10px";
      groups.get(k).forEach((slot) => {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "btn btn-outline-secondary";
        btn.textContent = slot.display;
        if (slot.disabled) {
          btn.classList.add("disabled");
          btn.setAttribute("aria-disabled", "true");
          btn.style.pointerEvents = "none";
          btn.style.opacity = "0.6";
          btn.title = "Khung giờ không khả dụng";
        }
        btn.addEventListener("click", () => {
          setSelectedDate(date);
          setSelectedTime(`${slot.display}`, slot.time);
          // hover/active effect
          quickTimeSlots
            .querySelectorAll("button")
            .forEach((b) => b.classList.remove("active"));
          btn.classList.add("active");
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
    if (loadingOverlay) loadingOverlay.style.display = "flex";
  }
  function hideLoading() {
    if (loadingOverlay) loadingOverlay.style.display = "none";
  }
  function showError(message) {
    console.error(message);
  }

  // Init
  initializeForm();
});
