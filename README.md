# 🎓 SiPelajar

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white">
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white">
  <img src="https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white">
  <img src="https://img.shields.io/badge/Status-Version_1.0-success?style=for-the-badge">
</p>

<p align="center">
  A comprehensive <strong>School Management System</strong> built with <strong>Laravel 12</strong>, designed to streamline academic administration through secure, role-based access control.
</p>

---

# 📖 Overview

**SiPelajar** is an integrated web-based platform designed to optimize school operational efficiency. It provides tailored dashboards and specialized modules for **Administrators**, **Teachers**, **Students**, and **Homeroom Teachers (Wali Kelas)**, fostering a more organized and data-driven educational environment.

---

# ✨ Core Features

## 🔐 Authentication & Security
- Secure Multi-role Authentication & Authorization.
- Role-based Access Control (RBAC).
- Comprehensive Form Validation & CSRF Protection.

## 👨‍💼 Administrator
- **Management Modules**: Comprehensive CRUD operations for School Locations, Majors, Classrooms, Teachers, Students, and User Accounts.
- **Data Utility**: Bulk actions for classroom management and robust Excel-based data import capabilities.

## 👨‍🏫 Teacher
- **Assignment Management**: Full CRUD lifecycle for assignments with file attachment support.
- **Submission & Grading**: Real-time monitoring of student submissions and integrated grading/feedback system.
- **Attendance**: Streamlined tracking of personal and classroom attendance.
- **Academic Analytics**: Dedicated Grade Recap module with custom filtering for data-driven assessment.

## 👨‍🎓 Student
- **Assignment Workflow**: View, submit, and update assignments with status tracking (Dynamic submission disabling after grading).
- **Academic Tracking**: View attendance records and real-time latest assessment scores.

## 🤝 Homeroom Teacher (Wali Kelas)
- **Attendance Oversight**: Dedicated verification and reporting tools for classroom attendance.

---

# 🛠 Tech Stack

| Technology | Purpose |
|------------|---------|
| Laravel 12 | Backend Framework |
| PHP 8.2+ | Language |
| MySQL | Database |
| Blade | Template Engine |
| Bootstrap 5 | UI/UX |

---

# 🚀 Setup Instructions

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/MasMuham24/SiPelajar.git
   ```
2. **Install Dependencies**:
   ```bash
   composer install
   npm install
   ```
3. **Environment Setup**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. **Database Migration & Seeding**:
   ```bash
   php artisan migrate --seed
   ```
5. **Storage Setup**:
   ```bash
   php artisan storage:link
   ```
6. **Launch**:
   ```bash
   php artisan serve
   ```

---

# 📈 Progress Status

| Module | Status |
|---------|--------|
| Authentication & RBAC | ✅ Completed |
| Administration (CRUDs) | ✅ Completed |
| Assignment & Submission | ✅ Completed |
| Attendance System | ✅ Completed |
| Grade Recap & Analytics | ✅ Completed |
| Homeroom Teacher Verification | ✅ Completed |

---

# 📌 Future Roadmap
- PDF/Excel Export Capabilities.
- Announcement & Notification System.
- Activity Logs & Advanced Analytics Dashboard.
- Responsive Mobile-first Layout Refinement.

---

# 📄 License
This project is licensed under the MIT License.

# 👨‍💻 Author
**Muhammad Syafi'i** | [GitHub](https://github.com/MasMuham24)
