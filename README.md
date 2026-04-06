<div align="center">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap" />
  <img src="https://img.shields.io/badge/Railway-0B0D0E?style=for-the-badge&logo=railway&logoColor=white" alt="Railway" />

  <h1>🤝 CollabCrew</h1>
  <p>A professional, two-sided freelance talent marketplace built natively in PHP and MySQL.</p>
  
  <strong><a href="https://elegant-prosperity-production-3c20.up.railway.app">🚀 View Live Demo</a></strong>
</div>

---

## 📖 Overview
CollabCrew is heavily inspired by the modern UI and architecture of Upwork. It provides a seamless ecosystem for **Clients** to post jobs and hire talent, and **Freelancers** to bid on projects and build their professional reputation.

## 📸 Platform Previews

<div align="center">
  <img src="assets/landing-page.png" alt="CollabCrew Landing Page" width="800"/>
  <p><em>Modern, responsive landing page designed to attract top talent and clients</em></p>
</div>

<div align="center">
  <img src="assets/job-feed.png" alt="Freelancer Job Feed" width="800"/>
  <p><em>Robust Job Feed with dynamic querying and real-time updates</em></p>
</div>

<div align="center">
  <img src="assets/client-dashboard.png" alt="Client Job Dashboard" width="800"/>
  <p><em>Client Dashboard for managing postings and reviewing proposals</em></p>
</div>

<div align="center">
  <img src="assets/proposal.png" alt="Submitting a Proposal" width="800"/>
  <p><em>Detailed Proposal Submission System</em></p>
</div>

<div align="center">
  <img src="assets/messaging.png" alt="Real-time Messaging" width="800"/>
  <p><em>Secure direct messaging between Client and Hired Freelancer</em></p>
</div>

---

## ✨ Key Features

### 🔄 Dual-Sided Unified Accounts
- Users can seamlessly switch between **Client mode** (posting jobs, hiring) and **Freelancer mode** (submitting proposals, finding work) from a single account.
- Complete settings dashboard to update Professional Titles, Bios, and Hourly Rates.

### 📝 Advanced Proposal System
- Freelancers submit detailed **Proposals** including a custom cover letter, specific bid amount, and estimated delivery timeline.
- Clients get a visually rich dashboard to review these proposals, compare bid amounts, and officially **Hire** the best candidate.

### 💬 Built-in Communication & Reviews
- **Direct Messaging**: Once a proposal is accepted, an exclusive chat interface opens up for direct collaboration.
- **Review System**: When a client marks a project as "Completed", they must leave a 1-5 star rating and comment, firmly attaching feedback to the Freelancer's public profile.

---

## 🛠️ Architecture & Under the Hood

### The Database Hierarchy 
Built with a highly normalized, relational MySQL schema using **Foreign Key Cascading**. This ensures absolute data integrity:
> *If a user deletes their account, all their projects, proposals, active contracts, and messages are instantly purged from the system.*

### Environment Agnostic Setup (`db.php`)
The database connection handles fallback detection intelligently. By utilizing environment variables (`getenv()`), the code works perfectly on **both** a Local XAMPP environment and a Cloud Production environment (like Railway) without altering a single line of code.

### Tech Stack
*   **Frontend**: HTML5, CSS3, Bootstrap 5 (Customized Green/White Upwork theme), Bootstrap Icons
*   **Backend**: PHP 8.1+ (Session management, secure prepared statements for SQL injection prevention)
*   **Database**: MySQL (Relational tables)
*   **Deployment**: Railway (Containerized Nixpacks build)

---

## 🚀 Running the Project

### Local Development (XAMPP/MAMP)
Refer to the `STARTUP_GUIDE.md` included in this repository for step-by-step local setup instructions.

### Cloud Deployment
Configured out-of-the-box for [Railway.app](https://railway.app/).
1. Connect this repo to Railway via the `deployment` branch.
2. Spin up a MySQL service and inject the variables: `MYSQLHOST`, `MYSQLUSER`, `MYSQL_ROOT_PASSWORD`, `MYSQL_DATABASE`.
3. The `composer.json` ensures the `ext-mysqli` driver is built at runtime.
