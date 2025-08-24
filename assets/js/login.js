// Global variables
let forgotPhoneNumber = "";
let forgotOtpVerified = false;
let forgotOtpTimer = null;
let forgotOtpExpiryTime = null;

// ========================================
// VALIDATION FUNCTIONS
// ========================================

// Function to validate phone number
function validateForgotPhone() {
  const phoneInput = document.getElementById("forgotPhone");
  if (!phoneInput) return true;

  const errorElement = document.getElementById("forgotPhoneError");
  if (errorElement) {
    errorElement.remove();
  }

  const phone = phoneInput.value.trim();

  if (!phone) {
    const errorDiv = document.createElement("div");
    errorDiv.id = "forgotPhoneError";
    errorDiv.className = "text-danger small mt-1 d-block w-100";
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>Vui lòng nhập số điện thoại`;

    const inputGroup = phoneInput.closest(".input-group");
    if (inputGroup) {
      inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
    } else {
      phoneInput.parentNode.appendChild(errorDiv);
    }

    phoneInput.classList.add("is-invalid");
    return false;
  }

  // Validate phone number
  const phoneDigits = phone.replace(/\D/g, "");
  if (phoneDigits.length < 9 || phoneDigits.length > 11) {
    const errorDiv = document.createElement("div");
    errorDiv.id = "forgotPhoneError";
    errorDiv.className = "text-danger small mt-1 d-block w-100";
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>Số điện thoại phải có 9-11 chữ số`;

    const inputGroup = phoneInput.closest(".input-group");
    if (inputGroup) {
      inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
    } else {
      phoneInput.parentNode.appendChild(errorDiv);
    }

    phoneInput.classList.add("is-invalid");
    return false;
  }

  phoneInput.classList.remove("is-invalid");
  return true;
}

// Function to validate OTP
function validateForgotOtp() {
  const otpInput = document.getElementById("forgotOtpCode");
  if (!otpInput) return true;

  const errorElement = document.getElementById("forgotOtpCodeError");
  if (errorElement) {
    errorElement.remove();
  }

  const otp = otpInput.value.trim();

  if (!otp) {
    const errorDiv = document.createElement("div");
    errorDiv.id = "forgotOtpCodeError";
    errorDiv.className = "text-danger small mt-1 d-block w-100";
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>Vui lòng nhập mã OTP`;

    const inputGroup = otpInput.closest(".input-group");
    if (inputGroup) {
      inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
    } else {
      otpInput.parentNode.appendChild(errorDiv);
    }

    otpInput.classList.add("is-invalid");
    return false;
  }

  if (!/^[0-9]{6}$/.test(otp)) {
    const errorDiv = document.createElement("div");
    errorDiv.id = "forgotOtpCodeError";
    errorDiv.className = "text-danger small mt-1 d-block w-100";
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>Mã OTP phải có 6 chữ số`;

    const inputGroup = otpInput.closest(".input-group");
    if (inputGroup) {
      inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
    } else {
      otpInput.parentNode.appendChild(errorDiv);
    }

    otpInput.classList.add("is-invalid");
    return false;
  }

  otpInput.classList.remove("is-invalid");
  return true;
}

// Function to validate new password
function validateNewPassword() {
  const passwordInput = document.getElementById("newPassword");
  if (!passwordInput) return true;

  const errorElement = document.getElementById("newPasswordError");
  if (errorElement) {
    errorElement.remove();
  }

  const password = passwordInput.value;

  if (!password) {
    const errorDiv = document.createElement("div");
    errorDiv.id = "newPasswordError";
    errorDiv.className = "text-danger small mt-1 d-block w-100";
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>Vui lòng nhập mật khẩu mới`;

    const inputGroup = passwordInput.closest(".input-group");
    if (inputGroup) {
      inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
    } else {
      passwordInput.parentNode.appendChild(errorDiv);
    }

    passwordInput.classList.add("is-invalid");
    return false;
  }

  // Check minimum length
  if (password.length < 6) {
    const errorDiv = document.createElement("div");
    errorDiv.id = "newPasswordError";
    errorDiv.className = "text-danger small mt-1 d-block w-100";
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>Mật khẩu phải có ít nhất 6 ký tự`;

    const inputGroup = passwordInput.closest(".input-group");
    if (inputGroup) {
      inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
    } else {
      passwordInput.parentNode.appendChild(errorDiv);
    }

    passwordInput.classList.add("is-invalid");
    return false;
  }

  // Check for special characters
  if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
    const errorDiv = document.createElement("div");
    errorDiv.id = "newPasswordError";
    errorDiv.className = "text-danger small mt-1 d-block w-100";
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>Mật khẩu phải chứa ít nhất một ký tự đặc biệt (!@#$%^&*(),.?":{}|<>)`;

    const inputGroup = passwordInput.closest(".input-group");
    if (inputGroup) {
      inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
    } else {
      passwordInput.parentNode.appendChild(errorDiv);
    }

    passwordInput.classList.add("is-invalid");
    return false;
  }

  passwordInput.classList.remove("is-invalid");
  return true;
}

// Function to validate confirm password
function validateConfirmPassword() {
  const passwordInput = document.getElementById("newPassword");
  const confirmPasswordInput = document.getElementById("confirmNewPassword");
  if (!confirmPasswordInput) return true;

  const errorElement = document.getElementById("confirmPasswordError");
  if (errorElement) {
    errorElement.remove();
  }

  const password = passwordInput ? passwordInput.value : "";
  const confirmPassword = confirmPasswordInput.value;

  if (!confirmPassword) {
    const errorDiv = document.createElement("div");
    errorDiv.id = "confirmPasswordError";
    errorDiv.className = "text-danger small mt-1 d-block w-100";
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>Vui lòng xác nhận mật khẩu`;

    const inputGroup = confirmPasswordInput.closest(".input-group");
    if (inputGroup) {
      inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
    } else {
      confirmPasswordInput.parentNode.appendChild(errorDiv);
    }

    confirmPasswordInput.classList.add("is-invalid");
    return false;
  }

  if (password !== confirmPassword) {
    const errorDiv = document.createElement("div");
    errorDiv.id = "confirmPasswordError";
    errorDiv.className = "text-danger small mt-1 d-block w-100";
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>Mật khẩu xác nhận không khớp`;

    const inputGroup = confirmPasswordInput.closest(".input-group");
    if (inputGroup) {
      inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
    } else {
      confirmPasswordInput.parentNode.appendChild(errorDiv);
    }

    confirmPasswordInput.classList.add("is-invalid");
    return false;
  }

  confirmPasswordInput.classList.remove("is-invalid");
  return true;
}

// ========================================
// EXISTING FUNCTIONS
// ========================================

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

// Show login form
function showLoginForm() {
  document.getElementById("loginForm").classList.add("active");
  document.getElementById("forgotPasswordForm").classList.remove("active");
}

// Show forgot password form
function showForgotPassword() {
  document.getElementById("loginForm").classList.remove("active");
  document.getElementById("forgotPasswordForm").classList.add("active");
}

// Show specific forgot password step
function showForgotStep(step) {
  // Hide all steps
  document
    .querySelectorAll("#forgotPasswordForm .form-step")
    .forEach((s) => s.classList.remove("active"));

  // Show current step
  document.getElementById(`forgotStep${step}`).classList.add("active");

  // Update step indicators
  document.querySelectorAll(".step-dot").forEach((dot, index) => {
    if (index + 1 <= step) {
      dot.classList.add("active");
    } else {
      dot.classList.remove("active");
    }
  });
}

function showForgotStep1() {
  showForgotStep(1);
}

function showForgotStep2() {
  showForgotStep(2);
}

function showForgotStep3() {
  showForgotStep(3);
}

// Send OTP for forgot password
document
  .getElementById("sendForgotOtpBtn")
  .addEventListener("click", function () {
    // Validate phone number
    if (!validateForgotPhone()) {
      return;
    }

    // Disable button and show loading
    const button = this;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang gửi...';

    // Get phone number from input
    const phone = document.getElementById("forgotPhone").value.trim();

    // Normalize phone number
    let normalizedPhone = phone.replace(/\D/g, "");
    if (normalizedPhone.startsWith("0")) {
      normalizedPhone = normalizedPhone.substring(1);
    }
    if (!normalizedPhone.startsWith("84")) {
      normalizedPhone = "84" + normalizedPhone;
    }

    forgotPhoneNumber = phone;

    // Add timeout to prevent button from being stuck
    const timeoutId = setTimeout(() => {
      button.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Gửi OTP';
      button.disabled = false;
      console.error("Request timeout");
    }, 10000); // 10 seconds timeout

    fetch("./send_otp", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        phone_number: normalizedPhone,
        check_database: true, // Kiểm tra số điện thoại trong database
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Show step 2
          showForgotStep2();

          // Update target phone display
          document.getElementById("forgotTargetPhone").textContent =
            normalizedPhone;

          // Reset button
          button.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Gửi OTP';
          button.disabled = false;

          // Start timer
          forgotOtpExpiryTime = Math.floor(Date.now() / 1000) + 300;
          document.getElementById("forgotOtpTimerAlert").style.display =
            "block";
          updateForgotOtpTimer();
          forgotOtpTimer = setInterval(updateForgotOtpTimer, 1000);

          // Check if test mode
          if (data.test_mode && data.otp_code) {
            document.getElementById("forgotTestOtpCode").textContent =
              data.otp_code;
            document.getElementById("forgotTestModeAlert").style.display =
              "block";
          } else {
            document.getElementById("forgotTestModeAlert").style.display =
              "none";
          }
        } else {
          // Clear timeout
          clearTimeout(timeoutId);
          // Show error in field
          const phoneInput = document.getElementById("forgotPhone");
          const errorDiv = document.createElement("div");
          errorDiv.id = "forgotPhoneError";
          errorDiv.className = "text-danger small mt-1 d-block w-100";
          errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>${data.message}`;

          const inputGroup = phoneInput.closest(".input-group");
          if (inputGroup) {
            inputGroup.parentNode.insertBefore(
              errorDiv,
              inputGroup.nextSibling
            );
          } else {
            phoneInput.parentNode.appendChild(errorDiv);
          }

          phoneInput.classList.add("is-invalid");
          button.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Gửi OTP';
          button.disabled = false;
        }
      })
      .catch((error) => {
        // Clear timeout
        clearTimeout(timeoutId);
        console.error("Error:", error);
        // Show error in field
        const phoneInput = document.getElementById("forgotPhone");
        const errorDiv = document.createElement("div");
        errorDiv.id = "forgotPhoneError";
        errorDiv.className = "text-danger small mt-1 d-block w-100";
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>Có lỗi xảy ra khi gửi OTP`;

        const inputGroup = phoneInput.closest(".input-group");
        if (inputGroup) {
          inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
        } else {
          phoneInput.parentNode.appendChild(errorDiv);
        }

        phoneInput.classList.add("is-invalid");
        button.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Gửi OTP';
        button.disabled = false;
      });
  });

// Verify OTP for forgot password
document
  .getElementById("verifyForgotOtpBtn")
  .addEventListener("click", function () {
    // Validate OTP
    if (!validateForgotOtp()) {
      return;
    }

    // Disable button and show loading
    const button = this;
    button.disabled = true;
    button.innerHTML =
      '<i class="fas fa-spinner fa-spin me-1"></i>Đang xác thực...';

    // Get OTP code from input
    const otpCode = document.getElementById("forgotOtpCode").value.trim();

    // Normalize phone number
    let normalizedPhone = forgotPhoneNumber.replace(/\D/g, "");
    if (normalizedPhone.startsWith("0")) {
      normalizedPhone = normalizedPhone.substring(1);
    }
    if (!normalizedPhone.startsWith("84")) {
      normalizedPhone = "84" + normalizedPhone;
    }

    // Add timeout to prevent button from being stuck
    const timeoutId = setTimeout(() => {
      button.innerHTML = '<i class="fas fa-check me-1"></i>Xác thực';
      button.disabled = false;
      console.error("Request timeout");
    }, 10000); // 10 seconds timeout

    fetch("./verify_otp", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        phone_number: normalizedPhone,
        otp_code: otpCode,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        // Clear timeout
        clearTimeout(timeoutId);

        const statusDiv = document.getElementById("forgotOtpStatus");
        statusDiv.style.display = "block";

        if (data.success) {
          statusDiv.className = "alert alert-success";
          statusDiv.innerHTML =
            '<i class="fas fa-check-circle me-2"></i>' + data.message;
          forgotOtpVerified = true;
          button.innerHTML = '<i class="fas fa-check me-1"></i>Đã xác thực';
          button.disabled = true;

          // Stop timer
          if (forgotOtpTimer) {
            clearInterval(forgotOtpTimer);
            forgotOtpTimer = null;
          }
          document.getElementById("forgotOtpTimerAlert").style.display = "none";

          // Show continue button
          const continueBtn = document.createElement("button");
          continueBtn.type = "button";
          continueBtn.className = "continue-btn";
          continueBtn.innerHTML = '<i class="fas fa-arrow-right"></i>Tiếp tục';
          continueBtn.onclick = function () {
            showForgotStep3();
          };

          // Create button container for better alignment
          const buttonContainer = document.createElement("div");
          buttonContainer.className = "button-container";

          // Move the existing "Quay lại" button to the container
          const backBtn = document.querySelector("#forgotStep2 .btn-secondary");
          if (backBtn) {
            backBtn.parentNode.removeChild(backBtn);
            buttonContainer.appendChild(backBtn);
          }

          // Add continue button to container
          buttonContainer.appendChild(continueBtn);

          // Add button container after the status div
          statusDiv.parentNode.insertBefore(
            buttonContainer,
            statusDiv.nextSibling
          );
        } else {
          // Clear timeout
          clearTimeout(timeoutId);

          statusDiv.className = "alert alert-danger";
          statusDiv.innerHTML =
            '<i class="fas fa-exclamation-triangle me-2"></i>' + data.message;
          button.innerHTML = '<i class="fas fa-check me-1"></i>Xác thực';
          button.disabled = false;
        }
      })
      .catch((error) => {
        // Clear timeout
        clearTimeout(timeoutId);

        console.error("Error:", error);
        // Show error in OTP field
        const otpInput = document.getElementById("forgotOtpCode");
        const errorDiv = document.createElement("div");
        errorDiv.id = "forgotOtpCodeError";
        errorDiv.className = "text-danger small mt-1 d-block w-100";
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>Có lỗi xảy ra khi xác thực OTP`;

        const inputGroup = otpInput.closest(".input-group");
        if (inputGroup) {
          inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
        } else {
          otpInput.parentNode.appendChild(errorDiv);
        }

        otpInput.classList.add("is-invalid");
        button.innerHTML = '<i class="fas fa-check me-1"></i>Xác thực';
        button.disabled = false;
      });
  });

// Reset password
document
  .getElementById("resetPasswordBtn")
  .addEventListener("click", function () {
    // Validate passwords
    if (!validateNewPassword() || !validateConfirmPassword()) {
      return;
    }

    // Disable button and show loading
    const button = this;
    button.disabled = true;
    button.innerHTML =
      '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';

    // Get password values
    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmNewPassword").value;

    // Normalize phone number
    let normalizedPhone = forgotPhoneNumber.replace(/\D/g, "");
    if (normalizedPhone.startsWith("0")) {
      normalizedPhone = normalizedPhone.substring(1);
    }
    if (!normalizedPhone.startsWith("84")) {
      normalizedPhone = "84" + normalizedPhone;
    }

    // Add timeout to prevent button from being stuck
    const timeoutId = setTimeout(() => {
      button.innerHTML = '<i class="fas fa-save me-2"></i>Đặt lại mật khẩu';
      button.disabled = false;
      console.error("Request timeout");
    }, 10000); // 10 seconds timeout

    console.log("Sending reset password request:", {
      phone_number: normalizedPhone,
      new_password: newPassword,
    });

    fetch("./reset_password", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        phone_number: normalizedPhone,
        new_password: newPassword,
      }),
    })
      .then((response) => {
        console.log("Response status:", response.status);
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        // Clear timeout
        clearTimeout(timeoutId);
        console.log("Response data:", data);

        if (data.success) {
          // Show success message in the center of the form
          const step3Container = document.getElementById("forgotStep3");
          const successDiv = document.createElement("div");
          successDiv.id = "resetPasswordSuccess";
          successDiv.className =
            "alert alert-success text-center mt-3 mb-3 d-block w-100";
          successDiv.innerHTML = `<i class="fas fa-check-circle me-2"></i>${data.message}`;

          // Insert after the title and before the form fields
          const title = step3Container.querySelector("h3, h4, h5");
          if (title) {
            title.parentNode.insertBefore(successDiv, title.nextSibling);
          } else {
            // If no title found, insert at the beginning of step3
            step3Container.insertBefore(successDiv, step3Container.firstChild);
          }

          // Reset button
          button.innerHTML = '<i class="fas fa-save me-2"></i>Đặt lại mật khẩu';
          button.disabled = false;

          // Redirect to login form after 2 seconds
          setTimeout(() => {
            showLoginForm();
          }, 2000);
        } else {
          // Show error in password field
          const passwordInput = document.getElementById("newPassword");
          const errorDiv = document.createElement("div");
          errorDiv.id = "newPasswordError";
          errorDiv.className = "text-danger small mt-1 d-block w-100";
          errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>${data.message}`;

          const inputGroup = passwordInput.closest(".input-group");
          if (inputGroup) {
            inputGroup.parentNode.insertBefore(
              errorDiv,
              inputGroup.nextSibling
            );
          } else {
            passwordInput.parentNode.appendChild(errorDiv);
          }

          passwordInput.classList.add("is-invalid");
          button.innerHTML = '<i class="fas fa-save me-2"></i>Đặt lại mật khẩu';
          button.disabled = false;
        }
      })
      .catch((error) => {
        // Clear timeout
        clearTimeout(timeoutId);

        console.error("Error:", error);
        // Show error in password field
        const passwordInput = document.getElementById("newPassword");
        const errorDiv = document.createElement("div");
        errorDiv.id = "newPasswordError";
        errorDiv.className = "text-danger small mt-1 d-block w-100";
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>Có lỗi xảy ra khi đặt lại mật khẩu`;

        const inputGroup = passwordInput.closest(".input-group");
        if (inputGroup) {
          inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
        } else {
          passwordInput.parentNode.appendChild(errorDiv);
        }

        passwordInput.classList.add("is-invalid");
        button.innerHTML = '<i class="fas fa-save me-2"></i>Đặt lại mật khẩu';
        button.disabled = false;
      });
  });

// Update forgot OTP timer
function updateForgotOtpTimer() {
  if (!forgotOtpExpiryTime) return;

  const now = Math.floor(Date.now() / 1000);
  const remainingTime = forgotOtpExpiryTime - now;

  if (remainingTime <= 0) {
    clearInterval(forgotOtpTimer);
    document.getElementById("forgotOtpTimer").textContent = "HẾT HẠN";
    document.getElementById("forgotOtpTimer").className = "timer text-danger";
    return;
  }

  const minutes = Math.floor(remainingTime / 60);
  const seconds = remainingTime % 60;
  document.getElementById("forgotOtpTimer").textContent = `${minutes}:${seconds
    .toString()
    .padStart(2, "0")}`;

  if (remainingTime <= 60) {
    document.getElementById("forgotOtpTimer").className = "timer text-danger";
  } else if (remainingTime <= 120) {
    document.getElementById("forgotOtpTimer").className = "timer text-warning";
  } else {
    document.getElementById("forgotOtpTimer").className = "timer text-success";
  }
}

// Initialize validations when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  // Add validation for forgot phone
  const forgotPhoneInput = document.getElementById("forgotPhone");
  if (forgotPhoneInput) {
    forgotPhoneInput.addEventListener("input", validateForgotPhone);
    forgotPhoneInput.addEventListener("blur", validateForgotPhone);
  }

  // Add validation for forgot OTP
  const forgotOtpInput = document.getElementById("forgotOtpCode");
  if (forgotOtpInput) {
    forgotOtpInput.addEventListener("input", validateForgotOtp);
    forgotOtpInput.addEventListener("blur", validateForgotOtp);
  }

  // Add validation for new password
  const newPasswordInput = document.getElementById("newPassword");
  if (newPasswordInput) {
    newPasswordInput.addEventListener("input", validateNewPassword);
    newPasswordInput.addEventListener("blur", validateNewPassword);
  }

  // Add validation for confirm password
  const confirmPasswordInput = document.getElementById("confirmNewPassword");
  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener("input", validateConfirmPassword);
    confirmPasswordInput.addEventListener("blur", validateConfirmPassword);
  }
});
