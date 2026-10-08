# Skillbridg — Skill Exchange & Peer Learning Portal

**Project Type:** College-Level MCA Minor Project (Web Development Units 1–3)  
**Version:** 1.0  
**Technology Stack:** HTML5, CSS3, Vanilla JavaScript, Core PHP, MySQL  

---

## 📌 Product Overview

Skillbridg is a college-focused web application that enables students to exchange knowledge and learn technical/practical skills from one another.

Students can:
- Register and manage a student account securely.
- Build a personal profile with academic details and bio.
- List skills they can **Teach** and skills they want to **Learn**.
- Search for skills and discover peer student providers.
- Send and receive structured **Learning Requests**.
- Accept or reject requests.
- Track request lifecycle states (**Pending** $\rightarrow$ **Accepted** $\rightarrow$ **Completed** or **Pending** $\rightarrow$ **Rejected**).
- Submit post-completion rating and feedback.
- Monitor metrics via a dynamic **Dashboard**.

---

## 🛠️ Technology Architecture

- **Frontend:** HTML5, CSS3 (Vanilla CSS design system), Vanilla JavaScript (Form validation, confirm dialogs).
- **Backend:** Core PHP (Procedural/Modular architecture, Session management, PDO Prepared Statements).
- **Database:** MySQL (Relational schema, InnoDB engine, Foreign Key integrity constraints).

---

## 📂 Project Directory Structure

```text
Skillbridg/
├── config/
│   └── database.php       # Central PDO MySQL connection setup
├── includes/
│   ├── auth.php           # Authentication & authorization helpers
│   ├── functions.php      # Reusable helpers & sanitization
│   ├── header.php         # Page header template
│   ├── footer.php         # Page footer template
│   └── navbar.php         # Dynamic navigation bar
├── public/
│   ├── index.php          # Homepage / Landing page
│   ├── register.php       # Student registration
│   ├── login.php          # Student login
│   ├── logout.php         # Logout session handler
│   ├── dashboard.php      # Main student dashboard & stats
│   ├── profile.php        # View student profile
│   ├── edit-profile.php   # Edit profile information
│   ├── skills.php         # Manage teach & learn skills
│   ├── add-skill.php      # Add new skill mapping
│   ├── edit-skill.php     # Edit proficiency / skill type
│   ├── delete-skill.php   # Delete skill mapping
│   ├── find-skills.php    # Skill search & student discovery
│   ├── student.php        # Public peer student profile
│   ├── send-request.php   # Send learning request
│   ├── requests.php       # Incoming & outgoing request portal
│   ├── accept-request.php # Accept request handler
│   ├── reject-request.php # Reject request handler
│   ├── complete-request.php # Mark interaction completed
│   └── feedback.php       # Submit post-completion feedback
├── assets/
│   ├── css/
│   │   └── style.css      # Core CSS design tokens & stylesheet
│   └── js/
│       └── script.js      # Client validation & DOM interaction
├── database/
│   ├── schema.sql         # DDL database schema script
│   └── seed.sql           # Sample demo seed data
├── index.php              # Root entry point redirect
└── README.md              # Project documentation
```

---

## 🚀 Setup & Installation Instructions

1. **Database Setup:**
   - Open MySQL (e.g., via XAMPP/WAMP phpMyAdmin or MySQL CLI).
   - Execute the SQL DDL script located at `database/schema.sql`.
   - (Optional) Import sample seed data from `database/seed.sql`.

2. **Configuration:**
   - Check `config/database.php` to verify MySQL connection credentials:
     - Host: `localhost`
     - Database Name: `skillbridg_db`
     - Username: `root`
     - Password: `""` (or your MySQL password)

3. **Running the Application:**
   - Move the `SkillBridge` project folder to your local server document root (e.g., `htdocs/` for XAMPP).
   - Start Apache & MySQL services.
   - Open your browser and navigate to: `http://localhost/SkillBridge/` or `http://localhost/SkillBridge/public/index.php`.

---

## 🔑 Demo Accounts (from `seed.sql`)

- **Password for all demo accounts:** `password123`
- **Rahul Sharma:** `rahul@example.com` (Teaches Python & MySQL)
- **Ananya Verma:** `ananya@example.com` (Teaches Web Dev & UI/UX)
- **Vikram Singh:** `vikram@example.com` (Teaches Core PHP & Data Structures)
- **Priya Nair:** `priya@example.com` (Teaches Public Speaking & ML Basics)

---

## 🎓 Academic Viva & Syllabus Alignment

Skillbridg satisfies Web Development Units 1–3 syllabus requirements:
- **Unit 1:** Semantic HTML markup, CSS Flexbox/Grid responsive styling, JavaScript DOM manipulation & client-side validation.
- **Unit 2:** Core PHP processing, GET/POST handling, session authorization, custom function helpers, string/array operations.
- **Unit 3:** MySQL relational database schema, PDO prepared statements for SQLi protection, relational JOIN queries, full CRUD operations.
