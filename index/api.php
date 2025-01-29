<?php
header('Content-Type: application/json');

@include 'config.php';

// Handle different actions via GET parameters
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Approve lender
    if ($action === 'approve' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("UPDATE lender SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Lender approved successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating lender: ' . $conn->error]);
        }
        $stmt->close();
        exit();
    }

    // Decline lender
    if ($action === 'decline' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("UPDATE lender SET status = 'declined' WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Lender declined successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating lender: ' . $conn->error]);
        }
        $stmt->close();
        exit();
    }

    // Fetch lenders based on status
    if ($action === 'fetch' && isset($_GET['status'])) {
        $status = $_GET['status'];
        $stmt = $conn->prepare("SELECT * FROM lender WHERE status = ?");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $lenders = [];
        while ($row = $result->fetch_assoc()) {
            $lenders[] = $row;
        }
        echo json_encode($lenders);
        $stmt->close();
        exit();
    }
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>
