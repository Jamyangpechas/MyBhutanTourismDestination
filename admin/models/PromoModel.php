<?php

class PromoModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get single-row promo banner settings.
     */
    public function getSettings(): array {
        $stmt = $this->pdo->query("SELECT title, description, btn_text, btn_url FROM promo_banner_settings WHERE id = 1");
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $defaults = [
            'title'       => 'Plan Your High-Value Travel Experience',
            'description' => 'Bhutan welcomes guests through a sustainable tourism model that directly protects its environment and preserves local culture.',
            'btn_text'    => 'Apply for Visa & SDF',
            'btn_url'     => '#'
        ];

        return $data ?: $defaults;
    }

    /**
     * Upsert promo banner settings safely using PDO parameters.
     */
    public function updateSettings(array $data): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO promo_banner_settings (id, title, description, btn_text, btn_url)
            VALUES (1, :title, :description, :btn_text, :btn_url)
            ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                description = VALUES(description),
                btn_text = VALUES(btn_text),
                btn_url = VALUES(btn_url)
        ");

        return $stmt->execute([
            ':title'       => $data['title'],
            ':description' => $data['description'],
            ':btn_text'    => $data['btn_text'],
            ':btn_url'     => $data['btn_url']
        ]);
    }
}