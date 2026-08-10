<?php

class LuxuryModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getSettings(): array {
        $stmt = $this->pdo->query("SELECT * FROM luxury_section ORDER BY id ASC LIMIT 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: [];
    }

    public function updateSettings(array $data): bool {
        $check = (int) $this->pdo->query("SELECT COUNT(*) FROM luxury_section")->fetchColumn();

        if ($check > 0) {
            $sql = "UPDATE luxury_section SET 
                        eyebrow       = :eyebrow,
                        title         = :title,
                        paragraph_1   = :paragraph_1,
                        paragraph_2   = :paragraph_2,
                        divider_quote = :divider_quote,
                        card_1_label  = :card_1_label,
                        card_1_image  = :card_1_image,
                        card_2_label  = :card_2_label,
                        card_2_image  = :card_2_image
                    WHERE id = (SELECT id FROM (SELECT id FROM luxury_section ORDER BY id ASC LIMIT 1) AS tmp)";
        } else {
            $sql = "INSERT INTO luxury_section 
                        (eyebrow, title, paragraph_1, paragraph_2, divider_quote, card_1_label, card_1_image, card_2_label, card_2_image) 
                    VALUES 
                        (:eyebrow, :title, :paragraph_1, :paragraph_2, :divider_quote, :card_1_label, :card_1_image, :card_2_label, :card_2_image)";
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':eyebrow'       => $data['eyebrow'],
            ':title'         => $data['title'],
            ':paragraph_1'   => $data['paragraph_1'],
            ':paragraph_2'   => $data['paragraph_2'],
            ':divider_quote' => $data['divider_quote'],
            ':card_1_label'  => $data['card_1_label'],
            ':card_1_image'  => $data['card_1_image'],
            ':card_2_label'  => $data['card_2_label'],
            ':card_2_image'  => $data['card_2_image'],
        ]);
    }
}