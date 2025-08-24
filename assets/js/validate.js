// ========================================
// VALIDATION UTILITIES - REUSABLE FUNCTIONS
// ========================================

// Global validation utilities
const ValidationUtils = {
  // Show alert function
  showAlert: function (message, type) {
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${
      type === "error" ? "danger" : type === "warning" ? "warning" : "success"
    } alert-dismissible fade show`;
    alertDiv.innerHTML = `
            <i class="fas fa-${
              type === "success"
                ? "check-circle"
                : type === "warning"
                ? "exclamation-triangle"
                : "times-circle"
            } me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

    // Insert vào đầu card-body
    const cardBody = document.querySelector(".card-body");
    if (cardBody) {
      cardBody.insertBefore(alertDiv, cardBody.firstChild);
    }

    // Auto remove sau 5 giây
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.remove();
      }
    }, 5000);
  },

  // Format time function
  formatTime: function (seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, "0")}`;
  },

  // Normalize phone number
  normalizePhoneNumber: function (phone) {
    let normalizedPhone = phone;
    // Loại bỏ tất cả ký tự không phải số
    normalizedPhone = normalizedPhone.replace(/\D/g, "");
    // Nếu bắt đầu bằng 0, bỏ số 0 đầu
    if (normalizedPhone.startsWith("0")) {
      normalizedPhone = normalizedPhone.substring(1);
    }
    // Nếu chưa có 84, thêm vào
    if (!normalizedPhone.startsWith("84")) {
      normalizedPhone = "84" + normalizedPhone;
    }
    return normalizedPhone;
  },
};

// ========================================
// PASSWORD VALIDATION MODULE
// ========================================

class PasswordValidator {
  constructor() {
    this.currentPassword = "";
    this.newPassword = "";
    this.confirmPassword = "";
  }

  // Validate current password
  validateCurrentPassword(password) {
    if (!password || password.trim() === "") {
      return { isValid: false, message: "Vui lòng nhập mật khẩu hiện tại" };
    }
    if (password.length < 1) {
      return {
        isValid: false,
        message: "Mật khẩu hiện tại không được để trống",
      };
    }
    return { isValid: true, message: "" };
  }

  // Validate new password
  validateNewPassword(password) {
    if (password.length > 50) {
      return { isValid: false, message: "Mật khẩu không được quá 20 ký tự" };
    }

    if (password.length < 6) {
      return { isValid: false, message: "Mật khẩu phải có ít nhất 6 ký tự" };
    }

    // Kiểm tra kí tự đặc biệt
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

    if (!hasSpecialChar) {
      return {
        isValid: false,
        message: "Mật khẩu phải chứa ít nhất một kí tự đặc biệt (!@#$%^&)",
      };
    }

    return { isValid: true, message: "" };
  }

  // Validate confirm password
  validateConfirmPassword(password, newPassword) {
    if (!password || password.trim() === "") {
      return { isValid: false, message: "Vui lòng xác nhận mật khẩu mới" };
    }
    if (password !== newPassword) {
      return { isValid: false, message: "Mật khẩu xác nhận không khớp" };
    }
    return { isValid: true, message: "" };
  }

  // Validate all fields
  validateAll(currentPassword, newPassword, confirmPassword) {
    const errors = [];

    // Validate current password
    const currentResult = this.validateCurrentPassword(currentPassword);
    if (!currentResult.isValid) {
      errors.push(currentResult.message);
    }

    // Validate new password
    const newResult = this.validateNewPassword(newPassword);
    if (!newResult.isValid) {
      errors.push(newResult.message);
    }

    // Validate confirm password
    const confirmResult = this.validateConfirmPassword(
      confirmPassword,
      newPassword
    );
    if (!confirmResult.isValid) {
      errors.push(confirmResult.message);
    }

    return {
      isValid: errors.length === 0,
      errors: errors,
    };
  }

  // Show error message
  showError(elementId, message) {
    const element = document.getElementById(elementId);
    if (element) {
      element.textContent = message;
      element.style.display = "block";
      element.classList.add("text-danger");
    }
  }

  // Hide error message
  hideError(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
      element.textContent = "";
      element.style.display = "none";
      element.classList.remove("text-danger");
    }
  }

  // Clear all errors
  clearAllErrors() {
    this.hideError("currentPasswordError");
    this.hideError("newPasswordError");
    this.hideError("confirmPasswordError");
  }

  // Real-time validation
  setupRealTimeValidation() {
    const currentPasswordInput = document.getElementById("currentPassword");
    const newPasswordInput = document.getElementById("newPassword");
    const confirmPasswordInput = document.getElementById("confirmPassword");

    if (currentPasswordInput) {
      currentPasswordInput.addEventListener("input", () => {
        const result = this.validateCurrentPassword(currentPasswordInput.value);
        if (result.isValid) {
          this.hideError("currentPasswordError");
        } else {
          this.showError("currentPasswordError", result.message);
        }
      });
    }

    if (newPasswordInput) {
      newPasswordInput.addEventListener("input", () => {
        const result = this.validateNewPassword(newPasswordInput.value);
        if (result.isValid) {
          this.hideError("newPasswordError");
        } else {
          this.showError("newPasswordError", result.message);
        }

        // Re-validate confirm password if it has value
        if (confirmPasswordInput && confirmPasswordInput.value) {
          const confirmResult = this.validateConfirmPassword(
            confirmPasswordInput.value,
            newPasswordInput.value
          );
          if (confirmResult.isValid) {
            this.hideError("confirmPasswordError");
          } else {
            this.showError("confirmPasswordError", confirmResult.message);
          }
        }
      });
    }

    if (confirmPasswordInput) {
      confirmPasswordInput.addEventListener("input", () => {
        const result = this.validateConfirmPassword(
          confirmPasswordInput.value,
          newPasswordInput.value
        );
        if (result.isValid) {
          this.hideError("confirmPasswordError");
        } else {
          this.showError("confirmPasswordError", result.message);
        }
      });
    }
  }
}

// ========================================
// PASSWORD CHANGE FUNCTIONS
// ========================================

// Global validator instance
const passwordValidator = new PasswordValidator();

// Function to handle password change form submission
function handlePasswordChange(event) {
  event.preventDefault();

  // Clear previous errors and messages
  passwordValidator.clearAllErrors();

  // Remove existing message
  const existingMessage = document.getElementById("passwordChangeMessage");
  if (existingMessage) {
    existingMessage.remove();
  }

  // Get form data
  const currentPassword = document.getElementById("currentPassword").value;
  const newPassword = document.getElementById("newPassword").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  // Validate all fields
  const validation = passwordValidator.validateAll(
    currentPassword,
    newPassword,
    confirmPassword
  );

  if (!validation.isValid) {
    // Show first error
    if (validation.errors.length > 0) {
      showPasswordChangeMessage(validation.errors[0], "error");
    }
    return false;
  }

  // Show loading state
  const submitBtn = document.getElementById("changePasswordBtn");
  const originalText = submitBtn.innerHTML;
  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
  submitBtn.disabled = true;

  // Send AJAX request
  fetch("./change_password", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      currentPassword: currentPassword,
      newPassword: newPassword,
      confirmPassword: confirmPassword,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Success
        showPasswordChangeMessage(data.message, "success");

        // Clear form
        document.getElementById("changePasswordForm").reset();

        // Clear all errors
        passwordValidator.clearAllErrors();

        // Close modal after 2 seconds
        setTimeout(() => {
          const modal = bootstrap.Modal.getInstance(
            document.getElementById("changePasswordModal")
          );
          if (modal) {
            modal.hide();
          }
        }, 2000);
      } else {
        // Error
        showPasswordChangeMessage(
          data.message || "Có lỗi xảy ra khi thay đổi mật khẩu",
          "error"
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showPasswordChangeMessage(
        "Có lỗi xảy ra khi kết nối đến server",
        "error"
      );
    })
    .finally(() => {
      // Reset button state
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    });

  return false;
}

// Show password change message
function showPasswordChangeMessage(message, type) {
  // Remove existing message
  const existingMessage = document.getElementById("passwordChangeMessage");
  if (existingMessage) {
    existingMessage.remove();
  }

  // Create new message element
  const messageDiv = document.createElement("div");
  messageDiv.id = "passwordChangeMessage";
  messageDiv.className = `alert alert-${
    type === "success" ? "success" : "danger"
  } mt-3 mb-0`;
  messageDiv.innerHTML = `
    <i class="fas fa-${
      type === "success" ? "check-circle" : "exclamation-triangle"
    } me-2"></i>
    ${message}
  `;

  // Insert at the end of modal-body
  const modalBody = document.querySelector("#changePasswordModal .modal-body");
  if (modalBody) {
    modalBody.insertBefore(messageDiv, modalBody.firstChild);
  }

  // Auto remove after 5 seconds for success, 10 seconds for error
  setTimeout(
    () => {
      if (messageDiv.parentNode) {
        messageDiv.remove();
      }
    },
    type === "success" ? 5000 : 10000
  );
}

// Toggle password visibility
function togglePasswordVisibility(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);

  if (input.type === "password") {
    input.type = "text";
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  } else {
    input.type = "password";
    icon.classList.remove("fa-eye-slash");
    icon.classList.add("fa-eye");
  }
}

// ========================================
// REGISTRATION VALIDATION (ORIGINAL CODE)
// ========================================

// Không cần xử lý chọn role vì chỉ có patient

// Validation password (VI keys)
document.addEventListener("DOMContentLoaded", function () {
  // Initialize password validation
  passwordValidator.setupRealTimeValidation();

  // Original registration validation
  const xacNhanMatKhau = document.getElementById("xac_nhan_mat_khau");
  if (xacNhanMatKhau) {
    xacNhanMatKhau.addEventListener("input", function () {
      const password = document.getElementById("mat_khau").value;
      const confirmPassword = this.value;

      if (password !== confirmPassword) {
        this.setCustomValidity("Mật khẩu xác nhận không khớp!");
      } else {
        this.setCustomValidity("");
      }
    });
  }
});
