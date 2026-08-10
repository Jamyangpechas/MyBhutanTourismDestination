<?php

class SdfModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get single-row SDF headings configuration.
     */
    public function getHeadings(): array {
        $stmt = $this->pdo->query("SELECT eyebrow, intro, closing_title, closing_desc FROM sdf_settings WHERE id = 1");
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $defaults = [
            'eyebrow'       => 'WHY Sustainable Development Fee?',
            'intro'         => 'Your Sustainable Development Fee (SDF) contribution helps Bhutan.',
            'closing_title' => 'Your journey becomes part of something greater.',
            'closing_desc'  => 'Come for the stillness. Leave knowing you helped preserve it.'
        ];

        return $data ?: $defaults;
    }

    /**
     * Update section headings.
     */
    public function updateHeadings(array $data): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO sdf_settings (id, eyebrow, intro, closing_title, closing_desc)
            VALUES (1, :eyebrow, :intro, :closing_title, :closing_desc)
            ON DUPLICATE KEY UPDATE
                eyebrow = VALUES(eyebrow),
                intro = VALUES(intro),
                closing_title = VALUES(closing_title),
                closing_desc = VALUES(closing_desc)
        ");

        return $stmt->execute([
            ':eyebrow'       => $data['eyebrow'],
            ':intro'         => $data['intro'],
            ':closing_title' => $data['closing_title'],
            ':closing_desc'  => $data['closing_desc']
        ]);
    }

    /**
     * Get all active feature cards sorted by ID/order.
     */
    public function getFeatures(): array {
        $stmt = $this->pdo->query("SELECT id, title, image, `desc` FROM sdf_features ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get a single card by ID.
     */
    public function getFeatureById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT id, title, image, `desc` FROM sdf_features WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Create or update a feature card.
     */
    public function saveFeature(array $data): bool {
        if (!empty($data['id'])) {
            // Update
            if (!empty($data['image'])) {
                $stmt = $this->pdo->prepare("UPDATE sdf_features SET title = :title, image = :image, `desc` = :desc WHERE id = :id");
                return $stmt->execute([
                    ':title' => $data['title'],
                    ':image' => $data['image'],
                    ':desc'  => $data['desc'],
                    ':id'     => $data['id']
                ]);
            } else {
                $stmt = $this->pdo->prepare("UPDATE sdf_features SET title = :title, `desc` = :desc WHERE id = :id");
                return $stmt->execute([
                    ':title' => $data['title'],
                    ':desc'  => $data['desc'],
                    ':id'     => $data['id']
                ]);
            }
        } else {
            // Insert
            $stmt = $this->pdo->prepare("INSERT INTO sdf_features (title, image, `desc`) VALUES (:title, :image, :desc)");
            return $stmt->execute([
                ':title' => $data['title'],
                ':image' => $data['image'],
                ':desc'  => $data['desc']
            ]);
        }
    }

    /**
     * Delete a card by ID.
     */
    public function deleteFeature(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM sdf_features WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}