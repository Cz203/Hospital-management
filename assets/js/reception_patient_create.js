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
              if (genderInput) {
                // Set giá trị giới tính từ CCCD data
                // CCCD service trả về 'Nam' hoặc 'Nữ' (khớp với option trong select)
                const genderValue = String(result.cccd_data.gioi_tinh).trim();

                console.log("CCCD Gender Value:", genderValue); // Debug log
                console.log(
                  "Current gender input value before set:",
                  genderInput.value
                ); // Debug log

                // LUÔN set giá trị từ CCCD (không check điều kiện)
                // Đảm bảo giá trị khớp với option trong select
                // Form có: 'Nam', 'Nữ', 'Khác'
                let setValue = null;
                if (
                  genderValue === "Nam" ||
                  genderValue === "Nữ" ||
                  genderValue === "Khác"
                ) {
                  setValue = genderValue;
                } else {
                  // Nếu giá trị không khớp, thử tìm option tương ứng
                  // (fallback cho trường hợp có giá trị khác)
                  for (let option of genderInput.options) {
                    if (
                      option.text.trim() === genderValue ||
                      option.value === genderValue
                    ) {
                      setValue = option.value;
                      console.log("Matched gender option:", setValue); // Debug log
                      break;
                    }
                  }
                }

                // Set giá trị nếu tìm thấy
                if (setValue) {
                  genderInput.value = setValue;
                  console.log(
                    "Set gender to:",
                    setValue,
                    "Current value after set:",
                    genderInput.value
                  ); // Debug log
                } else {
                  console.warn(
                    "No matching gender option found for:",
                    genderValue
                  ); // Debug log
                }

                // KHÔNG disable field để giá trị được gửi đi khi submit
                // Chỉ thêm class để style (visual indicator)
                genderInput.classList.add("cccd-locked");
                // Thêm attribute để đánh dấu là field từ CCCD
                genderInput.setAttribute("data-cccd-filled", "true");
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

          // Xử lý BHYT - luôn clear trước khi set lại
          if (bhytBox && bhytIdInput && bhytCodeInput) {
            if (result.bao_hiem_y_te) {
              const bh = result.bao_hiem_y_te;
              // Set giá trị BHYT
              bhytIdInput.value = bh.id || "";
              bhytCodeInput.value = bh.ma_bao_hiem || "";

              if (bhytMaSpan)
                bhytMaSpan.textContent = bh.ma_bao_hiem || "Không có";

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
              console.log("BHYT found and set:", {
                id: bhytIdInput.value,
                code: bhytCodeInput.value,
              });
            } else {
              // Không có BHYT -> clear & ẩn box
              clearBHYTFields();
            }
          }
        } else {
          cccdVerified = false;
          // Clear BHYT fields khi verify thất bại
          clearBHYTFields();

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
        // Clear BHYT fields khi có lỗi
        clearBHYTFields();
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

      // Debug: Log giá trị giới tính trước khi submit
      const genderInput = document.getElementById("gioi_tinh");
      if (genderInput) {
        console.log(
          "Before submit - Gender value:",
          genderInput.value,
          "Disabled:",
          genderInput.disabled
        ); // Debug log

        // QUAN TRỌNG: Enable lại các field bị disabled để giá trị được gửi đi
        // Các field disabled sẽ không được gửi trong form submission
        if (genderInput.disabled) {
          genderInput.disabled = false;
          console.log("Enabled gender input before submit"); // Debug log
        }
      }

      // Đảm bảo BHYT fields được xử lý đúng khi submit
      const bhytIdInput = document.getElementById("bao_hiem_y_te_id");
      const bhytCodeInput = document.getElementById("bao_hiem_y_te");

      // Nếu không có BHYT (box ẩn), đảm bảo các hidden input là rỗng
      const bhytBox = document.getElementById("bhyt-info-box");
      if (bhytBox && bhytBox.classList.contains("d-none")) {
        if (bhytIdInput) bhytIdInput.value = "";
        if (bhytCodeInput) bhytCodeInput.value = "";
        console.log("BHYT box hidden - cleared BHYT fields before submit");
      }

      // Log giá trị BHYT trước khi submit để debug
      console.log("Before submit - BHYT values:", {
        id: bhytIdInput ? bhytIdInput.value : "N/A",
        code: bhytCodeInput ? bhytCodeInput.value : "N/A",
      });
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
   * Clear BHYT fields
   */
  function clearBHYTFields() {
    const bhytBox = document.getElementById("bhyt-info-box");
    const bhytIdInput = document.getElementById("bao_hiem_y_te_id");
    const bhytCodeInput = document.getElementById("bao_hiem_y_te");
    const bhytMaSpan = document.getElementById("bhyt_ma");
    const bhytTrangThaiSpan = document.getElementById("bhyt_trang_thai");
    const bhytHanSpan = document.getElementById("bhyt_han");
    const bhytHuongMucSpan = document.getElementById("bhyt_huong_muc");

    if (bhytIdInput) bhytIdInput.value = "";
    if (bhytCodeInput) bhytCodeInput.value = "";
    if (bhytMaSpan) bhytMaSpan.textContent = "";
    if (bhytTrangThaiSpan) bhytTrangThaiSpan.textContent = "";
    if (bhytHanSpan) bhytHanSpan.textContent = "";
    if (bhytHuongMucSpan) bhytHuongMucSpan.textContent = "";
    if (bhytBox) bhytBox.classList.add("d-none");

    console.log("BHYT fields cleared");
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
      // Không cần enable vì không disable nữa
      genderInput.classList.remove("cccd-locked");
      genderInput.removeAttribute("data-cccd-filled");
    }
    if (addressInput) {
      addressInput.readOnly = false;
      addressInput.classList.remove("cccd-locked");
    }

    // Clear BHYT fields khi unlock (người dùng thay đổi CCCD)
    clearBHYTFields();
  }
});
