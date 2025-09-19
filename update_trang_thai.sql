USE hospital_management;

ALTER TABLE lich_hen 
MODIFY COLUMN trang_thai ENUM('Chờ xác nhận','Đã xác nhận','Đang khám','Hoàn thành','Đã khám xong','hủy') 
DEFAULT 'Chờ xác nhận';

-- Kiểm tra kết quả
DESCRIBE lich_hen;
