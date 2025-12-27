# Secure Online Banking System

A secure, role-based online banking web application built using **PHP, MySQL, and Bootstrap**, with a strong focus on **cybersecurity best practices**.

---

## 🚀 Features

### 🔐 Authentication & Security
- User registration and login
- Secure password hashing (bcrypt)
- CSRF protection on all forms
- Login rate limiting (5 attempts / 15 minutes)
- Session hardening (session regeneration)
- Secure password reset with expiring tokens
- SQL Injection prevention using PDO prepared statements

### 👥 User Features
- Automatic bank account creation on registration
- View account balance and account number
- Secure money transfers
- Transaction history

### 🛠 Admin Features
- Role-based access control (Admin / User)
- Admin dashboard
- Block / unblock user accounts
- View user accounts and balances

---

## 🧰 Tech Stack

- **Backend:** PHP (PDO)
- **Database:** MySQL
- **Frontend:** HTML, CSS, Bootstrap 5
- **Security:** CSRF Tokens, Password Hashing, Session Management
- **Server:** Apache (XAMPP)

---

## 🗄 Database Design

Main tables:
- `users`
- `accounts`
- `transactions`
- `login_attempts`

All tables are normalized and linked using foreign keys.

---

## ⚙️ Installation & Setup

1. Install **XAMPP**
2. Clone this repository:
   ```bash
   git clone https://github.com/your-username/banking-system.git
# Secure Online Banking System

A secure, role-based online banking web application built using **PHP, MySQL, and Bootstrap**, with a strong focus on **cybersecurity best practices**.

---

## 🚀 Features

### 🔐 Authentication & Security
- User registration and login
- Secure password hashing (bcrypt)
- CSRF protection on all forms
- Login rate limiting (5 attempts / 15 minutes)
- Session hardening (session regeneration)
- Secure password reset with expiring tokens
- SQL Injection prevention using PDO prepared statements

### 👥 User Features
- Automatic bank account creation on registration
- View account balance and account number
- Secure money transfers
- Transaction history

### 🛠 Admin Features
- Role-based access control (Admin / User)
- Admin dashboard
- Block / unblock user accounts
- View user accounts and balances

---

## 🧰 Tech Stack

- **Backend:** PHP (PDO)
- **Database:** MySQL
- **Frontend:** HTML, CSS, Bootstrap 5
- **Security:** CSRF Tokens, Password Hashing, Session Management
- **Server:** Apache (XAMPP)

---

## 🗄 Database Design

Main tables:
- `users`
- `accounts`
- `transactions`
- `login_attempts`

All tables are normalized and linked using foreign keys.

---

## ⚙️ Installation & Setup

1. Install **XAMPP**
2. Clone this repository:
   ```bash
   git clone https://github.com/your-username/banking-system.git
3.Move the project to:
   C:\xampp\htdocs\
C:\xampp\htdocs\
4.Import database:

  Open phpMyAdmin
  Create a database (e.g. banking_system)
  Import the SQL file from /sql/database.sql

5.Update database credentials:
      config/db.php
6.Start Apache & MySQL from XAMPP
7.Open in browser:
     http://localhost/banking-system/public/

