<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Build search SQL
$sql = "SELECT p.*, u.username as client_name 
        FROM projects p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.status = 'open'";

if (!empty($search_query)) {
    $search_param = "%{$search_query}%";
    $sql .= " AND (p.title LIKE ? OR p.category LIKE ? OR p.required_skills LIKE ?)";
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($search_query)) {
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
}
$stmt->execute();
$result = $stmt->get_result();

require_once 'includes/header.php';
?>

<div class="row mt-4 mb-5">
    <!-- Left Sidebar: Filters -->
    <div class="col-md-3 mb-4">
        <div class="card p-4 border-0 shadow-sm" style="border-radius: 12px; position: sticky; top: 80px;">
            <h5 class="fw-bold mb-4 fs-5">Filters</h5>
            
            <form action="view_works.php" method="GET">
                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted text-uppercase mb-2">Search Jobs</label>
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0" placeholder="Title, keyword, skill" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
                        <button class="btn btn-primary px-3" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </form>

            <div class="mb-4">
                <label class="fw-bold small text-muted text-uppercase mb-2">Experience Level</label>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="exp1" checked>
                    <label class="form-check-label text-dark" for="exp1">Entry Level</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="exp2" checked>
                    <label class="form-check-label text-dark" for="exp2">Intermediate</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="exp3" checked>
                    <label class="form-check-label text-dark" for="exp3">Expert</label>
                </div>
            </div>

            <div class="mb-4">
                <label class="fw-bold small text-muted text-uppercase mb-2">Project Length</label>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="len1">
                    <label class="form-check-label text-dark" for="len1">Less than 1 month</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="len2">
                    <label class="form-check-label text-dark" for="len2">1 to 3 months</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="len3">
                    <label class="form-check-label text-dark" for="len3">More than 3 months</label>
                </div>
            </div>
            
            <a href="view_works.php" class="text-upwork text-decoration-none fw-bold small">Clear all filters</a>
        </div>
    </div>

    <!-- Right Side: Job Feed -->
    <div class="col-md-9">
        <h3 class="fw-bold fs-2 mb-4">Jobs matching your criteria</h3>

        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="card p-4 mb-3 border border-light shadow-sm job-card position-relative" style="border-radius: 12px; transition: background-color 0.2s;">
                    <style>
                        .job-card:hover {
                            background-color: #f7fff7 !important;
                            border-color: #e4ebe4 !important;
                        }
                        .job-link::after {
                            content: "";
                            position: absolute;
                            top: 0; left: 0; right: 0; bottom: 0;
                        }
                    </style>
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h4 class="fw-bold fs-5 mb-0">
                            <a href="project_details.php?id=<?php echo $row['id']; ?>" class="text-upwork text-decoration-none job-link stretched-link"><?php echo htmlspecialchars($row['title']); ?></a>
                        </h4>
                        <!-- Like/Save Heart Icon -->
                        <div style="z-index: 2; position: relative;" class="text-muted border rounded-circle p-2 bg-white d-inline-flex justify-content-center align-items-center cursor-pointer">
                            <i class="bi bi-heart fs-6"></i>
                        </div>
                    </div>
                    
                    <div class="text-muted small fw-bold mb-3 d-flex align-items-center gap-3">
                        <span><i class="bi bi-tag me-1"></i> Fixed-price - <?php echo ucfirst($row['experience_level']); ?></span>
                        <span><i class="bi bi-calendar-event me-1"></i> Est. Time: <?php echo ucfirst($row['project_length']); ?></span>
                        <span class="text-dark fw-bold fs-6">Budget: $<?php echo number_format($row['budget'], 2); ?></span>
                    </div>

                    <p class="text-dark mb-3" style="font-size: 0.95rem; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                        <?php echo nl2br(htmlspecialchars($row['description'])); ?>
                    </p>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <?php 
                        if (!empty($row['required_skills'])) {
                            $skills = explode(',', $row['required_skills']);
                            foreach($skills as $skill) {
                                echo '<span class="badge bg-light text-secondary border rounded-pill px-3 py-2 fw-medium">'.htmlspecialchars(trim($skill)).'</span>';
                            }
                        }
                        ?>
                    </div>

                    <div class="text-muted small d-flex gap-4">
                        <span><i class="bi bi-patch-check-fill text-primary"></i> Payment verified</span>
                        <span><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i><i class="bi bi-star-fill text-warning"></i> 5.0</span>
                        <span>Client: <?php echo htmlspecialchars($row['client_name']); ?></span>
                        <span>Posted <?php echo date('M d, g:i a', strtotime($row['created_at'])); ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card p-5 border-0 shadow-sm text-center" style="border-radius: 12px;">
                <i class="bi bi-search display-1 text-muted mb-4 opacity-50 d-block"></i>
                <h3 class="fw-bold">No jobs found</h3>
                <p class="text-muted">There are currenty no open projects matching your search criteria.</p>
                <a href="view_works.php" class="btn btn-outline-primary rounded-pill px-5 mt-3 fw-bold">Clear Filters</a>
            </div>
        <?php endif; ?>
        <?php $stmt->close(); ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
