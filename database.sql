-- Database schema for Smart Daily Expense Planner
-- Import file ini ke MySQL/phpMyAdmin di XAMPP.

CREATE DATABASE IF NOT EXISTS smart_daily_expense_planner
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE smart_daily_expense_planner;

CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  icon VARCHAR(50) DEFAULT NULL,
  color VARCHAR(20) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS expenses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  amount DECIMAL(15,2) NOT NULL,
  expense_date DATE NOT NULL,
  notes TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_expenses_category
    FOREIGN KEY (category_id) REFERENCES categories (id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  INDEX idx_expenses_category_id (category_id),
  INDEX idx_expenses_expense_date (expense_date),
  INDEX idx_expenses_created_at (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO categories (name, icon, color) VALUES
  ('Makanan', 'fa-utensils', '#0d6efd'),
  ('Minuman', 'fa-glass-water', '#20c997'),
  ('Transportasi', 'fa-bus', '#0dcaf0'),
  ('Belanja', 'fa-bag-shopping', '#6610f2'),
  ('Hiburan', 'fa-film', '#fd7e14'),
  ('Kesehatan', 'fa-notes-medical', '#dc3545'),
  ('Pendidikan', 'fa-book', '#198754'),
  ('Tagihan', 'fa-file-invoice-dollar', '#6c757d'),
  ('Lainnya', 'fa-ellipsis', '#343a40')
ON DUPLICATE KEY UPDATE
  icon = VALUES(icon),
  color = VALUES(color);
