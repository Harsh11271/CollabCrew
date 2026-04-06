<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Build search SQL for users who are freelancers or 'both'
$sql = "SELECT u.id, u.username, u.title, u.bio, u.hourly_rate, u.created_at, 
        (SELECT AVG(rating) FROM reviews WHERE reviewee_id = u.id) as avg_rating,
        (SELECT COUNT(id) FROM reviews WHERE reviewee_id = u.id) as review_count
        FROM users u 
        WHERE u.role IN ('freelancer', 'both')";

if (!empty($search_query)) {
    $search_param = "%{$search_query}%";
    $sql .= " AND (u.title LIKE ? OR u.bio LIKE ? OR u.username LIKE ?)";
}

$sql .= " ORDER BY avg_rating DESC, u.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($search_query)) {
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
}
$stmt->execute();
$result = $stmt->get_result();

require_once 'includes/header.php';
?>

<div class="row mt-4 mb-5">
    <div class="col-12 mb-4 d-flex justify-content-between align-items-end">
        <div>
            <h2 class="fw-bold fs-1 text-dark">Find top talent</h2>
            <p class="text-muted fs-5 mb-0">Browse and connect with expert freelancers worldwide.</p>
        </div>
    </div>

    <!-- Left Sidebar: Filters -->
    <div class="col-md-3 mb-4">
        <div class="card p-4 border-0 shadow-sm" style="border-radius: 12px; position: sticky; top: 80px;">
            <h5 class="fw-bold mb-4 fs-5">Filters</h5>
            
            <form action="find_talent.php" method="GET">
                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted text-uppercase mb-2">Search Freelancers</label>
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0" placeholder="Title, keyword, skill" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
                        <button class="btn btn-primary px-3" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </form>

            <div class="mb-4">
                <label class="fw-bold small text-muted text-uppercase mb-2">Hourly Rate</label>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="rate1" checked>
                    <label class="form-check-label text-dark" for="rate1">Any hourly rate</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="rate2">
                    <label class="form-check-label text-dark" for="rate2">$10 and below</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="rate3">
                    <label class="form-check-label text-dark" for="rate3">$10 - $30</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="rate4">
                    <label class="form-check-label text-dark" for="rate4">$30 - $60</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="rate5">
                    <label class="form-check-label text-dark" for="rate5">$60 and above</label>
                </div>
            </div>
            
            <a href="find_talent.php" class="text-upwork text-decoration-none fw-bold small">Clear all filters</a>
        </div>
    </div>

    <!-- Right Side: Talent Feed -->
    <div class="col-md-9">
        <?php if ($result->num_rows > 0): ?>
            <div class="row">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="col-12 mb-3">
                        <div class="card p-4 border border-light shadow-sm talent-card position-relative" style="border-radius: 12px; transition: background-color 0.2s;">
                            <style>
                                .talent-card:hover {
                                    background-color: #f7fff7 !important;
                                    border-color: #e4ebe4 !important;
                                }
                                .talent-link::after {
                                    content: "";
                                    position: absolute;
                                    top: 0; left: 0; right: 0; bottom: 0;
                                }
                            </style>
                            <div class="row">
                                <div class="col-md-2 d-flex justify-content-center align-items-start mb-3 mb-md-0">
                                    <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center mt-2" style="width: 80px; height: 80px; font-size: 2.5rem;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h4 class="fw-bold fs-4 mb-1">
                                                <a href="freelancer_profile.php?id=<?php echo $row['id']; ?>" class="text-upwork text-decoration-none talent-link stretched-link"><?php echo htmlspecialchars($row['username']); ?></a> <i class="bi bi-patch-check-fill text-primary ms-1 fs-5" title="Identity Verified"></i>
                                            </h4>
                                            <h5 class="text-dark fs-5 fw-medium mb-2"><?php echo htmlspecialchars($row['title'] ?: 'Freelance Professional'); ?></h5>
                                        </div>
                                        <div style="z-index: 2; position: relative;" class="text-muted border rounded-circle p-2 bg-white d-inline-flex justify-content-center align-items-center cursor-pointer">
                                            <i class="bi bi-heart fs-6"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="text-muted small fw-bold mb-3 d-flex align-items-center gap-4">
                                        <span class="text-dark fw-bold fs-6">$<?php echo $row['hourly_rate'] ? number_format($row['hourly_rate'], 2).'/hr' : 'TBD'; ?></span>
                                        <?php if ($row['review_count'] > 0): ?>
                                            <span>
                                                <i class="bi bi-star-fill text-warning me-1"></i>
                                                <span class="text-dark"><?php echo number_format($row['avg_rating'], 1); ?></span> 
                                                <span class="text-muted fw-normal">(<?php echo $row['review_count']; ?> jobs)</span>
                                            </span>
                                        <?php else: ?>
                                            <span><i class="bi bi-star text-warning me-1"></i> <span class="text-muted fw-normal">No reviews yet</span></span>
                                        <?php endif; ?>
                                        <span><i class="bi bi-geo-alt-fill text-success me-1"></i> Worldwide</span>
                                    </div>

                                    <p class="text-dark mb-0" style="font-size: 0.95rem; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?php echo nl2br(htmlspecialchars($row['bio'] ?: 'This freelancer has not added a bio yet.')); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="card p-5 border-0 shadow-sm text-center" style="border-radius: 12px;">
                <i class="bi bi-search display-1 text-muted mb-4 opacity-50 d-block"></i>
                <h3 class="fw-bold">No freelancers found</h3>
                <p class="text-muted">There are no freelancers matching your search criteria right now.</p>
                <a href="find_talent.php" class="btn btn-outline-primary rounded-pill px-5 mt-3 fw-bold">Clear Filters</a>
            </div>
        <?php endif; ?>
        <?php $stmt->close(); ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
