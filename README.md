# SEMS — Student Exam Management System

A complete PHP + MySQL web application for managing academic examinations.

---

## 🚀 Quick Start

### 1. Setup
- Copy the `sems/` folder into `htdocs/` (XAMPP) or `www/` (WAMP)
- Open phpMyAdmin and run `database.sql` to create the database and tables
- Edit `include/db.php` if your MySQL username/password differ from root/""

### 2. Login
| Role    | Email             | Password    |
|---------|-------------------|-------------|
| Admin   | admin@sems.com    | admin@1234  |
| Teacher | (created by admin)| sems@1234   |
| Student | (created by admin)| sems@1234   |

> **Note:** The default admin password hash in `database.sql` is for `admin@1234`.
> If it doesn't work, generate a fresh hash and update the SQL:
> ```php
> echo password_hash('admin@1234', PASSWORD_BCRYPT, ['cost'=>12]);
> ```

---

## 📁 Folder Structure

```
sems/
├── index.php                 ← Entry point (redirects to login)
├── loginpage.php             ← Login form
├── login.php                 ← Login processing
├── logout.php                ← Logout
├── register.php              ← Student registration form
├── register-process.php      ← Registration handler
├── forgot-password.php       ← OTP-based password reset
├── database.sql              ← Full DB setup with seed data
│
├── include/
│   ├── db.php                ← Database connection
│   ├── auth.php              ← Session guards
│   ├── functions.php         ← Helpers (grades, GPA, flash messages)
│   ├── layout.php            ← Sidebar + topbar (included in all pages)
│   └── layout_end.php        ← Closing tags
│
├── admin/                    ← Admin panel
│   ├── dashboardadmin.php
│   ├── approve-requests.php
│   ├── manage-users.php
│   ├── manage-students.php
│   ├── manage-teachers.php
│   ├── manage-exams.php
│   ├── manage-subjects.php
│   ├── manage-routine.php
│   ├── issue-admit-cards.php
│   └── publish-results.php
│
├── teacher/                  ← Teacher panel
│   ├── teacherdashboard.php
│   ├── enter-marks.php
│   ├── view-students.php
│   └── view-routine.php
│
├── student/                  ← Student panel
│   ├── studentdashboard.php
│   ├── view-admit-card.php
│   ├── view-result.php
│   ├── view-routine.php
│   └── profile.php
│
├── assets/
│   ├── css/style.css
│   └── js/main.js
│
└── uploads/
    └── documents/            ← Student uploaded documents
```

---

## 🔄 Workflow

### Admin Workflow
1. Student submits registration → appears in **Registration Requests**
2. Admin fills in program/semester → clicks **Approve** → account created
3. Admin creates **Exams** → sets **Subjects** → builds **Exam Routine**
4. Admin issues **Admit Cards** to eligible students
5. Teachers enter marks → Admin marks exam as **Completed**
6. Admin clicks **Generate & Publish** → results visible to students

### Teacher Workflow
1. Login → select exam & subject → enter marks → **Save as Draft**
2. Review → **Submit Marks** (locks the entry)

### Student Workflow
1. Register → wait for admin approval → login
2. View **Admit Card** → print for exam day
3. View **Exam Routine** → know the schedule
4. After results published → view **Results** with subject-wise breakdown

---

## 🔒 Security Features
- Prepared statements (no SQL injection)
- bcrypt password hashing (cost 12)
- Session fixation prevention (`session_regenerate_id`)
- Role-based access control on every page
- Brute-force lockout after 5 failed logins
- XSS prevention with `htmlspecialchars()`
- File upload validation (MIME type + size limit)

---

## ⚙️ Requirements
- PHP 8.0+
- MySQL 8.0+
- Apache (XAMPP/WAMP) or Nginx
- PHP extensions: `mysqli`, `mbstring`, `fileinfo`
