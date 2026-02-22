Election Management System (EMS2)
1️⃣ Problems it Solves

Manual election management is slow, error-prone, and non-transparent.

Difficulty in tracking voters, candidates, and election results.

Candidate images and voter data are not securely stored.

No role-based access control in traditional election systems.

Lack of real-time visualization of voting results.

2️⃣ Agenda

Build a secure, digital platform for managing elections.

Provide separate dashboards for Admins, Voters, and Candidates.

Enable secure voter registration, authentication, and voting.

Ensure real-time results and analytics for transparency.

Make the system responsive and easy to use.

3️⃣ What the Project Does

Admin Portal:

Create, edit, and manage elections.

Add, update, and delete candidates.

Manage voter registration and records.

View analytics and results with charts and tables.

Voter Portal:

Browse active elections.

Cast votes securely.

Track personal and election results.

Candidate Portal:

Manage profile and candidate image.

View personal vote share and rankings.

Public Pages:

Landing page with live stats and featured candidates.

Registration and login for users.

4️⃣ Cool Features

🏠 Public Pages: Candidate overview, live stats, landing page.

👤 Authentication & Roles: Admin, Voter, Candidate login, session security.

🎓 Voter Features: Vote securely, track elections, view results.

👨‍💼 Admin Features: Dashboard analytics, election/candidate/voter management.

👨‍💼 Candidate Features: Profile management, vote tracking, election ranking.

🔧 Technical Features:

Responsive UI with Tailwind CSS.

Secure image handling for candidates.

Chart.js for results visualization.

Clean, modern, mobile-friendly design.

5️⃣ Project Structure
EMS2/
├── admin/             # Admin portal
│   ├── dashboard.php
│   ├── elections.php
│   ├── candidates.php
│   ├── voters.php
│   ├── results.php
├── candidate/         # Candidate portal
│   ├── dashboard.php
│   ├── profile.php
│   ├── results.php
├── voter/             # Voter portal
│   ├── dashboard.php
│   ├── active_elections.php
│   ├── results.php
├── public/            # Public-facing pages
│   ├── index.php
│   ├── login.php
│   ├── register.php
├── includes/          # Shared code
│   ├── db.php
│   ├── auth.php
│   ├── image_utils.php
│   ├── navbar.php
│   ├── footer.php
├── uploads/           # Candidate images
├── assets/            # Static files (SVG, icons)
└── README.md

6️⃣ Technology Stack

Backend:

PHP 7.x / 8.x

MySQL

Frontend:

HTML5

Tailwind CSS

Font Awesome (icons)

JavaScript

7️⃣ Getting Started

Environment Setup:

Install XAMPP (Apache, PHP, MySQL).

Place project in C:\xampp\htdocs\EMS2.

Database:

Create a database (e.g., election_db).

Import or create the required tables (schema needed).

Configure Connection:

Update database credentials in includes/db.php.

Run Application:

Start Apache & MySQL in XAMPP.

Open http://localhost/EMS2/public/index.php
.

Register or log in to start using the system.