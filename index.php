<?php
session_start();
require_once 'config/db.php';

// Fetch the latest 6 open projects
$stmt = $conn->prepare("SELECT p.id, p.title, p.budget, p.experience_level, p.project_length, p.created_at, u.username as client_name 
                        FROM projects p 
                        JOIN users u ON p.user_id = u.id 
                        WHERE p.status = 'open' 
                        ORDER BY p.created_at DESC LIMIT 6");
$stmt->execute();
$recent_jobs = $stmt->get_result();
$stmt->close();

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<div class="container-fluid mb-5 p-0 position-relative" style="margin-top: 10px;">
    <div class="row g-0 justify-content-center">
        <div class="col-11 col-lg-10 position-relative rounded-4 overflow-hidden shadow-sm" style="height: 500px;">
            <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80" 
                 alt="Collaboration" 
                 class="img-fluid w-100 h-100" style="object-fit: cover; filter: brightness(0.6);">
            
            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center p-5 text-white" style="background: linear-gradient(to right, rgba(0, 30, 0, 0.7) 0%, rgba(0,0,0,0) 100%);">
                <div class="col-lg-6">
                    <h1 class="display-3 fw-bold mb-3" style="line-height: 1.1;">Hire the experts your<br>business needs</h1>
                    <p class="fs-5 mb-5 opacity-75">Access a global network of independent professionals and agencies.</p>
                    
                    <div class="bg-white p-2 rounded-pill d-flex align-items-center mb-3">
                        <select class="form-select border-0 bg-transparent fw-bold text-dark w-auto px-4" style="box-shadow: none;">
                            <option>Talent</option>
                            <option>Projects</option>
                            <option>Jobs</option>
                        </select>
                        <div style="height: 30px; border-left: 1px solid #ddd; margin: 0 10px;"></div>
                        <input type="text" class="form-control border-0 bg-transparent px-3" placeholder="Search by skill or project name" style="box-shadow: none;">
                        <button class="btn btn-primary rounded-pill px-4 ms-2">Search</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Trusted By Logos -->
<div class="container mb-5 text-center">
    <p class="text-muted fw-bold small mb-3 text-uppercase">Trusted by growing businesses</p>
    <div class="d-flex justify-content-center flex-wrap gap-4 gap-md-5 opacity-50">
        <i class="bi bi-microsoft fs-3"></i>
        <i class="bi bi-apple fs-3"></i>
        <i class="bi bi-google fs-3"></i>
        <i class="bi bi-android fs-3"></i>
        <i class="bi bi-meta fs-3"></i>
    </div>
</div>

<!-- Categories -->
<div class="container mb-5 mt-5 pt-4">
    <h2 class="fw-bold fs-2 mb-4">Find freelancers for every type of work</h2>
    
    <div class="row g-3">
        <?php
        $categories = [
            ['icon' => 'bi-code-slash', 'name' => 'Development & IT', 'rating' => '4.85/5'],
            ['icon' => 'bi-palette', 'name' => 'Design & Creative', 'rating' => '4.91/5'],
            ['icon' => 'bi-graph-up', 'name' => 'Sales & Marketing', 'rating' => '4.77/5'],
            ['icon' => 'bi-pencil-square', 'name' => 'Writing & Translation', 'rating' => '4.92/5'],
            ['icon' => 'bi-headset', 'name' => 'Admin & Customer Support', 'rating' => '4.77/5'],
            ['icon' => 'bi-calculator', 'name' => 'Finance & Accounting', 'rating' => '4.8/5'],
            ['icon' => 'bi-person-gear', 'name' => 'Engineering & Architecture', 'rating' => '4.9/5'],
            ['icon' => 'bi-briefcase', 'name' => 'Legal', 'rating' => '4.85/5']
        ];
        foreach ($categories as $cat):
        ?>
            <div class="col-md-3 col-sm-6">
                <a href="find_talent.php" class="card p-3 border h-100 text-decoration-none text-dark skill-card">
                    <div class="d-flex flex-column justify-content-between h-100">
                        <i class="bi <?php echo $cat['icon']; ?> fs-3 mb-4 text-upwork"></i>
                        <div>
                            <h5 class="fw-bold fs-6 mb-1"><?php echo $cat['name']; ?></h5>
                            <div class="text-muted small"><i class="bi bi-star-fill text-upwork me-1"></i> <?php echo $cat['rating']; ?></div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <style>
        .skill-card {
            border-radius: 12px;
            transition: all 0.2s;
            cursor: pointer;
        }
        .skill-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            background-color: #f9fdf9;
        }
    </style>
</div>

<!-- How it works -->
<div class="container mb-5 pt-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <h2 class="fw-bold fs-2 text-dark m-0">How it works</h2>
        <a href="join.php" class="btn btn-outline-primary rounded-pill px-4 text-dark fw-bold border d-none d-md-inline-block">Sign up to hire</a>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card bg-success text-white border-0 h-100 position-relative overflow-hidden" style="border-radius: 16px; min-height: 250px;">
                <div class="p-4 position-relative z-1 h-100 d-flex flex-column">
                    <h3 class="fw-bold fs-2 mb-3" style="letter-spacing: -1px;">CollabCrew</h3>
                    <p class="fs-5 mb-auto">Post a job and hire a pro</p>
                    <div class="align-self-start mt-4 bg-white text-success rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:30px; height:30px;">1</div>
                </div>
                <div class="position-absolute w-100 h-100 top-0 start-0 opacity-50" style="background: linear-gradient(45deg, transparent, #22c55e);"></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 h-100 position-relative overflow-hidden" style="border-radius: 16px; min-height: 250px;">
                <img src="https://images.unsplash.com/photo-1542744173-8e7e53415bb0?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" 
                     class="position-absolute w-100 h-100" style="object-fit: cover; filter: brightness(0.8);">
                <div class="p-4 position-relative z-1 h-100 d-flex flex-column text-white justify-content-end">
                    <p class="fs-5 fw-bold mb-0">Browse and buy projects</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 h-100 position-relative overflow-hidden" style="border-radius: 16px; min-height: 250px;">
                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" 
                     class="position-absolute w-100 h-100" style="object-fit: cover; filter: brightness(0.8);">
                <div class="p-4 position-relative z-1 h-100 d-flex flex-column text-white justify-content-end">
                    <p class="fs-5 fw-bold mb-0">Let us help you find the right talent</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pricing Insights -->
<div class="container-fluid bg-dark text-white p-0 mb-5" style="margin-top: 80px;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-4 mb-lg-0 pe-lg-5">
                <h2 class="display-5 fw-bold mb-4">Get insights into freelancer pricing</h2>
                <p class="fs-5 text-light opacity-75 mb-4">We've collected extensive data on freelancer rates. Explore typical costs for projects.</p>
                <div class="bg-white rounded-pill px-2 py-1 d-inline-flex align-items-center w-100">
                    <input type="text" class="form-control border-0 bg-transparent px-3" placeholder="Search rates e.g. web developer" style="box-shadow:none;">
                    <button class="btn btn-primary rounded-pill px-4">Search</button>
                </div>
            </div>
            <div class="col-lg-7 d-flex justify-content-center">
                <div class="bg-black p-4 rounded-4 border border-secondary shadow-lg w-100 text-center position-relative" style="max-width: 600px;">
                    <div class="position-absolute start-50 top-50 translate-middle w-75 h-75" style="background: radial-gradient(circle, rgba(20,168,0,0.3) 0%, rgba(0,0,0,0) 70%); z-index:0;"></div>
                    <div class="position-relative" style="z-index:1;">
                        <p class="text-muted fw-bold mb-4">Cost distribution</p>
                        <div class="d-flex justify-content-between text-muted small mb-2 px-3">
                            <span>$10/hr</span><span>$50/hr</span><span>$100+/hr</span>
                        </div>
                        <div class="w-100 bg-secondary rounded-pill mb-4 position-relative" style="height: 12px;">
                            <div class="position-absolute top-0 start-50 translate-middle-x bg-upwork h-100 shadow" style="width: 40%; border-radius: 12px; box-shadow: 0 0 10px #14a800;"></div>
                            <div class="position-absolute top-50 translate-middle bg-white rounded-circle border border-dark" style="width: 18px; height: 18px; left: 30%;"></div>
                            <div class="position-absolute top-50 translate-middle bg-white rounded-circle border border-dark" style="width: 18px; height: 18px; left: 70%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Choose how to hire -->
<div class="container mb-5 pt-4">
    <div class="text-center mb-5">
        <h2 class="fw-bold display-6">Choose how you want to hire</h2>
        <p class="text-muted fs-5">Find the exact support you need.</p>
    </div>
    
    <div class="row g-4 justify-content-center">
        <div class="col-md-5">
            <div class="card h-100 p-5 rounded-4" style="border: 1px solid #e4ebe4;">
                <h3 class="fw-bold mb-3 fs-3">Talent Marketplace &trade;</h3>
                <p class="text-muted fs-5 mb-4">Find professionals and agencies for hourly or fixed-price projects.</p>
                <h5 class="fw-bold mb-3 small text-uppercase text-muted">Best for:</h5>
                <ul class="list-unstyled mb-5">
                    <li class="mb-3 d-flex"><i class="bi bi-check2 text-upwork fs-5 me-3"></i> Complex projects that require precise skills</li>
                    <li class="mb-3 d-flex"><i class="bi bi-check2 text-upwork fs-5 me-3"></i> Longer-term engagements</li>
                    <li class="mb-3 d-flex"><i class="bi bi-check2 text-upwork fs-5 me-3"></i> Teams and agencies</li>
                </ul>
                <div class="mt-auto">
                    <a href="join.php" class="btn btn-outline-primary rounded-pill px-5 py-2 fw-bold w-100 border">Find talent now</a>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card h-100 p-5 rounded-4 shadow-sm" style="border: 1px solid #14a800; border-top: 8px solid #14a800;">
                <h3 class="fw-bold mb-3 fs-3">Project Catalog &trade;</h3>
                <p class="text-muted fs-5 mb-4">Buy ready-to-start projects that are predefined and ready-to-go.</p>
                <h5 class="fw-bold mb-3 small text-uppercase text-muted">Best for:</h5>
                <ul class="list-unstyled mb-5">
                    <li class="mb-3 d-flex"><i class="bi bi-check2 text-upwork fs-5 me-3"></i> Pre-packaged work with clear deliverables</li>
                    <li class="mb-3 d-flex"><i class="bi bi-check2 text-upwork fs-5 me-3"></i> Getting started quickly without negotiating</li>
                    <li class="mb-3 d-flex"><i class="bi bi-check2 text-upwork fs-5 me-3"></i> Single, discrete tasks</li>
                </ul>
                <div class="mt-auto">
                    <a href="view_works.php" class="btn btn-primary rounded-pill px-5 py-2 fw-bold w-100">Browse projects</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Available Work Section -->
<div class="container mt-5 pt-5 mb-5 border-top">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold fs-1 text-dark">Explore available work</h2>
            <p class="text-muted fs-5 mb-0">Browse top-rated projects below or sign up to see more.</p>
        </div>
        <a href="view_works.php" class="btn btn-light rounded-pill px-4 fw-bold text-dark border d-none d-md-block">Browse all jobs <i class="bi bi-arrow-right ms-2"></i></a>
    </div>

    <div class="row">
        <?php if ($recent_jobs->num_rows > 0): ?>
            <?php while($job = $recent_jobs->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 p-4 border border-light shadow-sm position-relative job-card" style="border-radius: 12px;">
                        <div class="mb-3">
                            <span class="badge bg-light text-dark border px-2 py-1 mb-2 fw-medium rounded-pill"><?php echo ucfirst($job['experience_level']); ?></span>
                            <h5 class="fw-bold fs-5 mb-1 text-truncate">
                                <a href="project_details.php?id=<?php echo $job['id']; ?>" class="text-dark text-decoration-none stretched-link"><?php echo htmlspecialchars($job['title']); ?></a>
                            </h5>
                            <div class="text-muted small">Posted <?php echo date('M d', strtotime($job['created_at'])); ?> by <?php echo htmlspecialchars($job['client_name']); ?></div>
                        </div>
                        
                        <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold fs-5 text-dark">$<?php echo number_format($job['budget'], 2); ?></div>
                                <div class="text-muted small">Fixed-price</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-dark small"><i class="bi bi-clock text-upwork me-1"></i> <?php echo ucfirst($job['project_length']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-search display-4 text-muted mb-3 opacity-50 d-block"></i>
                <h4 class="fw-bold">No jobs posted yet</h4>
                <p class="text-muted">Be the first to post a job!</p>
                <a href="join.php" class="btn btn-primary rounded-pill px-4 mt-2">Sign Up to Post</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-12 text-center mt-3 d-md-none">
        <a href="view_works.php" class="btn btn-light rounded-pill px-4 fw-bold text-dark border w-100">Browse all jobs <i class="bi bi-arrow-right ms-2"></i></a>
    </div>
</div>

<style>
    .job-card { transition: transform 0.2s, box-shadow 0.2s; }
    .job-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; border-color: #14a800 !important; }
</style>

<!-- CTA -->
<div class="container mb-5 pb-5">
    <div class="bg-upwork text-white rounded-4 p-5 shadow text-center">
        <h2 class="display-5 fw-bold mb-4">Find freelancers who can help you build what's next</h2>
        <a href="join.php" class="btn btn-light text-upwork fs-5 fw-bold rounded-pill px-5 py-3">Sign up for free</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
