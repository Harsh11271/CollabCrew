# CollabCrew: A Professional Freelance Marketplace

CollabCrew is a fully-featured, two-sided freelance talent marketplace built natively in PHP and MySQL. Heavily inspired by the modern UI and architecture of Upwork, it provides a seamless ecosystem for **Clients** to post jobs and hire talent, and **Freelancers** to bid on projects and build their professional reputation.

## 🚀 Key Features

### Dual-Sided Unified Accounts
- Users can seamlessly switch between **Client mode** (posting jobs, hiring) and **Freelancer mode** (submitting proposals, finding work) from a single account.
- Complete settings dashboard to update Professional Titles, Bios, and Hourly Rates.

### Advanced Proposal System
- Freelancers don't just "apply" with a click. They must submit detailed **Proposals** including a custom cover letter, a specific bid amount, and an estimated delivery timeline.
- Clients get a visually rich dashboard to review these proposals, compare bid amounts, read cover letters, and officially **Hire** the best candidate.

### Interactive Work Dashboards
- **For Clients**: Manage active postings, view proposal counts, and mark active contracts as "Completed".
- **For Freelancers**: View a dedicated "Active Contracts" dashboard tracking accepted proposals and agreed-upon bid amounts.

### Built-in Communication & Reviews
- **Direct Messaging**: Once a proposal is accepted, an exclusive chat interface opens up allowing the client and freelancer to communicate directly on the platform.
- **Review System**: When a client marks a project as "Completed", they are prompted to leave a 1-5 star rating and comment. This feedback is permanently attached to the Freelancer's public profile.

### Modern Job Feed (Find Work)
- A robust, query-based job feed allowing freelancers to search jobs by keyword and see immediately the required skills, budget, project length, and experience level.

---

## 🛠️ Technology Stack
*   **Frontend**: HTML5, CSS3, Bootstrap 5 (Customized Green/White Upwork theme), Bootstrap Icons
*   **Backend**: PHP (Session management, prepared statements)
*   **Database**: MySQL (Relational tables with foreign key cascading)

## 📦 File Architecture 
*   `index.php` / `register.php`: Authentication and onboarding.
*   `dashboard.php`: The main control bridge for active jobs and contracts.
*   `view_works.php`: The public job feed for freelancers.
*   `post_project.php`: Form for clients to construct detailed job listings.
*   `project_details.php`: Handles viewing job specifics, submitting proposals, and client proposal review.
*   `freelancer_profile.php`: Public portfolios showcasing bio, skills, and past reviews.
*   `messages.php`: Real-time style message board for hired contracts.
*   `profile.php`: The user settings portal.

## ⚙️ How to Run Locally 
Please refer to the detailed `STARTUP_GUIDE.md` file included in this repository for step-by-step instructions on running this application on a local development server like XAMPP.

## 🚀 Deployment to Railway
This project is configured for automated deployment to [Railway.app](https://railway.app).

1.  **Branch Selection**: Connect your GitHub repository to Railway and select the `deployment` branch.
2.  **Add MySQL**: Add a MySQL service to your Railway project.
3.  **Variables**: Link the MySQL service variables to your web service.
4.  **Database Import**: Import the `database.sql` file into the Railway MySQL instance via the dashboard.
