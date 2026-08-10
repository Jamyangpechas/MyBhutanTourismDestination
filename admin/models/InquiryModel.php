<?php

class InquiryModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAllInquiries(): array {
        $stmt = $this->pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMetrics(): array {
        $stmt = $this->pdo->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count
            FROM inquiries
        ");
        $metrics = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total'     => (int)($metrics['total'] ?? 0),
            'new'       => (int)($metrics['new_count'] ?? 0),
            'confirmed' => (int)($metrics['confirmed_count'] ?? 0),
        ];
    }

    /**
     * Inserts a new traveler inquiry into the database.
     */
    public function createInquiry(array $data): bool {
        $sql = "INSERT INTO inquiries 
                    (name, email, nationality, season, duration, adults, children, infants, status, created_at) 
                VALUES 
                    (:name, :email, :nationality, :season, :duration, :adults, :children, :infants, 'new', NOW())";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':name'        => trim($data['name'] ?? ''),
            ':email'       => trim($data['email'] ?? ''),
            ':nationality' => trim($data['nationality'] ?? ''),
            ':season'      => trim($data['season'] ?? 'Any'),
            ':duration'    => (int)($data['duration'] ?? 1),
            ':adults'      => (int)($data['adults'] ?? 1),
            ':children'    => (int)($data['children'] ?? 0),
            ':infants'     => (int)($data['infants'] ?? 0)
        ]);
    }

    public function updateStatus(int $id, string $status): bool {
        $validStatuses = ['new', 'contacted', 'confirmed', 'archived'];
        if (!in_array($status, $validStatuses, true)) {
            return false;
        }

        $stmt = $this->pdo->prepare("UPDATE inquiries SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function deleteInquiry(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM inquiries WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}