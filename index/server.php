<?php
header('Content-Type: application/json');

@include 'config.php';


if ($_GET['status']) {
    $status = $_GET['status'];
    
   
    $stmt = $conn->prepare("SELECT id, name, contact_number, address, email, image FROM lender WHERE status = ?");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $lenders = [];
    while ($row = $result->fetch_assoc()) {
        $lenders[] = $row;
    }
    
    echo json_encode($lenders);
    
    $stmt->close();
    $conn->close();
}
?>
