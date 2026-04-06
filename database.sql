CREATE DATABASE IF NOT EXISTS marketplace_db;
USE marketplace_db;

-- Users (Unified account for client/freelancer switching)
CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('client', 'freelancer', 'both') DEFAULT 'both',
    title VARCHAR(150) DEFAULT NULL, -- e.g., "Full Stack Developer"
    bio TEXT DEFAULT NULL,
    hourly_rate DECIMAL(10,2) DEFAULT NULL,
    skills VARCHAR(500) DEFAULT NULL,
    portfolio_url VARCHAR(255) DEFAULT NULL,
    experience_level ENUM('beginner', 'intermediate', 'expert') DEFAULT 'intermediate',
    profile_picture VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY username_unique (username),
    UNIQUE KEY email_unique (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Projects (Jobs posted by clients)
CREATE TABLE IF NOT EXISTS projects (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL, -- The client
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    budget DECIMAL(10,2) NOT NULL,
    category VARCHAR(100) DEFAULT NULL,
    required_skills VARCHAR(255) DEFAULT NULL, -- Comma-separated or basic JSON
    experience_level ENUM('beginner', 'intermediate', 'expert') DEFAULT 'intermediate',
    project_length ENUM('short', 'medium', 'long') DEFAULT 'medium',
    status ENUM('open', 'hired', 'completed', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_project_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Proposals (Bids from freelancers)
CREATE TABLE IF NOT EXISTS proposals (
    id INT NOT NULL AUTO_INCREMENT,
    project_id INT NOT NULL,
    freelancer_id INT NOT NULL,
    cover_letter TEXT NOT NULL,
    bid_amount DECIMAL(10,2) NOT NULL,
    delivery_time VARCHAR(100) NOT NULL, -- e.g., "Less than 1 week"
    status ENUM('pending', 'accepted', 'rejected', 'withdrawn') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_proposal (project_id, freelancer_id),
    CONSTRAINT fk_proposal_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,
    CONSTRAINT fk_proposal_freelancer FOREIGN KEY (freelancer_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Messages (Communication post-acceptance or during bidding)
CREATE TABLE IF NOT EXISTS messages (
    id INT NOT NULL AUTO_INCREMENT,
    project_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_msg_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,
    CONSTRAINT fk_msg_sender FOREIGN KEY (sender_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_msg_receiver FOREIGN KEY (receiver_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews (Feedback after project completion)
CREATE TABLE IF NOT EXISTS reviews (
    id INT NOT NULL AUTO_INCREMENT,
    project_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    reviewee_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_rev_project FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE,
    CONSTRAINT fk_rev_reviewer FOREIGN KEY (reviewer_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_rev_reviewee FOREIGN KEY (reviewee_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contracts (Active hires)
CREATE TABLE IF NOT EXISTS contracts (
    id INT NOT NULL AUTO_INCREMENT,
    project_id INT NOT NULL,
    client_id INT NOT NULL,
    freelancer_id INT NOT NULL,
    proposal_id INT NOT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_contract_project FOREIGN KEY (project_id) REFERENCES projects (id),
    CONSTRAINT fk_contract_client FOREIGN KEY (client_id) REFERENCES users (id),
    CONSTRAINT fk_contract_freelancer FOREIGN KEY (freelancer_id) REFERENCES users (id),
    CONSTRAINT fk_contract_proposal FOREIGN KEY (proposal_id) REFERENCES proposals (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
