-- ============================================================================
-- YEMEKHANECİ.COM.TR — VERİTABANI ŞEMASI
-- ============================================================================
-- Versiyon: 1.0
-- Tarih: Mayıs 2026
-- Database: MySQL 8 + utf8mb4_turkish_ci
-- Toplam tablo: 28 (24 ana + 4 yardımcı)
--
-- Kullanım: Bu dosya doğrudan import edilebilir veya Laravel migration'larına
-- dönüştürülebilir. Claude Code ile çalışırken referans olarak kullanın.
--
-- KULLANIM:
--   mysql -u root -p yemekhaneci < database_schema.sql
-- ============================================================================

-- Database oluştur (varsa atla)
CREATE DATABASE IF NOT EXISTS yemekhaneci
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_turkish_ci;

USE yemekhaneci;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- 1. CATEGORIES - Menü kategorileri (parent/child yapısı)
-- ============================================================================
CREATE TABLE categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parent_id BIGINT UNSIGNED NULL,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  icon VARCHAR(50),
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
  INDEX idx_parent (parent_id),
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 2. USERS - Tüm kullanıcılar (4 tip tek tabloda)
-- ============================================================================
CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(20) UNIQUE,
  password VARCHAR(255) NOT NULL COMMENT 'Argon2id hash',
  user_type ENUM('corporate','individual','supplier','admin') NOT NULL,
  email_verified_at TIMESTAMP NULL,
  phone_verified_at TIMESTAMP NULL,
  status ENUM('active','suspended','banned','pending') DEFAULT 'active',
  two_factor_secret VARCHAR(100) NULL,
  remember_token VARCHAR(100) NULL,
  last_login_at TIMESTAMP NULL,
  last_login_ip VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  INDEX idx_email (email),
  INDEX idx_phone (phone),
  INDEX idx_user_type (user_type),
  INDEX idx_status (status),
  INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 3. CORPORATE_PROFILES - Kurumsal müşteri profilleri
-- ============================================================================
CREATE TABLE corporate_profiles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL UNIQUE,
  company_name VARCHAR(200) NOT NULL,
  tax_office VARCHAR(100),
  tax_number VARCHAR(20),
  billing_address TEXT,
  employee_count INT,
  sector VARCHAR(100),
  e_invoice_enabled TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_tax_number (tax_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 4. SUPPLIER_PROFILES - Yemekçi profilleri
-- ============================================================================
CREATE TABLE supplier_profiles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL UNIQUE,
  company_name VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  logo_url VARCHAR(500),
  cover_image VARCHAR(500),
  bio TEXT,
  founded_year INT,
  daily_capacity INT COMMENT 'Günlük üretim kapasitesi (öğün)',
  kitchen_address TEXT,
  kitchen_lat DECIMAL(10,7),
  kitchen_lng DECIMAL(10,7),
  is_verified TINYINT(1) DEFAULT 0,
  verification_date TIMESTAMP NULL,
  rating_avg DECIMAL(3,2) DEFAULT 0,
  rating_count INT DEFAULT 0,
  total_orders INT DEFAULT 0,
  status ENUM('pending','active','suspended','rejected') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_slug (slug),
  INDEX idx_status (status),
  INDEX idx_verified (is_verified),
  INDEX idx_rating (rating_avg DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 5. SUPPLIER_DOCUMENTS - Yemekçi yasal belgeleri
-- ============================================================================
CREATE TABLE supplier_documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supplier_id BIGINT UNSIGNED NOT NULL,
  document_type ENUM('iso22000','haccp','tax_certificate','trade_registry','helal','signature','insurance','other') NOT NULL,
  file_url VARCHAR(500) NOT NULL,
  expiry_date DATE NULL,
  verified_by_admin TINYINT(1) DEFAULT 0,
  verified_at TIMESTAMP NULL,
  verified_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES supplier_profiles(id) ON DELETE CASCADE,
  FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_supplier (supplier_id),
  INDEX idx_type (document_type),
  INDEX idx_expiry (expiry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 6. SUPPLIER_SERVICE_AREAS - Yemekçi hizmet bölgeleri (poligon)
-- ============================================================================
CREATE TABLE supplier_service_areas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supplier_id BIGINT UNSIGNED NOT NULL,
  polygon GEOMETRY NOT NULL SRID 4326,
  city_codes JSON COMMENT '["41","34","16"]',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES supplier_profiles(id) ON DELETE CASCADE,
  SPATIAL INDEX idx_polygon (polygon),
  INDEX idx_supplier (supplier_id),
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 7. SUPPLIER_COST_MATRIX - Yemek maliyet matrisi (3 segment × 4 çeşit = 12 hücre)
-- ============================================================================
CREATE TABLE supplier_cost_matrix (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supplier_id BIGINT UNSIGNED NOT NULL,
  segment ENUM('ekonomik','genel','premium') NOT NULL,
  cesit_min INT NOT NULL COMMENT '3, 4, 5, 6 (6 = 6+)',
  cesit_max INT NOT NULL,
  price_per_meal DECIMAL(10,2) NOT NULL COMMENT 'TL/öğün - sadece hammadde + üretim',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES supplier_profiles(id) ON DELETE CASCADE,
  UNIQUE KEY uk_matrix (supplier_id, segment, cesit_min),
  INDEX idx_supplier (supplier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 8. SUPPLIER_PERSONNEL_COSTS - Personel maliyetleri (pozisyon × deneyim)
-- ============================================================================
CREATE TABLE supplier_personnel_costs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supplier_id BIGINT UNSIGNED NOT NULL,
  position ENUM(
    'ascibasi_senior','asci_senior','asci_junior','yardimci',
    'servis_senior','servis_junior',
    'bulasik','sofor','operasyon'
  ) NOT NULL,
  monthly_cost DECIMAL(10,2) NOT NULL COMMENT 'Brüt aylık maliyet (maaş+SGK+yemek+yol)',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES supplier_profiles(id) ON DELETE CASCADE,
  UNIQUE KEY uk_position (supplier_id, position),
  INDEX idx_supplier (supplier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 9. SUPPLIER_PERSONNEL_TEMPLATE - 100 kişilik sipariş için varsayılan personel
-- ============================================================================
CREATE TABLE supplier_personnel_template (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supplier_id BIGINT UNSIGNED NOT NULL UNIQUE,
  asci_count INT DEFAULT 1,
  yardimci_count INT DEFAULT 1,
  servis_count INT DEFAULT 2,
  bulasik_count INT DEFAULT 1,
  lojistik_count INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES supplier_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 10. SUPPLIER_FIXED_COSTS - Yemekçi sabit aylık giderler
-- ============================================================================
CREATE TABLE supplier_fixed_costs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supplier_id BIGINT UNSIGNED NOT NULL UNIQUE,
  kira DECIMAL(10,2) DEFAULT 0,
  enerji DECIMAL(10,2) DEFAULT 0 COMMENT 'Elektrik+su+gaz+internet',
  arac DECIMAL(10,2) DEFAULT 0 COMMENT 'Yakıt+bakım+amortisman',
  sigorta DECIMAL(10,2) DEFAULT 0,
  muhasebe DECIMAL(10,2) DEFAULT 0,
  dijital DECIMAL(10,2) DEFAULT 0 COMMENT 'POS, web hosting, SaaS abonelikler',
  diger DECIMAL(10,2) DEFAULT 0,
  distribution_rate INT DEFAULT 20 COMMENT 'Bu siparişe yüklenen sabit gider yüzdesi (5-50)',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES supplier_profiles(id) ON DELETE CASCADE,
  CHECK (distribution_rate BETWEEN 5 AND 50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 11. SUPPLIER_KITCHEN_EQUIPMENT - Mutfak ekipman birim fiyatları
-- ============================================================================
CREATE TABLE supplier_kitchen_equipment (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supplier_id BIGINT UNSIGNED NOT NULL,
  equipment_type ENUM('ocak','firin','bulasik','dolap','depo','davlumbaz','yemekhane','hat') NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  amortization_months INT DEFAULT 24 COMMENT '12-60 ay arası',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES supplier_profiles(id) ON DELETE CASCADE,
  UNIQUE KEY uk_equipment (supplier_id, equipment_type),
  INDEX idx_supplier (supplier_id),
  CHECK (amortization_months BETWEEN 12 AND 60)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 12. SUPPLIER_PROFIT_MARGIN - Kar marjı stratejisi
-- ============================================================================
CREATE TABLE supplier_profit_margin (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supplier_id BIGINT UNSIGNED NOT NULL UNIQUE,
  eko_margin DECIMAL(5,2) DEFAULT 18.00 COMMENT 'Ekonomik segment % marj',
  gen_margin DECIMAL(5,2) DEFAULT 25.00 COMMENT 'Genel segment % marj',
  pre_margin DECIMAL(5,2) DEFAULT 35.00 COMMENT 'Premium segment % marj',
  hacim_100 DECIMAL(5,2) DEFAULT -2.00 COMMENT '100+ kişi indirimi',
  hacim_200 DECIMAL(5,2) DEFAULT -4.00 COMMENT '200+ kişi indirimi',
  hacim_500 DECIMAL(5,2) DEFAULT -7.00 COMMENT '500+ kişi indirimi',
  ozel_margin DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Yemekçi manuel düzeltme (-15 ile +20 arası)',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES supplier_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 13. SUPPLIER_MENU_TEMPLATE - Menü şablonu yapısı
-- ============================================================================
CREATE TABLE supplier_menu_template (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supplier_id BIGINT UNSIGNED NOT NULL UNIQUE,
  salata_count INT DEFAULT 1 COMMENT '1-10 arası',
  salata_manuel JSON COMMENT '5+ çeşit için manuel dağılım: {"salata":2,"zeytinyagli":1,"meze":1,"yardimci":1}',
  tatli_active TINYINT(1) DEFAULT 1,
  tatli_options JSON COMMENT '{"tatli":true,"meyve":true,"mesrubat":true}',
  ayran_type ENUM('ayran','yogurt','ikisi','rotasyon') DEFAULT 'rotasyon',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES supplier_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 14. MENUS - Yemekçi standart menüleri
-- ============================================================================
CREATE TABLE menus (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supplier_id BIGINT UNSIGNED NOT NULL,
  category_id BIGINT UNSIGNED NULL,
  name VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  description TEXT,
  cover_image VARCHAR(500),
  base_price DECIMAL(10,2) NOT NULL COMMENT 'Kişi başı/öğün fiyatı',
  min_persons INT DEFAULT 50,
  max_persons INT DEFAULT 1000,
  prep_hours INT DEFAULT 24,
  is_active TINYINT(1) DEFAULT 1,
  is_published TINYINT(1) DEFAULT 0,
  view_count INT DEFAULT 0,
  order_count INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  FOREIGN KEY (supplier_id) REFERENCES supplier_profiles(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  UNIQUE KEY uk_supplier_slug (supplier_id, slug),
  INDEX idx_supplier (supplier_id),
  INDEX idx_category (category_id),
  INDEX idx_published (is_published),
  INDEX idx_price (base_price),
  FULLTEXT INDEX ft_search (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 15. MENU_ITEMS - Bir menüdeki yemekler
-- ============================================================================
CREATE TABLE menu_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  menu_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  image_url VARCHAR(500),
  dietary_tags JSON COMMENT '["helal","vegan","glutensiz"]',
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
  INDEX idx_menu (menu_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 16. MENU_IMAGES - Menü ek fotoğrafları
-- ============================================================================
CREATE TABLE menu_images (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  menu_id BIGINT UNSIGNED NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  alt_text VARCHAR(200),
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
  INDEX idx_menu (menu_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 17. PROACTIVE_PACKAGES - Yemekçi proaktif paketleri (Tür 2)
-- ============================================================================
CREATE TABLE proactive_packages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supplier_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  description TEXT,
  cover_image VARCHAR(500),
  price DECIMAL(10,2) NOT NULL,
  old_price DECIMAL(10,2) NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  max_orders INT DEFAULT 100,
  current_orders INT DEFAULT 0,
  status ENUM('draft','pending','active','expired','sold_out') DEFAULT 'draft',
  approved_by BIGINT UNSIGNED NULL,
  approved_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES supplier_profiles(id) ON DELETE CASCADE,
  FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
  UNIQUE KEY uk_supplier_slug (supplier_id, slug),
  INDEX idx_status (status),
  INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 18. LEADS - Wizard'dan gelen müşteri talepleri (mail isteği)
-- ============================================================================
CREATE TABLE leads (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(20) NULL,
  kisi_sayisi INT NOT NULL,
  ogun_dagilim JSON NOT NULL COMMENT '{"ogle":50,"aksam":0,"kumanya":0}',
  menu_config JSON NOT NULL COMMENT 'Menü konfigürasyonu (salata, tatli, ayran)',
  segment ENUM('ekonomik','genel','premium') NOT NULL,
  lokasyon TEXT,
  lokasyon_lat DECIMAL(10,7),
  lokasyon_lng DECIMAL(10,7),
  personel JSON NULL COMMENT 'Personel desteği detayları',
  mutfak JSON NULL COMMENT 'Mutfak yatırım ekipmanları',
  cumartesi ENUM('evet','hayir','yarim') DEFAULT 'hayir',
  notlar TEXT,
  mail_mode ENUM('toplu','ayri') NOT NULL,
  selected_supplier_ids JSON COMMENT '[1,5,8,12]',
  sent_at TIMESTAMP NULL,
  kvkk_onay TINYINT(1) DEFAULT 0,
  kvkk_ip VARCHAR(45),
  kvkk_user_agent VARCHAR(500),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_created (created_at DESC),
  INDEX idx_sent (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 19. QUOTE_REQUESTS - Özel teklif talepleri (Tür 1: Reaktif)
-- ============================================================================
CREATE TABLE quote_requests (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT UNSIGNED NOT NULL,
  category_id BIGINT UNSIGNED NULL,
  event_date DATE NOT NULL,
  persons_count INT NOT NULL,
  budget_min DECIMAL(10,2),
  budget_max DECIMAL(10,2),
  location_text TEXT,
  location_lat DECIMAL(10,7),
  location_lng DECIMAL(10,7),
  description TEXT,
  dietary_requirements JSON,
  service_type ENUM('delivery','self_service','full_service') DEFAULT 'delivery',
  status ENUM('open','closed','converted','expired') DEFAULT 'open',
  expires_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  INDEX idx_customer (customer_id),
  INDEX idx_status (status),
  INDEX idx_event (event_date),
  INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 20. QUOTES - Yemekçi tekliflerine yanıtlar
-- ============================================================================
CREATE TABLE quotes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  quote_request_id BIGINT UNSIGNED NOT NULL,
  supplier_id BIGINT UNSIGNED NOT NULL,
  price_total DECIMAL(10,2) NOT NULL,
  price_per_person DECIMAL(10,2) NOT NULL,
  included_items JSON COMMENT 'Tekliftekiler listesi',
  delivery_method VARCHAR(100),
  valid_until TIMESTAMP NOT NULL,
  message TEXT,
  status ENUM('sent','viewed','accepted','rejected','expired') DEFAULT 'sent',
  viewed_at TIMESTAMP NULL,
  responded_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (quote_request_id) REFERENCES quote_requests(id) ON DELETE CASCADE,
  FOREIGN KEY (supplier_id) REFERENCES supplier_profiles(id) ON DELETE CASCADE,
  UNIQUE KEY uk_request_supplier (quote_request_id, supplier_id),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 21. B2B_LISTINGS - B2B pazaryeri ilanları (Tür 3)
-- ============================================================================
CREATE TABLE b2b_listings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supplier_id BIGINT UNSIGNED NOT NULL,
  listing_type ENUM('need','offer') NOT NULL,
  category ENUM('capacity','dessert','raw_material','staff','equipment','other') NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  price DECIMAL(10,2),
  location_lat DECIMAL(10,7),
  location_lng DECIMAL(10,7),
  expires_at TIMESTAMP NULL,
  status ENUM('active','closed','expired') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES supplier_profiles(id) ON DELETE CASCADE,
  INDEX idx_type (listing_type),
  INDEX idx_category (category),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 22. ORDERS - Tüm siparişler
-- ============================================================================
CREATE TABLE orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(30) NOT NULL UNIQUE COMMENT 'YHC-YYYY-NNNNNN',
  customer_id BIGINT UNSIGNED NOT NULL,
  supplier_id BIGINT UNSIGNED NOT NULL,
  order_type ENUM('direct','quote','package','b2b') NOT NULL,
  source_id BIGINT UNSIGNED NULL COMMENT 'quote_id, package_id veya listing_id',
  event_date DATETIME NOT NULL,
  persons_count INT NOT NULL,
  delivery_address TEXT NOT NULL,
  delivery_lat DECIMAL(10,7),
  delivery_lng DECIMAL(10,7),
  subtotal DECIMAL(10,2) NOT NULL,
  commission DECIMAL(10,2) NOT NULL,
  commission_rate DECIMAL(5,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('pending','accepted','preparing','on_way','delivered','cancelled','disputed') DEFAULT 'pending',
  payment_status ENUM('pending','paid','refunded','failed') DEFAULT 'pending',
  special_notes TEXT,
  cancellation_reason TEXT,
  accepted_at TIMESTAMP NULL,
  delivered_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (supplier_id) REFERENCES supplier_profiles(id) ON DELETE CASCADE,
  INDEX idx_order_number (order_number),
  INDEX idx_customer (customer_id),
  INDEX idx_supplier (supplier_id),
  INDEX idx_status (status),
  INDEX idx_event (event_date),
  INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 23. MESSAGES - Platform içi mesajlaşma
-- ============================================================================
CREATE TABLE messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sender_id BIGINT UNSIGNED NOT NULL,
  receiver_id BIGINT UNSIGNED NOT NULL,
  context_type ENUM('order','quote_request','b2b','support') NOT NULL,
  context_id BIGINT UNSIGNED NOT NULL,
  message_text TEXT NOT NULL,
  attachment_url VARCHAR(500),
  is_read TINYINT(1) DEFAULT 0,
  read_at TIMESTAMP NULL,
  moderated TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_sender (sender_id),
  INDEX idx_receiver (receiver_id),
  INDEX idx_context (context_type, context_id),
  INDEX idx_unread (receiver_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 24. REVIEWS - Sipariş sonrası değerlendirmeler
-- ============================================================================
CREATE TABLE reviews (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL UNIQUE,
  customer_id BIGINT UNSIGNED NOT NULL,
  supplier_id BIGINT UNSIGNED NOT NULL,
  rating_overall TINYINT NOT NULL COMMENT '1-5',
  rating_food TINYINT,
  rating_service TINYINT,
  rating_punctuality TINYINT,
  comment TEXT,
  supplier_reply TEXT,
  photos JSON,
  is_moderated TINYINT(1) DEFAULT 0,
  helpful_count INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (supplier_id) REFERENCES supplier_profiles(id) ON DELETE CASCADE,
  INDEX idx_supplier (supplier_id),
  INDEX idx_rating (rating_overall),
  CHECK (rating_overall BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 25. PAYMENTS - Ödeme kayıtları
-- ============================================================================
CREATE TABLE payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  gateway ENUM('iyzico','paytr','bank_transfer','corporate_credit') NOT NULL,
  gateway_transaction_id VARCHAR(100),
  amount DECIMAL(10,2) NOT NULL,
  commission DECIMAL(10,2) NOT NULL,
  net_to_supplier DECIMAL(10,2) NOT NULL,
  status ENUM('pending','completed','failed','refunded','partial_refund') DEFAULT 'pending',
  paid_at TIMESTAMP NULL,
  refunded_at TIMESTAMP NULL,
  refund_reason TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  INDEX idx_order (order_id),
  INDEX idx_gateway (gateway),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 26. NOTIFICATIONS - Bildirimler (e-posta, SMS, in-app)
-- ============================================================================
CREATE TABLE notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  type VARCHAR(50) NOT NULL,
  channel ENUM('email','sms','in_app','whatsapp') NOT NULL,
  title VARCHAR(200) NOT NULL,
  body TEXT,
  data JSON,
  is_read TINYINT(1) DEFAULT 0,
  read_at TIMESTAMP NULL,
  sent_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_unread (user_id, is_read),
  INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 27. FAVORITES - Müşteri favorileri
-- ============================================================================
CREATE TABLE favorites (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT UNSIGNED NOT NULL,
  favoritable_type ENUM('menu','supplier','package') NOT NULL,
  favoritable_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY uk_favorite (customer_id, favoritable_type, favoritable_id),
  INDEX idx_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- 28. ACTIVITY_LOG - Audit trail (KVKK için 7 yıl)
-- ============================================================================
CREATE TABLE activity_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  action VARCHAR(100) NOT NULL,
  ip_address VARCHAR(45),
  user_agent VARCHAR(500),
  target_type VARCHAR(50),
  target_id BIGINT UNSIGNED,
  metadata JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_user (user_id),
  INDEX idx_action (action),
  INDEX idx_target (target_type, target_id),
  INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ============================================================================
-- BAŞLANGIÇ VERİLERİ (SEEDER)
-- ============================================================================

-- Kategoriler
INSERT INTO categories (name, slug, icon, sort_order) VALUES
  ('Toplu Yemek', 'toplu-yemek', '🍽️', 1),
  ('Kurumsal Davet', 'kurumsal-davet', '🎉', 2),
  ('Kahvaltı/İkram', 'kahvalti-ikram', '☕', 3),
  ('Düğün/Nişan', 'dugun-nisan', '💒', 4),
  ('Mezuniyet', 'mezuniyet', '🎓', 5),
  ('Ramazan/İftar', 'ramazan-iftar', '🌙', 6),
  ('Kokteyl/Açılış', 'kokteyl-acilis', '🥂', 7),
  ('Spor/Etkinlik', 'spor-etkinlik', '⚽', 8);

-- Sistem ayarları (gelecek migration için)
-- INSERT INTO settings (key, value) VALUES ...

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- BAŞARILI MESAJ
-- ============================================================================
SELECT 'Yemekhaneci.com.tr veritabanı şeması başarıyla oluşturuldu!' AS sonuc,
       28 AS toplam_tablo,
       'utf8mb4_turkish_ci' AS charset_collation;
