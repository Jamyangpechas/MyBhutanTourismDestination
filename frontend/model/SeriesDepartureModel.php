<?php

declare(strict_types=1);

class SeriesDepartureModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get active group departures for the frontend widget.
     */
    public function getActiveDepartures(): array {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    id, 
                    title, 
                    description, 
                    start_date, 
                    end_date, 
                    total_capacity, 
                    min_passengers, 
                    booked_seats, 
                    base_price, 
                    status 
                FROM departures 
                WHERE (start_date >= CURDATE() OR start_date IS NULL)
                  AND status IN ('open', 'guaranteed')
                ORDER BY start_date ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("SeriesDepartureModel Error (getActiveDepartures): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Process a booking reservation, save parent booking record, 
     * store all passenger passport details & binary files, update capacity, and adjust departure status.
     */
    public function processBooking(
        int $departureId, 
        string $customerEmail, 
        int $seatsRequested, 
        float $pricePerSeat,
        array $passengers
    ): array {
        try {
            $this->pdo->beginTransaction();

            // 1. Lock departure row for safe concurrency reading
            $stmt = $this->pdo->prepare("
                SELECT total_capacity, min_passengers, booked_seats, status 
                FROM departures 
                WHERE id = ? 
                FOR UPDATE
            ");
            $stmt->execute([$departureId]);
            $departure = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$departure) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Selected departure was not found.'];
            }

            if ($departure['status'] === 'sold_out' || $departure['status'] === 'cancelled') {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'This departure is no longer available for booking.'];
            }

            $currentBooked = (int)$departure['booked_seats'];
            $capacity      = (int)$departure['total_capacity'];
            $minPass       = (int)$departure['min_passengers'];
            $available     = $capacity - $currentBooked;

            if ($seatsRequested > $available) {
                $this->pdo->rollBack();
                return [
                    'success' => false, 
                    'message' => "Only {$available} seat(s) remaining for this departure."
                ];
            }

            // 2. Extract Lead Contact Name (Passenger 1)
            $leadPassenger = reset($passengers) ?: [];
            $customerName  = trim($leadPassenger['name'] ?? 'Primary Traveler');
            if (empty($customerName)) {
                $customerName = 'Primary Traveler';
            }

            // 3. Generate unique booking reference and total calculation
            $bookingRef  = 'BHU-' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 8));
            $totalAmount = $seatsRequested * $pricePerSeat;

            // 4. Save parent record into b2c_bookings
            $bookingStmt = $this->pdo->prepare("
                INSERT INTO b2c_bookings 
                (booking_reference, departure_id, customer_name, customer_email, seats_booked, total_amount, payment_status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            $bookingStmt->execute([
                $bookingRef, 
                $departureId, 
                $customerName, 
                $customerEmail, 
                $seatsRequested, 
                $totalAmount
            ]);

            $bookingId = (int)$this->pdo->lastInsertId();

            // 5. Save ALL individual passengers into booking_passengers table
            $passengerStmt = $this->pdo->prepare("
                INSERT INTO booking_passengers 
                (booking_id, full_name, passport_number, nationality, passport_expiry, passport_scan_data, passport_mime_type, is_autofilled)
                VALUES (:booking_id, :full_name, :passport_number, :nationality, :passport_expiry, :passport_scan_data, :passport_mime_type, :is_autofilled)
            ");

            foreach ($passengers as $p) {
                $fullName     = trim($p['name'] ?? '');
                $passport     = trim($p['passport'] ?? '');
                $national     = trim($p['nationality'] ?? '');
                
                $rawExpiry    = trim($p['expiry'] ?? '');
                $expiry       = (!empty($rawExpiry) && strtotime($rawExpiry) !== false) ? date('Y-m-d', strtotime($rawExpiry)) : null;
                
                $scanData     = $p['passport_scan_data'] ?? null;
                $mimeType     = $p['passport_mime_type'] ?? null;
                $isAutofilled = isset($p['is_autofilled']) ? (int)$p['is_autofilled'] : 0;

                if (!empty($fullName)) {
                    $passengerStmt->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);
                    $passengerStmt->bindValue(':full_name', $fullName, PDO::PARAM_STR);
                    $passengerStmt->bindValue(':passport_number', $passport, PDO::PARAM_STR);
                    $passengerStmt->bindValue(':nationality', $national, PDO::PARAM_STR);
                    
                    if ($expiry !== null) {
                        $passengerStmt->bindValue(':passport_expiry', $expiry, PDO::PARAM_STR);
                    } else {
                        $passengerStmt->bindValue(':passport_expiry', null, PDO::PARAM_NULL);
                    }

                    if ($scanData !== null) {
                        $passengerStmt->bindValue(':passport_scan_data', $scanData, PDO::PARAM_LOB);
                    } else {
                        $passengerStmt->bindValue(':passport_scan_data', null, PDO::PARAM_NULL);
                    }

                    if ($mimeType !== null) {
                        $passengerStmt->bindValue(':passport_mime_type', $mimeType, PDO::PARAM_STR);
                    } else {
                        $passengerStmt->bindValue(':passport_mime_type', null, PDO::PARAM_NULL);
                    }

                    $passengerStmt->bindValue(':is_autofilled', $isAutofilled, PDO::PARAM_INT);

                    $passengerStmt->execute();
                }
            }

            // 6. Update departure booked count & status
            $newBookedCount = $currentBooked + $seatsRequested;
            if ($newBookedCount >= $capacity) {
                $newStatus = 'sold_out';
            } elseif ($newBookedCount >= $minPass) {
                $newStatus = 'guaranteed';
            } else {
                $newStatus = 'open';
            }

            $updateStmt = $this->pdo->prepare("
                UPDATE departures 
                SET booked_seats = ?, status = ? 
                WHERE id = ?
            ");
            $updateStmt->execute([$newBookedCount, $newStatus, $departureId]);

            $this->pdo->commit();

            return [
                'success'     => true,
                'message'     => 'Your reservation has been created and is pending payment!',
                'booking_ref' => $bookingRef
            ];

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("SeriesDepartureModel Error (processBooking): " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Database error occurred while saving your reservation.'
            ];
        }
    }

    /**
     * CANCEL a booking without deleting record from DB:
     * Updates payment_status to 'cancelled', restores seats to the departure, and updates departure status.
     */
    public function cancelBooking(int $bookingId): array {
        try {
            $this->pdo->beginTransaction();

            // 1. Fetch booking details and lock the row
            $stmt = $this->pdo->prepare("
                SELECT departure_id, seats_booked, payment_status 
                FROM b2c_bookings 
                WHERE id = ? 
                FOR UPDATE
            ");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Booking not found.'];
            }

            if ($booking['payment_status'] === 'cancelled') {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Booking is already cancelled.'];
            }

            $departureId = (int)$booking['departure_id'];
            $seatsFreed  = (int)$booking['seats_booked'];

            // 2. Mark payment_status as 'cancelled' (Record remains in database)
            $cancelStmt = $this->pdo->prepare("
                UPDATE b2c_bookings 
                SET payment_status = 'cancelled' 
                WHERE id = ?
            ");
            $cancelStmt->execute([$bookingId]);

            // 3. Fetch and lock departure to restore seats
            $depStmt = $this->pdo->prepare("
                SELECT total_capacity, min_passengers, booked_seats 
                FROM departures 
                WHERE id = ? 
                FOR UPDATE
            ");
            $depStmt->execute([$departureId]);
            $departure = $depStmt->fetch(PDO::FETCH_ASSOC);

            if ($departure) {
                $currentBooked = (int)$departure['booked_seats'];
                $capacity      = (int)$departure['total_capacity'];
                $minPass       = (int)$departure['min_passengers'];

                // Deduct seats booked by this booking
                $newBookedCount = max(0, $currentBooked - $seatsFreed);

                // Recalculate status
                if ($newBookedCount >= $capacity) {
                    $newStatus = 'sold_out';
                } elseif ($newBookedCount >= $minPass) {
                    $newStatus = 'guaranteed';
                } else {
                    $newStatus = 'open';
                }

                // Update departure
                $updateStmt = $this->pdo->prepare("
                    UPDATE departures 
                    SET booked_seats = ?, status = ? 
                    WHERE id = ?
                ");
                $updateStmt->execute([$newBookedCount, $newStatus, $departureId]);
            }

            $this->pdo->commit();

            return [
                'success' => true, 
                'message' => "Booking #{$bookingId} cancelled and {$seatsFreed} seat(s) restored."
            ];

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("SeriesDepartureModel Error (cancelBooking): " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Database error occurred while cancelling booking.'
            ];
        }
    }
}