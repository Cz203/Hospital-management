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
const totalSteps = 3;

// Load saved step from sessionStorage on page load
function loadSavedStep() {
  // Check if there's a saved step from form submission
  const savedStep = sessionStorage.getItem("registerFormStep");

  // Check if there's an error message in the page (backend validation error)
  const hasError =
    document.querySelector("#step3 .alert-danger") ||
    document.querySelector("#step2 .alert-danger");

  if (savedStep && hasError) {
    // There was a form submission with error, restore the step
    currentStep = parseInt(savedStep);
    showStep(currentStep);

    // Mark OTP as verified if we're past step 1
    if (currentStep >= 2) {
      otpVerified = true;

      // Enable step 1 next button
      const step1NextBtn = document.getElementById("step1NextBtn");
      if (step1NextBtn) {
        step1NextBtn.disabled = false;
        step1NextBtn.className = "btn btn-primary";
      }
    }

    // Enable step 2 next button if we're past step 2
    if (currentStep >= 3) {
      const step2NextBtn = document.getElementById("step2NextBtn");
      if (step2NextBtn) {
        step2NextBtn.disabled = false;
        step2NextBtn.className = "btn btn-primary";
      }
    }

    // Clear the saved step after restoring
    sessionStorage.removeItem("registerFormStep");
  } else {
    // Normal page load or success redirect
    // Clear any saved step and start from step 1
    sessionStorage.removeItem("registerFormStep");
    currentStep = 1;
    showStep(currentStep);
  }
}

function showStep(step) {
  // Removed: Don't save step to sessionStorage anymore
  currentStep = step;

  // Reset auto-advance flags when changing steps
  step1AutoAdvanceScheduled = false;

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

  // Check step completion when showing step
  if (step === 2) {
    // Check step 2 completion when showing step 2
    setTimeout(function () {
      if (typeof checkStep2Completion === "function") {
        checkStep2Completion();
      }
    }, 100);
  } else if (step === 3) {
    // Check step 3 completion when showing step 3
    setTimeout(function () {
      if (typeof checkStep3Completion === "function") {
        checkStep3Completion();
      }
    }, 100);
  }
}

// Step 1 Next button
document.getElementById("step1NextBtn").addEventListener("click", function () {
  if (currentStep === 1) {
    currentStep = 2;
    showStep(currentStep);
  }
});

// Step 2 Previous button
document.getElementById("step2PrevBtn").addEventListener("click", function () {
  currentStep = 1;
  showStep(currentStep);
});

// Step 2 Next button
document.getElementById("step2NextBtn").addEventListener("click", function () {
  if (currentStep === 2) {
    currentStep = 3;
    showStep(currentStep);
  }
});

// Step 3 Previous button
document.getElementById("step3PrevBtn").addEventListener("click", function () {
  currentStep = 2;
  showStep(currentStep);
});

// Enable step 1 next button when OTP is verified
// Auto-advance to step 2 when OTP is verified
let step1AutoAdvanceScheduled = false;
function checkStep1Completion() {
  // Check if OTP is verified (use global otpVerified variable)
  const step1NextBtn = document.getElementById("step1NextBtn");
  if (!step1NextBtn) return;

  step1NextBtn.disabled = !otpVerified;
  if (otpVerified) {
    step1NextBtn.className = "btn btn-primary";

    // Auto-advance to step 2 after a short delay
    if (currentStep === 1 && !step1AutoAdvanceScheduled) {
      step1AutoAdvanceScheduled = true;
      setTimeout(function () {
        if (currentStep === 1 && otpVerified) {
          currentStep = 2;
          showStep(currentStep);
        }
        step1AutoAdvanceScheduled = false;
      }, 800); // Delay 800ms để user thấy feedback
    }
  } else {
    step1NextBtn.className = "btn btn-secondary";
    step1AutoAdvanceScheduled = false;
  }
}

// Check step 3 completion - enable submit button when all fields are valid
function checkStep3Completion() {
  if (currentStep !== 3) return;

  const submitBtn = document.getElementById("submitBtn");
  if (!submitBtn) return;

  // Check required fields
  const tenInput = document.getElementById("ten");
  const emailInput = document.getElementById("email");
  const cccdInput = document.getElementById("cccd");
  const dateInput = document.getElementById("ngay_sinh");

  if (!tenInput || !emailInput || !cccdInput) return;

  const ten = tenInput.value.trim();
  const email = emailInput.value.trim();
  const cccd = cccdInput.value.trim();
  const ngaySinh = dateInput ? dateInput.value.trim() : "";

  // Basic validation checks (without showing error messages)
  const isNameValid = ten.length >= 2;
  const isEmailValid =
    email.length > 0 && email.includes("@") && email.includes(".");
  const isCCCDValid = cccd.length === 12 && cccdVerified;

  // Check date of birth (optional but should be valid if filled)
  let isDateValid = true;
  if (ngaySinh) {
    const date = new Date(ngaySinh);
    const today = new Date();
    const age = today.getFullYear() - date.getFullYear();
    const monthDiff = today.getMonth() - date.getMonth();
    const actualAge =
      monthDiff < 0 || (monthDiff === 0 && today.getDate() < date.getDate())
        ? age - 1
        : age;
    isDateValid = actualAge >= 6 && !isNaN(date.getTime());
  }

  const isComplete = isNameValid && isEmailValid && isCCCDValid && isDateValid;

  submitBtn.disabled = !isComplete;
  if (isComplete) {
    submitBtn.className = "btn btn-primary";
  } else {
    submitBtn.className = "btn btn-secondary";
  }
}

// Enable step 2 next button when password is valid and confirmed
// User must manually click the next button to proceed to step 3
function checkStep2Completion() {
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

  // Enable/disable step 2 next button
  const step2NextBtn = document.getElementById("step2NextBtn");
  if (!step2NextBtn) return;

  const isComplete = passwordValid && passwordMatch;

  step2NextBtn.disabled = !isComplete;
  if (isComplete) {
    step2NextBtn.className = "btn btn-primary";
    // User must manually click the button to proceed - no auto-advance
  } else {
    step2NextBtn.className = "btn btn-secondary";
  }
}

// Submit button for step 3
document.addEventListener("DOMContentLoaded", function () {
  const submitBtn = document.getElementById("submitBtn");
  if (submitBtn) {
    submitBtn.addEventListener("click", function () {
      // Simple validation for step 3
      const requiredFields = [
        "ten", // Changed from "ho_ten" to "ten" to match the actual field ID
        "email",
        "cccd",
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
      const isNameValid = validateName();
      const isEmailValid = validateEmail();

      if (!isNameValid || !isEmailValid) {
        isValid = false;
      }

      if (!isValid) {
        // Remove existing error messages
        const existingErrors = document.querySelectorAll(".validation-error");
        existingErrors.forEach((error) => error.remove());

        // Create and show error message
        const errorDiv = document.createElement("div");
        const errorId = "validation-error-" + Date.now();
        errorDiv.id = errorId;
        errorDiv.className =
          "alert alert-danger alert-dismissible fade show validation-error d-flex align-items-start";
        errorDiv.innerHTML =
          '<i class="fas fa-exclamation-triangle me-2 mt-1"></i>' +
          '<div class="flex-grow-1">' +
          '<strong>Vui lòng sửa các lỗi sau:</strong><ul class="mb-0 mt-2">' +
          errors.map((error) => `<li>${error}</li>`).join("") +
          "</ul>" +
          "</div>" +
          '<button type="button" class="btn-close ms-auto mt-1" aria-label="Close" onclick="document.getElementById(\'' +
          errorId +
          "').remove()\"></button>";

        // Insert error message at the top of step 3
        const step3 = document.getElementById("step3");
        const firstChild = step3.firstChild;
        step3.insertBefore(errorDiv, firstChild);

        // Auto close after 3 seconds
        const autoCloseTimer = setTimeout(() => {
          const errorElement = document.getElementById(errorId);
          if (errorElement && errorElement.parentNode) {
            errorElement.classList.remove("show");
            setTimeout(() => {
              if (errorElement.parentNode) {
                errorElement.remove();
              }
            }, 150); // Fade out animation
          }
        }, 3000);

        // Clear timer when close button is clicked
        const closeBtn = errorDiv.querySelector(".btn-close");
        if (closeBtn) {
          closeBtn.addEventListener("click", function () {
            clearTimeout(autoCloseTimer);
          });
        }

        // Prevent form submission
        return false;
      } else {
        // Save current step before submitting
        sessionStorage.setItem("registerFormStep", currentStep.toString());

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

      // Validate số điện thoại Việt Nam trước
      const validation = validateVietnamesePhoneNumber(phone);
      if (!validation.valid) {
        showPhoneValidationMessage(validation.message, "error");
        return;
      }

      // Nếu hợp lệ, hiển thị thông báo success
      showPhoneValidationMessage("Số điện thoại hợp lệ", "success");

      // Đợi 500ms sau khi user ngừng gõ mới check database
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

// Validate số điện thoại Việt Nam
function validateVietnamesePhoneNumber(phoneNumber) {
  // Loại bỏ tất cả ký tự không phải số
  let phone = phoneNumber.replace(/\D/g, "");

  // Nếu bắt đầu bằng 84, bỏ 84 đầu để check
  if (phone.startsWith("84")) {
    phone = phone.substring(2);
  } else if (phone.startsWith("0")) {
    phone = phone.substring(1); // Bỏ số 0 đầu
  }

  // Danh sách các đầu số hợp lệ của Việt Nam
  const validPrefixes = [
    // Viettel
    "32",
    "33",
    "34",
    "35",
    "36",
    "37",
    "38",
    "39",
    "86",
    "96",
    "97",
    "98",
    // Vinaphone
    "81",
    "82",
    "83",
    "84",
    "85",
    "88",
    "91",
    "94",
    // Mobifone
    "70",
    "76",
    "77",
    "78",
    "79",
    "89",
    "90",
    "93",
    // Vietnamobile
    "52",
    "56",
    "58",
    "92",
    // Gmobile
    "59",
    "99",
  ];

  // Kiểm tra độ dài (phải có 9 chữ số sau khi bỏ đầu số 0)
  if (phone.length !== 9) {
    return {
      valid: false,
      message: "Số điện thoại phải có 10 chữ số.",
    };
  }

  // Kiểm tra đầu số có hợp lệ không
  const prefix = phone.substring(0, 2);
  if (!validPrefixes.includes(prefix)) {
    return {
      valid: false,
      message: "Số điện thoại không hợp lệ.",
    };
  }

  return {
    valid: true,
    normalized: "84" + phone,
  };
}

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
  // Show alert function with auto-close after 3 seconds and close button
  showAlert: function (message, type) {
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${
      type === "error" ? "danger" : type === "warning" ? "warning" : "success"
    } alert-dismissible fade show d-flex align-items-center`;

    // Generate unique ID for this alert
    const alertId = "alert-" + Date.now();
    alertDiv.id = alertId;

    alertDiv.innerHTML = `
            <i class="fas fa-${
              type === "success"
                ? "check-circle"
                : type === "warning"
                ? "exclamation-triangle"
                : "times-circle"
            } me-2"></i>
            <span class="flex-grow-1">${message}</span>
            <button type="button" class="btn-close ms-auto" aria-label="Close" onclick="document.getElementById('${alertId}').remove()"></button>
        `;

    // Insert vào đầu card-body
    const cardBody = document.querySelector(".card-body");
    if (cardBody) {
      cardBody.insertBefore(alertDiv, cardBody.firstChild);
    }

    // Auto remove sau 3 giây
    const autoCloseTimer = setTimeout(() => {
      const alertElement = document.getElementById(alertId);
      if (alertElement && alertElement.parentNode) {
        alertElement.classList.remove("show");
        setTimeout(() => {
          if (alertElement.parentNode) {
            alertElement.remove();
          }
        }, 150); // Fade out animation
      }
    }, 3000);

    // Clear timer when close button is clicked
    const closeBtn = alertDiv.querySelector(".btn-close");
    if (closeBtn) {
      closeBtn.addEventListener("click", function () {
        clearTimeout(autoCloseTimer);
      });
    }
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

// Restore OTP verification status from sessionStorage on page load
function restoreOtpStatus() {
  // Removed: Don't restore OTP status anymore, always start fresh
  otpVerified = false;
  phoneNumber = "";
}

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
            // Generate unique ID for this alert
            const alertId = "otp-status-" + Date.now();
            statusDiv.className =
              "alert alert-success alert-dismissible fade show d-flex align-items-center";
            statusDiv.innerHTML =
              '<i class="fas fa-check-circle me-2"></i>' +
              '<span class="flex-grow-1">' +
              data.message +
              "</span>" +
              '<button type="button" class="btn-close ms-auto" aria-label="Close" onclick="document.getElementById(\'otpStatus\').style.display=\'none\'"></button>';
            statusDiv.id = "otpStatus";
            otpVerified = true;

            // Auto close after 3 seconds
            const autoCloseTimer = setTimeout(() => {
              if (statusDiv && statusDiv.parentNode) {
                statusDiv.classList.remove("show");
                setTimeout(() => {
                  if (statusDiv.parentNode) {
                    statusDiv.style.display = "none";
                  }
                }, 150); // Fade out animation
              }
            }, 3000);

            // Clear timer when close button is clicked
            const closeBtn = statusDiv.querySelector(".btn-close");
            if (closeBtn) {
              closeBtn.addEventListener("click", function () {
                clearTimeout(autoCloseTimer);
              });
            }

            // Removed: Don't save to sessionStorage anymore

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

            // Enable step 1 next button
            if (typeof checkStep1Completion === "function") {
              checkStep1Completion();
            }

            // Enable submit button
            document.getElementById("submitBtn").disabled = false;
          } else {
            statusDiv.className =
              "alert alert-danger alert-dismissible fade show d-flex align-items-center";
            statusDiv.innerHTML =
              '<i class="fas fa-exclamation-triangle me-2"></i>' +
              '<span class="flex-grow-1">' +
              data.message +
              "</span>" +
              '<button type="button" class="btn-close ms-auto" aria-label="Close" onclick="document.getElementById(\'otpStatus\').style.display=\'none\'"></button>';
            this.innerHTML = '<i class="fas fa-check me-1"></i>Xác thực';
            this.disabled = false;

            // Auto close after 3 seconds
            const autoCloseTimer = setTimeout(() => {
              if (statusDiv && statusDiv.parentNode) {
                statusDiv.classList.remove("show");
                setTimeout(() => {
                  if (statusDiv.parentNode) {
                    statusDiv.style.display = "none";
                  }
                }, 150); // Fade out animation
              }
            }, 3000);

            // Clear timer when close button is clicked
            const closeBtn = statusDiv.querySelector(".btn-close");
            if (closeBtn) {
              closeBtn.addEventListener("click", function () {
                clearTimeout(autoCloseTimer);
              });
            }
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

      // Save current step to sessionStorage before submitting
      // This will be used to restore the step if there's an error from backend
      sessionStorage.setItem("registerFormStep", currentStep.toString());
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

  // Check step 2 completion for next button
  if (typeof checkStep2Completion === "function") {
    checkStep2Completion();
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

  // Check step 2 completion for next button
  if (typeof checkStep2Completion === "function") {
    checkStep2Completion();
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
  // Load saved step and OTP status from sessionStorage
  loadSavedStep();
  restoreOtpStatus();

  // Auto-capitalize name
  autoCapitalizeName();

  // Add validation for date of birth
  const dateInput = document.getElementById("ngay_sinh");
  if (dateInput) {
    dateInput.addEventListener("change", function () {
      validateDateOfBirth();
      checkStep3Completion();
    });
    dateInput.addEventListener("input", function () {
      validateDateOfBirth();
      checkStep3Completion();
    });
  }

  // Add validation for name
  const nameInput = document.getElementById("ten");
  if (nameInput) {
    nameInput.addEventListener("input", function () {
      validateName();
      checkStep3Completion();
    });
    nameInput.addEventListener("blur", function () {
      validateName();
      checkStep3Completion();
    });
  }

  // Add validation for email
  const emailInput = document.getElementById("email");
  if (emailInput) {
    emailInput.addEventListener("input", function () {
      validateEmail();
      checkStep3Completion();
    });
    emailInput.addEventListener("blur", function () {
      validateEmail();
      checkStep3Completion();
    });
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

  // ========================================
  // CCCD VALIDATION & VERIFICATION
  // ========================================

  const cccdInput = document.getElementById("cccd");
  const cccdResultDiv = document.getElementById("cccd-verification-result");
  let cccdVerified = false;

  if (cccdInput) {
    // Format CCCD input (chỉ cho phép số)
    cccdInput.addEventListener("input", function (e) {
      this.value = this.value.replace(/\D/g, ""); // Chỉ giữ lại số

      // Clear verification result khi user thay đổi
      cccdVerified = false;
      if (cccdResultDiv) {
        cccdResultDiv.innerHTML = "";
      }

      // Unlock all auto-filled fields when CCCD changes
      unlockCCCDFields();
    });

    // Verify CCCD khi blur (rời khỏi input)
    cccdInput.addEventListener("blur", async function () {
      const cccdValue = this.value.trim();

      if (cccdValue.length === 0) {
        cccdResultDiv.innerHTML = "";
        return;
      }

      if (cccdValue.length !== 12) {
        showCCCDResult("error", "CCCD phải có đúng 12 số");
        return;
      }

      // Show loading
      showCCCDResult(
        "info",
        '<i class="fas fa-spinner fa-spin"></i> Đang xác thực CCCD...'
      );

      // Get other form data for verification
      const ten = document.getElementById("name")?.value.trim() || "";
      const ngaySinh = document.getElementById("ngay_sinh")?.value || "";

      try {
        const response = await fetch("./verify_cccd", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: new URLSearchParams({
            cccd: cccdValue,
            ten: ten,
            ngay_sinh: ngaySinh,
          }),
        });

        const result = await response.json();

        if (result.success && result.verified) {
          cccdVerified = true;
          showCCCDResult(
            "success",
            '<i class="fas fa-check-circle"></i> ' + result.message
          );

          // Check step 3 completion after CCCD is verified
          if (typeof checkStep3Completion === "function") {
            setTimeout(function () {
              checkStep3Completion();
            }, 300);
          }

          // Auto fill data if available
          if (result.cccd_data) {
            // Fill Họ tên
            if (result.cccd_data.ten) {
              const nameInput = document.getElementById("name");
              if (nameInput && !nameInput.value) {
                nameInput.value = result.cccd_data.ten;
              }
              // Lock field
              if (nameInput) {
                nameInput.readOnly = true;
                nameInput.classList.add("cccd-locked");
              }
            }

            // Fill Ngày sinh
            if (result.cccd_data.ngay_sinh) {
              const dobInput = document.getElementById("ngay_sinh");
              if (dobInput && !dobInput.value) {
                dobInput.value = result.cccd_data.ngay_sinh;
              }
              // Lock field
              if (dobInput) {
                dobInput.readOnly = true;
                dobInput.classList.add("cccd-locked");
              }
            }

            // Fill Giới tính
            if (result.cccd_data.gioi_tinh) {
              const genderInput = document.getElementById("gioi_tinh");
              if (genderInput) {
                // Map incoming value to server enum and display text
                const raw = String(result.cccd_data.gioi_tinh).trim();
                const toEnum = (v) => {
                  const s = String(v || "")
                    .trim()
                    .toLowerCase();
                  if (s === "nữ" || s === "nu") return "Nu";
                  if (s === "khác" || s === "khac") return "Khac";
                  if (s === "nam" || s === "male") return "Nam";
                  // Fallback: return as-is but capitalized first letter
                  return v.charAt(0).toUpperCase() + v.slice(1);
                };
                const toDisplay = (enumVal) => {
                  if (enumVal === "Nu") return "Nữ";
                  if (enumVal === "Khac") return "Khác";
                  return "Nam";
                };

                const enumVal = toEnum(raw);
                const displayText = toDisplay(enumVal);

                // Try to find a matching option by value or by display text
                let matched = false;
                for (let i = 0; i < genderInput.options.length; i++) {
                  const opt = genderInput.options[i];
                  if (
                    opt.value === enumVal ||
                    opt.text.trim().toLowerCase() ===
                      displayText.toLowerCase() ||
                    opt.text.trim().toLowerCase() === enumVal.toLowerCase()
                  ) {
                    genderInput.value = opt.value;
                    matched = true;
                    break;
                  }
                }

                // If we didn't match an existing option, create/update a temporary option
                if (!matched) {
                  let temp = genderInput.querySelector(
                    'option[data-temp="true"]'
                  );
                  if (!temp) {
                    temp = document.createElement("option");
                    temp.setAttribute("data-temp", "true");
                    genderInput.appendChild(temp);
                  }
                  temp.value = enumVal;
                  temp.text = displayText;
                  genderInput.value = enumVal;
                }

                // Lock field visually and create hidden input so value is submitted
                genderInput.classList.add("cccd-locked");
                const form =
                  genderInput.closest("form") || document.querySelector("form");
                if (form) {
                  const hiddenId = "gioi_tinh_hidden";
                  let hidden = document.getElementById(hiddenId);
                  if (!hidden) {
                    hidden = document.createElement("input");
                    hidden.type = "hidden";
                    hidden.id = hiddenId;
                    hidden.name = genderInput.name || "gioi_tinh";
                    form.appendChild(hidden);
                  }
                  // Hidden carries server enum value
                  hidden.value = enumVal;
                }

                // Disable the visible select for UX
                genderInput.disabled = true;
              }
            }

            // Fill Địa chỉ
            if (result.cccd_data.dia_chi) {
              const addressInput = document.getElementById("dia_chi");
              if (addressInput && !addressInput.value) {
                addressInput.value = result.cccd_data.dia_chi;
              }
              // Lock field
              if (addressInput) {
                addressInput.readOnly = true;
                addressInput.classList.add("cccd-locked");
              }
            }

            // Show success message with filled info
            showCCCDResult(
              "success",
              '<i class="fas fa-check-circle"></i> Xác thực thành công!'
            );
          }
        } else {
          cccdVerified = false;
          let errorMsg = result.message || "CCCD không hợp lệ";
          if (result.suggestion) {
            errorMsg += "<br><small>" + result.suggestion + "</small>";
          }
          showCCCDResult(
            "error",
            '<i class="fas fa-times-circle"></i> ' + errorMsg
          );
        }
      } catch (error) {
        cccdVerified = false;
        showCCCDResult(
          "error",
          '<i class="fas fa-exclamation-triangle"></i> Lỗi kết nối. Vui lòng thử lại!'
        );
      }
    });
  }

  function showCCCDResult(type, message) {
    if (!cccdResultDiv) return;

    let className = "";
    switch (type) {
      case "success":
        className = "alert alert-success";
        break;
      case "error":
        className = "alert alert-danger";
        break;
      case "info":
        className = "alert alert-info";
        break;
    }

    // Generate unique ID for this alert
    const alertId = "cccd-alert-" + Date.now();

    // Create alert with close button - use flexbox to position close button at end
    const alertHTML = `
      <div id="${alertId}" class="${className} alert-dismissible fade show d-flex align-items-center py-2 px-3 mb-0">
        <span class="flex-grow-1">${message}</span>
        <button type="button" class="btn-close ms-auto" aria-label="Close" onclick="document.getElementById('${alertId}').remove()"></button>
      </div>
    `;

    cccdResultDiv.innerHTML = alertHTML;

    // Auto close after 3 seconds (skip for info/loading messages)
    if (type !== "info") {
      const autoCloseTimer = setTimeout(() => {
        const alertElement = document.getElementById(alertId);
        if (alertElement && alertElement.parentNode) {
          alertElement.classList.remove("show");
          setTimeout(() => {
            if (alertElement.parentNode) {
              alertElement.remove();
            }
          }, 150); // Fade out animation
        }
      }, 3000);

      // Clear timer when close button is clicked
      const closeBtn = document
        .getElementById(alertId)
        ?.querySelector(".btn-close");
      if (closeBtn) {
        closeBtn.addEventListener("click", function () {
          clearTimeout(autoCloseTimer);
        });
      }
    }
  }

  // Function to unlock CCCD-filled fields
  function unlockCCCDFields() {
    const nameInput = document.getElementById("name");
    const dobInput = document.getElementById("ngay_sinh");
    const genderInput = document.getElementById("gioi_tinh");
    const addressInput = document.getElementById("dia_chi");

    if (nameInput) {
      nameInput.readOnly = false;
      nameInput.classList.remove("cccd-locked");
    }
    if (dobInput) {
      dobInput.readOnly = false;
      dobInput.classList.remove("cccd-locked");
    }
    if (genderInput) {
      genderInput.disabled = false;
      genderInput.classList.remove("cccd-locked");
      // Remove hidden field used to carry value when select was disabled
      const hidden = document.getElementById("gioi_tinh_hidden");
      if (hidden && hidden.parentNode) {
        hidden.parentNode.removeChild(hidden);
      }
      // Remove any temporary option created for display
      const tempOpt = genderInput.querySelector('option[data-temp="true"]');
      if (tempOpt && tempOpt.parentNode) {
        tempOpt.parentNode.removeChild(tempOpt);
      }
    }
    if (addressInput) {
      addressInput.readOnly = false;
      addressInput.classList.remove("cccd-locked");
    }
  }

  // Validate CCCD before form submit
  const registerForm = document.getElementById("registerForm");
  if (registerForm) {
    registerForm.addEventListener("submit", function (e) {
      const cccdValue = cccdInput?.value.trim();

      if (cccdValue && !cccdVerified) {
        e.preventDefault();
        alert("Vui lòng chờ xác thực CCCD hoàn tất!");
        return false;
      }
    });
  }
});
