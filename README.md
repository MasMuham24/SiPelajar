# 🎓 SiPelajar

---

# 📖 Overview

**SiPelajar** is an integrated web-based platform designed to optimize school operational efficiency. It provides tailored dashboards and specialized modules for **Administrators**, **Teachers**, **Students**, and **Homeroom Teachers (Wali Kelas)**, fostering a more organized and data-driven educational environment.

---

# ✨ Core Features

## 🔐 Authentication & Security

* Secure Multi-role Authentication & Authorization.
* Role-based Access Control (RBAC).
* Comprehensive Form Validation & CSRF Protection.

## 👨‍💼 Administrator

* **Management Modules**: Comprehensive CRUD operations for School Locations, Majors, Classrooms, Teachers, Students, and User Accounts.
* **Data Utility**: Bulk actions for classroom management and robust Excel-based data import capabilities.

## 👨‍🏫 Teacher

* **Assignment Management**: Full CRUD lifecycle for assignments with file attachment support.
* **Submission & Grading**: Real-time monitoring of student submissions and integrated grading/feedback system.
* **Attendance**: Streamlined tracking of personal and classroom attendance.
* **Academic Analytics**: Dedicated Grade Recap module with custom filtering for data-driven assessment.

## 👨‍🎓 Student

* **Assignment Workflow**: View, submit, and update assignments with status tracking (Dynamic submission disabling after grading).
* **Academic Tracking**: View attendance records and real-time latest assessment scores.

## 🤝 Homeroom Teacher (Wali Kelas)

* **Attendance Oversight**: Dedicated verification and reporting tools for classroom attendance.

---

# 🛠 Tech Stack

| Technology  | Purpose           |
| ----------- | ----------------- |
| Laravel 12  | Backend Framework |
| PHP 8.2+    | Language          |
| MySQL       | Database          |
| Blade       | Template Engine   |
| Bootstrap 5 | UI/UX             |

---

# 🚀 Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/MasMuham24/SiPelajar.git
cd SiPelajar
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Migration & Seeding

```bash
php artisan migrate --seed
```

### 5. Storage Setup

```bash
php artisan storage:link
```

### 6. Launch

```bash
php artisan serve
```

The application will be available at:

```text
http://127.0.0.1:8000
```

---

# 🔑 Demo Account

Use the following account to access the application:

| Field        | Value     |
| ------------ | --------- |
| **Username** | `susanto` |
| **Password** | `123456`  |

> **Note:** This is a demo account intended for testing and demonstration purposes.

---

# 📈 Progress Status

| Module                        | Status      |
| ----------------------------- | ----------- |
| Authentication & RBAC         | ✅ Completed |
| Administration (CRUDs)        | ✅ Completed |
| Assignment & Submission       | ✅ Completed |
| Attendance System             | ✅ Completed |
| Grade Recap & Analytics       | ✅ Completed |
| Homeroom Teacher Verification | ✅ Completed |

---

# 📌 Future Roadmap

* PDF/Excel Export Capabilities.
* Announcement & Notification System.
* Activity Logs & Advanced Analytics Dashboard.
* Responsive Mobile-first Layout Refinement.

---

# 📄 License

This project is licensed under the MIT License.

---

# 👨‍💻 Author

**Muhammad Syafi'i** | [GitHub](https://github.com/MasMuham24)
