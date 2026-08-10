<?php

class PlanModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Fetch all travel steps with separated title and description.
     */
    public function getSteps(): array {
        $stmt = $this->pdo->query("SELECT title, description FROM plan_steps ORDER BY step_order ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Replace all existing steps with updated titles and descriptions.
     */
    public function updateSteps(array $titles, array $descriptions): bool {
        try {
            $this->pdo->beginTransaction();

            $this->pdo->exec("DELETE FROM plan_steps");

            $stmt = $this->pdo->prepare("INSERT INTO plan_steps (step_order, title, description) VALUES (:order, :title, :description)");
            
            $order = 1;
            foreach ($titles as $idx => $title) {
                $cleanTitle = trim($title);
                $cleanDesc = trim($descriptions[$idx] ?? '');

                if ($cleanTitle !== '' || $cleanDesc !== '') {
                    $stmt->execute([
                        ':order'       => $order++,
                        ':title'       => $cleanTitle,
                        ':description' => $cleanDesc
                    ]);
                }
            }

            if ($this->pdo->inTransaction()) {
                return $this->pdo->commit();
            }

            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new Exception("Database step update failed: " . $e->getMessage());
        }
    }

    /**
     * Fetch key-value pairs of calculator base rates.
     */
    public function getRates(): array {
        $stmt = $this->pdo->query("SELECT rate_key, rate_value FROM plan_rates");
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $defaults = [
            'sdf_intl'           => 100,
            'sdf_indian'         => 1200,
            'visa_fee'           => 40,
            'monument_rate'      => 12,
            'accommodation_rate' => 150,
            'guide_rate'         => 40,
            'transport_rate'     => 60,
            'misc_rate'          => 20
        ];

        return array_merge($defaults, $results ?: []);
    }

    /**
     * Bulk update calculator rates.
     */
    public function updateRates(array $rates): bool {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                INSERT INTO plan_rates (rate_key, rate_value) 
                VALUES (:key, :val) 
                ON DUPLICATE KEY UPDATE rate_value = VALUES(rate_value)
            ");

            foreach ($rates as $key => $val) {
                $stmt->execute([
                    ':key' => $key,
                    ':val' => (float)$val
                ]);
            }

            if ($this->pdo->inTransaction()) {
                return $this->pdo->commit();
            }

            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new Exception("Database rate update failed: " . $e->getMessage());
        }
    }
}