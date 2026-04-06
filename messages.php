<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get active chat from query params
$active_project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
$active_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_text'], $_POST['project_id'], $_POST['receiver_id'])) {
    $msg_text = trim($_POST['message_text']);
    $p_id = (int)$_POST['project_id'];
    $r_id = (int)$_POST['receiver_id'];

    if (!empty($msg_text) && $p_id > 0 && $r_id > 0) {
        $insert_msg = $conn->prepare("INSERT INTO messages (project_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
        $insert_msg->bind_param("iiis", $p_id, $user_id, $r_id, $msg_text);
        $insert_msg->execute();
        $insert_msg->close();
        
        // Redirect to prevent form resubmission
        header("Location: messages.php?project_id=$p_id&user_id=$r_id");
        exit;
    }
}

// Fetch all contacts from contracts
$contacts = [];
$contacts_query = $conn->prepare("
    SELECT c.project_id, p.title as project_title,
           IF(c.client_id = ?, c.freelancer_id, c.client_id) as other_user_id,
           u.username as other_user_name,
           u.profile_picture as other_user_avatar
    FROM contracts c
    JOIN projects p ON c.project_id = p.id
    JOIN users u ON u.id = IF(c.client_id = ?, c.freelancer_id, c.client_id)
    WHERE c.client_id = ? OR c.freelancer_id = ?
    ORDER BY c.created_at DESC
");
$contacts_query->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$contacts_query->execute();
$result = $contacts_query->get_result();
while ($row = $result->fetch_assoc()) {
    $contacts[] = $row;
    
    // If no active chat is selected, default to the first one
    if ($active_project_id === 0 && $active_user_id === 0) {
        $active_project_id = $row['project_id'];
        $active_user_id = $row['other_user_id'];
    }
}
$contacts_query->close();

// Fetch messages for active chat
$messages = [];
$active_other_username = "Unknown User";
$active_project_title = "Unknown Project";

if ($active_project_id > 0 && $active_user_id > 0) {
    // Get other user's info for header
    foreach ($contacts as $c) {
        if ($c['project_id'] == $active_project_id && $c['other_user_id'] == $active_user_id) {
            $active_other_username = $c['other_user_name'];
            $active_project_title = $c['project_title'];
            break;
        }
    }
    
    // Fallback if not found in contacts (e.g. messaging before contract is officially created? No, we restricted to contracts)
    if ($active_other_username === "Unknown User") {
        $u_info = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $u_info->bind_param("i", $active_user_id);
        $u_info->execute();
        $u_info->bind_result($active_other_username);
        $u_info->fetch();
        $u_info->close();
        
        $p_info = $conn->prepare("SELECT title FROM projects WHERE id = ?");
        $p_info->bind_param("i", $active_project_id);
        $p_info->execute();
        $p_info->bind_result($active_project_title);
        $p_info->fetch();
        $p_info->close();
    }

    $msg_query = $conn->prepare("
        SELECT id, sender_id, message, created_at 
        FROM messages 
        WHERE project_id = ? 
          AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
        ORDER BY created_at ASC
    ");
    $msg_query->bind_param("iiiii", $active_project_id, $user_id, $active_user_id, $active_user_id, $user_id);
    $msg_query->execute();
    $msg_res = $msg_query->get_result();
    $last_msg_id = 0;
    while ($m = $msg_res->fetch_assoc()) {
        $messages[] = $m;
        $last_msg_id = $m['id'];
    }
    $msg_query->close();
}

require_once 'includes/header.php';
?>

<div class="container-fluid py-4" style="height: calc(100vh - 70px); max-height: calc(100vh - 70px);">
    <div class="row h-100 bg-white rounded-3 shadow border overflow-hidden mx-lg-3">
        
        <!-- Sidebar: Contacts -->
        <div class="col-md-4 col-lg-3 border-end p-0 h-100 d-flex flex-column">
            <div class="p-3 border-bottom bg-light">
                <h5 class="mb-0 fw-bold">Messages</h5>
            </div>
            
            <div class="overflow-auto flex-grow-1">
                <?php if (count($contacts) > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach($contacts as $contact): 
                            $is_active = ($contact['project_id'] == $active_project_id && $contact['other_user_id'] == $active_user_id);
                        ?>
                        <a href="messages.php?project_id=<?php echo $contact['project_id']; ?>&user_id=<?php echo $contact['other_user_id']; ?>" 
                           class="list-group-item list-group-item-action p-3 <?php echo $is_active ? 'bg-light border-start border-4 border-success' : ''; ?>">
                            <div class="d-flex align-items-center">
                                <div class="position-relative me-3">
                                    <img src="uploads/profiles/<?php echo htmlspecialchars($contact['other_user_avatar']); ?>" class="rounded-circle bg-secondary object-fit-cover" width="40" height="40" alt="Avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($contact['other_user_name']); ?>&background=random'">
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-dark text-truncate"><?php echo htmlspecialchars($contact['other_user_name']); ?></div>
                                    <div class="small text-muted text-truncate"><?php echo htmlspecialchars($contact['project_title']); ?></div>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-chat-dots display-1 d-block mb-3 opacity-25"></i>
                        <h6 class="fw-bold">No conversations yet.</h6>
                        <p class="small">Hire somebody or get hired to start chatting!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="col-md-8 col-lg-9 p-0 h-100 d-flex flex-column bg-light">
            <?php if ($active_project_id > 0 && $active_user_id > 0): ?>
                
                <!-- Chat Header -->
                <div class="p-3 border-bottom bg-white d-flex align-items-center shadow-sm z-1">
                    <div class="me-3">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($active_other_username); ?>&background=random" class="rounded-circle" width="45" height="45">
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold"><?php echo htmlspecialchars($active_other_username); ?></h5>
                        <div class="small text-muted fw-semibold text-upwork"><?php echo htmlspecialchars($active_project_title); ?></div>
                    </div>
                </div>

                <!-- Messages Container -->
                <div class="flex-grow-1 overflow-auto p-4 d-flex flex-column" id="chatContainer">
                    
                    <div class="text-center mb-4">
                        <span class="badge bg-secondary opacity-50 rounded-pill px-3 py-2 fw-normal">
                            Conversation started for <?php echo htmlspecialchars($active_project_title); ?>
                        </span>
                    </div>

                    <?php if (count($messages) > 0): ?>
                        <?php foreach($messages as $msg): 
                            $is_mine = ($msg['sender_id'] == $user_id);
                        ?>
                            <div class="mb-3 d-flex flex-column <?php echo $is_mine ? 'align-items-end' : 'align-items-start'; ?>">
                                <div class="px-3 py-2 rounded-4 shadow-sm text-break" 
                                     style="max-width: 75%; font-size: 0.95rem; <?php echo $is_mine ? 'background-color: #14a800; color: white; border-bottom-right-radius: 4px !important;' : 'background-color: white; border: 1px solid #e4ebe4; border-bottom-left-radius: 4px !important; color: #222;'; ?>">
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                </div>
                                <div class="small text-muted mt-1 fw-medium" style="font-size: 0.75rem;">
                                    <?php echo date('M d, g:i a', strtotime($msg['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="m-auto text-center text-muted">
                            <i class="bi bi-chat-quote display-4 opacity-25 mb-3"></i>
                            <p>Send a message to start the conversation.</p>
                        </div>
                    <?php endif; ?>
                    
                </div>

                <!-- Input Area -->
                <div class="p-3 bg-white border-top z-1">
                    <form method="POST" action="messages.php" id="messageForm">
                        <input type="hidden" name="project_id" id="activeProjectId" value="<?php echo $active_project_id; ?>">
                        <input type="hidden" name="receiver_id" id="activeReceiverId" value="<?php echo $active_user_id; ?>">
                        <div class="input-group align-items-end bg-light rounded-pill p-1 border">
                            <textarea name="message_text" id="messageText" class="form-control border-0 bg-transparent shadow-none" rows="1" placeholder="Type your message..." required style="resize: none; max-height: 100px; padding-left: 20px; padding-top: 12px;"></textarea>
                            <button type="submit" id="sendMessageBtn" class="btn btn-primary rounded-circle m-1 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                    </form>
                </div>

            <?php else: ?>
                <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted bg-white">
                    <img src="https://ui-avatars.com/api/?name=Messages&background=14a800&color=fff&rounded=true" class="mb-3 opacity-50" width="80">
                    <h5 class="fw-bold text-dark">Select a conversation</h5>
                    <p>Choose a contact from the sidebar to view your messages.</p>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<style>
    /* Custom Scrollbar for Chat */
    #chatContainer::-webkit-scrollbar {
        width: 8px;
    }
    #chatContainer::-webkit-scrollbar-track {
        background: transparent;
    }
    #chatContainer::-webkit-scrollbar-thumb {
        background-color: rgba(0,0,0,0.1);
        border-radius: 10px;
    }
    /* Auto-resize textarea */
    textarea {
        overflow-y: hidden;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var chatContainer = document.getElementById("chatContainer");
        function scrollToBottom() {
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        }
        scrollToBottom();

        // Auto-resize textarea
        const tx = document.getElementById("messageText");
        if(tx) {
            tx.setAttribute("style", "height:" + (tx.scrollHeight) + "px;overflow-y:hidden;resize:none;padding-left:20px;padding-top:12px;");
            tx.addEventListener("input", OnInput, false);
            
            // Enter to send
            tx.addEventListener("keydown", function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    document.getElementById("messageForm").dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}));
                }
            });
        }

        function OnInput() {
            this.style.height = "auto";
            this.style.height = (this.scrollHeight) + "px";
            if (this.scrollHeight > 100) {
                this.style.overflowY = 'auto';
            }
        }

        const messageForm = document.getElementById('messageForm');
        const activeProjectId = document.getElementById('activeProjectId')?.value;
        const activeReceiverId = document.getElementById('activeReceiverId')?.value;
        let lastMessageId = <?php echo isset($last_msg_id) ? $last_msg_id : 0; ?>;
        let currentUserId = <?php echo $_SESSION['user_id']; ?>;

        if (messageForm && activeProjectId && activeReceiverId) {
            // Send Message logic
            messageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const textVal = tx.value.trim();
                if (!textVal) return;

                tx.value = '';
                tx.style.height = 'auto'; // reset height

                fetch('api_messages.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        project_id: activeProjectId,
                        receiver_id: activeReceiverId,
                        message_text: textVal
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        pollMessages(); // Fetch immediately after success
                    }
                })
                .catch(err => console.error(err));
            });

            // Polling logic
            function pollMessages() {
                const ts = new Date().getTime();
                fetch(`api_messages.php?project_id=${activeProjectId}&user_id=${activeReceiverId}&last_id=${lastMessageId}&t=${ts}`, {
                    cache: 'no-store'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            lastMessageId = msg.id;
                            
                            const isMine = (msg.sender_id === currentUserId);
                            const alignClass = isMine ? 'align-items-end' : 'align-items-start';
                            const bubbleStyle = isMine 
                                ? 'background-color: #14a800; color: white; border-bottom-right-radius: 4px !important;' 
                                : 'background-color: white; border: 1px solid #e4ebe4; border-bottom-left-radius: 4px !important; color: #222;';

                            const msgHTML = `
                                <div class="mb-3 d-flex flex-column ${alignClass}">
                                    <div class="px-3 py-2 rounded-4 shadow-sm text-break" style="max-width: 75%; font-size: 0.95rem; ${bubbleStyle}">
                                        ${msg.message}
                                    </div>
                                    <div class="small text-muted mt-1 fw-medium" style="font-size: 0.75rem;">
                                        ${msg.created_at}
                                    </div>
                                </div>
                            `;
                            chatContainer.insertAdjacentHTML('beforeend', msgHTML);
                        });
                        scrollToBottom();
                    }
                })
                .catch(err => console.error('Polling error:', err));
            }

            // Poll every 3 seconds
            setInterval(pollMessages, 3000);
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>
