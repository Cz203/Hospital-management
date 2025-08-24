// ========================================
// PASSWORD TOGGLE FUNCTIONALITY
// ========================================

// Function to toggle password visibility
function togglePasswordVisibility(inputId, iconId) {
  const passwordInput = document.getElementById(inputId);
  const icon = document.getElementById(iconId);

  if (passwordInput.type === "password") {
    // Show password
    passwordInput.type = "text";
    icon.className = "fas fa-eye-slash";
    icon.title = "Ẩn mật khẩu";
  } else {
    // Hide password
    passwordInput.type = "password";
    icon.className = "fas fa-eye";
    icon.title = "Hiển thị mật khẩu";
  }
}

// ========================================
// MULTI-STEP FORM FUNCTIONALITY
// ========================================

// Multi-step form functionality
let currentStep = 1;
const totalSteps = 2;

function showStep(step) {
  // Hide all steps
  document
    .querySelectorAll(".form-step")
    .forEach((s) => (s.style.display = "none"));

  // Show current step
  document.getElementById(`step${step}`).style.display = "block";

  // Update progress bar
  const progress = ((step - 1) / (totalSteps - 1)) * 100;
  document.getElementById("progressBar").style.width = progress + "%";

  // Update step indicators
  document.querySelectorAll(".step-indicator").forEach((indicator, index) => {
    if (index + 1 <= step) {
      indicator.classList.add("active");
    } else {
      indicator.classList.remove("active");
    }
  });
}

// Next step button
document.getElementById("nextStepBtn").addEventListener("click", function () {
  if (currentStep < totalSteps) {
    currentStep++;
    showStep(currentStep);
  }
});

// Previous step button
document.getElementById("prevStepBtn").addEventListener("click", function () {
  if (currentStep > 1) {
    currentStep--;
    showStep(currentStep);
  }
});

// Enable next step button when OTP is verified and password is confirmed
function checkStep1Completion() {
  // Check if OTP is verified
  const otpVerified =
    document.getElementById("verifyOtpBtn").disabled &&
    document.getElementById("verifyOtpBtn").innerHTML.includes("Đã xác thực");

  // Check if password meets all requirements
  const password = document.getElementById("mat_khau").value;
  const hasMinLength = password.length >= 6;
  const hasMaxLength = password.length <= 50;
  const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
  const passwordValid = hasMinLength && hasMaxLength && hasSpecialChar;

  // Check if confirm password matches
  const confirmPassword = document.getElementById("xac_nhan_mat_khau").value;
  const passwordMatch =
    password && confirmPassword && password === confirmPassword;

  // Enable/disable next step button
  const nextStepBtn = document.getElementById("nextStepBtn");
  const isComplete = otpVerified && passwordValid && passwordMatch;

  nextStepBtn.disabled = !isComplete;
  if (isComplete) {
    nextStepBtn.className = "btn btn-primary";
  } else {
    nextStepBtn.className = "btn btn-secondary";
  }
}

// Submit button for step 2
document.addEventListener("DOMContentLoaded", function () {
  const submitBtn = document.getElementById("submitBtn");
  if (submitBtn) {
    submitBtn.addEventListener("click", function () {
      // Simple validation for step 2
      const requiredFields = [
        "ten", // Changed from "ho_ten" to "ten" to match the actual field ID
        "ngay_sinh",
        "gioi_tinh",
        "so_dien_thoai",
        "email",
        "dia_chi",
      ];
      let isValid = true;
      const errors = [];

      requiredFields.forEach((fieldId) => {
        const field = document.getElementById(fieldId);
        if (field && !field.value.trim()) {
          isValid = false;
          // Get field name from label
          const label = field.previousElementSibling;
          const fieldName = label
            ? label.textContent.replace("*", "").trim()
            : fieldId;
          errors.push(`${fieldName} không được để trống`);
        }
      });

      // Additional validations
      const isDateValid = validateDateOfBirth();
      const isNameValid = validateName();
      const isEmailValid = validateEmail();

      if (!isDateValid || !isNameValid || !isEmailValid) {
        isValid = false;
      }

      if (!isValid) {
        // Remove existing error messages
        const existingErrors = document.querySelectorAll(".validation-error");
        existingErrors.forEach((error) => error.remove());

        // Create and show error message
        const errorDiv = document.createElement("div");
        errorDiv.className = "alert alert-danger validation-error";
        errorDiv.innerHTML =
          '<i class="fas fa-exclamation-triangle me-2"></i><strong>Vui lòng sửa các lỗi sau:</strong><ul class="mb-0 mt-2">' +
          errors.map((error) => `<li>${error}</li>`).join("") +
          "</ul>";

        // Insert error message at the top of step 2
        const step2 = document.getElementById("step2");
        const firstChild = step2.firstChild;
        step2.insertBefore(errorDiv, firstChild);

        // Prevent form submission
        return false;
      } else {
        // Submit the form
        document.querySelector("form").submit();
      }
    });
  }
});

// Phone number validation with real-time check
let phoneCheckTimeout = null;

document.addEventListener("DOMContentLoaded", function () {
  const phoneInput = document.getElementById("so_dien_thoai");

  if (phoneInput) {
    // Thêm event listener cho input số điện thoại
    phoneInput.addEventListener("input", function () {
      const phone = this.value.trim();

      // Clear timeout cũ nếu có
      if (phoneCheckTimeout) {
        clearTimeout(phoneCheckTimeout);
      }

      // Xóa thông báo cũ
      removePhoneValidationMessage();

      // Kiểm tra độ dài tối thiểu
      if (phone.length < 9) {
        return;
      }

      // Đợi 500ms sau khi user ngừng gõ mới check
      phoneCheckTimeout = setTimeout(() => {
        checkPhoneForRegistration(phone);
      }, 500);
    });
  }

  // Check if there are any error messages in step 2
  const step2 = document.getElementById("step2");
  if (step2) {
    const errorMessages = step2.querySelectorAll(".alert-danger");

    if (errorMessages.length > 0) {
      // Switch to step 2
      currentStep = 2;
      showStep(2);

      // Auto verify OTP if phone and OTP are filled
      const phoneInput = document.getElementById("so_dien_thoai");
      const otpInput = document.getElementById("otp_code");
      const verifyBtn = document.getElementById("verifyOtpBtn");

      if (phoneInput && phoneInput.value && otpInput && otpInput.value) {
        // Simulate OTP verification
        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<i class="fas fa-check me-1"></i>Đã xác thực';
        verifyBtn.className = "btn btn-success";

        // Enable next step button
        const nextStepBtn = document.getElementById("nextStepBtn");
        if (nextStepBtn) {
          nextStepBtn.disabled = false;
          nextStepBtn.className = "btn btn-primary";
        }
      }
    }
  }
});

// Function để hiển thị thông báo validation
function showPhoneValidationMessage(message, type) {
  removePhoneValidationMessage();

  const phoneInput = document.getElementById("so_dien_thoai");
  const inputGroup = phoneInput.closest(".input-group");

  const messageDiv = document.createElement("div");
  messageDiv.className = `phone-validation-message mt-2 small ${
    type === "error" ? "text-danger" : "text-success"
  }`;
  messageDiv.innerHTML = `<i class="fas fa-${
    type === "error" ? "times-circle" : "check-circle"
  } me-1"></i>${message}`;

  inputGroup.parentNode.appendChild(messageDiv);
}

// Function để xóa thông báo validation
function removePhoneValidationMessage() {
  const existingMessage = document.querySelector(".phone-validation-message");
  if (existingMessage) {
    existingMessage.remove();
  }
}

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
// OTP MANAGEMENT (ORIGINAL CODE)
// ========================================

let phoneNumber = "";
let otpVerified = false;
let otpTimer = null;
let otpExpiryTime = null;

// Gửi OTP
document.addEventListener("DOMContentLoaded", function () {
  const sendOtpBtn = document.getElementById("sendOtpBtn");
  if (sendOtpBtn) {
    sendOtpBtn.addEventListener("click", function () {
      // Dừng timer cũ nếu có
      if (otpTimer) {
        clearInterval(otpTimer);
        otpTimer = null;
      }
      document.getElementById("otpTimerAlert").style.display = "none";

      const phone = document.getElementById("so_dien_thoai").value.trim();

      if (!phone) {
        ValidationUtils.showAlert("Vui lòng nhập số điện thoại", "warning");
        return;
      }

      // Kiểm tra số điện thoại có hợp lệ không
      const phoneDigits = phone.replace(/\D/g, "");
      if (phoneDigits.length < 9 || phoneDigits.length > 11) {
        ValidationUtils.showAlert(
          "Số điện thoại phải có 9-11 chữ số",
          "warning"
        );
        return;
      }

      // Disable button và hiển thị loading
      this.disabled = true;
      this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang gửi...';

      // Chuẩn hóa số điện thoại
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

      // Lưu số gốc cho form submit
      phoneNumber = phone;

      // Sử dụng số đã chuẩn hóa cho Vonage API
      const fullPhoneNumber = normalizedPhone;

      fetch("./send_otp", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          phone_number: fullPhoneNumber,
          check_database: true,
          for_registration: true,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Hiển thị OTP section
            document.getElementById("otpSection").classList.add("show");
            document.getElementById("otpStatus").style.display = "none";

            // Cập nhật hiển thị số điện thoại đích (số thực tế gửi SMS)
            document.getElementById("targetPhoneDisplay").textContent =
              fullPhoneNumber;

            // Reset button
            this.innerHTML =
              '<i class="fas fa-paper-plane me-1"></i>Gửi lại OTP';
            this.disabled = false;

            // Bắt đầu timer cho OTP (5 phút = 300 giây)
            otpExpiryTime = Math.floor(Date.now() / 1000) + 300;
            document.getElementById("otpTimerAlert").style.display = "block";
            updateOtpTimer();
            otpTimer = setInterval(updateOtpTimer, 1000);

            // Kiểm tra nếu là test mode (hết tiền)
            if (data.test_mode && data.otp_code) {
              // Hiển thị mã OTP trong alert box
              document.getElementById("testOtpCode").textContent =
                data.otp_code;
              document.getElementById("testModeAlert").style.display = "block";

              // Hiển thị thông báo với mã OTP
              ValidationUtils.showAlert(
                "Mã OTP đã được tạo (TEST MODE). Mã OTP: " +
                  data.otp_code +
                  ". Vui lòng sử dụng mã này để xác thực.",
                "success"
              );
            }
          } else {
            ValidationUtils.showAlert("Lỗi: " + data.message, "error");
            this.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Gửi OTP';
            this.disabled = false;
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          ValidationUtils.showAlert("Có lỗi xảy ra khi gửi OTP", "error");
          this.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Gửi OTP';
          this.disabled = false;
        });
    });
  }

  // Xác thực OTP
  const verifyOtpBtn = document.getElementById("verifyOtpBtn");
  if (verifyOtpBtn) {
    verifyOtpBtn.addEventListener("click", function () {
      const otpCode = document.getElementById("otp_code").value.trim();

      if (!otpCode) {
        ValidationUtils.showAlert("Vui lòng nhập mã OTP", "warning");
        return;
      }

      if (!/^[0-9]{6}$/.test(otpCode)) {
        ValidationUtils.showAlert("Mã OTP phải có 6 chữ số", "warning");
        return;
      }

      // Disable button
      this.disabled = true;
      this.innerHTML =
        '<i class="fas fa-spinner fa-spin me-1"></i>Đang xác thực...';

      // Chuẩn hóa số điện thoại cho verify
      let normalizedPhone = phoneNumber;

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

      // Sử dụng số đã chuẩn hóa cho verify
      const fullPhoneNumber = normalizedPhone;

      fetch("./verify_otp", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          phone_number: fullPhoneNumber,
          otp_code: otpCode,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          const statusDiv = document.getElementById("otpStatus");
          statusDiv.style.display = "block";

          if (data.success) {
            statusDiv.className = "alert alert-success";
            statusDiv.innerHTML =
              '<i class="fas fa-check-circle me-2"></i>' + data.message;
            otpVerified = true;
            this.innerHTML = '<i class="fas fa-check me-1"></i>Đã xác thực';
            this.disabled = true;

            // Dừng timer khi OTP được verify thành công
            if (otpTimer) {
              clearInterval(otpTimer);
              otpTimer = null;
            }
            document.getElementById("otpTimerAlert").style.display = "none";

            // Update OTP status for password validation
            if (typeof updateOtpStatus === "function") {
              updateOtpStatus(true);
            }

            // Enable submit button
            document.getElementById("submitBtn").disabled = false;
          } else {
            statusDiv.className = "alert alert-danger";
            statusDiv.innerHTML =
              '<i class="fas fa-exclamation-triangle me-2"></i>' + data.message;
            this.innerHTML = '<i class="fas fa-check me-1"></i>Xác thực';
            this.disabled = false;
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          ValidationUtils.showAlert("Có lỗi xảy ra khi xác thực OTP", "error");
          this.innerHTML = '<i class="fas fa-check me-1"></i>Xác thực';
          this.disabled = false;
        });
    });
  }

  // Validate form trước khi submit
  const registerForm = document.getElementById("registerForm");
  if (registerForm) {
    registerForm.addEventListener("submit", function (e) {
      if (!otpVerified) {
        e.preventDefault();
        ValidationUtils.showAlert(
          "Vui lòng xác thực số điện thoại bằng OTP trước khi đăng ký",
          "warning"
        );
        return false;
      }

      const password = document.getElementById("mat_khau").value;
      const confirmPassword =
        document.getElementById("xac_nhan_mat_khau").value;

      if (password !== confirmPassword) {
        e.preventDefault();
        ValidationUtils.showAlert("Mật khẩu xác nhận không khớp", "warning");
        return false;
      }
    });
  }
});

// Function để cập nhật timer
function updateOtpTimer() {
  if (!otpExpiryTime) return;

  const now = Math.floor(Date.now() / 1000);
  const remainingTime = otpExpiryTime - now;

  if (remainingTime <= 0) {
    // OTP đã hết hạn
    clearInterval(otpTimer);
    document.getElementById("otpTimer").textContent = "HẾT HẠN";
    document.getElementById("otpTimer").className = "fw-bold fs-5 text-danger";
    document.getElementById("otpTimerAlert").className = "alert alert-danger";
    return;
  }

  document.getElementById("otpTimer").textContent =
    ValidationUtils.formatTime(remainingTime);

  // Đổi màu khi gần hết hạn (dưới 1 phút)
  if (remainingTime <= 60) {
    document.getElementById("otpTimer").className = "fw-bold fs-5 text-danger";
  } else if (remainingTime <= 120) {
    document.getElementById("otpTimer").className = "fw-bold fs-5 text-warning";
  } else {
    document.getElementById("otpTimer").className = "fw-bold fs-5 text-success";
  }
}

// ========================================
// PASSWORD VALIDATION FUNCTIONS
// ========================================

// Function to validate password with detailed error messages
function validatePassword() {
  const password = document.getElementById("mat_khau").value;
  const errorElement = document.getElementById("passwordError");

  // Remove existing error element if any
  if (errorElement) {
    errorElement.remove();
  }

  // Create error element
  const errorDiv = document.createElement("div");
  errorDiv.id = "passwordError";
  errorDiv.className = "text-danger small mt-1 d-block w-100";

  let isValid = true;
  let errorMessage = "";

  // Check minimum length
  if (password.length < 6) {
    isValid = false;
    errorMessage = "Mật khẩu phải có ít nhất 6 ký tự";
  }
  // Check maximum length
  else if (password.length > 50) {
    isValid = false;
    errorMessage = "Mật khẩu không được quá 50 ký tự";
  }
  // Check for special characters
  else if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
    isValid = false;
    errorMessage =
      'Mật khẩu phải chứa ít nhất một ký tự đặc biệt (!@#$%^&*(),.?":{}|<>)';
  }

  if (!isValid) {
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>${errorMessage}`;
    const passwordInput = document.getElementById("mat_khau");
    const inputGroup = passwordInput.closest(".input-group");
    if (inputGroup) {
      inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
    } else {
      passwordInput.parentNode.appendChild(errorDiv);
    }

    // Add error styling to input
    passwordInput.classList.add("is-invalid");
    passwordInput.classList.remove("is-valid");
  } else {
    // Remove error styling from input
    const passwordInput = document.getElementById("mat_khau");
    passwordInput.classList.remove("is-invalid");
  }

  // Also validate confirm password if it has value
  const confirmPassword = document.getElementById("xac_nhan_mat_khau").value;
  if (confirmPassword) {
    validateConfirmPassword();
  }

  // Check step completion for next button
  if (typeof checkStep1Completion === "function") {
    checkStep1Completion();
  }

  return isValid;
}

// Function to validate confirm password
function validateConfirmPassword() {
  const password = document.getElementById("mat_khau").value;
  const confirmPassword = document.getElementById("xac_nhan_mat_khau").value;
  const errorElement = document.getElementById("confirmPasswordError");

  // Remove existing error element if any
  if (errorElement) {
    errorElement.remove();
  }

  // Create error element
  const errorDiv = document.createElement("div");
  errorDiv.id = "confirmPasswordError";
  errorDiv.className = "text-danger small mt-1 d-block w-100";

  let isValid = true;
  let errorMessage = "";

  if (confirmPassword && password !== confirmPassword) {
    isValid = false;
    errorMessage = "Mật khẩu xác nhận không khớp với mật khẩu";
  }

  if (!isValid) {
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>${errorMessage}`;
    const confirmPasswordInput = document.getElementById("xac_nhan_mat_khau");
    const inputGroup = confirmPasswordInput.closest(".input-group");
    if (inputGroup) {
      inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
    } else {
      confirmPasswordInput.parentNode.appendChild(errorDiv);
    }

    // Add error styling to input
    confirmPasswordInput.classList.add("is-invalid");
    confirmPasswordInput.classList.remove("is-valid");
  } else if (confirmPassword) {
    // Remove error styling from input
    const confirmPasswordInput = document.getElementById("xac_nhan_mat_khau");
    confirmPasswordInput.classList.remove("is-invalid");
  }

  // Check step completion for next button
  if (typeof checkStep1Completion === "function") {
    checkStep1Completion();
  }

  return isValid;
}

// ========================================
// ADDITIONAL VALIDATION FUNCTIONS
// ========================================

// Function to validate date of birth (minimum 6 years old)
function validateDateOfBirth() {
  const dateInput = document.getElementById("ngay_sinh");
  if (!dateInput) return true;

  const selectedDate = new Date(dateInput.value);
  const today = new Date();
  const age = today.getFullYear() - selectedDate.getFullYear();
  const monthDiff = today.getMonth() - selectedDate.getMonth();

  // Calculate exact age
  let exactAge = age;
  if (
    monthDiff < 0 ||
    (monthDiff === 0 && today.getDate() < selectedDate.getDate())
  ) {
    exactAge--;
  }

  const errorElement = document.getElementById("dateOfBirthError");
  if (errorElement) {
    errorElement.remove();
  }

  if (dateInput.value && exactAge < 6) {
    const errorDiv = document.createElement("div");
    errorDiv.id = "dateOfBirthError";
    errorDiv.className = "text-danger small mt-1 d-block w-100";
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>Tuổi phải từ 6 tuổi trở lên (hiện tại: ${exactAge} tuổi)`;

    const inputGroup = dateInput.closest(".input-group");
    if (inputGroup) {
      inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
    } else {
      dateInput.parentNode.appendChild(errorDiv);
    }

    dateInput.classList.add("is-invalid");
    dateInput.classList.remove("is-valid");
    return false;
  } else if (dateInput.value) {
    dateInput.classList.remove("is-invalid");
  }

  return true;
}

// Function to auto-capitalize first letter of each word in name
function autoCapitalizeName() {
  const nameInput = document.getElementById("ten");
  if (!nameInput) return;

  nameInput.addEventListener("input", function () {
    const words = this.value.split(" ");
    const capitalizedWords = words.map((word) => {
      if (word.length > 0) {
        return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
      }
      return word;
    });
    this.value = capitalizedWords.join(" ");
  });

  // Also apply on blur event to handle copy-paste
  nameInput.addEventListener("blur", function () {
    const words = this.value.split(" ");
    const capitalizedWords = words.map((word) => {
      if (word.length > 0) {
        return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
      }
      return word;
    });
    this.value = capitalizedWords.join(" ");
  });
}

// Function to validate name (not empty and proper format)
function validateName() {
  const nameInput = document.getElementById("ten");
  if (!nameInput) return true;

  const errorElement = document.getElementById("nameError");
  if (errorElement) {
    errorElement.remove();
  }

  const name = nameInput.value.trim();

  if (name && name.length < 2) {
    const errorDiv = document.createElement("div");
    errorDiv.id = "nameError";
    errorDiv.className = "text-danger small mt-1 d-block w-100";
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>Họ và tên phải có ít nhất 2 ký tự`;

    const inputGroup = nameInput.closest(".input-group");
    if (inputGroup) {
      inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
    } else {
      nameInput.parentNode.appendChild(errorDiv);
    }

    nameInput.classList.add("is-invalid");
    nameInput.classList.remove("is-valid");
    return false;
  } else if (name) {
    nameInput.classList.remove("is-invalid");
  }

  return true;
}

// Function to validate email (must be @gmail.com)
function validateEmail() {
  const emailInput = document.getElementById("email");
  if (!emailInput) return true;

  const errorElement = document.getElementById("emailError");
  if (errorElement) {
    errorElement.remove();
  }

  const email = emailInput.value.trim();

  if (email && !email.endsWith("@gmail.com")) {
    const errorDiv = document.createElement("div");
    errorDiv.id = "emailError";
    errorDiv.className = "text-danger small mt-1 d-block w-100";
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>Email phải có định dạng @gmail.com`;

    const inputGroup = emailInput.closest(".input-group");
    if (inputGroup) {
      inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
    } else {
      emailInput.parentNode.appendChild(errorDiv);
    }

    emailInput.classList.add("is-invalid");
    return false;
  } else if (email) {
    emailInput.classList.remove("is-invalid");
  }

  return true;
}

// Initialize additional validations when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  // Auto-capitalize name
  autoCapitalizeName();

  // Add validation for date of birth
  const dateInput = document.getElementById("ngay_sinh");
  if (dateInput) {
    dateInput.addEventListener("change", validateDateOfBirth);
    dateInput.addEventListener("input", validateDateOfBirth);
  }

  // Add validation for name
  const nameInput = document.getElementById("ten");
  if (nameInput) {
    nameInput.addEventListener("input", validateName);
    nameInput.addEventListener("blur", validateName);
  }

  // Add validation for email
  const emailInput = document.getElementById("email");
  if (emailInput) {
    emailInput.addEventListener("input", validateEmail);
    emailInput.addEventListener("blur", validateEmail);
  }

  // Update step 2 validation to include new validations
  const submitBtn = document.getElementById("submitBtn");
  if (submitBtn) {
    submitBtn.addEventListener("click", function () {
      // Validate all fields including new ones
      const isDateValid = validateDateOfBirth();
      const isNameValid = validateName();
      const isEmailValid = validateEmail();

      if (!isDateValid || !isNameValid || !isEmailValid) {
        return false;
      }

      // Continue with existing validation logic...
    });
  }
});
