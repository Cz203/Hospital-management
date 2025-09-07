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
    // Page detection helpers
    this.isOnPatientAppointments = this.isOnPatientAppointments.bind(this);
    // Notifications bridge
    this.pushBell = (msg, type) => {
      try {
        if (typeof window.addNotification === "function") {
          window.addNotification(msg, type || "info");
        } else {
          // Queue until layout initializes notifications
          window.__notifQueue = window.__notifQueue || [];
          window.__notifQueue.push({ message: msg, type: type || "info" });
        }
      } catch (_) {
        window.__notifQueue = window.__notifQueue || [];
        window.__notifQueue.push({ message: msg, type: type || "info" });
      }
    };
  }

  // Initialize socket connection
  init(userId, userRole, userName) {
    this.userId = userId;
    this.userRole = userRole;
    this.userName = userName;

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
      this.socket = io(serverUrl, {
        transports: ["websocket", "polling"],
        timeout: 20000,
        forceNew: true,
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

    // Authentication
    this.socket.on("authenticated", (data) => {
      console.log("Socket authenticated:", data);
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
        userName: this.userName,
      });
    }
  }

  // Handle appointment notifications
  handleAppointmentNotification(data) {
    console.log("New appointment notification:", data);

    // Push to bell only (no toast)
    // Bell dropdown for doctors
    this.pushBell(data.message, "info");

    // Update appointment count if on dashboard
    this.updateAppointmentCount();

    // Auto-refresh appointments if on appointment management page
    if (window.location.pathname.includes("appointment_management")) {
      console.log("Auto-refreshing appointments...");
      setTimeout(() => {
        this.refreshAppointments();
      }, 1000);
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
    // Push to bell only (no toast)
    this.pushBell("Có cập nhật lịch hẹn mới", "info");

    // Refresh appointments if on relevant page
    if (window.location.pathname.includes("appointment")) {
      setTimeout(() => {
        this.refreshAppointments();
      }, 1000);
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
                    Admin: ${stats.connectedAdmins}
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
