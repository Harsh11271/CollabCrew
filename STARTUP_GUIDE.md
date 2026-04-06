# Startup & Installation Guide

This guide will walk you through setting up **UpClone (My Project)** on your local Windows machine using XAMPP.

## Prerequisites
- Download and install [XAMPP for Windows](https://www.apachefriends.org/download.html).

---

## Step 1: Start XAMPP Server
1. Open the **XAMPP Control Panel**.
2. Click the **Start** button next to **Apache** (the web server).
3. Click the **Start** button next to **MySQL** (the database).
   *Wait until the background of both turns green.*

---

## Step 2: Database Initialization 
Because the database architecture is highly relational (managing users, projects, proposals, messages, and reviews), you will import the pre-built SQL file.

1. Open your browser and navigate to: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. **Important:** If you have an old database named `marketplace_db` from a previous version, select it and **Drop** (delete) all the tables. If you don't have one, create a new database named `marketplace_db`.
3. Select the `marketplace_db` database.
4. Click on the **Import** tab at the top of the screen.
5. Click **Choose File** and locate the `database.sql` file inside your project folder (`My_Project/database.sql`).
6. Scroll down and click **Import** (or **Go**). 
7. You should see a green success message, verifying that all 5 tables (`users`, `projects`, `proposals`, `messages`, `reviews`) have been constructed with their foreign keys intact.

---

## Step 3: Connect & Launch the Application
1. Ensure this entire project folder (`My_Project`) is placed correctly inside the XAMPP public directory (usually `C:\xampp\htdocs\My_Project` or `D:\XAMPP\Xampp\htdocs\My_Project`).
2. Open the file `config/db.php` in a text editor.
3. Ensure line 5 points to your database correctly:
   ```php
   $dbname = 'marketplace_db';
   ```
4. Open a new web browser tab and navigate to: [http://localhost/My_Project](http://localhost/My_Project)

---

## 🧪 Recommended Testing Flow
To fully experience the two-sided marketplace features (Messaging, Proposal Bidding, Hiring), we recommend opening **two different browser windows** (e.g., Chrome and an Incognito window) so you can log into two accounts simultaneously:

1. **Window 1 (The Client)**: Create an account. Click "Post a Job".
2. **Window 2 (The Freelancer)**: Create a second account. Go to "Find Work", click on the Job, and submit a "Proposal" (Bid amount & Cover letter).
3. **Window 1 (The Client)**: Go to your Dashboard, click on the Job, review the proposal, and click "Hire".
4. **Both Windows**: Navigate to the Dashboard, view the active contract, and click the "Messages" button to chat!
5. **Window 1 (The Client)**: When done, click "Mark Completed" and leave a 5-star review that will appear on the Freelancer's public profile.
