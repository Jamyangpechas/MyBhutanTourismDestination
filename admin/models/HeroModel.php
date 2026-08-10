<?php
declare(strict_types=1);

class HeroModel {
    private PDO $db;
    private int $cacheTtl = 3600;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    private function getCached(string $key, callable $fallback) {
        if (function_exists('apcu_fetch')) {
            $success = false;
            $data = apcu_fetch($key, $success);
            if ($success) {
                return $data;
            }
        }

        $data = $fallback();

        if (function_exists('apcu_store') && $data !== null) {
            apcu_store($key, $data, $this->cacheTtl);
        }

        return $data;
    }

    private function resolveMediaPath(?string $path, string $type): array {
        if (empty($path) || $type === 'none') {
            return ['type' => 'none', 'path' => null];
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return ['type' => $type, 'path' => $path];
        }

        $webPath     = '/' . ltrim($path, '/');
        $projectRoot = realpath(__DIR__ . '/../../') ?: __DIR__ . '/../..';
        $diskPath    = $projectRoot . $webPath;

        if (!file_exists($diskPath)) {
            error_log("HeroModel Notice: Physical media file not found on disk at " . $diskPath);
            return ['type' => 'none', 'path' => null];
        }

        return ['type' => $type, 'path' => $webPath];
    }

    public function getHeroSettings(): array {
        return $this->getCached('hero_settings', function() {
            try {
                $stmt = $this->db->query("SELECT * FROM hero_settings ORDER BY id ASC LIMIT 1");
                $hero = $stmt->fetch(PDO::FETCH_ASSOC);

                if (is_array($hero)) {
                    $resolved = $this->resolveMediaPath($hero['media_path'] ?? null, $hero['media_type'] ?? 'none');
                    $hero['media_type'] = $resolved['type'];
                    $hero['media_path'] = $resolved['path'];
                    return $hero;
                }
            } catch (PDOException $e) {
                error_log("Error in getHeroSettings: " . $e->getMessage());
            }

            return [
                'eyebrow'    => 'BHUTAN BELIEVE',
                'title'      => 'Experience Stillness. Make an Impact.',
                'media_type' => 'none',
                'media_path' => null
            ];
        });
    }

    public function updateHeroSettings(string $eyebrow, string $title, ?string $mediaType = null, ?string $mediaPath = null): bool {
        $existing = $this->getHeroSettings();

        if ($mediaPath !== null) {
            $mediaPath = '/' . ltrim($mediaPath, '/');
        }

        if (!empty($existing) && isset($existing['id'])) {
            $sql = "UPDATE hero_settings SET eyebrow = :eyebrow, title = :title";
            $params = [
                ':eyebrow' => $eyebrow,
                ':title'   => $title,
                ':id'      => $existing['id']
            ];

            if ($mediaType !== null) {
                $sql .= ", media_type = :media_type, media_path = :media_path";
                $params[':media_type'] = $mediaType;
                $params[':media_path'] = $mediaPath;
            }

            $sql .= " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } else {
            $sql = "INSERT INTO hero_settings (eyebrow, title, media_type, media_path) 
                    VALUES (:eyebrow, :title, :media_type, :media_path)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':eyebrow'    => $eyebrow,
                ':title'      => $title,
                ':media_type' => $mediaType ?? 'none',
                ':media_path' => $mediaPath
            ]);
        }
    }

    public function clearCache(): void {
        if (function_exists('apcu_delete')) {
            apcu_delete('hero_settings');
        }
    }
}