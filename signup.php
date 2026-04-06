<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$role = isset($_GET['role']) ? $_GET['role'] : 'client';
if (!in_array($role, ['client', 'freelancer'])) {
    $role = 'client';
}

$title = $role === 'freelancer' ? 'Sign up to find work' : 'Sign up to hire talent';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $country = $_POST['country'];
    $post_role = $_POST['role'];

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($country)) {
        $error = "All fields are required.";
    } else {
        // Map First and Last name to username
        $username = $first_name . " " . $last_name;

        // Check if email or exact username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "An account with that email or name already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $post_role);

            if ($stmt->execute()) {
                $success = "Your account has been created successfully.";
            } else {
                $error = "Something went wrong. Please try again later.";
            }
        }
        $stmt->close();
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center mt-5 mb-5 border-top pt-5">
    <div class="col-md-7 col-lg-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold fs-2 text-dark"><?php echo $title; ?></h2>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger rounded-3"><i class="bi bi-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success rounded-3"><i class="bi bi-check-circle me-2"></i><?php echo $success; ?> <br><br> <a href="login.php" class="btn btn-primary rounded-pill px-4">Log in to continue</a></div>
        <?php endif; ?>
        
        <?php if (empty($success)): ?>
        <div class="card border-0 p-3">
            <form action="signup.php?role=<?php echo $role; ?>" method="POST">
                <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
                
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label for="first_name" class="form-label fw-bold small text-muted">First name</label>
                        <input type="text" name="first_name" class="form-control px-3" id="first_name" style="border-radius: 8px;" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="last_name" class="form-label fw-bold small text-muted">Last name</label>
                        <input type="text" name="last_name" class="form-control px-3" id="last_name" style="border-radius: 8px;" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label fw-bold small text-muted">Work email address</label>
                    <input type="email" name="email" class="form-control px-3" id="email" style="border-radius: 8px;" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label fw-bold small text-muted">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control px-3" id="password" style="border-radius: 8px 0 0 8px;" placeholder="Password (8 or more characters)" required minlength="8">
                        <span class="input-group-text bg-white" style="border-radius: 0 8px 8px 0; cursor: pointer;" onclick="togglePassword()">
                            <i class="bi bi-eye-slash" id="togglePasswordIcon"></i>
                        </span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="country" class="form-label fw-bold small text-muted">Country</label>
                    <select name="country" class="form-select px-3" id="country" style="border-radius: 8px;" required>
                        <option value="United States">United States</option>
                        <option value="United Kingdom">United Kingdom</option>
                        <option value="Canada">Canada</option>
                        <option value="Australia">Australia</option>
                        <option value="India" selected>India</option>
                        <option value="Germany">Germany</option>
                        <option value="France">France</option>
                        <!-- Keep list short for MVP -->
                    </select>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="sendEmails" style="accent-color: #14a800;">
                    <label class="form-check-label small" for="sendEmails">
                        Send me emails with tips on how to find talent that fits my needs.
                    </label>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="termsCheck" required style="accent-color: #14a800;">
                    <label class="form-check-label small" for="termsCheck">
                        Yes, I understand and agree to the <a href="#" class="text-upwork text-decoration-none fw-bold">CollabCrew Terms of Service</a>, including the <a href="#" class="text-upwork text-decoration-none fw-bold">User Agreement</a> and <a href="#" class="text-upwork text-decoration-none fw-bold">Privacy Policy</a>.
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold text-center mb-4" style="border-radius: 20px;">Create my account</button>
            </form>
            
            <div class="text-center mt-2 border-top pt-4">
                <p class="mb-0 text-muted">Already have an account? <a href="login.php" class="text-upwork text-decoration-none fw-bold">Log In</a></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const icon = document.getElementById('togglePasswordIcon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
