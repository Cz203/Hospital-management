CREATE TABLE IF NOT EXISTS phieu_boc_so (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ngay DATE NOT NULL,
  so_thu_tu INT NOT NULL,
  benh_nhan_id INT NOT NULL,
  bac_si_id INT NOT NULL,
  lich_hen_id INT NULL,
  trang_thai ENUM('cho','dang_goi','dang_kham','bo_lo','xong','huy') DEFAULT 'cho',
  uu_tien TINYINT DEFAULT 0,
  quay VARCHAR(50) NULL,
  ghi_chu TEXT NULL,
  thoi_gian_goi DATETIME NULL,
  thoi_gian_bat_dau DATETIME NULL,
  thoi_gian_ket_thuc DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_ticket (ngay, bac_si_id, so_thu_tu),
  KEY idx_waiting (ngay, bac_si_id, trang_thai, uu_tien),
  KEY idx_patient (benh_nhan_id),
  CONSTRAINT fk_ticket_bn FOREIGN KEY (benh_nhan_id) REFERENCES benh_nhan(id) ON DELETE CASCADE,
  CONSTRAINT fk_ticket_bs FOREIGN KEY (bac_si_id) REFERENCES bac_si(id) ON DELETE CASCADE,
  CONSTRAINT fk_ticket_lich FOREIGN KEY (lich_hen_id) REFERENCES lich_hen(id) ON DELETE SET NULL
);

