<?php

class BrandModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getBrandData() {
        $stmt = $this->pdo->query("SELECT * FROM brand_showcase ORDER BY id DESC LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function saveBrandData($data) {
        $existing = $this->getBrandData();

        $fields = [
            'eyebrow', 'heading', 'manifesto',
            'block1_title', 'block1_subline', 'block1_theme', 'block1_exp',
            'block2_title', 'block2_subline', 'block2_theme', 'block2_exp',
            'block3_title', 'block3_subline', 'block3_theme', 'block3_exp',
            'block4_title', 'block4_subline', 'block4_theme', 'block4_exp',
            'block5_title', 'block5_subline', 'block5_theme', 'block5_exp',
            'block6_title', 'block6_subline', 'block6_theme', 'block6_exp'
        ];

        $params = [];
        foreach ($fields as $field) {
            $params[":{$field}"] = $data[$field] ?? '';
        }

        if (!empty($existing['id'])) {
            $setAssignments = [];
            foreach ($fields as $field) {
                $setAssignments[] = "{$field} = :{$field}";
            }
            $sql = "UPDATE brand_showcase SET " . implode(', ', $setAssignments) . " WHERE id = :id";
            $params[':id'] = $existing['id'];
        } else {
            $columnList = implode(', ', $fields);
            $paramList  = ':' . implode(', :', $fields);
            $sql = "INSERT INTO brand_showcase ({$columnList}) VALUES ({$paramList})";
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}