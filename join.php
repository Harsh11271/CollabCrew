<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
require_once 'includes/header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-8 col-lg-6">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark" style="font-size: 2.2rem;">Join as a client or freelancer</h2>
        </div>
        
        <form action="signup.php" method="GET" id="joinForm">
            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <label class="card h-100 p-4 border rounded-3 position-relative role-card" for="roleClient" style="cursor: pointer; transition: all 0.2s;">
                        <input type="radio" name="role" value="client" id="roleClient" class="position-absolute" style="top: 15px; right: 15px; width: 1.2rem; height: 1.2rem; accent-color: #14a800;" required>
                        <i class="bi bi-person-workspace fs-2 text-dark mb-3 d-block"></i>
                        <span class="fs-5 fw-bold text-dark d-block">I'm a client, hiring for a project</span>
                    </label>
                </div>
                <div class="col-md-6">
                    <label class="card h-100 p-4 border rounded-3 position-relative role-card" for="roleFreelancer" style="cursor: pointer; transition: all 0.2s;">
                        <input type="radio" name="role" value="freelancer" id="roleFreelancer" class="position-absolute" style="top: 15px; right: 15px; width: 1.2rem; height: 1.2rem; accent-color: #14a800;" required>
                        <i class="bi bi-briefcase fs-2 text-dark mb-3 d-block"></i>
                        <span class="fs-5 fw-bold text-dark d-block">I'm a freelancer, looking for work</span>
                    </label>
                </div>
            </div>
            
            <style>
                .role-card:hover {
                    background-color: #f2f7f2 !important;
                    border-color: #14a800 !important;
                }
                input[type="radio"]:checked + i + span {
                    color: #14a800 !important;
                }
                .role-card:has(input[type="radio"]:checked) {
                    border-color: #14a800 !important;
                    border-width: 2px !important;
                    background-color: #f2f7f2 !important;
                }
            </style>
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold" id="createAccountBtn" disabled>Create Account</button>
            </div>
            
            <div class="text-center mt-5">
                <p class="text-muted">Already have an account? <a href="login.php" class="text-upwork text-decoration-none fw-bold">Log In</a></p>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('input[name="role"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.getElementById('createAccountBtn').disabled = false;
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>
