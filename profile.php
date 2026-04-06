<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $title = trim($_POST['title']);
    $bio = trim($_POST['bio']);
    $hourly_rate = !empty($_POST['hourly_rate']) ? floatval($_POST['hourly_rate']) : NULL;
    $role = $_POST['role'];
    $skills = trim($_POST['skills'] ?? '');
    $portfolio_url = trim($_POST['portfolio_url'] ?? '');
    $exp_level = $_POST['experience_level'] ?? 'intermediate';

    $update_stmt = $conn->prepare("UPDATE users SET title = ?, bio = ?, hourly_rate = ?, role = ?, skills = ?, portfolio_url = ?, experience_level = ? WHERE id = ?");
    $update_stmt->bind_param("ssdssssi", $title, $bio, $hourly_rate, $role, $skills, $portfolio_url, $exp_level, $user_id);
    if ($update_stmt->execute()) {
        $success = "Profile updated successfully.";
    } else {
        $error = "Failed to update profile.";
    }
    $update_stmt->close();
}

// Handle Password Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    if (empty($current_password) || empty($new_password)) {
        $error = "Both password fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();
        
        if (password_verify($current_password, $hashed_password)) {
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $pw_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $pw_stmt->bind_param("si", $new_hashed, $user_id);
            if ($pw_stmt->execute()) {
                $success = "Password updated successfully.";
            } else {
                $error = "Failed to update password.";
            }
            $pw_stmt->close();
        } else {
            $error = "Current password is incorrect.";
        }
    }
}

// Fetch current user data
$stmt = $conn->prepare("SELECT username, email, title, bio, hourly_rate, role, profile_picture, created_at, skills, portfolio_url, experience_level FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $title, $bio, $hourly_rate, $role, $profile_picture, $created_at, $skills, $portfolio_url, $experience_level);
$stmt->fetch();
$stmt->close();

require_once 'includes/header.php';
?>

<div class="row mt-4">
    <!-- Left Sidebar Settings Menu -->
    <div class="col-md-3 mb-4">
        <div class="card p-3 shadow-sm border-0">
            <h5 class="fw-bold mb-3 ms-2">Settings</h5>
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <button class="nav-link text-start active" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab" aria-selected="true">Profile Details</button>
                <button class="nav-link text-start" id="v-pills-security-tab" data-bs-toggle="pill" data-bs-target="#v-pills-security" type="button" role="tab" aria-selected="false">Password & Security</button>
                <button class="nav-link text-start" id="v-pills-proposals-tab" data-bs-toggle="pill" data-bs-target="#v-pills-proposals" type="button" role="tab" aria-selected="false">My Proposals</button>
            </div>
        </div>
    </div>

    <!-- Right Side Content -->
    <div class="col-md-9">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger rounded-3"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success rounded-3"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="tab-content" id="v-pills-tabContent">
            
            <!-- Profile Info Tab -->
            <div class="tab-pane fade show active" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                <div class="card p-4 shadow-sm border-0 mb-4">
                    <h4 class="fw-bold mb-4">Public Profile</h4>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0 fw-bold"><?php echo htmlspecialchars($username); ?></h5>
                            <p class="text-muted mb-0"><?php echo htmlspecialchars($email); ?></p>
                            <small class="text-muted">Member since <?php echo date('M Y', strtotime($created_at)); ?></small>
                        </div>
                    </div>

                    <form action="profile.php" method="POST">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Your Primary Role</label>
                            <select name="role" class="form-select bg-light border-0">
                                <option value="both" <?php echo ($role == 'both') ? 'selected' : ''; ?>>Both (Client & Freelancer)</option>
                                <option value="client" <?php echo ($role == 'client') ? 'selected' : ''; ?>>Client (Hiring)</option>
                                <option value="freelancer" <?php echo ($role == 'freelancer') ? 'selected' : ''; ?>>Freelancer (Looking for work)</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold small">Professional Title</label>
                                <input type="text" name="title" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($title ?? ''); ?>" placeholder="e.g. Senior Full Stack Engineer">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold small">Hourly Rate ($)</label>
                                <input type="number" step="0.01" name="hourly_rate" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($hourly_rate ?? ''); ?>" placeholder="e.g. 45.00">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Skills (comma separated)</label>
                            <input type="text" name="skills" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($skills ?? ''); ?>" placeholder="e.g. PHP, React, MySQL, UI Design">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Portfolio URL</label>
                                <input type="url" name="portfolio_url" class="form-control bg-light border-0" value="<?php echo htmlspecialchars($portfolio_url ?? ''); ?>" placeholder="https://yourportfolio.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Experience Level</label>
                                <select name="experience_level" class="form-select bg-light border-0">
                                    <option value="beginner" <?php echo ($experience_level == 'beginner') ? 'selected' : ''; ?>>Entry Level</option>
                                    <option value="intermediate" <?php echo ($experience_level == 'intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                                    <option value="expert" <?php echo ($experience_level == 'expert') ? 'selected' : ''; ?>>Expert</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small">Professional Overview (Bio)</label>
                            <textarea name="bio" class="form-control bg-light border-0" rows="5" placeholder="Highlight your top skills, experience, and interests."><?php echo htmlspecialchars($bio ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-pill px-4">Save Changes</button>
                    </form>
                </div>
            </div>

            <!-- Password & Security Tab -->
            <div class="tab-pane fade" id="v-pills-security" role="tabpanel" aria-labelledby="v-pills-security-tab">
                <div class="card p-4 shadow-sm border-0">
                    <h4 class="fw-bold mb-4">Password & Security</h4>
                    <form action="profile.php" method="POST">
                        <input type="hidden" name="update_password" value="1">
                        <div class="mb-3 w-75">
                            <label class="form-label fw-bold small">Current Password</label>
                            <input type="password" name="current_password" class="form-control bg-light border-0" required>
                        </div>
                        <div class="mb-4 w-75">
                            <label class="form-label fw-bold small">New Password</label>
                            <input type="password" name="new_password" class="form-control bg-light border-0" required>
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Update Password</button>
                    </form>
                </div>
            </div>

            <!-- Active Proposals Tab -->
            <div class="tab-pane fade" id="v-pills-proposals" role="tabpanel" aria-labelledby="v-pills-proposals-tab">
                <div class="card p-4 shadow-sm border-0">
                    <h4 class="fw-bold mb-4">My Submitted Proposals</h4>
                    <?php
                    $app_sql = "SELECT p.id, p.title as project_title, pr.bid_amount, pr.status, pr.created_at 
                                FROM proposals pr 
                                JOIN projects p ON pr.project_id = p.id 
                                WHERE pr.freelancer_id = ? 
                                ORDER BY pr.created_at DESC";
                    $app_stmt = $conn->prepare($app_sql);
                    $app_stmt->bind_param("i", $user_id);
                    $app_stmt->execute();
                    $app_result = $app_stmt->get_result();
                    
                    if ($app_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border-top">
                                <thead class="table-light text-muted small">
                                    <tr>
                                        <th>PROJECT</th>
                                        <th>YOUR BID</th>
                                        <th>STATUS</th>
                                        <th>DATE SUBMITTED</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($app = $app_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><a href="project_details.php?id=<?php echo $app['id']; ?>" class="text-decoration-none fw-bold text-upwork"><?php echo htmlspecialchars($app['project_title']); ?></a></td>
                                            <td class="fw-bold text-dark">$<?php echo number_format($app['bid_amount'], 2); ?></td>
                                            <td>
                                                <?php if($app['status'] == 'pending'): ?>
                                                    <span class="badge bg-warning text-dark border rounded-pill">Pending</span>
                                                <?php elseif($app['status'] == 'accepted'): ?>
                                                    <span class="badge bg-success rounded-pill">Hired</span>
                                                <?php elseif($app['status'] == 'withdrawn'): ?>
                                                    <span class="badge bg-secondary rounded-pill">Withdrawn</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger rounded-pill">Rejected</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted small"><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-folder2-open display-4 text-muted mb-3 d-block"></i>
                            <h5 class="fw-bold">No active proposals</h5>
                            <p class="text-muted">You haven't submitted any proposals yet.</p>
                            <a href="view_works.php" class="btn btn-outline-primary rounded-pill px-4 mt-2">Search for jobs</a>
                        </div>
                    <?php endif; 
                    $app_stmt->close();
                    ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
