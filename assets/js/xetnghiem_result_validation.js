// Xét nghiệm Result Validation JavaScript
// So sánh kết quả với ngưỡng và in đậm khi vượt ngưỡng

class XetNghiemResultValidator {
    constructor() {
        this.chiSoData = new Map(); // Cache dữ liệu chỉ số
        this.init();
    }

    init() {
        // Load dữ liệu chỉ số khi trang load
        this.loadChiSoData();
        
        // Thêm event listener cho các input kết quả
        this.addResultInputListeners();
    }

    // Load dữ liệu chỉ số từ database
    async loadChiSoData() {
        try {
            const response = await fetch('./?action=get_chi_so_xet_nghiem');
            const data = await response.json();
            
            if (data.success && data.chi_so) {
                data.chi_so.forEach(item => {
                    this.chiSoData.set(item.ten_chi_so.toLowerCase(), {
                        chi_so_tu: parseFloat(item.chi_so_tu),
                        chi_so_den: parseFloat(item.chi_so_den),
                        don_vi: item.don_vi
                    });
                    console.log('Added chi so:', item.ten_chi_so.toLowerCase(), 'with range:', item.chi_so_tu, '-', item.chi_so_den);
                });
                console.log('Loaded chi so data:', this.chiSoData);
            } else {
                console.log('No chi so data loaded. Response:', data);
            }
        } catch (error) {
            console.error('Error loading chi so data:', error);
            console.log('API response failed, chiSoData will be empty');
            console.log('Error details:', error.message);
            console.log('Stack trace:', error.stack);
            console.log('Full error object:', error);
        }
    }

    // Thêm event listener cho các input kết quả
    addResultInputListeners() {
        // Sử dụng event delegation để handle dynamic rows
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('result-input')) {
                this.validateResult(e.target);
            }
        });

        // Thêm keyup để validation ngay khi gõ xong
        document.addEventListener('keyup', (e) => {
            if (e.target.classList.contains('result-input')) {
                this.validateResult(e.target);
            }
        });

        // Thêm change để validation khi paste hoặc thay đổi giá trị
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('result-input')) {
                this.validateResult(e.target);
            }
        });

        // Thêm paste để validation khi paste dữ liệu
        document.addEventListener('paste', (e) => {
            if (e.target.classList.contains('result-input')) {
                // Delay một chút để paste hoàn thành
                setTimeout(() => {
                    this.validateResult(e.target);
                }, 10);
            }
        });

        // Cũng handle khi focus out
        document.addEventListener('blur', (e) => {
            if (e.target.classList.contains('result-input')) {
                this.validateResult(e.target);
            }
        }, true);
    }

    // Validate kết quả và in đậm nếu vượt ngưỡng
    validateResult(input) {
        const row = input.closest('tr');
        const testNameInput = row.querySelector('.test-name-input');
        const resultInput = input;
        
        if (!testNameInput || !resultInput) return;

        const testName = testNameInput.value.trim().toLowerCase();
        const resultValue = parseFloat(resultInput.value);
        
        console.log('Validating:', testName, resultValue);
        console.log('Available chi so data:', this.chiSoData);

        // Tìm chỉ số tương ứng
        let chiSoInfo = null;
        for (const [key, value] of this.chiSoData) {
            console.log('Checking key:', key, 'against testName:', testName);
            if (testName.includes(key) || key.includes(testName)) {
                chiSoInfo = value;
                console.log('Found match with key:', key);
                break;
            }
        }

        // Nếu không tìm thấy, thử tìm theo tên chính xác
        if (!chiSoInfo) {
            console.log('No match found, trying exact match for:', testName);
            chiSoInfo = this.chiSoData.get(testName);
            if (chiSoInfo) {
                console.log('Found exact match:', chiSoInfo);
            } else {
                console.log('No exact match found either');
            }
        }

        if (chiSoInfo && !isNaN(resultValue)) {
            const { chi_so_tu, chi_so_den } = chiSoInfo;
            
            console.log('Found chi so info:', chiSoInfo);
            console.log('Range:', chi_so_tu, '-', chi_so_den);
            
            // Kiểm tra vượt ngưỡng
            const isOutOfRange = resultValue < chi_so_tu || resultValue > chi_so_den;
            
            console.log('Is out of range:', isOutOfRange);
            
            // Thêm/xóa class in đậm
            if (isOutOfRange) {
                resultInput.classList.add('fw-bold', 'text-danger');
                resultInput.title = `Vượt ngưỡng! Khoảng tham chiếu: ${chi_so_tu} - ${chi_so_den} ${chiSoInfo.don_vi}`;
            } else {
                resultInput.classList.remove('fw-bold', 'text-danger');
                resultInput.title = `Trong khoảng tham chiếu: ${chi_so_tu} - ${chi_so_den} ${chiSoInfo.don_vi}`;
            }
        } else {
            // Reset styling nếu không có dữ liệu
            resultInput.classList.remove('fw-bold', 'text-danger');
            resultInput.title = '';
            console.log('No chi so info found for:', testName);
            console.log('Result value:', resultValue, 'isNaN:', isNaN(resultValue));
            console.log('chiSoInfo:', chiSoInfo);
        }
    }

    // Validate tất cả kết quả trong bảng
    validateAllResults() {
        const resultInputs = document.querySelectorAll('.result-input');
        resultInputs.forEach(input => this.validateResult(input));
    }

    // Thêm chỉ số mới vào cache
    addChiSo(tenChiSo, chiSoTu, chiSoDen, donVi) {
        this.chiSoData.set(tenChiSo.toLowerCase(), {
            chi_so_tu: parseFloat(chiSoTu),
            chi_so_den: parseFloat(chiSoDen),
            don_vi: donVi
        });
    }

    // Lấy thông tin chỉ số
    getChiSoInfo(tenChiSo) {
        return this.chiSoData.get(tenChiSo.toLowerCase());
    }
}

// Khởi tạo validator khi DOM ready
document.addEventListener('DOMContentLoaded', function() {
    window.xetNghiemValidator = new XetNghiemResultValidator();
});

// Function để validate kết quả khi thêm row mới
function validateNewResultRow(row) {
    if (window.xetNghiemValidator) {
        const resultInput = row.querySelector('.result-input');
        if (resultInput) {
            window.xetNghiemValidator.validateResult(resultInput);
        }
    }
}

// Function để validate tất cả khi load dữ liệu
function validateAllResults() {
    if (window.xetNghiemValidator) {
        window.xetNghiemValidator.validateAllResults();
    }
}
