// Socket.IO Client for Hospital Management System
class SocketManager {
  constructor() {
    this.socket = null;
    this.isConnected = false;
    this.reconnectAttempts = 0;
    this.maxReconnectAttempts = 5;
    this.reconnectInterval = 3000;
    this.userId = null;
    this.userRole = null;
    this.userName = null;
    this._queueReloadTimer = null;
    this._tokenRefreshTimer = null; // Timer để refresh token
    // Page detection helpers
    this.isOnPatientAppointments = this.isOnPatientAppointments.bind(this);
    this.isOnDoctorExamination = this.isOnDoctorExamination.bind(this);
    this.isOnReceptionQueue = this.isOnReceptionQueue.bind(this);
    // Notifications bridge: display only if UI is ready; do not store client-side
    this.pushBell = (msg, type) => {
      try {
        if (typeof window.addNotification === "function") {
          window.addNotification(msg, type || "info");
        }
      } catch (_) {}
    };
  }

  // Initialize socket connection
  init(userId, userRole) {
    this.userId = userId;
    this.userRole = userRole;
    this.userName = null;

    // Load Socket.IO client library if not already loaded
    if (typeof io === "undefined") {
      this.loadSocketIOLibrary().then(() => {
        this.connect();
      });
    } else {
      this.connect();
    }
  }

  // Detect patient appointments page reliably
  isOnPatientAppointments() {
    if (
      document &&
      document.body &&
      document.body.dataset &&
      document.body.dataset.page === "patient_appointments"
    ) {
      return true;
    }
    const href =
      window && window.location && window.location.href
        ? window.location.href
        : "";
    return href.includes("patient_appointments");
  }

  // Detect doctor examination page reliably (pretty URL or action param)
  isOnDoctorExamination() {
    try {
      if (
        document &&
        document.body &&
        document.body.dataset &&
        document.body.dataset.page === "doctor_examination"
      ) {
        return true;
      }
    } catch (_) {}
    try {
      const href = window?.location?.href || "";
      const path = window?.location?.pathname || "";
      if (path.includes("doctor_examination")) return true;
      if (href.includes("action=doctor_examination")) return true;
    } catch (_) {}
    return false;
  }

  // Detect reception queue page
  isOnReceptionQueue() {
    try {
      const path = window?.location?.pathname || "";
      if (path.includes("reception_queue")) return true;
    } catch (_) {}
    try {
      const table = document.getElementById("queue-table");
      const sel = document.getElementById("f-doctor");
      if (table && sel && typeof window.loadQueue === "function") return true;
    } catch (_) {}
    return false;
  }

  // Strong reload strategy for patient appointments page
  forceReloadPatientAppointments() {
    try {
      // Dispatch custom event first (for partial refresh listeners)
      const refreshEvent = new CustomEvent("appointmentRefresh");
      document.dispatchEvent(refreshEvent);

      // Try soft reload
      setTimeout(() => {
        // Fallback to hard reload
        if (this.isOnPatientAppointments()) {
          const current = window.location.href;
          window.location.assign(current);
        }
      }, 500);
    } catch (e) {
      // Final fallback
      setTimeout(() => {
        window.location.href = window.location.href;
      }, 700);
    }
  }

  // Load Socket.IO client library dynamically
  loadSocketIOLibrary() {
    return new Promise((resolve, reject) => {
      if (typeof io !== "undefined") {
        resolve();
        return;
      }

      const script = document.createElement("script");
      script.src = "https://cdn.socket.io/4.7.2/socket.io.min.js";
      script.onload = resolve;
      script.onerror = reject;
      document.head.appendChild(script);
    });
  }

  // Connect to socket server
  connect() {
    try {
      const metaMode = document.querySelector('meta[name="socket-mode"]');
      const metaProd = document.querySelector('meta[name="socket-server-url"]');
      const metaDev = document.querySelector('meta[name="socket-dev-url"]');
      const mode = metaMode
        ? (metaMode.getAttribute("content") || "auto").trim()
        : "auto";
      const prodUrl = metaProd
        ? (metaProd.getAttribute("content") || "").trim()
        : "";
      const devUrl = metaDev
        ? (metaDev.getAttribute("content") || "").trim()
        : "";
      // URL override via query ?socket=dev|prod
      const params = new URLSearchParams(window.location.search);
      const override = params.get("socket");
      let pickMode = mode;
      if (override === "dev" || override === "prod") pickMode = override;
      let serverUrl = "";
      if (pickMode === "dev") serverUrl = devUrl || "http://localhost:3001";
      else if (pickMode === "prod")
        serverUrl = prodUrl || devUrl || "http://localhost:3001";
      else {
        // auto: nếu chạy https thì ưu tiên prod (https), ngược lại ưu tiên dev
        if (location.protocol === "https:")
          serverUrl = prodUrl || devUrl || "http://localhost:3001";
        else serverUrl = devUrl || prodUrl || "http://localhost:3001";
      }
      console.log("[Socket] Connecting to:", serverUrl);
      // If a JWT for socket auth was provided via meta tag, include it in the handshake auth
      const metaToken = document.querySelector(
        'meta[name="socket-auth-token"]'
      );
      const socketAuth = metaToken
        ? { token: (metaToken.getAttribute("content") || "").trim() }
        : {};
      this.socket = io(serverUrl, {
        transports: ["websocket", "polling"],
        timeout: 20000,
        forceNew: true,
        auth: socketAuth,
      });

      this.setupEventListeners();
      this.authenticate();
    } catch (error) {
      console.error("Socket connection error:", error);
      this.handleReconnect();
    }
  }

  // Setup socket event listeners
  setupEventListeners() {
    // Connection events
    this.socket.on("connect", () => {
      console.log("Socket connected:", this.socket.id);
      this.isConnected = true;
      this.reconnectAttempts = 0;
      this.showConnectionStatus("connected");
      // Bắt đầu schedule refresh token sau khi connect thành công
      this.scheduleTokenRefresh();
    });

    this.socket.on("disconnect", (reason) => {
      console.log("Socket disconnected:", reason);
      this.isConnected = false;
      this.showConnectionStatus("disconnected");

      if (reason === "io server disconnect") {
        // Server disconnected, try to reconnect
        this.handleReconnect();
      }
    });

    this.socket.on("connect_error", (error) => {
      try {
        console.error(
          "[Socket] connect_error:",
          error && (error.message || error.toString())
        );
      } catch (_) {
        console.error("[Socket] connect_error");
      }
      this.isConnected = false;
      this.showConnectionStatus("error");
      this.handleReconnect();
    });

    // Authentication (no console output in production)
    this.socket.on("authenticated", (data) => {
      // intentionally no-op
    });

    this.socket.on("error", (error) => {
      console.error("Socket error:", error);
      this.showNotification("error", "Lỗi kết nối: " + error.message);
    });

    // Appointment notifications
    this.socket.on("appointment_notification", (data) => {
      this.handleAppointmentNotification(data);
    });

    this.socket.on("patient_booking_confirmation", (data) => {
      this.handlePatientBookingConfirmation(data);
    });

    this.socket.on("appointment_cancelled_by_doctor", (data) => {
      this.handleAppointmentCancelledByDoctor(data);
    });

    this.socket.on("appointment_cancelled_by_patient", (data) => {
      this.handleAppointmentCancelledByPatient(data);
    });

    this.socket.on("appointment_status_changed", (data) => {
      this.handleAppointmentStatusChanged(data);
    });

    this.socket.on("appointment_update", (data) => {
      this.handleAppointmentUpdate(data);
    });

    this.socket.on("appointment_status_change", (data) => {
      this.handleAppointmentStatusChange(data);
    });

    // Queue update for reception queue page
    this.socket.on("queue_update", (data) => {
      this.handleQueueUpdate(data);
    });

    // Statistics
    this.socket.on("appointment_stats", (data) => {
      this.handleAppointmentStats(data);
    });

    // Ping/Pong for connection health
    this.socket.on("pong", () => {
      // Connection is alive
    });
  }

  // Authenticate with server
  authenticate() {
    if (this.socket && this.userId && this.userRole) {
      this.socket.emit("authenticate", {
        userId: this.userId,
        role: this.userRole,
      });
    }
  }

  // Handle appointment notifications
  handleAppointmentNotification(data) {
    console.log("New appointment notification:", data);

    // Only doctors should see this bell message
    if (this.userRole === "doctor") {
      var who = data && data.patientName ? data.patientName : "Bệnh nhân";
      var msg =
        data && data.message ? data.message : "Bạn vừa có một lịch mới: " + who;
      this.pushBell(msg, "info");
    }

    // Update appointment count if on dashboard
    this.updateAppointmentCount();

    // Auto-refresh doctor views
    if (window.location.pathname.includes("appointment_management")) {
      console.log("Auto-refreshing appointments...");
      setTimeout(() => {
        this.refreshAppointments();
      }, 1000);
    }
    if (this.isOnDoctorExamination()) {
      try {
        if (typeof refreshExamination === "function") {
          setTimeout(() => refreshExamination(), 600);
        } else {
          setTimeout(() => window.location.reload(), 800);
        }
      } catch (_) {
        setTimeout(() => window.location.reload(), 800);
      }
    }

    // Reception: refresh queue if relevant
    if (this.userRole === "letan" && this.isOnReceptionQueue()) {
      this.refreshReceptionQueueIfRelevant(data);
    }
  }

  // Handle patient booking confirmation
  handlePatientBookingConfirmation(data) {
    console.log("Patient booking confirmation:", data);

    // Push to bell only (no toast)
    // Bell dropdown for patients
    this.pushBell(data.message, "success");

    // Auto-refresh appointments if on patient appointments page
    if (this.isOnPatientAppointments()) {
      console.log("Auto-refreshing patient appointments...");
      this.forceReloadPatientAppointments();
    }
  }

  // Handle appointment updates
  handleAppointmentUpdate(data) {
    console.log("Appointment update:", data);
    // Only doctors should see this bell message
    if (this.userRole === "doctor") {
      var who = data && data.patientName ? data.patientName : "";
      var msg =
        data && data.message
          ? data.message
          : "Bạn vừa có một lịch mới" + (who ? ": " + who : "");
      this.pushBell(msg, "info");
    }

    // Refresh appointments if on relevant page
    if (window.location.pathname.includes("appointment")) {
      setTimeout(() => {
        this.refreshAppointments();
      }, 1000);
    }
    // Refresh doctor examination page if open
    if (this.isOnDoctorExamination()) {
      try {
        if (typeof refreshExamination === "function") {
          setTimeout(() => refreshExamination(), 600);
        } else {
          setTimeout(() => window.location.reload(), 800);
        }
      } catch (_) {
        setTimeout(() => window.location.reload(), 800);
      }
    }

    // Reception: refresh queue if relevant
    if (this.userRole === "letan" && this.isOnReceptionQueue()) {
      this.refreshReceptionQueueIfRelevant(data);
    }
  }

  // Handle appointment status changes
  handleAppointmentStatusChange(data) {
    console.log("Appointment status change:", data);

    // Push to bell only (no toast)
    // Bell dropdown for patients
    this.pushBell(data.message, "info");

    // Auto-refresh appointments if on relevant page
    if (window.location.pathname.includes("appointment")) {
      setTimeout(() => {
        this.refreshAppointments();
      }, 1000);
    }
    // Also refresh doctor examination if open
    if (window.location.pathname.includes("doctor_examination")) {
      try {
        if (typeof refreshExamination === "function") {
          setTimeout(() => refreshExamination(), 600);
        } else {
          setTimeout(() => window.location.reload(), 800);
        }
      } catch (_) {
        setTimeout(() => window.location.reload(), 800);
      }
    }

    // Reception: refresh queue if relevant
    if (this.userRole === "letan" && this.isOnReceptionQueue()) {
      this.refreshReceptionQueueIfRelevant(data);
    }
  }

  // Handle appointment cancelled by doctor
  handleAppointmentCancelledByDoctor(data) {
    console.log("Appointment cancelled by doctor:", data);

    // Push to bell only (no toast)
    // Bell dropdown for patients
    this.pushBell(data.message, "warning");

    // Auto-refresh appointments if on patient appointments page
    if (this.isOnPatientAppointments()) {
      console.log("Auto-refreshing patient appointments...");
      this.forceReloadPatientAppointments();
    }
  }

  // Handle appointment cancelled by patient
  handleAppointmentCancelledByPatient(data) {
    console.log("Appointment cancelled by patient:", data);

    // Push to bell only (no toast)
    // Bell dropdown for doctors
    this.pushBell(data.message, "warning");

    // Auto-refresh appointments if on appointment management page
    if (window.location.pathname.includes("appointment_management")) {
      console.log("Auto-refreshing doctor appointments...");
      setTimeout(() => {
        this.refreshAppointments();
      }, 1000);
    }
  }

  // Handle appointment status changed
  handleAppointmentStatusChanged(data) {
    console.log("Appointment status changed:", data);

    // Push to bell only (no toast)
    // Bell dropdown for patients
    this.pushBell(data.message, "info");

    // Auto-refresh: patient page -> force reload; doctor page -> table refresh
    if (this.isOnPatientAppointments()) {
      console.log(
        "Auto-refreshing patient appointments after status change..."
      );
      this.forceReloadPatientAppointments();
      return;
    }
    if (window.location.pathname.includes("appointment_management")) {
      setTimeout(() => {
        this.refreshAppointments();
      }, 600);
    }
  }

  // Handle appointment statistics
  handleAppointmentStats(data) {
    console.log("Appointment stats:", data);
    // Update stats display if available
    this.updateStatsDisplay(data);
  }

  // Handle queue update for reception queue page
  handleQueueUpdate(data) {
    console.log("Queue update:", data);

    // Only handle if user is reception
    if (this.userRole !== "letan") {
      return;
    }

    // Check if we're on the queue page
    if (!this.isOnReceptionQueue()) {
      return;
    }

    // Trigger custom event that queue.php can listen to
    // This allows the queue page to handle its own refresh logic
    const queueUpdateEvent = new CustomEvent("queueUpdate", {
      detail: data,
    });
    window.dispatchEvent(queueUpdateEvent);
  }

  // Show browser notification
  showBrowserNotification(title, message, icon) {
    if ("Notification" in window && Notification.permission === "granted") {
      new Notification(title, {
        body: message,
        icon: icon || "/assets/img/notification-icon.png",
        badge: "/assets/img/badge-icon.png",
        tag: "appointment-notification",
        requireInteraction: true,
      });
    }
  }

  // Request notification permission
  requestNotificationPermission() {
    if ("Notification" in window && Notification.permission === "default") {
      Notification.requestPermission().then((permission) => {
        if (permission === "granted") {
          console.log("Notification permission granted");
        }
      });
    }
  }

  // Show in-app notification
  showNotification(type, message) {
    // Create notification element
    const notification = document.createElement("div");
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;

    notification.innerHTML = `
            <i class="fas fa-bell me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
      if (notification.parentNode) {
        notification.remove();
      }
    }, 5000);
  }

  // Show connection status
  showConnectionStatus(status) {
    const statusElement = document.getElementById("socket-status");
    if (statusElement) {
      statusElement.className = `badge bg-${
        status === "connected" ? "success" : "danger"
      }`;
      statusElement.textContent =
        status === "connected" ? "Đã kết nối" : "Mất kết nối";
    }
  }

  // Update appointment count
  updateAppointmentCount() {
    // This will be implemented based on your dashboard structure
    const countElement = document.querySelector(".appointment-count");
    if (countElement) {
      // Increment count or fetch new count
      const currentCount = parseInt(countElement.textContent) || 0;
      countElement.textContent = currentCount + 1;
    }
  }

  // Update stats display
  updateStatsDisplay(stats) {
    // Update connection stats if available
    const statsElement = document.getElementById("connection-stats");
    if (statsElement) {
      statsElement.innerHTML = `
                <small class="text-muted">
                    Bác sĩ: ${stats.connectedDoctors} | 
                    Bệnh nhân: ${stats.connectedPatients} | 
                    Admin: ${stats.connectedAdmins} |
                    Lễ tân: ${stats.connectedReceptionists}
                </small>
            `;
    }
  }

  // Play notification sound
  playNotificationSound() {
    try {
      const audio = new Audio("/assets/sounds/notification.mp3");
      audio.volume = 0.3;
      audio
        .play()
        .catch((e) => console.log("Could not play notification sound:", e));
    } catch (error) {
      console.log("Notification sound not available");
    }
  }

  // Refresh appointments
  refreshAppointments() {
    if (window.location.pathname.includes("appointment_management")) {
      console.log("Refreshing appointments...");

      // Try to find refresh button first
      const refreshBtn = document.querySelector(
        '[onclick="refreshAppointments()"]'
      );
      if (refreshBtn) {
        console.log("Found refresh button, clicking...");
        refreshBtn.click();
        return;
      }

      // Try to find any refresh button
      const anyRefreshBtn = document.querySelector(
        'button[title*="refresh"], button[title*="Refresh"], .btn-refresh'
      );
      if (anyRefreshBtn) {
        console.log("Found alternative refresh button, clicking...");
        anyRefreshBtn.click();
        return;
      }

      // Try to trigger a custom event for appointment refresh
      const refreshEvent = new CustomEvent("appointmentRefresh");
      document.dispatchEvent(refreshEvent);
      console.log("Dispatched appointmentRefresh event");

      // Fallback: reload page after a short delay
      setTimeout(() => {
        console.log("Fallback: reloading page...");
        window.location.reload();
      }, 2000);
    }
  }

  // Reception queue refresh (debounced) when event is relevant to selected doctor
  refreshReceptionQueueIfRelevant(data) {
    try {
      var sel = document.getElementById("f-doctor");
      if (!sel || typeof window.loadQueue !== "function") return;
      var currentDoctor = String(sel.value || "");
      var eventDoctor =
        data && data.doctorId != null ? String(data.doctorId) : "";
      if (
        currentDoctor === "0" ||
        (eventDoctor && eventDoctor === currentDoctor)
      ) {
        clearTimeout(this._queueReloadTimer);
        this._queueReloadTimer = setTimeout(function () {
          window.loadQueue();
        }, 250);
      }
    } catch (_) {}
  }

  // Handle reconnection
  handleReconnect() {
    if (this.reconnectAttempts < this.maxReconnectAttempts) {
      this.reconnectAttempts++;
      console.log(
        `Attempting to reconnect... (${this.reconnectAttempts}/${this.maxReconnectAttempts})`
      );

      setTimeout(() => {
        this.connect();
      }, this.reconnectInterval);
    } else {
      console.log("Max reconnection attempts reached");
      this.showNotification("error", "Không thể kết nối đến server realtime");
    }
  }

  // Send ping to keep connection alive
  ping() {
    if (this.socket && this.isConnected) {
      this.socket.emit("ping");
    }
  }

  // Disconnect socket
  disconnect() {
    if (this.socket) {
      this.socket.disconnect();
      this.socket = null;
      this.isConnected = false;
    }
    // Clear token refresh timer
    if (this._tokenRefreshTimer) {
      clearTimeout(this._tokenRefreshTimer);
      this._tokenRefreshTimer = null;
    }
  }

  // Decode JWT token để lấy thông tin exp (expiration time)
  decodeJWT(token) {
    try {
      if (!token) return null;
      const parts = token.split(".");
      if (parts.length !== 3) return null;
      const payload = JSON.parse(atob(parts[1]));
      return payload;
    } catch (e) {
      console.error("[Socket] Error decoding JWT:", e);
      return null;
    }
  }

  // Refresh JWT token từ server
  async refreshToken() {
    try {
      const response = await fetch("./?action=refresh_socket_token", {
        method: "GET",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
        },
      });

      if (!response.ok) {
        throw new Error("Failed to refresh token");
      }

      const data = await response.json();
      if (data.success && data.token) {
        // Cập nhật meta tag với token mới
        let metaToken = document.querySelector(
          'meta[name="socket-auth-token"]'
        );
        if (!metaToken) {
          metaToken = document.createElement("meta");
          metaToken.setAttribute("name", "socket-auth-token");
          document.head.appendChild(metaToken);
        }
        metaToken.setAttribute("content", data.token);

        console.log("[Socket] Token refreshed successfully");

        // Reconnect với token mới nếu đang connected
        if (this.isConnected && this.socket) {
          this.socket.disconnect();
          setTimeout(() => {
            this.connect();
          }, 500);
        }

        // Schedule refresh tiếp theo
        this.scheduleTokenRefresh();
        return true;
      } else {
        throw new Error(data.message || "Failed to refresh token");
      }
    } catch (error) {
      console.error("[Socket] Error refreshing token:", error);
      return false;
    }
  }

  // Schedule token refresh trước khi hết hạn (5 phút trước khi expire)
  scheduleTokenRefresh() {
    // Clear timer cũ nếu có
    if (this._tokenRefreshTimer) {
      clearTimeout(this._tokenRefreshTimer);
      this._tokenRefreshTimer = null;
    }

    try {
      const metaToken = document.querySelector(
        'meta[name="socket-auth-token"]'
      );
      if (!metaToken) {
        console.log("[Socket] No token found, skipping refresh schedule");
        return;
      }

      const token = metaToken.getAttribute("content");
      if (!token) {
        console.log("[Socket] Empty token, skipping refresh schedule");
        return;
      }

      const payload = this.decodeJWT(token);
      if (!payload || !payload.exp) {
        console.log(
          "[Socket] Cannot decode token or no exp, skipping refresh schedule"
        );
        return;
      }

      const now = Math.floor(Date.now() / 1000);
      const exp = payload.exp;
      const timeUntilExpiry = exp - now;

      // Refresh 5 phút trước khi hết hạn (300 giây)
      const refreshTime = Math.max(0, (timeUntilExpiry - 300) * 1000);

      if (refreshTime <= 0) {
        // Token sắp hết hạn hoặc đã hết hạn, refresh ngay
        console.log("[Socket] Token expiring soon, refreshing immediately");
        this.refreshToken();
        return;
      }

      console.log(
        `[Socket] Token refresh scheduled in ${Math.floor(
          refreshTime / 1000
        )} seconds`
      );

      this._tokenRefreshTimer = setTimeout(() => {
        console.log("[Socket] Scheduled token refresh triggered");
        this.refreshToken();
      }, refreshTime);
    } catch (error) {
      console.error("[Socket] Error scheduling token refresh:", error);
    }
  }
}

// Global socket manager instance
window.socketManager = new SocketManager();

// Auto-initialize for all users
document.addEventListener("DOMContentLoaded", function () {
  // Check if user is logged in
  const userId = document.querySelector('meta[name="user-id"]')?.content;
  const userRole = document.querySelector('meta[name="user-role"]')?.content;
  const userName = document.querySelector('meta[name="user-name"]')?.content;

  if (userId && userRole) {
    // Request notification permission for all users
    window.socketManager.requestNotificationPermission();

    // Initialize socket connection for all users
    window.socketManager.init(userId, userRole, userName);

    // Send ping every 10 minutes only if user is active
    setInterval(() => {
      // Only ping if user is active (page visible, mouse moved recently, etc.)
      if (!document.hidden && window.socketManager.isConnected) {
        window.socketManager.ping();
      }
    }, 600000); // 10 minutes
  }
});
