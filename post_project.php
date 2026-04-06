<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";
$success = "";

// Check if user has client role or both
$user_id = $_SESSION['user_id'];
$stmt_role = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt_role->bind_param("i", $user_id);
$stmt_role->execute();
$stmt_role->bind_result($user_role);
$stmt_role->fetch();
$stmt_role->close();

if ($user_role === 'freelancer') {
    $error = "Only users registered as Clients can post jobs. Please update your profile settings.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error)) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $budget = floatval($_POST['budget']);
    $category = trim($_POST['category']);
    $required_skills = trim($_POST['required_skills']);
    $experience_level = $_POST['experience_level'];
    $project_length = $_POST['project_length'];

    if (empty($title) || empty($description) || empty($budget)) {
        $error = "Title, description, and budget are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO projects (user_id, title, description, budget, category, required_skills, experience_level, project_length) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdssss", $user_id, $title, $description, $budget, $category, $required_skills, $experience_level, $project_length);
        
        if ($stmt->execute()) {
            $success = "Job posted successfully!";
        } else {
            $error = "Failed to post job. Please try again.";
        }
        $stmt->close();
    }
}

require_once 'includes/header.php';
?>

<?php if (!empty($error) && $error === "Only users registered as Clients can post jobs. Please update your profile settings."): ?>
<div class="row justify-content-center mt-5">
    <div class="col-md-6 text-center">
        <div class="card p-5 border-0 shadow-sm rounded-4">
            <i class="bi bi-exclamation-triangle display-3 text-warning mb-3"></i>
            <h4 class="fw-bold mb-3"><?php echo htmlspecialchars($error); ?></h4>
            <a href="profile.php" class="btn btn-outline-primary rounded-pill px-4">Go to Profile Settings</a>
        </div>
    </div>
</div>
<?php else: ?>

<div class="row justify-content-center mt-4 mb-5">
    <div class="col-12">
        <!-- Progress Bar -->
        <div class="mb-5">
            <div class="d-flex align-items-center mb-2">
                <span class="text-muted fw-bold small">1/5</span>
                <span class="ms-2 text-muted small">Job post</span>
            </div>
            <div class="progress" style="height: 4px; border-radius: 2px;">
                <div class="progress-bar bg-upwork" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger rounded-3"><i class="bi bi-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success rounded-3"><i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?> <a href="view_works.php" class="alert-link text-decoration-none fw-bold">View in Job Feed</a>.</div>
        <?php endif; ?>

        <form action="post_project.php" method="POST">
            <div class="row g-5">
                <!-- Left Column: Form -->
                <div class="col-lg-7">
                    <h2 class="display-6 fw-bold text-dark mb-3">Let's start with a strong title.</h2>
                    <p class="text-muted fs-5 mb-5">This helps your job post stand out to the right candidates. It's the first thing they'll see, so make it count!</p>
                    
                    <div class="mb-4">
                        <label for="title" class="form-label fw-bold">Write a title for your job post</label>
                        <input type="text" name="title" class="form-control form-control-lg border px-4" id="title" placeholder="e.g. Build responsive WordPress site with booking/payment plugin" style="border-radius: 8px;" required>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="category" class="form-label fw-bold">Job Category</label>
                            <select name="category" class="form-select px-3" id="category" style="border-radius: 8px;">
                                <option value="Web Development">Web Development</option>
                                <option value="Mobile App Development">Mobile App Development</option>
                                <option value="Design & Creative">Design & Creative</option>
                                <option value="Writing & Translation">Writing & Translation</option>
                                <option value="Admin Support">Admin Support</option>
                                <option value="Data Science & AI">Data Science & AI</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="budget" class="form-label fw-bold">Fixed Price Budget ($)</label>
                            <input type="number" step="1" name="budget" class="form-control px-3" id="budget" placeholder="e.g. 500" style="border-radius: 8px;" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="experience_level" class="form-label fw-bold">Experience Level</label>
                            <select name="experience_level" class="form-select px-3" id="experience_level" style="border-radius: 8px;">
                                <option value="beginner">Entry Level</option>
                                <option value="intermediate" selected>Intermediate</option>
                                <option value="expert">Expert</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="project_length" class="form-label fw-bold">Project Length</label>
                            <select name="project_length" class="form-select px-3" id="project_length" style="border-radius: 8px;">
                                <option value="short">Short term (< 1 month)</option>
                                <option value="medium" selected>Medium term (1-3 months)</option>
                                <option value="long">Long term (3+ months)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="required_skills" class="form-label fw-bold">Required Skills</label>
                        <input type="text" name="required_skills" class="form-control px-3" id="required_skills" placeholder="e.g. PHP, React, MySQL (comma separated)" style="border-radius: 8px;">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-bold">Describe what you need</label>
                        <textarea name="description" class="form-control px-3" id="description" rows="6" placeholder="Provide a detailed description of your project..." style="border-radius: 8px;" required></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="dashboard.php" class="btn btn-light rounded-pill px-4 text-dark fw-bold border">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5">Post Job Now</button>
                    </div>
                </div>

                <!-- Right Column: Helper -->
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="card bg-light p-4 border-0 rounded-4 sticky-top" style="top: 100px;">
                        <h5 class="fw-bold mb-3"><i class="bi bi-lightbulb text-upwork me-2"></i>Example titles</h5>
                        <ul class="list-unstyled">
                            <li class="mb-3 d-flex align-items-start">
                                <i class="bi bi-dot text-upwork fs-4 me-1" style="line-height: 1;"></i>
                                <span>Build responsive WordPress site with booking/payment functionality</span>
                            </li>
                            <li class="mb-3 d-flex align-items-start">
                                <i class="bi bi-dot text-upwork fs-4 me-1" style="line-height: 1;"></i>
                                <span>Graphic designer needed to design ad creative for multiple campaigns</span>
                            </li>
                            <li class="mb-3 d-flex align-items-start">
                                <i class="bi bi-dot text-upwork fs-4 me-1" style="line-height: 1;"></i>
                                <span>Facebook ad specialist needed for product launch</span>
                            </li>
                        </ul>
                        
                        <hr>
                        
                        <h5 class="fw-bold mb-3"><i class="bi bi-info-circle text-upwork me-2"></i>Tips</h5>
                        <ul class="list-unstyled text-muted small">
                            <li class="mb-2"><i class="bi bi-check2 text-upwork me-2"></i>Be specific and descriptive</li>
                            <li class="mb-2"><i class="bi bi-check2 text-upwork me-2"></i>Use keywords that freelancers search for</li>
                            <li class="mb-2"><i class="bi bi-check2 text-upwork me-2"></i>Keep it clear and under 50 characters if possible</li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
