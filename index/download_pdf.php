<?php
session_start();
include 'config.php';
require_once('vendor/tecnickcom/tcpdf/tcpdf.php');  

// Function to calculate rental duration
function calculateRentalDuration($startDate, $endDate) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = $start->diff($end);
    return $interval->days;
}

// Function to calculate penalty
function calculatePenalty($amount, $rentalDuration, $maxRentDays, $penaltyRate = 0.05) {
    if ($rentalDuration > $maxRentDays) {
        $overdueDays = $rentalDuration - $maxRentDays;
        return $amount * $penaltyRate * $overdueDays;
    }
    return 0;
}

if (isset($_GET['order_reference'])) {
    $order_reference = $_GET['order_reference'];

    // Updated order query to include penalty amount and total price
    $order_query = "SELECT o.id AS order_id, o.reference_number, o.order_date, 
                    o.delivery_method, o.total_price, o.penalty_amount,
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
        // Updated query to include rent_days and penalty_amount
        $order_details_query = "
            SELECT 
                od.id AS detail_id,
                od.quantity, 
                od.price, 
                p.product_name, 
                p.image AS product_image,
                p.rent_days,
                p.penalty_amount,
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
        $pdf->Ln(10);
        $pdf->Cell(0, 10, 'Order Reference: ' . $order['reference_number'], 0, 1);
        $pdf->Cell(0, 10, 'Name: ' . $order['firstname'] . ' ' . $order['lastname'], 0, 1);
        $pdf->Cell(0, 10, 'Email: ' . $order['email'], 0, 1);
        $pdf->Cell(0, 10, 'Contact Number: ' . $order['contact_number'], 0, 1);
        $pdf->Cell(0, 10, 'Address: ' . $order['address'], 0, 1);
        $pdf->Cell(0, 10, 'Order Date: ' . date('F j, Y, g:i a', strtotime($order['order_date'])), 0, 1);
        
        // Order details table header
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(40, 10, 'Product Name', 1);
        $pdf->Cell(15, 10, 'Qty', 1);
        $pdf->Cell(25, 10, 'Price', 1);
        $pdf->Cell(25, 10, 'Start Date', 1);
        $pdf->Cell(25, 10, 'End Date', 1);
        $pdf->Cell(20, 10, 'Duration', 1);
        $pdf->Cell(20, 10, 'Max Days', 1);
        $pdf->Cell(20, 10, 'Penalty', 1);
        $pdf->Ln();
        
        $total = 0;
        $totalPenalty = 0;

        $pdf->SetFont('helvetica', '', 10);
        while ($detail = $order_details_result->fetch_assoc()) {
            $subtotal = $detail['quantity'] * $detail['price'];
            $rentalDuration = calculateRentalDuration($detail['start_date'], $detail['end_date']);
            $penalty = calculatePenalty(
                $subtotal,
                $rentalDuration,
                $detail['rent_days'],
                $detail['penalty_amount'] ?? 0.05
            );
            
            $total += $subtotal;
            $totalPenalty += $penalty;

            // Format dates
            $startDate = date('Y-m-d', strtotime($detail['start_date']));
            $endDate = date('Y-m-d', strtotime($detail['end_date']));
            
            $pdf->Cell(40, 10, $detail['product_name'], 1);
            $pdf->Cell(15, 10, $detail['quantity'], 1);
            $pdf->Cell(25, 10, 'Php ' . number_format($detail['price'], 2), 1);
            $pdf->Cell(25, 10, $startDate, 1);
            $pdf->Cell(25, 10, $endDate, 1);
            $pdf->Cell(20, 10, $rentalDuration . ' days', 1);
            $pdf->Cell(20, 10, $detail['rent_days'] . ' days', 1);
            $pdf->Cell(20, 10, 'Php ' . number_format($penalty, 2), 1);
            $pdf->Ln();

            // Add penalty details if overdue
            if ($rentalDuration > $detail['rent_days']) {
                $pdf->SetFont('helvetica', 'I', 9);
                $overdueDays = $rentalDuration - $detail['rent_days'];
                $penaltyRate = ($detail['penalty_amount'] ?? 0.05) * 100;
                $pdf->Cell(190, 8, "    Overdue by $overdueDays days - Penalty rate: $penaltyRate% per day", 'LR', 1);
                $pdf->SetFont('helvetica', '', 10);
            }
        }
        
        // Summary
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(150, 10, 'Subtotal:', 0, 0, 'R');
        $pdf->Cell(40, 10, 'Php ' . number_format($total, 2), 0, 1);
        
        if ($totalPenalty > 0) {
            $pdf->Cell(150, 10, 'Total Penalty:', 0, 0, 'R');
            $pdf->Cell(40, 10, 'Php ' . number_format($totalPenalty, 2), 0, 1);
            
            $pdf->Cell(150, 10, 'Final Total:', 0, 0, 'R');
            $pdf->Cell(40, 10, 'Php ' . number_format($total + $totalPenalty, 2), 0, 1);
        }

        // Add footer note about penalties
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->MultiCell(0, 10, 'Note: A penalty of 5% per day is applied for rentals that exceed the maximum rent days.', 0, 'L');
        
        // Output PDF
        $pdf->Output('order_' . $order['reference_number'] . '.pdf', 'D');

    } else {
        echo "Order not found!";
    }
} else {
    echo "No order reference provided!";
}
?>