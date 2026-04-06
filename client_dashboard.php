<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$u_stmt = $conn->prepare("SELECT username, role FROM users WHERE id = ?");
$u_stmt->bind_param("i", $user_id);
$u_stmt->execute();
$u_stmt->bind_result($username, $user_role);
$u_stmt->fetch();
$u_stmt->close();

// Stats: total jobs posted
$stat1 = $conn->prepare("SELECT COUNT(*) FROM projects WHERE user_id = ?");
$stat1->bind_param("i", $user_id);
$stat1->execute();
$stat1->bind_result($total_jobs);
$stat1->fetch();
$stat1->close();

// Stats: open jobs
$stat2 = $conn->prepare("SELECT COUNT(*) FROM projects WHERE user_id = ? AND status = 'open'");
$stat2->bind_param("i", $user_id);
$stat2->execute();
$stat2->bind_result($open_jobs);
$stat2->fetch();
$stat2->close();

// Stats: hired / in-progress
$stat3 = $conn->prepare("SELECT COUNT(*) FROM projects WHERE user_id = ? AND status = 'hired'");
$stat3->bind_param("i", $user_id);
$stat3->execute();
$stat3->bind_result($hired_jobs);
$stat3->fetch();
$stat3->close();

// Stats: total proposals received across all my jobs
$stat4 = $conn->prepare("SELECT COUNT(*) FROM proposals pr JOIN projects p ON pr.project_id = p.id WHERE p.user_id = ?");
$stat4->bind_param("i", $user_id);
$stat4->execute();
$stat4->bind_result($total_proposals);
$stat4->fetch();
$stat4->close();

// My posted jobs with proposal count
$jobs_stmt = $conn->prepare("SELECT p.id, p.title, p.budget, p.status, p.created_at, 
                              (SELECT COUNT(*) FROM proposals pr WHERE pr.project_id = p.id) as proposal_count
                              FROM projects p WHERE p.user_id = ? ORDER BY p.created_at DESC");
$jobs_stmt->bind_param("i", $user_id);
$jobs_stmt->execute();
$my_jobs = $jobs_stmt->get_result();

// Active contracts
$contracts_stmt = $conn->prepare("SELECT c.id, c.status as contract_status, c.created_at as hired_at, 
                                   p.id as project_id, p.title as project_title, p.budget,
                                   u.id as freelancer_id, u.username as freelancer_name
                                   FROM contracts c 
                                   JOIN projects p ON c.project_id = p.id 
                                   JOIN users u ON c.freelancer_id = u.id
                                   WHERE c.client_id = ? ORDER BY c.created_at DESC");
$contracts_stmt->bind_param("i", $user_id);
$contracts_stmt->execute();
$my_contracts = $contracts_stmt->get_result();

require_once 'includes/header.php';
?>

<div class="row mt-4 mb-5">
    <!-- Page Header -->
    <div class="col-12 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-dark mb-1">Welcome, <?php echo htmlspecialchars($username); ?></h2>
                <p class="text-muted mb-0">Client Dashboard — manage your job posts, proposals, and hires.</p>
            </div>
            <a href="post_project.php" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="bi bi-plus-lg me-2"></i>Post a Job</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-md-3 col-6 mb-4">
        <div class="card p-4 border-0 shadow-sm text-center" style="border-radius: 12px;">
            <div class="fs-2 fw-bold text-dark"><?php echo $total_jobs; ?></div>
            <div class="text-muted small fw-bold">Total Jobs Posted</div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-4">
        <div class="card p-4 border-0 shadow-sm text-center" style="border-radius: 12px;">
            <div class="fs-2 fw-bold text-upwork"><?php echo $open_jobs; ?></div>
            <div class="text-muted small fw-bold">Open Jobs</div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-4">
        <div class="card p-4 border-0 shadow-sm text-center" style="border-radius: 12px;">
            <div class="fs-2 fw-bold text-primary"><?php echo $hired_jobs; ?></div>
            <div class="text-muted small fw-bold">In Progress</div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-4">
        <div class="card p-4 border-0 shadow-sm text-center" style="border-radius: 12px;">
            <div class="fs-2 fw-bold text-warning"><?php echo $total_proposals; ?></div>
            <div class="text-muted small fw-bold">Proposals Received</div>
        </div>
    </div>

    <!-- My Posted Jobs -->
    <div class="col-12 mb-4">
        <div class="card p-4 border-0 shadow-sm" style="border-radius: 12px;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">My Posted Jobs</h4>
                <a href="post_project.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">+ New Job</a>
            </div>
            
            <?php if ($my_jobs->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th>JOB TITLE</th>
                            <th>BUDGET</th>
                            <th>PROPOSALS</th>
                            <th>STATUS</th>
                            <th>POSTED</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($job = $my_jobs->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <a href="project_details.php?id=<?php echo $job['id']; ?>" class="text-decoration-none fw-bold text-dark"><?php echo htmlspecialchars($job['title']); ?></a>
                            </td>
                            <td class="fw-bold">$<?php echo number_format($job['budget'], 2); ?></td>
                            <td>
                                <span class="badge bg-light text-dark border rounded-pill"><?php echo $job['proposal_count']; ?> proposals</span>
                            </td>
                            <td>
                                <?php 
                                $status_classes = [
                                    'open' => 'bg-success',
                                    'hired' => 'bg-primary',
                                    'completed' => 'bg-secondary',
                                    'closed' => 'bg-danger'
                                ];
                                $badge_class = $status_classes[$job['status']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?php echo $badge_class; ?> rounded-pill"><?php echo ucfirst($job['status']); ?></span>
                            </td>
                            <td class="text-muted small"><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                            <td>
                                <a href="project_details.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-light rounded-pill border">View</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-briefcase display-4 text-muted mb-3 d-block opacity-50"></i>
                <h5 class="fw-bold">No jobs posted yet</h5>
                <p class="text-muted">Start hiring by posting your first job.</p>
                <a href="post_project.php" class="btn btn-primary rounded-pill px-4 mt-2">Post a Job</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active Contracts -->
    <div class="col-12">
        <div class="card p-4 border-0 shadow-sm" style="border-radius: 12px;">
            <h4 class="fw-bold mb-4">Active Contracts</h4>
            
            <?php if ($my_contracts->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th>PROJECT</th>
                            <th>FREELANCER</th>
                            <th>BUDGET</th>
                            <th>STATUS</th>
                            <th>HIRED ON</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($contract = $my_contracts->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($contract['project_title']); ?></td>
                            <td><i class="bi bi-person-circle text-upwork me-1"></i> <?php echo htmlspecialchars($contract['freelancer_name']); ?></td>
                            <td class="fw-bold">$<?php echo number_format($contract['budget'], 2); ?></td>
                            <td>
                                <?php 
                                $c_status = [
                                    'active' => '<span class="badge bg-primary rounded-pill">Active</span>',
                                    'completed' => '<span class="badge bg-success rounded-pill">Completed</span>',
                                    'cancelled' => '<span class="badge bg-danger rounded-pill">Cancelled</span>'
                                ];
                                echo $c_status[$contract['contract_status']] ?? '<span class="badge bg-secondary rounded-pill">Unknown</span>';
                                ?>
                            </td>
                            <td class="text-muted small"><?php echo date('M d, Y', strtotime($contract['hired_at'])); ?></td>
                            <td>
                                <a href="messages.php?project_id=<?php echo $contract['project_id']; ?>&user_id=<?php echo $contract['freelancer_id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi bi-chat-dots me-1"></i> Message</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-file-earmark-check display-4 text-muted mb-3 d-block opacity-50"></i>
                <h5 class="fw-bold">No active contracts</h5>
                <p class="text-muted">Once you hire a freelancer, their contract will appear here.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
$jobs_stmt->close();
$contracts_stmt->close();
require_once 'includes/footer.php'; 
?>
