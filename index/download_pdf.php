<?php
session_start();
include 'config.php';
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');  

// Ensure that the order reference is passed and valid
if (isset($_GET['order_reference'])) {
    $order_reference = $_GET['order_reference'];

    // Fetch the order details from the database
    $order_query = "SELECT o.id AS order_id, o.reference_number, o.order_date, o.delivery_method, 
                    c.firstname, c.lastname, c.email, c.contact_number, c.address
                    FROM orders o
                    JOIN customer c ON o.customer_id = c.customer_id
                    WHERE o.reference_number = ?";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("s", $order_reference);
    $stmt->execute();
    $order_result = $stmt->get_result();
    $order = $order_result->fetch_assoc();

    if ($order) {
        // Fetch order details
        $order_details_query = "
            SELECT 
                od.id AS detail_id,
                od.quantity, 
                od.price, 
                p.product_name, 
                p.image AS product_image,
                od.start_date, 
                od.end_date
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            WHERE od.order_id = ?";
        $stmt = $conn->prepare($order_details_query);
        $stmt->bind_param("i", $order['order_id']);
        $stmt->execute();
        $order_details_result = $stmt->get_result();

        // Create PDF
        $pdf = new TCPDF();
        $pdf->AddPage();
        
        // Set title
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Order Confirmation', 0, 1, 'C');
        
        // Customer information
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Ln(10);  // Line break
        $pdf->Cell(0, 10, 'Order Reference: ' . $order['reference_number'], 0, 1);
        $pdf->Cell(0, 10, 'Name: ' . $order['firstname'] . ' ' . $order['lastname'], 0, 1);
        $pdf->Cell(0, 10, 'Email: ' . $order['email'], 0, 1);
        $pdf->Cell(0, 10, 'Contact Number: ' . $order['contact_number'], 0, 1);
        $pdf->Cell(0, 10, 'Address: ' . $order['address'], 0, 1);
        $pdf->Cell(0, 10, 'Order Date: ' . $order['order_date'], 0, 1);
        $pdf->Cell(0, 10, 'Delivery Method: ' . $order['delivery_method'], 0, 1);
        
        // Order details table
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(40, 10, 'Product Name', 1);
        $pdf->Cell(25, 10, 'Quantity', 1);
        $pdf->Cell(25, 10, 'Price', 1);
        $pdf->Cell(30, 10, 'Start Date', 1);
        $pdf->Cell(30, 10, 'End Date', 1);
        $pdf->Cell(30, 10, 'Subtotal', 1);
        $pdf->Ln();
        
        $pdf->SetFont('helvetica', '', 12);
        while ($detail = $order_details_result->fetch_assoc()) {
            $shipping_subtotal = strtolower($order['delivery_method']) == 'pickup' ? 0 : $detail['quantity'] * $detail['shippingfee'];
            $subtotal = $detail['quantity'] * $detail['price'] + $shipping_subtotal;
            
            $pdf->Cell(40, 10, $detail['product_name'], 1);
            $pdf->Cell(25, 10, $detail['quantity'], 1);
            $pdf->Cell(25, 10, '₱' . number_format($detail['price'], 2), 1);
            $pdf->Cell(30, 10, $detail['start_date'], 1); 
            $pdf->Cell(30, 10, $detail['end_date'], 1);  
            $pdf->Cell(30, 10, '₱' . number_format($subtotal, 2), 1);
            $pdf->Ln();
        }
        
        // Output PDF
        $pdf->Output('order_' . $order['reference_number'] . '.pdf', 'D');  

    } else {
        echo "Order not found!";
    }
} else {
    echo "No order reference provided!";
}
?>
