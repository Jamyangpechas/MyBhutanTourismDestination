<?php

class EventModel {
    private PDO $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    /**
     * Fetch all events from the `events` table
     */
    public function getAllEvents(): array {
        $stmt = $this->db->query("SELECT * FROM `events` ORDER BY `id` ASC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $events = [];
        foreach ($results as $row) {
            $events[(int)$row['id']] = $row;
        }

        return $events;
    }

    /**
     * Insert a new record into the `events` table
     */
    public function addEvent(array $data): bool {
        $sql = "INSERT INTO `events` 
                (`title`, `season`, `category`, `date`, `tag`, `location`, `media`, `media_type`, `description`, `highlights`) 
                VALUES 
                (:title, :season, :category, :date, :tag, :location, :media, :media_type, :description, :highlights)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title'       => $data['title'],
            ':season'      => $data['season'],
            ':category'    => $data['category'],
            ':date'        => $data['date'],
            ':tag'         => $data['tag'],
            ':location'    => $data['location'],
            ':media'       => $data['media'] ?? '',
            ':media_type'  => $data['media_type'] ?? 'image',
            ':description' => $data['desc'] ?? '',
            ':highlights'  => $data['highlights']
        ]);
    }

    /**
     * Update an event by ID in the `events` table
     */
    public function updateEvent(int $id, array $data): bool {
        $sql = "UPDATE `events` 
                SET `title` = :title, 
                    `season` = :season, 
                    `category` = :category, 
                    `date` = :date, 
                    `tag` = :tag, 
                    `location` = :location, 
                    `media` = :media, 
                    `media_type` = :media_type, 
                    `description` = :description, 
                    `highlights` = :highlights 
                WHERE `id` = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'          => $id,
            ':title'       => $data['title'],
            ':season'      => $data['season'],
            ':category'    => $data['category'],
            ':date'        => $data['date'],
            ':tag'         => $data['tag'],
            ':location'    => $data['location'],
            ':media'       => $data['media'] ?? '',
            ':media_type'  => $data['media_type'] ?? 'image',
            ':description' => $data['desc'] ?? '',
            ':highlights'  => $data['highlights']
        ]);
    }

    /**
     * Delete an event row by ID
     */
    public function deleteEvent(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM `events` WHERE `id` = :id");
        return $stmt->execute([':id' => $id]);
    }
}