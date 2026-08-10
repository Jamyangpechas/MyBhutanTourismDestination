<?php

class Departure {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Fetch all departures ordered by start date with updated booked counts & customer bookings.
     */
    public function getAll(): array {
        try {
            // Recalculates booked_seats dynamically from active bookings
            $sql = "
                SELECT 
                    d.*,
                    COALESCE((
                        SELECT SUM(b.seats_booked) 
                        FROM b2c_bookings b 
                        WHERE b.departure_id = d.id 
                          AND b.payment_status != 'cancelled'
                    ), 0) AS booked_seats
                FROM departures d 
                ORDER BY d.start_date ASC
            ";
            $stmt = $this->pdo->query($sql);
            $departures = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // Attach customer bookings to each departure for admin modal views
            foreach ($departures as &$dep) {
                $dep['bookings'] = $this->getBookingsForDeparture((int)$dep['id']);
            }

            return $departures;
        } catch (PDOException $e) {
            error_log("Departure::getAll Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch single departure by ID along with its customer bookings.
     */
    public function getById(int $id): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM departures WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $result['bookings'] = $this->getBookingsForDeparture($id);
                return $result;
            }

            return null;
        } catch (PDOException $e) {
            error_log("Departure::getById Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper method to fetch associated B2C bookings for a departure.
     * Joins booking_passengers using explicit aggregations compatible with strict SQL modes.
     */
    private function getBookingsForDeparture(int $departureId): array {
        try {
            $sql = "
                SELECT 
                    b.id,
                    b.booking_reference, 
                    COALESCE(MAX(p.full_name), b.customer_name, 'N/A') AS customer_name, 
                    b.customer_email, 
                    b.seats_booked, 
                    b.total_amount, 
                    b.payment_status,
                    MAX(CASE 
                        WHEN p.passport_scan_data IS NOT NULL AND LENGTH(p.passport_scan_data) > 0 THEN p.id 
                        ELSE NULL 
                    END) AS passenger_passport_id,
                    b.created_at
                FROM b2c_bookings b
                LEFT JOIN booking_passengers p ON p.booking_id = b.id
                WHERE b.departure_id = :departure_id 
                GROUP BY 
                    b.id, 
                    b.booking_reference, 
                    b.customer_name, 
                    b.customer_email, 
                    b.seats_booked, 
                    b.total_amount, 
                    b.payment_status, 
                    b.created_at
                ORDER BY b.id DESC
            ";
            $bookingStmt = $this->pdo->prepare($sql);
            $bookingStmt->execute([':departure_id' => $departureId]);
            return $bookingStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Departure::getBookingsForDeparture Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new departure entry
     */
    public function create(array $data): bool {
        $sql = "INSERT INTO departures 
                (title, description, start_date, end_date, total_capacity, min_passengers, base_price, booked_seats) 
                VALUES (:title, :description, :start_date, :end_date, :total_capacity, :min_passengers, :base_price, :booked_seats)";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':title'          => $data['title'],
                ':description'    => $data['description'] ?? '',
                ':start_date'     => $data['start_date'],
                ':end_date'       => $data['end_date'],
                ':total_capacity' => (int)($data['total_capacity'] ?? 12),
                ':min_passengers' => (int)($data['min_passengers'] ?? 4),
                ':base_price'     => (float)($data['base_price'] ?? 0.00),
                ':booked_seats'   => (int)($data['booked_seats'] ?? 0)
            ]);
        } catch (PDOException $e) {
            error_log("Departure::create Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing departure entry
     */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE departures SET 
                    title          = :title, 
                    description    = :description, 
                    start_date     = :start_date, 
                    end_date       = :end_date, 
                    total_capacity = :total_capacity, 
                    min_passengers = :min_passengers, 
                    base_price     = :base_price, 
                    booked_seats   = :booked_seats 
                WHERE id = :id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id'             => $id,
                ':title'          => $data['title'],
                ':description'    => $data['description'] ?? '',
                ':start_date'     => $data['start_date'],
                ':end_date'       => $data['end_date'],
                ':total_capacity' => (int)($data['total_capacity'] ?? 12),
                ':min_passengers' => (int)($data['min_passengers'] ?? 4),
                ':base_price'     => (float)($data['base_price'] ?? 0.00),
                ':booked_seats'   => (int)($data['booked_seats'] ?? 0)
            ]);
        } catch (PDOException $e) {
            error_log("Departure::update Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a departure by ID
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM departures WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Departure::delete Error: " . $e->getMessage());
            return false;
        }
    }
}