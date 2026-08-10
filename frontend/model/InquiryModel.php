<?php

class InquiryModel {
    private PDO $db;

    public function __construct(PDO $dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Save trip inquiry into the database securely with error handling
     *
     * @param array $data Validated input array
     * @return int|bool Returns inserted inquiry ID on success, false on failure
     */
    public function createInquiry(array $data) {
        $sql = "INSERT INTO inquiries 
                (name, email, nationality, season, duration, adults, children, infants, interests, estimated_total, status) 
                VALUES 
                (:name, :email, :nationality, :season, :duration, :adults, :children, :infants, :interests, :estimated_total, :status)";

        try {
            $stmt = $this->db->prepare($sql);

            // Normalize interests input array or string
            $interests = '';
            if (!empty($data['interests'])) {
                if (is_array($data['interests'])) {
                    $cleaned = array_map('trim', array_filter($data['interests']));
                    $interests = implode(', ', $cleaned);
                } else {
                    $interests = trim($data['interests']);
                }
            }

            // Validate email format
            $rawEmail = trim($data['email'] ?? '');
            $email = filter_var($rawEmail, FILTER_VALIDATE_EMAIL) ? $rawEmail : null;

            if (!$email) {
                error_log("Inquiry Error: Invalid email format provided.");
                return false;
            }

            $success = $stmt->execute([
                ':name'            => trim($data['name'] ?? ''),
                ':email'           => $email,
                ':nationality'     => trim($data['nationality'] ?? ''),
                ':season'          => trim($data['season'] ?? ''),
                ':duration'        => max(1, (int) ($data['duration'] ?? 1)),
                ':adults'          => max(1, (int) ($data['adults'] ?? 1)),
                ':children'        => max(0, (int) ($data['children'] ?? 0)),
                ':infants'         => max(0, (int) ($data['infants'] ?? 0)),
                ':interests'       => $interests,
                ':estimated_total' => (float) ($data['estimated_total'] ?? 0.00),
                ':status'          => 'new'
            ]);

            return $success ? (int) $this->db->lastInsertId() : false;

        } catch (PDOException $e) {
            error_log("Inquiry Database Error: " . $e->getMessage());
            return false;
        }
    }
}