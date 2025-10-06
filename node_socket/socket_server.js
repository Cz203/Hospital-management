const express = require("express");
const http = require("http");
const socketIo = require("socket.io");
const path = require("path");

const app = express();
const server = http.createServer(app);

// Build CORS origins from ENV (comma-separated), with sensible fallbacks
function getAllowedOrigins() {
  const raw =
    process.env.SOCKET_ALLOWED_ORIGINS || process.env.CORS_ORIGINS || "";
  const parsed = raw
    .split(",")
    .map((s) => s && s.trim())
    .filter(Boolean);
  if (parsed.length) return parsed;
  // defaults for local dev + typical PHP base path
  return [
    "http://localhost:3000",
    "http://localhost/clinic-management",
    "http://127.0.0.1/clinic-management",
  ];
}

const io = socketIo(server, {
  cors: {
    origin: getAllowedOrigins(),
    methods: ["GET", "POST"],
    credentials: true,
  },
});

// Middleware
app.use(express.json());

// HTTP endpoint to receive notifications from PHP
app.post("/emit", (req, res) => {
  try {
    const { event, data } = req.body;

    if (!event || !data) {
      return res.status(400).json({ error: "Missing event or data" });
    }

    // Handle different event types
    if (event === "new_appointment" && data.doctorId) {
      // Send to specific doctor
      const doctorSocket = connectedUsers.doctors.get(data.doctorId.toString());
      if (doctorSocket) {
        doctorSocket.emit("appointment_notification", {
          type: "new_appointment",
          message: `Bệnh nhân ${data.patientName} đã đặt lịch hẹn vào ${data.appointmentDate} lúc ${data.appointmentTime}`,
          ...data,
        });
        console.log(`Notification sent to doctor ${data.doctorId}`);
      }
    } else if (event === "patient_booking_confirmation" && data.patientId) {
      // Send to specific patient
      const patientSocket = connectedUsers.patients.get(
        data.patientId.toString()
      );
      if (patientSocket) {
        patientSocket.emit("patient_booking_confirmation", data);
        console.log(`Confirmation sent to patient ${data.patientId}`);
      }
    } else if (event === "appointment_cancelled_by_doctor" && data.patientId) {
      // Send to specific patient when doctor cancels
      const patientSocket = connectedUsers.patients.get(
        data.patientId.toString()
      );
      if (patientSocket) {
        patientSocket.emit("appointment_cancelled_by_doctor", data);
        console.log(
          `Doctor cancellation notification sent to patient ${data.patientId}`
        );
      }
    } else if (event === "appointment_cancelled_by_patient" && data.doctorId) {
      // Send to specific doctor when patient cancels
      const doctorSocket = connectedUsers.doctors.get(data.doctorId.toString());
      if (doctorSocket) {
        doctorSocket.emit("appointment_cancelled_by_patient", data);
        console.log(
          `Patient cancellation notification sent to doctor ${data.doctorId}`
        );
      }
    } else if (event === "appointment_status_changed" && data.patientId) {
      // Send to specific patient when status changes
      const patientSocket = connectedUsers.patients.get(
        data.patientId.toString()
      );
      if (patientSocket) {
        patientSocket.emit("appointment_status_changed", data);
        console.log(
          `Status change notification sent to patient ${data.patientId}`
        );
      }
    } else if (event === "appointment_update" && data && data.doctorId) {
      // Target only the intended doctor and doctors group
      io.to(`doctor_${data.doctorId}`).emit("appointment_update", data);
      io.to("all_doctors").emit("appointment_update", data);
      console.log(`Appointment update sent to doctor ${data.doctorId}`, data);
    } else {
      // Emit to all connected clients (fallback)
      io.emit(event, data);
    }

    console.log(`Emitted event: ${event}`, data);
    res.json({ success: true, message: "Event emitted successfully" });
  } catch (error) {
    console.error("Error emitting event:", error);
    res.status(500).json({ error: "Internal server error" });
  }
});

// Store connected users by role and ID
const connectedUsers = {
  doctors: new Map(),
  patients: new Map(),
  admins: new Map(),
  receptionists: new Map(), // lễ tân
};

// Socket.IO connection handling
io.on("connection", (socket) => {
  console.log("User connected:", socket.id);

  // Handle user authentication and role assignment
  socket.on("authenticate", (data) => {
    const { userId, role, userName } = data;

    if (!userId || !role) {
      socket.emit("error", { message: "Missing user information" });
      return;
    }

    // Store user information
    socket.userId = userId;
    socket.role = role;
    socket.userName = userName || "Unknown User";

    // Add to appropriate role group
    switch (role) {
      case "doctor":
        connectedUsers.doctors.set(userId, socket);
        socket.join(`doctor_${userId}`);
        socket.join("all_doctors");
        console.log(`Doctor ${userName} (ID: ${userId}) connected`);
        break;
      case "patient":
        connectedUsers.patients.set(userId, socket);
        socket.join(`patient_${userId}`);
        console.log(`Patient ${userName} (ID: ${userId}) connected`);
        break;
      case "admin":
        connectedUsers.admins.set(userId, socket);
        socket.join(`admin_${userId}`);
        socket.join("all_admins");
        console.log(`Admin ${userName} (ID: ${userId}) connected`);
        break;
      case "letan":
        connectedUsers.receptionists.set(userId, socket);
        socket.join(`reception_${userId}`);
        socket.join("all_receptionists");
        console.log(`Receptionist ${userName} (ID: ${userId}) connected`);
        break;
    }

    socket.emit("authenticated", {
      message: "Successfully authenticated",
      userId: userId,
      role: role,
    });
  });

  // Handle new appointment notification
  socket.on("new_appointment", (data) => {
    const { doctorId, patientName, appointmentTime, appointmentDate } = data;

    // Send notification to specific doctor
    const doctorSocket = connectedUsers.doctors.get(doctorId.toString());
    if (doctorSocket) {
      doctorSocket.emit("appointment_notification", {
        type: "new_appointment",
        message: `Bệnh nhân ${patientName} đã đặt lịch hẹn vào ${appointmentDate} lúc ${appointmentTime}`,
        patientName: patientName,
        appointmentTime: appointmentTime,
        appointmentDate: appointmentDate,
        timestamp: new Date().toISOString(),
      });
      console.log(
        `Notification sent to doctor ${doctorId} about new appointment from ${patientName}`
      );
    }

    // Also broadcast to all doctors (optional)
    io.to("all_doctors").emit("appointment_update", {
      type: "new_appointment",
      doctorId: doctorId,
      patientName: patientName,
      appointmentTime: appointmentTime,
      appointmentDate: appointmentDate,
    });
  });

  // Handle appointment status update
  socket.on("appointment_status_update", (data) => {
    const { appointmentId, newStatus, doctorId, patientId } = data;

    // Notify patient about status change
    if (patientId) {
      const patientSocket = connectedUsers.patients.get(patientId.toString());
      if (patientSocket) {
        patientSocket.emit("appointment_status_change", {
          appointmentId: appointmentId,
          newStatus: newStatus,
          message: `Lịch hẹn của bạn đã được cập nhật thành: ${newStatus}`,
          timestamp: new Date().toISOString(),
        });
      }
    }

    // Notify doctor about the update
    if (doctorId) {
      const doctorSocket = connectedUsers.doctors.get(doctorId.toString());
      if (doctorSocket) {
        doctorSocket.emit("appointment_updated", {
          appointmentId: appointmentId,
          newStatus: newStatus,
          message: `Lịch hẹn đã được cập nhật thành: ${newStatus}`,
          timestamp: new Date().toISOString(),
        });
      }
    }
  });

  // Handle appointment cancellation
  socket.on("appointment_cancelled", (data) => {
    const { doctorId, patientName, appointmentTime, appointmentDate } = data;

    const doctorSocket = connectedUsers.doctors.get(doctorId.toString());
    if (doctorSocket) {
      doctorSocket.emit("appointment_notification", {
        type: "cancelled",
        message: `Bệnh nhân ${patientName} đã hủy lịch hẹn vào ${appointmentDate} lúc ${appointmentTime}`,
        patientName: patientName,
        appointmentTime: appointmentTime,
        appointmentDate: appointmentDate,
        timestamp: new Date().toISOString(),
      });
    }
  });

  // Handle disconnect
  socket.on("disconnect", () => {
    console.log("User disconnected:", socket.id);

    if (socket.userId && socket.role) {
      switch (socket.role) {
        case "doctor":
          connectedUsers.doctors.delete(socket.userId);
          console.log(
            `Doctor ${socket.userName} (ID: ${socket.userId}) disconnected`
          );
          break;
        case "patient":
          connectedUsers.patients.delete(socket.userId);
          console.log(
            `Patient ${socket.userName} (ID: ${socket.userId}) disconnected`
          );
          break;
        case "admin":
          connectedUsers.admins.delete(socket.userId);
          console.log(
            `Admin ${socket.userName} (ID: ${socket.userId}) disconnected`
          );
          break;
        case "letan":
          connectedUsers.receptionists.delete(socket.userId);
          console.log(
            `Receptionist ${socket.userName} (ID: ${socket.userId}) disconnected`
          );
          break;
      }
    }
  });

  // Handle ping/pong for connection health
  socket.on("ping", () => {
    socket.emit("pong");
  });
});

// Broadcast appointment statistics
function broadcastAppointmentStats() {
  const stats = {
    connectedDoctors: connectedUsers.doctors.size,
    connectedPatients: connectedUsers.patients.size,
    connectedAdmins: connectedUsers.admins.size,
    connectedReceptionists: connectedUsers.receptionists.size,
    timestamp: new Date().toISOString(),
  };

  io.emit("appointment_stats", stats);
}

// Send stats every 5 minutes (to allow Render free tier to sleep)
// Only send stats if there are connected users
setInterval(() => {
  const totalUsers =
    connectedUsers.doctors.size +
    connectedUsers.patients.size +
    connectedUsers.admins.size;
  if (totalUsers > 0) {
    broadcastAppointmentStats();
  }
}, 300000); // 5 minutes instead of 30 seconds

// Health check endpoint for Render
app.get("/health", (req, res) => {
  res.json({
    status: "ok",
    timestamp: new Date().toISOString(),
    connectedUsers: {
      doctors: connectedUsers.doctors.size,
      patients: connectedUsers.patients.size,
      admins: connectedUsers.admins.size,
    },
  });
});

// Root endpoint
app.get("/", (req, res) => {
  res.json({
    message: "Clinic Management Socket Server",
    status: "running",
    timestamp: new Date().toISOString(),
  });
});

// Start server
const PORT = process.env.PORT || process.env.SOCKET_PORT || 3001;
server.listen(PORT, () => {
  console.log(`Socket.IO server running on port ${PORT}`);
  console.log(`Allowed origins: ${JSON.stringify(getAllowedOrigins())}`);
  console.log(`Server URL: http://localhost:${PORT}`);
  console.log(`Health check: http://localhost:${PORT}/health`);
});

// Graceful shutdown
process.on("SIGTERM", () => {
  console.log("SIGTERM received, shutting down gracefully");
  server.close(() => {
    console.log("Process terminated");
  });
});
