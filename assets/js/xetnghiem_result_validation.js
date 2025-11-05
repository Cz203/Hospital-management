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
                });
            }
        } catch (error) {
            console.error('Error loading chi so data:', error);
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
        const resultInput = input;
        if (!resultInput) return;

        const resultValue = parseFloat(resultInput.value);
        if (isNaN(resultValue)) {
            // Reset styling nếu không phải số
            resultInput.classList.remove('fw-bold', 'text-danger');
            resultInput.title = '';
            return;
        }

        // Ưu tiên sử dụng data attributes (cho form cố định)
        let chiSoInfo = null;
        const chiSoTu = resultInput.dataset.chiSoTu;
        const chiSoDen = resultInput.dataset.chiSoDen;
        const donVi = resultInput.dataset.donVi || '';

        if (chiSoTu !== undefined && chiSoDen !== undefined) {
            // Sử dụng data attributes nếu có (form cố định)
            chiSoInfo = {
                chi_so_tu: parseFloat(chiSoTu),
                chi_so_den: parseFloat(chiSoDen),
                don_vi: donVi
            };
        } else {
            // Fallback: Tìm theo tên xét nghiệm (cho form động)
            const row = input.closest('tr');
            const testNameInput = row?.querySelector('.test-name-input');
            
            if (!testNameInput) return;

            const testName = testNameInput.value.trim().toLowerCase();
            
            // Tìm chỉ số tương ứng
            for (const [key, value] of this.chiSoData) {
                if (testName.includes(key) || key.includes(testName)) {
                    chiSoInfo = value;
                    break;
                }
            }

            // Nếu không tìm thấy, thử tìm theo tên chính xác
            if (!chiSoInfo) {
                chiSoInfo = this.chiSoData.get(testName);
            }
        }

        if (chiSoInfo) {
            const { chi_so_tu, chi_so_den, don_vi } = chiSoInfo;
            
            // Kiểm tra vượt ngưỡng
            const isOutOfRange = resultValue < chi_so_tu || resultValue > chi_so_den;
            
            // Thêm/xóa class in đậm
            if (isOutOfRange) {
                resultInput.classList.add('fw-bold', 'text-danger');
                resultInput.title = `Vượt ngưỡng! Khoảng tham chiếu: ${chi_so_tu} - ${chi_so_den} ${don_vi || ''}`;
            } else {
                resultInput.classList.remove('fw-bold', 'text-danger');
                resultInput.title = `Trong khoảng tham chiếu: ${chi_so_tu} - ${chi_so_den} ${don_vi || ''}`;
            }
        } else {
            // Reset styling nếu không có dữ liệu
            resultInput.classList.remove('fw-bold', 'text-danger');
            resultInput.title = '';
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

