<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['proposal_id'])) {
    header("Location: dashboard.php");
    exit;
}

$proposal_id = intval($_POST['proposal_id']);

// Fetch proposal details + verify this user owns the project
$stmt = $conn->prepare("SELECT pr.id, pr.project_id, pr.freelancer_id, pr.status,
                         p.user_id as project_owner, p.status as project_status
                         FROM proposals pr
                         JOIN projects p ON pr.project_id = p.id
                         WHERE pr.id = ?");
$stmt->bind_param("i", $proposal_id);
$stmt->execute();
$result = $stmt->get_result();
$proposal = $result->fetch_assoc();
$stmt->close();

if (!$proposal) {
    $_SESSION['error'] = "Proposal not found.";
    header("Location: dashboard.php");
    exit;
}

// Only the project owner can hire
if ($proposal['project_owner'] != $user_id) {
    $_SESSION['error'] = "You are not authorized. You are logged in as user #" . $user_id . " but this project belongs to user #" . $proposal['project_owner'] . ". If you have multiple tabs open, log out and log back in as the correct account. Use an Incognito window for the second account.";
    header("Location: project_details.php?id=" . $proposal['project_id']);
    exit;
}

// Can only hire if proposal is pending and project is open
if ($proposal['status'] !== 'pending') {
    $_SESSION['error'] = "This proposal has already been processed.";
    header("Location: project_details.php?id=" . $proposal['project_id']);
    exit;
}

if ($proposal['project_status'] !== 'open') {
    $_SESSION['error'] = "This project is no longer open for hiring.";
    header("Location: project_details.php?id=" . $proposal['project_id']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // 1. Accept this proposal
    $accept = $conn->prepare("UPDATE proposals SET status = 'accepted' WHERE id = ?");
    $accept->bind_param("i", $proposal_id);
    $accept->execute();
    $accept->close();

    // 2. Reject all other pending proposals for this project
    $reject = $conn->prepare("UPDATE proposals SET status = 'rejected' WHERE project_id = ? AND id != ? AND status = 'pending'");
    $reject->bind_param("ii", $proposal['project_id'], $proposal_id);
    $reject->execute();
    $reject->close();

    // 3. Update project status to 'hired'
    $update_project = $conn->prepare("UPDATE projects SET status = 'hired' WHERE id = ?");
    $update_project->bind_param("i", $proposal['project_id']);
    $update_project->execute();
    $update_project->close();

    // 4. Create the contract
    $create_contract = $conn->prepare("INSERT INTO contracts (project_id, client_id, freelancer_id, proposal_id) VALUES (?, ?, ?, ?)");
    $create_contract->bind_param("iiii", $proposal['project_id'], $user_id, $proposal['freelancer_id'], $proposal_id);
    $create_contract->execute();
    $create_contract->close();

    $conn->commit();

    $_SESSION['success'] = "Freelancer hired successfully! A contract has been created.";
    header("Location: project_details.php?id=" . $proposal['project_id']);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Something went wrong. Please try again.";
    header("Location: project_details.php?id=" . $proposal['project_id']);
    exit;
}
?>
