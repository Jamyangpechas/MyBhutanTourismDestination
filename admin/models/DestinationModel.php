<?php

class DestinationModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllDestinations() {
        $stmt = $this->pdo->query("SELECT * FROM destinations ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDestinationById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM destinations WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addDestination($data) {
        $sql = "INSERT INTO destinations (title, badge, region, activity, media_path, media_type, description, highlights, tags) 
                VALUES (:title, :badge, :region, :activity, :media_path, :media_type, :description, :highlights, :tags)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':title'       => $data['title'],
            ':badge'       => $data['badge'],
            ':region'      => $data['region'],
            ':activity'    => $data['activity'],
            ':media_path'  => $data['media_path'],
            ':media_type'  => $data['media_type'],
            ':description' => $data['desc'],
            ':highlights'  => $data['highlights'],
            ':tags'        => $data['tags']
        ]);
    }

    public function updateDestination($id, $data) {
        $sql = "UPDATE destinations SET 
                    title = :title,
                    badge = :badge,
                    region = :region,
                    activity = :activity,
                    media_path = :media_path,
                    media_type = :media_type,
                    description = :description,
                    highlights = :highlights,
                    tags = :tags
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id'          => $id,
            ':title'       => $data['title'],
            ':badge'       => $data['badge'],
            ':region'      => $data['region'],
            ':activity'    => $data['activity'],
            ':media_path'  => $data['media_path'],
            ':media_type'  => $data['media_type'],
            ':description' => $data['desc'],
            ':highlights'  => $data['highlights'],
            ':tags'        => $data['tags']
        ]);
    }

    public function deleteDestination($id) {
        $stmt = $this->pdo->prepare("DELETE FROM destinations WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}