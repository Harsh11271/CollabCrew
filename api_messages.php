<?php
session_start();
require_once 'config/db.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle POST request (send message)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data && isset($_POST)) {
        $data = $_POST;
    }

    if (isset($data['project_id'], $data['receiver_id'], $data['message_text'])) {
        $p_id = (int)$data['project_id'];
        $r_id = (int)$data['receiver_id'];
        $msg_text = trim($data['message_text']);

        if (!empty($msg_text) && $p_id > 0 && $r_id > 0) {
            $insert_msg = $conn->prepare("INSERT INTO messages (project_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
            $insert_msg->bind_param("iiis", $p_id, $user_id, $r_id, $msg_text);
            $insert_msg->execute();
            $insert_id = $insert_msg->insert_id;
            $insert_msg->close();

            echo json_encode(['status' => 'success', 'message_id' => $insert_id]);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data provided']);
            exit;
        }
    }
}

// Handle GET request (fetch messages)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['project_id'], $_GET['user_id'])) {
    $active_project_id = (int)$_GET['project_id'];
    $active_user_id = (int)$_GET['user_id']; // This is the other user's ID
    $last_msg_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

    $messages = [];
    $msg_query = $conn->prepare("
        SELECT id, sender_id, message, created_at 
        FROM messages 
        WHERE project_id = ? 
          AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
          AND id > ?
        ORDER BY created_at ASC
    ");
    $msg_query->bind_param("iiiiii", $active_project_id, $user_id, $active_user_id, $active_user_id, $user_id, $last_msg_id);
    $msg_query->execute();
    $msg_res = $msg_query->get_result();
    while ($m = $msg_res->fetch_assoc()) {
        $messages[] = [
            'id' => $m['id'],
            'sender_id' => $m['sender_id'],
            'message' => nl2br(htmlspecialchars($m['message'])),
            'created_at' => date('M d, g:i a', strtotime($m['created_at']))
        ];
    }
    $msg_query->close();

    echo json_encode(['status' => 'success', 'messages' => $messages, 'current_user_id' => $user_id]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
