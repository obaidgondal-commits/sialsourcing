-- Fresh-install schema for MySQL 8 / MariaDB on Hostinger. No data or accounts.
-- This is not an in-place migration. Back up and map the existing database first.
-- Deliberately no DROP, TRUNCATE, default password, or private factory data.

CREATE TABLE settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_users (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(191) NOT NULL UNIQUE,
  description TEXT,
  details MEDIUMTEXT,
  icon VARCHAR(80) NOT NULL DEFAULT 'package',
  image VARCHAR(1000) NOT NULL DEFAULT '',
  hero_headline VARCHAR(500) NOT NULL DEFAULT '',
  meta_desc VARCHAR(500) NOT NULL DEFAULT '',
  certifications TEXT,
  moq VARCHAR(255) NOT NULL DEFAULT '',
  lead_time VARCHAR(255) NOT NULL DEFAULT '',
  materials TEXT,
  video_url VARCHAR(1000) NOT NULL DEFAULT '',
  sort_order INTEGER NOT NULL DEFAULT 0,
  is_active TINYINT NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX products_listing ON products (is_active, sort_order);

CREATE TABLE product_gallery (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  product_id INTEGER NOT NULL,
  image VARCHAR(1000) NOT NULL,
  caption VARCHAR(500) NOT NULL DEFAULT '',
  sort_order INTEGER NOT NULL DEFAULT 0,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX product_gallery_order ON product_gallery (product_id, sort_order);

CREATE TABLE product_faqs (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  product_id INTEGER NOT NULL,
  question VARCHAR(1000) NOT NULL,
  answer TEXT NOT NULL,
  sort_order INTEGER NOT NULL DEFAULT 0,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX product_faqs_order ON product_faqs (product_id, sort_order);

CREATE TABLE solutions (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  short_desc TEXT,
  description TEXT,
  icon VARCHAR(80) NOT NULL DEFAULT 'check-circle',
  sort_order INTEGER NOT NULL DEFAULT 0,
  is_active TINYINT NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lab_tests (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  category VARCHAR(100) NOT NULL DEFAULT 'Quality Control',
  description TEXT,
  standards VARCHAR(500) NOT NULL DEFAULT '',
  icon VARCHAR(80) NOT NULL DEFAULT 'check-circle',
  sort_order INTEGER NOT NULL DEFAULT 0,
  is_active TINYINT NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE faqs (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  question VARCHAR(1000) NOT NULL,
  answer TEXT NOT NULL,
  category VARCHAR(100) NOT NULL DEFAULT 'Sourcing',
  sort_order INTEGER NOT NULL DEFAULT 0,
  is_active TINYINT NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blog_posts (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(191) NOT NULL UNIQUE,
  excerpt TEXT,
  content MEDIUMTEXT,
  author VARCHAR(200) NOT NULL DEFAULT '',
  category VARCHAR(100) NOT NULL DEFAULT 'Insights',
  featured_image VARCHAR(1000) NOT NULL DEFAULT '',
  is_published TINYINT NOT NULL DEFAULT 0,
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX blog_listing ON blog_posts (is_published, published_at);

CREATE TABLE team_members (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(200) NOT NULL,
  role VARCHAR(200) NOT NULL,
  bio TEXT,
  location VARCHAR(200) NOT NULL DEFAULT '',
  linkedin VARCHAR(1000) NOT NULL DEFAULT '',
  email VARCHAR(200) NOT NULL DEFAULT '',
  image VARCHAR(1000) NOT NULL DEFAULT '',
  sort_order INTEGER NOT NULL DEFAULT 0,
  is_active TINYINT NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_submissions (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(200) NOT NULL,
  email VARCHAR(200) NOT NULL,
  company VARCHAR(200) NOT NULL DEFAULT '',
  phone VARCHAR(50) NOT NULL DEFAULT '',
  subject VARCHAR(200) NOT NULL DEFAULT '',
  message TEXT NOT NULL,
  is_read TINYINT NOT NULL DEFAULT 0,
  submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX enquiry_inbox ON contact_submissions (is_read, submitted_at);

CREATE TABLE manufacturer_applications (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  company_name VARCHAR(200) NOT NULL,
  contact_name VARCHAR(200) NOT NULL,
  email VARCHAR(200) NOT NULL,
  phone VARCHAR(50) NOT NULL DEFAULT '',
  city VARCHAR(100) NOT NULL,
  product_categories VARCHAR(255) NOT NULL,
  certifications VARCHAR(255) NOT NULL DEFAULT '',
  website VARCHAR(255) NOT NULL DEFAULT '',
  message TEXT,
  status VARCHAR(20) NOT NULL DEFAULT 'new',
  submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX applications_inbox ON manufacturer_applications (status, submitted_at);
