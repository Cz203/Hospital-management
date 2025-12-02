const express = require("express");
const http = require("http");
const socketIo = require("socket.io");
const path = require("path");
// Load .env từ project root để dùng chung SOCKET_* với PHP
require("dotenv").config({ path: path.join(__dirname, "..", ".env") });
const jwt = require("jsonwebtoken");
const Ajv = require("ajv");

// Validator instance
const ajv = new Ajv();

const app = express();
//Socket.IO cần server HTTP để nâng cấp connection sang WebSocket.
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
  // mặc định local
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

// Read auth secrets from env. Assumptions:
// - SOCKET_API_KEY: API key used by backend PHP to call /emit (fallback to API_KEY)
// - SOCKET_JWT_SECRET: secret used to sign/verify JWT tokens for socket handshake (fallback to API_SECRET)
const SOCKET_API_KEY = process.env.SOCKET_API_KEY || process.env.API_KEY || "";
const SOCKET_JWT_SECRET =
  process.env.SOCKET_JWT_SECRET || process.env.API_SECRET || "";

// Simple AJV schemas for /emit payload validation. Keep minimal but explicit.
const baseSchema = {
  type: "object",
  properties: {
    event: { type: "string" },
    data: { type: "object" },
  },
  required: ["event", "data"],
  additionalProperties: false,
};

const schemas = {
  new_appointment: {
    type: "object",
    properties: {
      doctorId: { type: ["string", "number"] },
      patientName: { type: "string" },
      appointmentDate: { type: "string" },
      appointmentTime: { type: "string" },
    },
    required: ["doctorId", "patientName"],
    additionalProperties: true,
  },
  patient_booking_confirmation: {
    type: "object",
    properties: { patientId: { type: ["string", "number"] } },
    required: ["patientId"],
    additionalProperties: true,
  },
  appointment_cancelled_by_doctor: {
    type: "object",
    properties: { patientId: { type: ["string", "number"] } },
    required: ["patientId"],
    additionalProperties: true,
  },
  appointment_cancelled_by_patient: {
    type: "object",
    properties: { doctorId: { type: ["string", "number"] } },
    required: ["doctorId"],
    additionalProperties: true,
  },
  appointment_status_changed: {
    type: "object",
    properties: { patientId: { type: ["string", "number"] } },
    required: ["patientId"],
    additionalProperties: true,
  },
  appointment_update: {
    type: "object",
    properties: { doctorId: { type: ["string", "number"] } },
    required: ["doctorId"],
    additionalProperties: true,
  },
};

// Precompile validators
const baseValidate = ajv.compile(baseSchema);
const validators = {};
Object.keys(schemas).forEach((k) => (validators[k] = ajv.compile(schemas[k])));

// Helper to validate /emit payloads
function validateEmitPayload(body) {
  if (!baseValidate(body)) return { valid: false, errors: baseValidate.errors };
  const ev = body.event;
  if (validators[ev]) {
    const ok = validators[ev](body.data);
    return { valid: ok, errors: validators[ev].errors };
  }
  // If we don't have a specific schema, accept base validation only
  return { valid: true };
}

// HTTP endpoint to receive notifications from PHP
//HTTP mở cổng (endpoint) nhận thông báo từ php
// HTTP endpoint to receive notifications from PHP
// This endpoint requires an API key (Authorization: Bearer <key>) or X-API-KEY header.
app.post("/emit", (req, res) => {
  try {
    // Simple auth for callers (PHP backend should send API key)
    const authHeader = req.get("authorization") || "";
    const apiKeyHeader = req.get("x-api-key") || "";
    const token =
      (authHeader.startsWith("Bearer ") && authHeader.slice(7)) ||
      apiKeyHeader ||
      "";
    if (!SOCKET_API_KEY || !token || token !== SOCKET_API_KEY) {
      return res.status(401).json({ error: "Unauthorized: invalid API key" });
    }

    const { event, data } = req.body || {};
    const validation = validateEmitPayload(req.body || {});
    if (!validation.valid) {
      return res
        .status(400)
        .json({ error: "Invalid payload", details: validation.errors });
    }

    if (!event || !data) {
      return res.status(400).json({ error: "Missing event or data" });
    }

    // Handle different event types (same behaviour as before)
    if (event === "new_appointment" && data.doctorId) {
      const doctorSocket = connectedUsers.doctors.get(String(data.doctorId));
      if (doctorSocket) {
        doctorSocket.emit("appointment_notification", {
          type: "new_appointment",
          message: `Bệnh nhân ${data.patientName} đã đặt lịch hẹn vào ${data.appointmentDate} lúc ${data.appointmentTime}`,
          ...data,
        });
        console.log(`Notification sent to doctor ${data.doctorId}`);
      }
    } else if (event === "patient_booking_confirmation" && data.patientId) {
      const patientSocket = connectedUsers.patients.get(String(data.patientId));
      if (patientSocket) {
        patientSocket.emit("patient_booking_confirmation", data);
        console.log(`Confirmation sent to patient ${data.patientId}`);
      }
    } else if (event === "appointment_cancelled_by_doctor" && data.patientId) {
      const patientSocket = connectedUsers.patients.get(String(data.patientId));
      if (patientSocket) {
        patientSocket.emit("appointment_cancelled_by_doctor", data);
        console.log(
          `Doctor cancellation notification sent to patient ${data.patientId}`
        );
      }
    } else if (event === "appointment_cancelled_by_patient" && data.doctorId) {
      const doctorSocket = connectedUsers.doctors.get(String(data.doctorId));
      if (doctorSocket) {
        doctorSocket.emit("appointment_cancelled_by_patient", data);
        console.log(
          `Patient cancellation notification sent to doctor ${data.doctorId}`
        );
      }
    } else if (event === "appointment_status_changed" && data.patientId) {
      const patientSocket = connectedUsers.patients.get(String(data.patientId));
      if (patientSocket) {
        patientSocket.emit("appointment_status_changed", data);
        console.log(
          `Status change notification sent to patient ${data.patientId}`
        );
      }
    } else if (event === "appointment_update" && data && data.doctorId) {
      io.to(`doctor_${data.doctorId}`).emit("appointment_update", data);
      io.to("all_doctors").emit("appointment_update", data);
      io.to("all_receptionists").emit("appointment_update", data);
      console.log(`Appointment update sent to doctor ${data.doctorId}`, data);
    } else {
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

// Socket.IO connection
// Middleware: bắt buộc JWT trong handshake: socket.handshake.auth.token
io.use((socket, next) => {
  try {
    const token =
      socket.handshake && socket.handshake.auth
        ? socket.handshake.auth.token
        : null;

    if (!token) {
      // Không có token => từ chối kết nối
      return next(new Error("Unauthorized: missing token"));
    }
    if (!SOCKET_JWT_SECRET) {
      console.warn("No SOCKET_JWT_SECRET configured; cannot verify JWT");
      return next(new Error("Server misconfigured"));
    }

    try {
      const payload = jwt.verify(token, SOCKET_JWT_SECRET);
      // expected claims: sub/userId/id, role, name
      socket.userId = String(payload.sub || payload.userId || payload.id || "");
      socket.role = payload.role || payload.r || "";
      socket.userName = payload.name || payload.username || "";
      socket.authFromJwt = true;
      return next();
    } catch (err) {
      console.warn(
        "JWT verification failed for socket handshake:",
        err && err.message
      );
      // fail the connection explicitly
      return next(new Error("Unauthorized"));
    }
  } catch (e) {
    return next(e);
  }
});

io.on("connection", (socket) => {
  console.log("User connected:", socket.id);

  // register helper - move a socket into connectedUsers maps + rooms
  function registerSocket(s, userId, role, userName) {
    if (!userId || !role) return;
    const idStr = String(userId);
    s.userId = idStr;
    s.role = role;
    s.userName = userName || s.userName || "Unknown User";
    switch (role) {
      case "doctor":
        connectedUsers.doctors.set(idStr, s);
        s.join(`doctor_${idStr}`);
        s.join("all_doctors");
        console.log(`Doctor ${s.userName} (ID: ${idStr}) connected`);
        break;
      case "patient":
        connectedUsers.patients.set(idStr, s);
        s.join(`patient_${idStr}`);
        console.log(`Patient ${s.userName} (ID: ${idStr}) connected`);
        break;
      case "admin":
        connectedUsers.admins.set(idStr, s);
        s.join(`admin_${idStr}`);
        s.join("all_admins");
        console.log(`Admin ${s.userName} (ID: ${idStr}) connected`);
        break;
      case "letan":
        connectedUsers.receptionists.set(idStr, s);
        s.join(`reception_${idStr}`);
        s.join("all_receptionists");
        console.log(`Receptionist ${s.userName} (ID: ${idStr}) connected`);
        break;
      default:
        // unknown role - do nothing
        break;
    }
    s.emit("authenticated", {
      message: "Successfully authenticated",
      userId: idStr,
      role: role,
    });
  }

  // If handshake provided JWT and we already set socket.userId/role, register immediately
  if (socket.userId && socket.role) {
    registerSocket(socket, socket.userId, socket.role, socket.userName);
  }

  // Backwards-compatible authenticate event: still accept it if client can't provide JWT at handshake
  socket.on("authenticate", (data) => {
    const { userId, role, userName } = data || {};
    if (!userId || !role) {
      socket.emit("error", { message: "Missing user information" });
      return;
    }
    registerSocket(socket, userId, role, userName || "Unknown User");
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
