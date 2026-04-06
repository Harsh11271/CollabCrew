<?php
session_start();
require_once 'config/db.php';

if (!isset($_GET['id'])) {
    header("Location: view_works.php");
    exit;
}

$freelancer_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'] ?? 0;

// Fetch freelancer data
$stmt = $conn->prepare("SELECT username, email, title, bio, hourly_rate, profile_picture, created_at, role FROM users WHERE id = ?");
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$stmt->bind_result($f_username, $f_email, $f_title, $f_bio, $f_hourly, $f_pic, $f_created, $f_role);

if (!$stmt->fetch()) {
    $stmt->close();
    header("Location: view_works.php");
    exit;
}
$stmt->close();

// Fetch reviews for this freelancer
$rev_stmt = $conn->prepare("SELECT r.rating, r.comment, r.created_at, u.username as client_name, p.title as project_title FROM reviews r JOIN users u ON r.reviewer_id = u.id JOIN projects p ON r.project_id = p.id WHERE r.reviewee_id = ? ORDER BY r.created_at DESC");
$rev_stmt->bind_param("i", $freelancer_id);
$rev_stmt->execute();
$reviews = $rev_stmt->get_result();

$total_reviews = $reviews->num_rows;
$sum_rating = 0;
// We'll calculate average rating later by iterating, or just do it in SQL. Let's iterate.
$review_list = [];
while($r = $reviews->fetch_assoc()) {
    $sum_rating += $r['rating'];
    $review_list[] = $r;
}
$avg_rating = $total_reviews > 0 ? number_format($sum_rating / $total_reviews, 1) : "No rating yet";

require_once 'includes/header.php';
?>

<div class="row mt-5 mb-5 justify-content-center">
    <!-- Main Profile Card -->
    <div class="col-md-9">
        <div class="card p-0 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="bg-light p-5 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 100px; height: 100px; font-size: 3rem;">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="ms-4">
                        <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($f_username); ?> 
                            <i class="bi bi-patch-check-fill text-primary ms-1 fs-5" title="Identity Verified"></i>
                        </h2>
                        <h5 class="text-dark mb-2"><?php echo htmlspecialchars($f_title ?: 'Freelancer'); ?></h5>
                        <p class="text-muted mb-0 small"><i class="bi bi-geo-alt-fill me-1"></i> Earth &bull; <?php echo date('g:i A local time'); ?></p>
                    </div>
                </div>
                <div class="text-end">
                    <div class="d-flex align-items-center mb-2 justify-content-end">
                        <i class="bi bi-star-fill text-warning fs-5 me-1"></i>
                        <span class="fs-4 fw-bold"><?php echo $avg_rating; ?></span>
                    </div>
                    <?php if ($user_id && $user_id !== $freelancer_id): ?>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary rounded-pill fw-bold"><i class="bi bi-heart"></i> Save</button>
                            <a href="#" class="btn btn-primary rounded-pill fw-bold" onclick="alert('Messaging system coming soon!');"><i class="bi bi-chat-text me-1"></i> Invite</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row g-0">
                <!-- Left Details -->
                <div class="col-md-3 border-end bg-white p-4">
                    <div class="mb-4">
                        <h4 class="fw-bold fs-3 mb-0">$<?php echo $f_hourly ? number_format($f_hourly, 2) : '0.00'; ?></h4>
                        <span class="text-muted small">Hourly rate</span>
                    </div>
                    <div class="mb-4">
                        <h4 class="fw-bold fs-3 mb-0"><?php echo $total_reviews; ?></h4>
                        <span class="text-muted small">Total Jobs</span>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Languages</h6>
                        <p class="small mb-1 text-dark fw-bold">English: <span class="text-muted fw-normal">Fluent</span></p>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Education</h6>
                        <p class="small text-muted">B.S. Computer Science<br>University of Technology</p>
                    </div>
                </div>

                <!-- Right Details -->
                <div class="col-md-9 bg-white p-5">
                    <h5 class="fw-bold mb-3 fs-3"><?php echo htmlspecialchars($f_title ?: 'Professional Overview'); ?></h5>
                    <p class="text-dark" style="line-height: 1.8; font-size: 1rem;">
                        <?php echo nl2br(htmlspecialchars($f_bio ?: 'This freelancer has not added a bio yet.')); ?>
                    </p>
                    <hr class="my-5">
                    
                    <h4 class="fw-bold mb-4">Work history and feedback</h4>
                    <?php if (count($review_list) > 0): ?>
                        <?php foreach($review_list as $review): ?>
                            <div class="mb-4 border-bottom pb-4">
                                <h6 class="fw-bold text-upwork fs-5 mb-2"><?php echo htmlspecialchars($review['project_title']); ?></h6>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="text-warning me-2">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="fw-bold text-dark me-2 border-end pe-2"><?php echo $review['rating']; ?>.0</span>
                                    <span class="text-muted small"><?php echo date('M Y', strtotime($review['created_at'])); ?></span>
                                </div>
                                <p class="text-dark fst-italic mb-2">"<?php echo nl2br(htmlspecialchars($review['comment'])); ?>"</p>
                                <span class="text-muted small fw-bold">From Client: <?php echo htmlspecialchars($review['client_name']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 bg-light rounded-3">
                            <p class="text-muted mb-0">No past work history or feedback yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
