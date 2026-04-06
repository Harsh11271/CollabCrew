<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$u_stmt = $conn->prepare("SELECT username, role, title, skills, hourly_rate FROM users WHERE id = ?");
$u_stmt->bind_param("i", $user_id);
$u_stmt->execute();
$u_stmt->bind_result($username, $user_role, $user_title, $user_skills, $hourly_rate);
$u_stmt->fetch();
$u_stmt->close();

// Stats: total proposals submitted
$stat1 = $conn->prepare("SELECT COUNT(*) FROM proposals WHERE freelancer_id = ?");
$stat1->bind_param("i", $user_id);
$stat1->execute();
$stat1->bind_result($total_proposals);
$stat1->fetch();
$stat1->close();

// Stats: pending proposals
$stat2 = $conn->prepare("SELECT COUNT(*) FROM proposals WHERE freelancer_id = ? AND status = 'pending'");
$stat2->bind_param("i", $user_id);
$stat2->execute();
$stat2->bind_result($pending_proposals);
$stat2->fetch();
$stat2->close();

// Stats: accepted (hired)
$stat3 = $conn->prepare("SELECT COUNT(*) FROM proposals WHERE freelancer_id = ? AND status = 'accepted'");
$stat3->bind_param("i", $user_id);
$stat3->execute();
$stat3->bind_result($accepted_proposals);
$stat3->fetch();
$stat3->close();

// Stats: active contracts
$stat4 = $conn->prepare("SELECT COUNT(*) FROM contracts WHERE freelancer_id = ? AND status = 'active'");
$stat4->bind_param("i", $user_id);
$stat4->execute();
$stat4->bind_result($active_contracts);
$stat4->fetch();
$stat4->close();

// My proposals
$proposals_stmt = $conn->prepare("SELECT pr.id, pr.bid_amount, pr.delivery_time, pr.status, pr.created_at,
                                   p.id as project_id, p.title as project_title, p.budget, p.status as project_status,
                                   u.username as client_name
                                   FROM proposals pr 
                                   JOIN projects p ON pr.project_id = p.id 
                                   JOIN users u ON p.user_id = u.id
                                   WHERE pr.freelancer_id = ? ORDER BY pr.created_at DESC");
$proposals_stmt->bind_param("i", $user_id);
$proposals_stmt->execute();
$my_proposals = $proposals_stmt->get_result();

// Active contracts
$contracts_stmt = $conn->prepare("SELECT c.id, c.status as contract_status, c.created_at as hired_at,
                                   p.id as project_id, p.title as project_title, p.budget,
                                   u.id as client_id, u.username as client_name
                                   FROM contracts c 
                                   JOIN projects p ON c.project_id = p.id 
                                   JOIN users u ON c.client_id = u.id
                                   WHERE c.freelancer_id = ? ORDER BY c.created_at DESC");
$contracts_stmt->bind_param("i", $user_id);
$contracts_stmt->execute();
$my_contracts = $contracts_stmt->get_result();

// Recommended jobs (recent open jobs matching skills)
$recommended_stmt = $conn->prepare("SELECT p.id, p.title, p.budget, p.experience_level, p.required_skills, p.created_at, u.username as client_name
                                     FROM projects p JOIN users u ON p.user_id = u.id
                                     WHERE p.status = 'open' 
                                     AND p.id NOT IN (SELECT project_id FROM proposals WHERE freelancer_id = ?)
                                     ORDER BY p.created_at DESC LIMIT 5");
$recommended_stmt->bind_param("i", $user_id);
$recommended_stmt->execute();
$recommended_jobs = $recommended_stmt->get_result();

require_once 'includes/header.php';
?>

<div class="row mt-4 mb-5">
    <!-- Page Header -->
    <div class="col-12 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-dark mb-1">Welcome, <?php echo htmlspecialchars($username); ?></h2>
                <p class="text-muted mb-0">Freelancer Dashboard — track your proposals, contracts, and find new work.</p>
            </div>
            <a href="view_works.php" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="bi bi-search me-2"></i>Find Work</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-md-3 col-6 mb-4">
        <div class="card p-4 border-0 shadow-sm text-center" style="border-radius: 12px;">
            <div class="fs-2 fw-bold text-dark"><?php echo $total_proposals; ?></div>
            <div class="text-muted small fw-bold">Total Proposals</div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-4">
        <div class="card p-4 border-0 shadow-sm text-center" style="border-radius: 12px;">
            <div class="fs-2 fw-bold text-warning"><?php echo $pending_proposals; ?></div>
            <div class="text-muted small fw-bold">Pending</div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-4">
        <div class="card p-4 border-0 shadow-sm text-center" style="border-radius: 12px;">
            <div class="fs-2 fw-bold text-upwork"><?php echo $accepted_proposals; ?></div>
            <div class="text-muted small fw-bold">Hired</div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-4">
        <div class="card p-4 border-0 shadow-sm text-center" style="border-radius: 12px;">
            <div class="fs-2 fw-bold text-primary"><?php echo $active_contracts; ?></div>
            <div class="text-muted small fw-bold">Active Contracts</div>
        </div>
    </div>

    <!-- My Proposals -->
    <div class="col-lg-8 mb-4">
        <div class="card p-4 border-0 shadow-sm" style="border-radius: 12px;">
            <h4 class="fw-bold mb-4">My Proposals</h4>
            
            <?php if ($my_proposals->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th>PROJECT</th>
                            <th>CLIENT</th>
                            <th>YOUR BID</th>
                            <th>STATUS</th>
                            <th>DATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($prop = $my_proposals->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <a href="project_details.php?id=<?php echo $prop['project_id']; ?>" class="text-decoration-none fw-bold text-dark"><?php echo htmlspecialchars($prop['project_title']); ?></a>
                            </td>
                            <td class="text-muted small"><?php echo htmlspecialchars($prop['client_name']); ?></td>
                            <td class="fw-bold">$<?php echo number_format($prop['bid_amount'], 2); ?></td>
                            <td>
                                <?php 
                                $status_map = [
                                    'pending' => '<span class="badge bg-warning text-dark rounded-pill">Pending</span>',
                                    'accepted' => '<span class="badge bg-success rounded-pill">Hired!</span>',
                                    'rejected' => '<span class="badge bg-danger rounded-pill">Rejected</span>',
                                    'withdrawn' => '<span class="badge bg-secondary rounded-pill">Withdrawn</span>'
                                ];
                                echo $status_map[$prop['status']] ?? '<span class="badge bg-secondary rounded-pill">Unknown</span>';
                                ?>
                            </td>
                            <td class="text-muted small"><?php echo date('M d', strtotime($prop['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-send display-4 text-muted mb-3 d-block opacity-50"></i>
                <h5 class="fw-bold">No proposals yet</h5>
                <p class="text-muted">Browse jobs and send your first proposal.</p>
                <a href="view_works.php" class="btn btn-outline-primary rounded-pill px-4 mt-2">Browse Jobs</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recommended Jobs Sidebar -->
    <div class="col-lg-4 mb-4">
        <div class="card p-4 border-0 shadow-sm" style="border-radius: 12px;">
            <h5 class="fw-bold mb-3"><i class="bi bi-lightning text-warning me-2"></i>Recommended for You</h5>
            
            <?php if ($recommended_jobs->num_rows > 0): ?>
                <?php while($rjob = $recommended_jobs->fetch_assoc()): ?>
                <a href="project_details.php?id=<?php echo $rjob['id']; ?>" class="text-decoration-none d-block mb-3 p-3 border rounded-3 rec-card" style="transition: all 0.2s;">
                    <h6 class="fw-bold text-dark mb-1 text-truncate"><?php echo htmlspecialchars($rjob['title']); ?></h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-upwork fw-bold">$<?php echo number_format($rjob['budget'], 2); ?></span>
                        <span class="badge bg-light text-dark border rounded-pill small"><?php echo ucfirst($rjob['experience_level']); ?></span>
                    </div>
                    <div class="text-muted small mt-1">by <?php echo htmlspecialchars($rjob['client_name']); ?></div>
                </a>
                <?php endwhile; ?>
                <a href="view_works.php" class="btn btn-outline-primary rounded-pill w-100 mt-2 fw-bold">See all jobs</a>
            <?php else: ?>
                <div class="text-center py-3 text-muted">
                    <p class="small mb-0">No new jobs match your profile yet.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
            .rec-card:hover { background-color: #f2f7f2; border-color: #14a800 !important; }
        </style>
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
                            <th>CLIENT</th>
                            <th>BUDGET</th>
                            <th>STATUS</th>
                            <th>STARTED</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($contract = $my_contracts->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($contract['project_title']); ?></td>
                            <td><i class="bi bi-person-circle text-upwork me-1"></i> <?php echo htmlspecialchars($contract['client_name']); ?></td>
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
                                <a href="messages.php?project_id=<?php echo $contract['project_id']; ?>&user_id=<?php echo $contract['client_id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi bi-chat-dots me-1"></i> Message</a>
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
                <p class="text-muted">Once a client hires you, your contract will appear here.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
$proposals_stmt->close();
$contracts_stmt->close();
$recommended_stmt->close();
require_once 'includes/footer.php'; 
?>
