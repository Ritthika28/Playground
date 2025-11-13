<?php
require 'config.php';
require 'fpdf/fpdf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];

    try {
        // Fetch booking details
        $booking_query = "
            SELECT b.*, u.username, u.email, t.name AS turf_name
            FROM bookings b
            JOIN users u ON b.user_id = u.user_id
            JOIN turfs t ON b.turf_id = t.turf_id
            WHERE b.booking_id = :booking_id
        ";
        $stmt = $pdo->prepare($booking_query);
        $stmt->execute([':booking_id' => $booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            throw new Exception("Booking not found.");
        }

        // Ensure booking status is 'Paid' or 'Approved'
        if (!in_array($booking['status'], ['Paid', 'Approved'])) {
            throw new Exception("Invoice can only be generated for approved or paid bookings.");
        }

        // Fetch booked sport
        $sport_query = "
            SELECT sc.sport_name, sc.cost
            FROM sports_cost sc
            JOIN bookings b ON sc.sport_id = b.sport_id AND sc.turf_id = b.turf_id
            WHERE b.booking_id = :booking_id
        ";
        $stmt = $pdo->prepare($sport_query);
        $stmt->execute([':booking_id' => $booking_id]);
        $booked_sport = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch selected amenities
        $amenities_query = "
            SELECT a.name, a.price
            FROM booking_amenities ba
            JOIN amenities a ON ba.amenity_id = a.amenity_id
            WHERE ba.booking_id = :booking_id
        ";
        $stmt = $pdo->prepare($amenities_query);
        $stmt->execute([':booking_id' => $booking_id]);
        $amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Create PDF invoice
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetMargins(10, 10, 10);

        // Add logo
        $pdf->Image('Assets/logo.jpg', 10, 10, 30);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 20, 'Turf Management System', 0, 1, 'C');
        $pdf->Ln(10);

        // Invoice title
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->SetTextColor(50, 50, 255);
        $pdf->Cell(0, 10, 'Booking Invoice', 0, 1, 'C');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(10);

        // Booking details
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(190, 10, 'Booking Details', 1, 1, 'C', true);

        $pdf->Cell(50, 10, 'Booking ID:', 1);
        $pdf->Cell(140, 10, $booking['booking_id'], 1, 1);

        $pdf->Cell(50, 10, 'User:', 1);
        $pdf->Cell(140, 10, $booking['username'], 1, 1);

        $pdf->Cell(50, 10, 'Email:', 1);
        $pdf->Cell(140, 10, $booking['email'], 1, 1);

        $pdf->Cell(50, 10, 'Turf:', 1);
        $pdf->Cell(140, 10, $booking['turf_name'], 1, 1);

        $pdf->Cell(50, 10, 'Booking Date:', 1);
        $pdf->Cell(140, 10, $booking['booking_date'], 1, 1);

        $pdf->Cell(50, 10, 'Time Slot:', 1);
        $pdf->Cell(140, 10, $booking['time_slot'], 1, 1);

        $pdf->Cell(50, 10, 'Status:', 1);
        $pdf->Cell(140, 10, $booking['status'], 1, 1);

        // Add conditional message based on booking status
        $pdf->Ln(5);
        if ($booking['status'] === 'Approved') {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor(255, 0, 0); // Set color to red
            $pdf->MultiCell(0, 10, 'Your booking has been approved for "Pay After Play".', 0, 'C');
            $pdf->SetTextColor(0, 0, 0); // Reset color to black
        }

        $pdf->Ln(5);

        // Booked Sport
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(190, 10, 'Booked Sport', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 12);
        if ($booked_sport) {
            $pdf->Cell(150, 10, $booked_sport['sport_name'], 1);
            $pdf->Cell(40, 10, 'INR ' . number_format($booked_sport['cost'], 2), 1, 1, 'R');
        } else {
            $pdf->Cell(190, 10, 'No sport selected.', 1, 1, 'C');
        }
        
        // Amenities
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(190, 10, 'Selected Amenities', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 12);
        if (count($amenities) > 0) {
            foreach ($amenities as $amenity) {
                $pdf->Cell(150, 10, $amenity['name'], 1);
                $pdf->Cell(40, 10, 'INR ' . number_format($amenity['price'], 2), 1, 1, 'R');
            }
        } else {
            $pdf->Cell(190, 10, 'No amenities selected.', 1, 1, 'C');
        }

        $pdf->Ln(5);

        // Total cost
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(190, 10, 'Total Cost', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(150, 10, 'Total Amount:', 1);
        $pdf->Cell(40, 10, 'INR ' . number_format($booking['total_cost'], 2), 1, 1, 'R');

        $pdf->Ln(10);

        // Footer
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 10, 'Thank you for booking with us!', 0, 1, 'C');

        // Output the PDF as a download
        $pdf->Output('D', 'Invoice_Booking_' . $booking['booking_id'] . '.pdf');
        exit;

    } catch (Exception $e) {
        echo "<script>alert('Error: " . htmlspecialchars($e->getMessage()) . "');</script>";
        echo "<script>window.history.back();</script>";
        exit;
    }
}
?>
