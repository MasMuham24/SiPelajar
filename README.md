# 🎓 SiPelajar

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white">
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white">
  <img src="https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white">
  <img src="https://img.shields.io/badge/Status-Active-success?style=for-the-badge">
  <img src="https://img.shields.io/github/license/MasMuham24/SiPelajar?style=for-the-badge">
</p>

<p align="center">
A modern <strong>School Management System</strong> built with <strong>Laravel 12</strong> that helps schools manage teachers, students, classrooms, attendance, and assignments through a secure role-based access system.
</p>

---

# 📖 Overview

**SiPelajar** is a web-based School Management System designed to simplify academic administration. It provides separate dashboards and permissions for **Administrators**, **Teachers**, and **Students**, making school management more organized and efficient.

The application includes attendance management, assignment management, user management, and classroom administration in one integrated platform.

---

# ✨ Features

## 🔐 Authentication

- Multi-role Authentication
- Manual Login System
- Role-Based Authorization
- Secure Password Authentication
- Profile Management

---

## 👨‍💼 Admin Features

- Dashboard
- Manage School Locations
- Manage Majors
- Manage Classrooms
- Manage Teachers
- Manage Students
- Manage User Accounts
- System Management

---

## 👨‍🏫 Teacher Features

- Dashboard
- Student Attendance
- Create Assignments
- Edit Assignments
- Delete Assignments
- Upload Assignment Attachments
- View Student Submissions
- Assignment Status Management

---

## 👨‍🎓 Student Features

- Dashboard
- School Attendance
- View Assignments
- Submit Assignments
- Upload Assignment Files
- View Submission Status

---

# 🛠 Tech Stack

| Technology | Version |
|------------|---------|
| Laravel | 12 |
| PHP | 8.2+ |
| MySQL | Latest |
| Blade | Template Engine |
| Bootstrap | 5 |
| HTML5 | ✓ |
| CSS3 | ✓ |
| JavaScript | ES6 |

---

# 📂 Folder Structure

```
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
tests/
```

---

# 🚀 Installation

## Clone Repository

```bash
git clone https://github.com/MasMuham24/SiPelajar.git
```

## Go to Project

```bash
cd SiPelajar
```

## Install Dependencies

```bash
composer install
```

## Copy Environment File

```bash
cp .env.example .env
```

Windows

```bash
copy .env.example .env
```

## Generate Application Key

```bash
php artisan key:generate
```

## Configure Database

Open `.env`

```
DB_DATABASE=your_database
DB_USERNAME=root
DB_PASSWORD=
```

## Run Migration & Seeder

```bash
php artisan migrate --seed
```

## Create Storage Link

```bash
php artisan storage:link
```

## Start Development Server

```bash
php artisan serve
```

Application URL

```
http://127.0.0.1:8000
```

---

# 👥 User Roles

| Role | Description |
|------|-------------|
| Admin | Full Access |
| Teacher | Manage Attendance & Assignments |
| Student | Attendance & Assignment Submission |

---

# 📋 Modules

- Authentication
- Dashboard
- School Location Management
- Major Management
- Classroom Management
- Teacher Management
- Student Management
- User Account Management
- Attendance System
- Assignment Management
- Assignment Submission

---

# 📈 Current Progress

| Module | Status |
|---------|--------|
| Authentication | ✅ Completed |
| Dashboard | ✅ Completed |
| School Location CRUD | ✅ Completed |
| Major CRUD | ✅ Completed |
| Classroom CRUD | ✅ Completed |
| Teacher CRUD | ✅ Completed |
| Student CRUD | ✅ Completed |
| User CRUD | ✅ Completed |
| Attendance System | ✅ Completed |
| Assignment CRUD | ✅ Completed |
| Assignment Submission | ✅ Completed |

---

# 📸 Screenshots

Coming Soon

```
screenshots/
│
├── login.png
├── admin-dashboard.png
├── teacher-dashboard.png
├── student-dashboard.png
├── attendance.png
├── assignments.png
└── submissions.png
```

---

# 🔒 Security

- CSRF Protection
- Form Validation
- Authentication Middleware
- Role Middleware
- Password Hashing
- Secure File Upload

---

# 📌 Future Improvements

- Assignment Grading
- Announcement Module
- Notification System
- Export PDF
- Export Excel
- Activity Log
- Responsive Mobile Layout
- Analytics Dashboard
- Attendance Reports

---

# 🤝 Contributing

Contributions are welcome.

1. Fork this repository
2. Create your feature branch

```bash
git checkout -b feature/NewFeature
```

3. Commit your changes

```bash
git commit -m "Add new feature"
```

4. Push to the branch

```bash
git push origin feature/NewFeature
```

5. Open a Pull Request

---

# 📄 License

This project is licensed under the MIT License.

---

# 👨‍💻 Author

**Muhammad Syafi'i**

- GitHub: https://github.com/MasMuham24

---

<p align="center">
Made with ❤️ using Laravel 12
</p>
