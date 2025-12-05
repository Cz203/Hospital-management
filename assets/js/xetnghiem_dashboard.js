// Load dashboard data
document.addEventListener("DOMContentLoaded", function() {
    loadLabDashboardData();
    
    // Set default date to today
    const today = new Date().toISOString().split("T")[0];
    document.getElementById("xetnghiem_date").value = today;
    
    // Load xetnghiem requests for today on page load
    loadXetnghiemRequests();
    
    // Add event listener for filter button
    document.getElementById("xetnghiem_filter_btn").addEventListener("click", loadXetnghiemRequests);
    
    // Event listeners for test result modal
    // Note: addTestRow button has been removed as forms are now fixed
    
    // Save test result button
    document.getElementById("saveTestResult").addEventListener("click", saveTestResult);
    
    // Print test result button
    document.getElementById("printTestResult").addEventListener("click", printTestResult);
    
    // Complete test result button
    document.getElementById("completeTestResult").addEventListener("click", completeTestResult);
});

function loadLabDashboardData(selectedDate = null) {
    // Use selected date or current date
    const date = selectedDate || new Date().toISOString().split("T")[0];
    
    // Load stats with date parameter
    fetch("./?action=get_lab_dashboard_stats&date=" + date)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("stat_total_today").textContent = data.stats.today || 0;
                document.getElementById("stat_completed").textContent = data.stats.completed || 0;
                document.getElementById("stat_pending").textContent = data.stats.pending || 0;
            }
        })
        .catch(error => console.error("Error loading stats:", error));
}

// Load xetnghiem requests (Đã yêu cầu)
function loadXetnghiemRequests() {
    const today = new Date().toISOString().split("T")[0];
    const date = document.getElementById("xetnghiem_date").value || today;
    const name = document.getElementById("xetnghiem_name").value;
    
    fetch("./?action=get_xetnghiem_requests&date=" + date + "&name=" + encodeURIComponent(name))
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector("#xetnghiemRequestedTable tbody");
            
            if (data.success && data.requests && data.requests.length > 0) {
                tbody.innerHTML = data.requests.map(req => 
                    "<tr>" +
                        "<td class=\"text-center\">" + (req.id || "") + "</td>" +
                        "<td class=\"text-center\">" + (req.ma_benh_nhan || "") + "</td>" +
                        "<td>" + (req.ho_ten || "") + "</td>" +
                        "<td class=\"text-center\">" + (req.tuoi || "") + "</td>" +
                        "<td class=\"text-center\">" + (req.gioi_tinh || "") + "</td>" +
                        "<td>" + (req.yeu_cau || "") + "</td>" +
                        "<td>" + formatDateTime(req.ngay_cap_nhat || req.ngay_tao) + "</td>" +
                        "<td class=\"text-center\">" +
                            "<span class=\"badge " + getXetnghiemStatusBadge(req.trang_thai) + "\">" +
                                (req.trang_thai || "Đã yêu cầu") +
                            "</span>" +
                        "</td>" +
                        "<td class=\"text-center\">" +
                            "<button class=\"btn btn-info btn-sm\" onclick=\"viewXetnghiemDetail(" + req.id + ")\">" +
                                "<i class=\"fas fa-eye me-1\"></i>Xem" +
                            "</button>" +
                        "</td>" +
                        "<td class=\"text-center\">" +
                            "<button class=\"btn btn-success btn-sm\" onclick=\"returnXetnghiemResult(" + req.id + ")\">" +
                                "<i class=\"fas fa-reply me-1\"></i>Trả kết quả" +
                            "</button>" +
                        "</td>" +
                    "</tr>"
                ).join("");
            } else {
                tbody.innerHTML = "<tr><td colspan=\"10\" class=\"text-center text-muted\">Không có dữ liệu</td></tr>";
            }
        })
        .catch(error => {
            console.error("Error loading xetnghiem requests:", error);
            document.querySelector("#xetnghiemRequestedTable tbody").innerHTML = 
                "<tr><td colspan=\"10\" class=\"text-center text-danger\">Lỗi tải dữ liệu</td></tr>";
        })
        .finally(() => {
            // Update dashboard stats with the selected date
            loadLabDashboardData(date);
        });
}

function getXetnghiemStatusBadge(status) {
    switch(status) {
        case "Đã yêu cầu": return "bg-warning";
        case "Hoàn thành": return "bg-success";
        case "Đã thanh toán": return "bg-info";
        default: return "bg-secondary";
    }
}

function viewXetnghiemDetail(id) {
    fetch("./?action=get_xetnghiem_detail&id=" + id)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.result) {
                const result = data.result;
                
                // Fill modal fields similar to sieuam
                document.getElementById("xn_clinic_name").value = "PHÒNG KHÁM ĐA KHOA THINHVIET";
                document.getElementById("xn_phone").value = "0777871608";
                document.getElementById("xn_quan").value = "Gò Vấp";
                document.getElementById("xn_patient_name").value = result.ho_ten || "";
                document.getElementById("xn_patient_age").value = result.tuoi || "";
                document.getElementById("xn_patient_gender").value = result.gioi_tinh || "";
                document.getElementById("xn_address").value = result.dia_chi || "Gò Vấp";
                document.getElementById("xn_patient_type").value = result.doi_tuong || "";
                document.getElementById("xn_insurance_number").value = result.so_the_bhyt || "";
                document.getElementById("xn_diagnosis").value = result.chan_doan || "";
                document.getElementById("xn_request").value = result.yeu_cau || "";
                
                // Set date
                const date = new Date(result.ngay_cap_nhat || result.ngay_tao || Date.now());
                const timeStr = date.toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit", second: "2-digit" });
                const timeInput = document.getElementById("xn_order_time");
                if (timeInput) {
                    timeInput.value = timeStr;
                }
                document.getElementById("xn_day").value = date.getDate().toString().padStart(2, "0");
                document.getElementById("xn_month").value = (date.getMonth() + 1).toString().padStart(2, "0");
                document.getElementById("xn_year").value = date.getFullYear();
                document.getElementById("xn_doctor").value = result.bac_si_kham || "";
                
                new bootstrap.Modal(document.getElementById("xetnghiemDetailModal")).show();
            } else {
                alert("Không tìm thấy thông tin xét nghiệm");
            }
        })
        .catch(error => {
            console.error("Error loading xetnghiem detail:", error);
            alert("Lỗi tải dữ liệu");
        });
}

function returnXetnghiemResult(id) {
    // Load patient data and show return result modal
    fetch("./?action=get_xetnghiem_result&id=" + id)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.result) {
                const result = data.result;
                
                // Fill patient information
                document.getElementById("result_patient_id").value = result.ma_benh_nhan || "";
                document.getElementById("result_patient_name").value = result.ho_ten || "";
                document.getElementById("result_patient_address").value = result.dia_chi || "";
                document.getElementById("result_diagnosis").value = result.chan_doan || "";
                document.getElementById("result_patient_age").value = result.tuoi || "";
                document.getElementById("result_patient_gender").value = result.gioi_tinh || "";
                document.getElementById("result_requesting_doctor").value = result.bac_si_yeu_cau || result.bac_si_kham || "";
                
                // Set registration date and time
                const date = new Date(result.ngay_cap_nhat || result.ngay_tao || Date.now());
                document.getElementById("result_reg_date").textContent = date.toLocaleDateString("vi-VN");
                document.getElementById("result_reg_time").textContent = date.toLocaleTimeString("vi-VN", {hour: "2-digit", minute: "2-digit"});
                
                // Set current date for result
                const today = new Date();
                document.getElementById("result_day").value = today.getDate().toString().padStart(2, "0");
                document.getElementById("result_month").value = (today.getMonth() + 1).toString().padStart(2, "0");
                document.getElementById("result_year").value = today.getFullYear();
                
                // Fill sample status if saved
                document.getElementById("result_sample_status").value = result.tinh_trang_mau || "";
                
                // Fill sample location if saved
                document.getElementById("result_sample_location").value = result.vi_tri_lay_mau || "";
                
                // Set test section title from yeu_cau (convert to uppercase)
                const yeuCauText = result.yeu_cau || "XÉT NGHIỆM MÁU - NƯỚC TIỂU - PHÂN";
                document.getElementById("test_section_title").textContent = yeuCauText.toUpperCase();
                
                // Get form type and chi so list
                const formType = data.form_type || "other";
                const chiSoList = data.chi_so_list || [];
                
                // Hide all form templates
                document.querySelectorAll(".test-form-template").forEach(template => {
                    template.style.display = "none";
                });
                
                // Convert form_type to template ID format (mau_toan_phan -> MauToanPhan)
                const formTemplateIdParts = formType.split("_").map(part => 
                    part.charAt(0).toUpperCase() + part.slice(1)
                );
                const formTemplateId = "form" + formTemplateIdParts.join("");
                
                // Show the appropriate form template
                const formTemplate = document.getElementById(formTemplateId);
                if (formTemplate) {
                    formTemplate.style.display = "block";
                } else {
                    // Fallback to "other" form
                    document.getElementById("formOther").style.display = "block";
                }
                
                // Get the appropriate tbody ID (same format)
                const tbodyId = "testResultsBody" + formTemplateIdParts.join("");
                const tbody = document.getElementById(tbodyId);
                
                if (tbody && chiSoList.length > 0) {
                    // Clear previous rows
                    tbody.innerHTML = "";
                    
                    // Create a map of saved results by test name
                    const savedResultsMap = {};
                    if (data.is_saved && data.test_details && data.test_details.length > 0) {
                        data.test_details.forEach(test => {
                            savedResultsMap[test.ten_xet_nghiem] = test;
                        });
                    }
                    
                    // Add header rows for "mau_toan_phan" form
                    if (formType === "mau_toan_phan") {
                        // Row 1: XN Huyết học (merge all 6 columns)
                        const headerRow1 = document.createElement("tr");
                        headerRow1.className = "fw-bold";
                        headerRow1.style.backgroundColor = "#f8f9fa";
                        headerRow1.innerHTML = 
                            "<td class=\"fw-bold text-start\" colspan=\"6\">" +
                                "<span>XN Huyết học</span>" +
                            "</td>";
                        tbody.appendChild(headerRow1);
                        
                        // Row 2: TPT tế bào máu(máy đếm larser) (merge all 6 columns)
                        const headerRow2 = document.createElement("tr");
                        headerRow2.className = "fw-bold";
                        headerRow2.style.backgroundColor = "#f8f9fa";
                        headerRow2.innerHTML = 
                            "<td class=\"fw-bold text-start\" colspan=\"6\">" +
                                "<span>TPT tế bào máu(máy đếm larser)</span>" +
                            "</td>";
                        tbody.appendChild(headerRow2);
                    }
                    
                    // Add header row for "mau_nuoc_tieu" form
                    if (formType === "mau_nuoc_tieu") {
                        // Row: Sinh Hóa (merge all 6 columns)
                        const headerRow = document.createElement("tr");
                        headerRow.className = "fw-bold";
                        headerRow.style.backgroundColor = "#f8f9fa";
                        headerRow.innerHTML = 
                            "<td class=\"fw-bold text-start\" colspan=\"6\">" +
                                "<span>Sinh Hóa</span>" +
                            "</td>";
                        tbody.appendChild(headerRow);
                    }
                    
                    // Render rows from chi so list
                    chiSoList.forEach((chiSo, index) => {
                        const savedResult = savedResultsMap[chiSo.xet_nghiem] || null;
                        
                        // STT starts from 1 for test results (header rows do not have STT)
                        const stt = index + 1;
                        
                        // Check if reference value contains "Âm tính" (case insensitive)
                        const referenceValue = (chiSo.gia_tri_tham_chieu || "").trim();
                        const isAmTinhReference = referenceValue.toLowerCase().indexOf("âm tính") !== -1;
                        
                        // Determine result value
                        const savedResultValue = savedResult ? savedResult.ket_qua : "";
                        const isDuongTinh = savedResultValue.trim().toLowerCase() === "dương tính" || savedResultValue.trim().toLowerCase() === "duong tinh";
                        
                        // Build result cell HTML
                        let resultCellHTML = "";
                        if (isAmTinhReference) {
                            // Use select for "Âm tính" reference
                            const amTinhSelected = savedResultValue.trim().toLowerCase() === "âm tính" || savedResultValue.trim().toLowerCase() === "am tinh" || (!savedResultValue.trim());
                            const duongTinhSelected = isDuongTinh;
                            resultCellHTML = 
                                "<select class=\"form-control form-control-sm result-input" + (isDuongTinh ? " fw-bold" : "") + "\" " +
                                    "style=\"" + (isDuongTinh ? "font-weight: bold; color: #dc3545; text-align: right;" : "") + "\">" +
                                    "<option value=\"\" " + (!amTinhSelected && !duongTinhSelected ? "selected" : "") + ">-- Chọn --</option>" +
                                    "<option value=\"Âm tính\" " + (amTinhSelected ? "selected" : "") + ">Âm tính</option>" +
                                    "<option value=\"Dương tính\" " + (duongTinhSelected ? "selected" : "") + ">Dương tính</option>" +
                                "</select>";
                        } else {
                            // Use input text for other references
                            resultCellHTML = 
                                "<input type=\"text\" class=\"form-control form-control-sm result-input\" value=\"" + savedResultValue + "\" placeholder=\"Nhập kết quả...\">";
                        }
                        
                        const row = document.createElement("tr");
                        row.innerHTML = 
                            "<td class=\"text-center\">" +
                                "<span>" + stt + "</span>" +
                            "</td>" +
                            "<td>" +
                                "<input type=\"hidden\" class=\"chi-so-id\" value=\"" + (chiSo.id || "") + "\">" +
                                "<input type=\"text\" class=\"form-control form-control-sm test-name-input\" value=\"" + (chiSo.xet_nghiem || "") + "\" readonly>" +
                            "</td>" +
                            "<td>" +
                                "<input type=\"text\" class=\"form-control form-control-sm\" value=\"" + (chiSo.gia_tri_tham_chieu || "") + "\" readonly>" +
                            "</td>" +
                            "<td>" +
                                resultCellHTML +
                            "</td>" +
                            "<td>" +
                                "<input type=\"text\" class=\"form-control form-control-sm\" value=\"" + (chiSo.don_vi || "") + "\" readonly>" +
                            "</td>" +
                            "<td>" +
                                "<input type=\"text\" class=\"form-control form-control-sm\" value=\"" + (chiSo.may_qtkt || "") + "\" readonly>" +
                            "</td>";
                        tbody.appendChild(row);
                        
                        // Add change listener for select to update styling when "Dương tính" is selected
                        if (isAmTinhReference) {
                            const resultSelect = row.querySelector(".result-input");
                            if (resultSelect) {
                                resultSelect.addEventListener("change", function() {
                                    if (this.value === "Dương tính") {
                                        this.classList.add("fw-bold");
                                        this.style.fontWeight = "bold";
                                        this.style.color = "#dc3545";
                                        this.style.textAlign = "right";
                                    } else {
                                        this.classList.remove("fw-bold");
                                        this.style.fontWeight = "";
                                        this.style.color = "";
                                        this.style.textAlign = "";
                                    }
                                });
                            }
                        }
                        
                        // Add header row "Miễn dịch" after STT 12 for "mau_nuoc_tieu" form
                        if (formType === "mau_nuoc_tieu" && stt === 12) {
                            const mienDichHeaderRow = document.createElement("tr");
                            mienDichHeaderRow.className = "fw-bold";
                            mienDichHeaderRow.style.backgroundColor = "#f8f9fa";
                            mienDichHeaderRow.innerHTML = 
                                "<td class=\"fw-bold text-start\" colspan=\"6\">" +
                                    "<span>Miễn dịch</span>" +
                                "</td>";
                            tbody.appendChild(mienDichHeaderRow);
                        }
                        
                        // Add header rows "Nước tiểu" and "Nước tiểu 10 thông số" after STT 14 for "mau_nuoc_tieu" form
                        if (formType === "mau_nuoc_tieu" && stt === 14) {
                            // Row 1: Nước tiểu
                            const nuocTieuHeaderRow1 = document.createElement("tr");
                            nuocTieuHeaderRow1.className = "fw-bold";
                            nuocTieuHeaderRow1.style.backgroundColor = "#f8f9fa";
                            nuocTieuHeaderRow1.innerHTML = 
                                "<td class=\"fw-bold text-start\" colspan=\"6\">" +
                                    "<span>Nước tiểu</span>" +
                                "</td>";
                            tbody.appendChild(nuocTieuHeaderRow1);
                            
                            // Row 2: Nước tiểu 10 thông số
                            const nuocTieuHeaderRow2 = document.createElement("tr");
                            nuocTieuHeaderRow2.className = "fw-bold";
                            nuocTieuHeaderRow2.style.backgroundColor = "#f8f9fa";
                            nuocTieuHeaderRow2.innerHTML = 
                                "<td class=\"fw-bold text-start\" colspan=\"6\">" +
                                    "<span>Nước tiểu 10 thông số</span>" +
                                "</td>";
                            tbody.appendChild(nuocTieuHeaderRow2);
                        }
                        
                        // Add validation listener for result input (only for text input, not select)
                        const resultInput = row.querySelector(".result-input");
                        if (resultInput && !isAmTinhReference && window.xetNghiemValidator) {
                            // Store chi so info for validation
                            resultInput.dataset.chiSoId = chiSo.id || "";
                            resultInput.dataset.testName = chiSo.xet_nghiem || "";
                            resultInput.dataset.chiSoTu = chiSo.chi_so_tu || "";
                            resultInput.dataset.chiSoDen = chiSo.chi_so_den || "";
                            resultInput.dataset.donVi = chiSo.don_vi || "";
                            
                            resultInput.addEventListener("input", function() {
                                if (window.xetNghiemValidator) {
                                    window.xetNghiemValidator.validateResult(this);
                                }
                            });
                            
                            resultInput.addEventListener("blur", function() {
                                if (window.xetNghiemValidator) {
                                    window.xetNghiemValidator.validateResult(this);
                                }
                            });
                        }
                    });
                    
                    // Validate all results after rendering
                    setTimeout(() => {
                        if (window.xetNghiemValidator) {
                            window.xetNghiemValidator.validateAllResults();
                        }
                    }, 100);
                }
                
                // Store the request ID and form type for saving
                document.getElementById("returnXetnghiemResultModal").setAttribute("data-request-id", id);
                document.getElementById("returnXetnghiemResultModal").setAttribute("data-form-type", formType);
                
                new bootstrap.Modal(document.getElementById("returnXetnghiemResultModal")).show();
            } else {
                alert("Không tìm thấy thông tin yêu cầu xét nghiệm");
            }
        })
        .catch(error => {
            console.error("Error loading xetnghiem request:", error);
            alert("Lỗi tải dữ liệu");
        });
}

function formatDateTime(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString("vi-VN") + " " + date.toLocaleTimeString("vi-VN", {hour: "2-digit", minute: "2-digit"});
}

// Test Results Management

function saveTestResult() {
    const requestId = document.getElementById("returnXetnghiemResultModal").getAttribute("data-request-id");
    if (!requestId) {
        alert("Không tìm thấy ID yêu cầu xét nghiệm");
        return;
    }

    // Validation: Check if sample status is filled
    const sampleStatus = document.getElementById("result_sample_status");
    if (!sampleStatus || !sampleStatus.value.trim()) {
        alert("Vui lòng nhập Chất lượng mẫu trước khi lưu!");
        if (sampleStatus) sampleStatus.focus();
        return;
    }

    // Validation: Check if sample location is filled
    const sampleLocation = document.getElementById("result_sample_location");
    if (!sampleLocation || !sampleLocation.value.trim()) {
        alert("Vui lòng nhập Vị trí lấy mẫu trước khi lưu!");
        if (sampleLocation) sampleLocation.focus();
        return;
    }

    // Get the active form type
    const formType = document.getElementById("returnXetnghiemResultModal").getAttribute("data-form-type") || "other";
    
    // Convert form_type to tbody ID format (mau_toan_phan -> MauToanPhan)
    const tbodyIdParts = formType.split("_").map(part => 
        part.charAt(0).toUpperCase() + part.slice(1)
    );
    const tbodyId = "testResultsBody" + tbodyIdParts.join("");
    const tbody = document.getElementById(tbodyId);
    
    if (!tbody) {
        alert("Không tìm thấy form xét nghiệm");
        return;
    }

    // Collect test results from the active form
    const testResults = [];
    const rows = tbody.querySelectorAll("tr");
    
    let actualIndex = 0; // Counter for actual test results (excluding header rows)
    
    for (let row of rows) {
        const chiSoIdInput = row.querySelector(".chi-so-id");
        const testNameInput = row.querySelector(".test-name-input");
        const referenceInput = row.querySelector("td:nth-child(3) input");
        const resultInput = row.querySelector(".result-input");
        const unitInput = row.querySelector("td:nth-child(5) input");
        const machineInput = row.querySelector("td:nth-child(6) input");
        
        // Skip header rows (rows without test-name-input or result-input)
        if (!testNameInput || !resultInput) {
            continue;
        }
        
        if (testNameInput && resultInput) {
            const testName = testNameInput.value.trim();
            // Get result value - handle both input and select elements
            const result = resultInput.tagName === "SELECT" ? resultInput.value.trim() : resultInput.value.trim();
            const referenceValue = referenceInput ? referenceInput.value.trim() : "";
            const unit = unitInput ? unitInput.value.trim() : "";
            const machine = machineInput ? machineInput.value.trim() : "";
            
            // Validation: Check if result is valid (only for text input, not select)
            if (result && resultInput.tagName !== "SELECT") {
                // Check if result is a negative number
                const resultNum = parseFloat(result);
                if (!isNaN(resultNum) && resultNum < 0) {
                    alert("Kết quả xét nghiệm không được là số âm! Vui lòng kiểm tra lại cột Kết quả cho xét nghiệm " + testName + ".");
                    resultInput.focus();
                    return;
                }
            }
            
            // Only add if there is a test name (always present in fixed form) or result
            if (testName || result) {
                actualIndex++; // Increment actual test result counter
                testResults.push({
                    stt: actualIndex, // Use actualIndex instead of testResults.length
                    chi_so_id: chiSoIdInput ? chiSoIdInput.value : "",
                    test_name: testName,
                    reference_value: referenceValue,
                    result: result,
                    unit: unit,
                    machine: machine
                });
            }
        }
    }
    
    // Validation: Check if all test results are filled
    if (testResults.length === 0) {
        alert("Không có dữ liệu xét nghiệm để lưu!");
        return;
    }
    
    const emptyResults = testResults.filter(test => !test.result || test.result.trim() === "");
    if (emptyResults.length > 0) {
        const emptyTestNames = emptyResults.map(test => test.test_name).filter(name => name).join(", ");
        alert("Vui lòng nhập đầy đủ tất cả kết quả xét nghiệm trước khi lưu!\n\nCác xét nghiệm chưa nhập kết quả: " + (emptyTestNames || "Không xác định"));
        return;
    }

    // Prepare form data
    const formData = new URLSearchParams();
    formData.append("request_id", requestId);
    formData.append("test_results", JSON.stringify(testResults));
    formData.append("examining_doctor", document.getElementById("result_examining_doctor").value);
    formData.append("sample_status", document.getElementById("result_sample_status").value);
    formData.append("sample_location", document.getElementById("result_sample_location").value);
    formData.append("result_date", document.getElementById("result_year").value + "-" + document.getElementById("result_month").value + "-" + document.getElementById("result_day").value);

    // Save test result
    fetch("./?action=save_xetnghiem_result", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Lưu kết quả xét nghiệm thành công!");
            loadXetnghiemRequests(); // Refresh the list
            
            // Re-validate all results after save
            setTimeout(() => {
                if (window.xetNghiemValidator) {
                    window.xetNghiemValidator.validateAllResults();
                }
            }, 100);
        } else {
            alert("Lỗi: " + (data.message || "Không thể lưu kết quả"));
        }
    })
    .catch(error => {
        console.error("Error saving test result:", error);
        alert("Lỗi hệ thống khi lưu kết quả");
    });
}

function printTestResult() {
    const requestId = document.getElementById("returnXetnghiemResultModal").getAttribute("data-request-id");
    if (!requestId) {
        alert("Không tìm thấy ID yêu cầu xét nghiệm");
        return;
    }

    // Validation: Check if sample status is filled
    const sampleStatus = document.getElementById("result_sample_status");
    if (!sampleStatus || !sampleStatus.value.trim()) {
        alert("Vui lòng nhập Chất lượng mẫu trước khi in!");
        if (sampleStatus) sampleStatus.focus();
        return;
    }
    
    // Open print window
    window.open("./?action=print_xetnghiem_result&id=" + requestId, "_blank");
    
    // Re-validate all results after print
    setTimeout(() => {
        if (window.xetNghiemValidator) {
            window.xetNghiemValidator.validateAllResults();
        }
    }, 100);
}

function completeTestResult() {
    const requestId = document.getElementById("returnXetnghiemResultModal").getAttribute("data-request-id");
    if (!requestId) {
        alert("Không tìm thấy ID yêu cầu xét nghiệm");
        return;
    }

    // Validation: Check if sample status is filled
    const sampleStatus = document.getElementById("result_sample_status");
    if (!sampleStatus || !sampleStatus.value.trim()) {
        alert("Vui lòng nhập Chất lượng mẫu trước khi hoàn thành!");
        if (sampleStatus) sampleStatus.focus();
        return;
    }
    
    if (!confirm("Bạn có chắc chắn muốn hoàn thành yêu cầu xét nghiệm này?")) {
        return;
    }
    
    const formData = new URLSearchParams();
    formData.append("request_id", requestId);
    
    fetch("./?action=complete_xetnghiem_request", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Hoàn thành yêu cầu xét nghiệm thành công!");
            loadXetnghiemRequests(); // Refresh the list
            bootstrap.Modal.getInstance(document.getElementById("returnXetnghiemResultModal")).hide(); // Close modal
        } else {
            alert("Lỗi: " + (data.message || "Không thể hoàn thành yêu cầu"));
        }
        
        // Re-validate all results after complete
        setTimeout(() => {
            if (window.xetNghiemValidator) {
                window.xetNghiemValidator.validateAllResults();
            }
        }, 100);
    })
    .catch(error => {
        console.error("Error completing test request:", error);
        alert("Lỗi hệ thống khi hoàn thành yêu cầu");
    });
}

