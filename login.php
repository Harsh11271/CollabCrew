<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $db_username, $db_password);
            $stmt->fetch();
            if (password_verify($password, $db_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $db_username;
                
                // Redirect based on role
                $role_stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
                $role_stmt->bind_param("i", $id);
                $role_stmt->execute();
                $role_stmt->bind_result($user_role);
                $role_stmt->fetch();
                $role_stmt->close();
                
                if ($user_role === 'client') {
                    header("Location: post_project.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
        $stmt->close();
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center mt-5 mb-5">
    <div class="col-md-5">
        <div class="card p-5 border-0 shadow" style="border-radius: 16px;">
            <div class="text-center mb-4">
                <h2 class="fw-bold fs-3 text-dark">Log in to CollabCrew</h2>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger rounded-3"><i class="bi bi-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label fw-bold small">Work Email Address</label>
                    <input type="email" name="email" class="form-control form-control-lg px-3 bg-light border-0" id="email" placeholder="Email" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label fw-bold small">Password</label>
                    <input type="password" name="password" class="form-control form-control-lg px-3 bg-light border-0" id="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fs-5 mb-3 rounded-pill fw-bold">Continue with Email</button>
            </form>
            <div class="text-center mt-2 border-top pt-4">
                <p class="mb-0 text-muted">Don't have a CollabCrew account?</p>
                <a href="join.php" class="btn btn-outline-primary w-100 mt-3 rounded-pill fw-bold">Sign Up</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
