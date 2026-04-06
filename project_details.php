<?php
session_start();
require_once 'config/db.php';

if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$project_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Handle Proposal Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_proposal'])) {
    $cover_letter = trim($_POST['cover_letter']);
    $bid_amount = floatval($_POST['bid_amount']);
    $delivery_time = trim($_POST['delivery_time']);

    if (empty($cover_letter) || empty($bid_amount) || empty($delivery_time)) {
        $error = "All proposal fields are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO proposals (project_id, freelancer_id, cover_letter, bid_amount, delivery_time) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisds", $project_id, $user_id, $cover_letter, $bid_amount, $delivery_time);
        if ($stmt->execute()) {
            $success = "Your proposal was submitted successfully!";
        } else {
            $error = "You have already submitted a proposal for this job or an error occurred.";
        }
        $stmt->close();
    }
}

// Handle session flash messages
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Fetch project details
$stmt = $conn->prepare("SELECT p.*, u.username as client_name, u.created_at as client_since FROM projects p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
    header("Location: view_works.php");
    exit;
}

$is_owner = ($project['user_id'] == $user_id);

// If freelancer, check if they already applied
$has_applied = false;
$proposal_status = "";
if (!$is_owner) {
    $chk = $conn->prepare("SELECT status FROM proposals WHERE project_id = ? AND freelancer_id = ?");
    $chk->bind_param("ii", $project_id, $user_id);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows > 0) {
        $has_applied = true;
        $chk->bind_result($proposal_status);
        $chk->fetch();
    }
    $chk->close();
}

require_once 'includes/header.php';
?>

<div class="row mt-4 mb-5 justify-content-center">
    <!-- Main Content -->
    <div class="col-md-9">
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger rounded-3"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success rounded-3"><i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm p-0 mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="p-5 bg-white border-bottom">
                <h2 class="fw-bold mb-4 fs-2 text-dark"><?php echo htmlspecialchars($project['title']); ?></h2>
                <div class="d-flex flex-wrap gap-4 text-muted small fw-bold text-uppercase mb-4">
                    <span><i class="bi bi-clock border rounded-circle p-1 me-1 text-primary"></i> Posted <?php echo date('M d, Y', strtotime($project['created_at'])); ?></span>
                    <span><i class="bi bi-geo-alt border rounded-circle p-1 me-1 text-success"></i> Worldwide</span>
                </div>
                <!-- Project Tags & Details -->
                <div class="row border-top border-bottom py-4 mb-4 bg-light bg-opacity-50">
                    <div class="col-md-4 mb-3 mb-md-0 d-flex align-items-center">
                        <i class="bi bi-cash-stack fs-3 text-secondary me-3"></i>
                        <div>
                            <div class="fw-bold text-dark">$<?php echo number_format($project['budget'], 2); ?></div>
                            <div class="text-muted small">Fixed-price</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0 d-flex align-items-center">
                        <i class="bi bi-person-badge fs-3 text-secondary me-3"></i>
                        <div>
                            <div class="fw-bold text-dark"><?php echo ucfirst($project['experience_level']); ?></div>
                            <div class="text-muted small">Experience Level</div>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <i class="bi bi-calendar-range fs-3 text-secondary me-3"></i>
                        <div>
                            <div class="fw-bold text-dark"><?php echo ucfirst($project['project_length']); ?></div>
                            <div class="text-muted small">Project Length</div>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <p class="fs-5 text-dark" style="line-height: 1.8;">
                        <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                    </p>
                </div>

                <div class="mb-4">
                    <h5 class="fw-bold mb-3 fs-5">Skills and Expertise</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <?php 
                        if (!empty($project['required_skills'])) {
                            $skills = explode(',', $project['required_skills']);
                            foreach($skills as $skill) {
                                echo '<span class="badge bg-light text-secondary border rounded-pill px-3 py-2 fw-medium fs-6">'.htmlspecialchars(trim($skill)).'</span>';
                            }
                        } else {
                            echo '<span class="text-muted fst-italic">Not specified</span>';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="mb-4 border-top pt-4">
                    <h5 class="fw-bold mb-3 fs-5">About the Client</h5>
                    <div class="d-flex gap-4 small pt-2">
                        <div>
                            <div class="fw-bold text-dark"><i class="bi bi-patch-check-fill text-primary"></i> Payment method verified</div>
                            <div class="text-muted mt-1"><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i> 5.0 of 1 reviews</div>
                        </div>
                        <div class="border-start ps-4">
                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($project['client_name']); ?></div>
                            <div class="text-muted mt-1">Member since <?php echo date('M d, Y', strtotime($project['client_since'])); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (!$is_owner): ?>
                <div class="p-4 bg-light border-top text-center">
                    <?php if ($project['status'] !== 'open'): ?>
                        <h4 class="text-danger fw-bold"><i class="bi bi-lock-fill me-2"></i> This job is no longer available.</h4>
                    <?php elseif ($has_applied): ?>
                        <h4 class="text-upwork fw-bold"><i class="bi bi-info-circle-fill me-2"></i> You have already submitted a proposal.</h4>
                        <p class="mb-0 text-muted">Status: <span class="badge bg-secondary"><?php echo ucfirst($proposal_status); ?></span></p>
                    <?php else: ?>
                        <div class="text-start mx-auto" style="max-width: 600px;">
                            <h4 class="fw-bold mb-4">Submit a Proposal</h4>
                            <form action="project_details.php?id=<?php echo $project_id; ?>" method="POST">
                                <input type="hidden" name="submit_proposal" value="1">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Your Bid Amount ($)</label>
                                    <input type="number" step="0.01" name="bid_amount" class="form-control" required placeholder="e.g. 500">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">How long will this project take?</label>
                                    <select name="delivery_time" class="form-select" required>
                                        <option value="Less than 1 week">Less than 1 week</option>
                                        <option value="1 to 4 weeks">1 to 4 weeks</option>
                                        <option value="1 to 3 months">1 to 3 months</option>
                                        <option value="More than 3 months">More than 3 months</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold small">Cover Letter</label>
                                    <textarea name="cover_letter" class="form-control" rows="6" placeholder="Introduce yourself and explain why you're a strong candidate for this job." required></textarea>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary rounded-pill py-2 fs-5 fw-bold">Submit Proposal</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Proposals Dash for Client -->
        <?php if ($is_owner): ?>
            <h3 class="fw-bold mb-4 fs-3 mt-5">Review Proposals</h3>
            <?php
            $prop_sql = "SELECT p.*, u.username as freelancer_name, u.title as freelancer_title, u.profile_picture 
                         FROM proposals p 
                         JOIN users u ON p.freelancer_id = u.id 
                         WHERE p.project_id = ? ORDER BY p.created_at DESC";
            $p_stmt = $conn->prepare($prop_sql);
            $p_stmt->bind_param("i", $project_id);
            $p_stmt->execute();
            $proposals = $p_stmt->get_result();
            
            if ($proposals->num_rows > 0):
                while ($pr = $proposals->fetch_assoc()):
            ?>
                    <div class="card p-4 border-0 shadow-sm mb-4" style="border-radius: 12px; border-top: 4px solid #14a800 !important;">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex">
                                <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="ms-3">
                                    <a href="freelancer_profile.php?id=<?php echo $pr['freelancer_id']; ?>" class="h5 fw-bold text-dark text-decoration-none text-upwork-hover"><?php echo htmlspecialchars($pr['freelancer_name']); ?></a>
                                    <div class="text-muted small"><?php echo htmlspecialchars($pr['freelancer_title'] ?: 'Freelancer'); ?></div>
                                    <div class="text-muted small mt-1">Submitted: <?php echo date('M d, Y', strtotime($pr['created_at'])); ?></div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold fs-4 text-dark mb-1">$<?php echo number_format($pr['bid_amount'], 2); ?></div>
                                <div class="text-muted small"><?php echo htmlspecialchars($pr['delivery_time']); ?></div>
                            </div>
                        </div>
                        
                        <div class="bg-light p-3 rounded-3 mb-4">
                            <h6 class="fw-bold mb-2 small text-uppercase text-muted">Cover Letter</h6>
                            <p class="text-dark mb-0 fs-6" style="line-height: 1.7;"><?php echo nl2br(htmlspecialchars($pr['cover_letter'])); ?></p>
                        </div>
                        
                        <div class="d-flex align-items-center border-top pt-3">
                            <span class="me-auto fw-bold">
                                Status: 
                                <?php if($pr['status'] == 'pending'): ?>
                                    <span class="badge bg-warning text-dark border ms-2">Pending Review</span>
                                <?php elseif($pr['status'] == 'accepted'): ?>
                                    <span class="badge bg-success ms-2">Hired</span>
                                <?php elseif($pr['status'] == 'rejected'): ?>
                                    <span class="badge bg-danger ms-2">Rejected</span>
                                <?php endif; ?>
                            </span>

                            <?php if ($pr['status'] == 'pending' && $project['status'] == 'open'): ?>
                                <form action="hire.php" method="POST" class="d-inline">
                                    <input type="hidden" name="proposal_id" value="<?php echo $pr['id']; ?>">
                                    <button type="submit" class="btn btn-primary fw-bold rounded-pill px-4" onclick="return confirm('Are you sure you want to hire this freelancer? This will close the job and reject other proposals.');"><i class="bi bi-check-lg me-1"></i> Hire Freelancer</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="card p-5 border-0 shadow-sm text-center bg-white" style="border-radius: 12px;">
                    <i class="bi bi-inbox text-muted display-4 mb-3 opacity-50"></i>
                    <h5 class="fw-bold">No proposals yet.</h5>
                    <p class="text-muted">Once freelancers submit proposals, they will appear here.</p>
                </div>
            <?php endif; $p_stmt->close(); ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
