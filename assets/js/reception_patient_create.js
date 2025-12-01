/**
 * Reception Patient Create - CCCD Verification
 */

document.addEventListener("DOMContentLoaded", function () {
  const cccdInput = document.getElementById("cccd");
  const form = document.getElementById("patientCreateForm");
  let cccdVerified = false;

  // Format CCCD input - only allow numbers
  if (cccdInput) {
    cccdInput.addEventListener("input", function (e) {
      this.value = this.value.replace(/\D/g, "");

      // Unlock all auto-filled fields when CCCD changes
      unlockCCCDFields();
    });

    // Verify CCCD when user leaves the field
    cccdInput.addEventListener("blur", async function () {
      const cccdValue = this.value.trim();

      // Reset verification status
      cccdVerified = false;

      // Skip if empty or invalid length
      if (!cccdValue || cccdValue.length !== 12) {
        if (cccdValue && cccdValue.length !== 12) {
          showCCCDResult(
            "warning",
            '<i class="fas fa-exclamation-triangle"></i> CCCD phải có đúng 12 số'
          );
        }
        return;
      }

      // Show loading
      showCCCDResult(
        "info",
        '<i class="fas fa-spinner fa-spin"></i> Đang xác thực CCCD...'
      );

      // Get other form data for verification
      const ten = document.getElementById("ten")?.value.trim() || "";
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

          // Auto fill data if available
          if (result.cccd_data) {
            // Fill Họ tên
            if (result.cccd_data.ten) {
              const nameInput = document.getElementById("ten");
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
              if (genderInput && !genderInput.value) {
                genderInput.value = result.cccd_data.gioi_tinh;
              }
              // Lock field (disabled for select)
              if (genderInput) {
                genderInput.disabled = true;
                genderInput.classList.add("cccd-locked");
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

          // Fill thông tin bảo hiểm y tế nếu có
          const bhytBox = document.getElementById("bhyt-info-box");
          const bhytIdInput = document.getElementById("bao_hiem_y_te_id");
          const bhytCodeInput = document.getElementById("bao_hiem_y_te");
          const bhytMaSpan = document.getElementById("bhyt_ma");
          const bhytTrangThaiSpan = document.getElementById("bhyt_trang_thai");
          const bhytHanSpan = document.getElementById("bhyt_han");
          const bhytHuongMucSpan = document.getElementById("bhyt_huong_muc");

          if (bhytBox && bhytIdInput && bhytCodeInput) {
            if (result.bao_hiem_y_te) {
              const bh = result.bao_hiem_y_te;
              bhytIdInput.value = bh.id || "";
              bhytCodeInput.value = bh.ma_bao_hiem || "";

              if (bhytMaSpan) bhytMaSpan.textContent = bh.ma_bao_hiem || "Không có";

              if (bhytTrangThaiSpan) {
                const statusText =
                  bh.con_han === true
                    ? "Còn hiệu lực"
                    : "Hết hạn hoặc không hiệu lực";
                bhytTrangThaiSpan.textContent = statusText;
              }

              if (bhytHanSpan) {
                const han =
                  (bh.ngay_bat_dau || "") +
                  " - " +
                  (bh.ngay_het_han || "Không rõ");
                bhytHanSpan.textContent = han;
              }

              if (bhytHuongMucSpan) {
                const percent =
                  typeof bh.huong_muc === "number"
                    ? Math.round(bh.huong_muc * 100) + "%"
                    : "";
                bhytHuongMucSpan.textContent = percent || "Không rõ";
              }

              bhytBox.classList.remove("d-none");
            } else {
              // Không có BHYT -> clear & ẩn box
              bhytIdInput.value = "";
              bhytCodeInput.value = "";
              if (bhytMaSpan) bhytMaSpan.textContent = "";
              if (bhytTrangThaiSpan) bhytTrangThaiSpan.textContent = "";
              if (bhytHanSpan) bhytHanSpan.textContent = "";
              if (bhytHuongMucSpan) bhytHuongMucSpan.textContent = "";
              bhytBox.classList.add("d-none");
            }
          }
        } else {
          cccdVerified = false;
          let errorMsg = result.message || "CCCD không hợp lệ";
          if (result.suggestion) {
            errorMsg += "<br><small>" + result.suggestion + "</small>";
          }
          showCCCDResult(
            "danger",
            '<i class="fas fa-times-circle"></i> ' + errorMsg
          );
        }
      } catch (error) {
        console.error("Error verifying CCCD:", error);
        showCCCDResult(
          "danger",
          '<i class="fas fa-times-circle"></i> Lỗi khi xác thực CCCD. Vui lòng thử lại.'
        );
      }
    });
  }

  // Form submit validation
  if (form) {
    form.addEventListener("submit", function (e) {
      const cccdValue = cccdInput?.value.trim();

      // If CCCD is entered but not verified yet, prevent submit
      if (cccdValue && cccdValue.length === 12 && !cccdVerified) {
        e.preventDefault();
        showCCCDResult(
          "warning",
          '<i class="fas fa-exclamation-triangle"></i> Vui lòng chờ xác thực CCCD hoàn tất hoặc nhấp ra ngoài ô CCCD để kích hoạt xác thực.'
        );
        cccdInput.focus();
        return false;
      }
    });
  }

  /**
   * Show CCCD verification result
   */
  function showCCCDResult(type, message) {
    const resultDiv = document.getElementById("cccd-verification-result");
    if (!resultDiv) return;

    const alertClass = `alert alert-${type} alert-dismissible fade show`;
    resultDiv.innerHTML = `
            <div class="${alertClass}" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
  }

  /**
   * Unlock CCCD-filled fields
   */
  function unlockCCCDFields() {
    const nameInput = document.getElementById("ten");
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
    }
    if (addressInput) {
      addressInput.readOnly = false;
      addressInput.classList.remove("cccd-locked");
    }
  }
});
