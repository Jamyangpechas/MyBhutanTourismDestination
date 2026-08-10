<?php
declare(strict_types=1);

class HeroModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Resolve stored media paths to absolute web URLs & check physical disk existence.
     */
    private function resolveMediaPath(?string $path, string $type): array {
        if (empty($path) || $type === 'none') {
            return ['type' => 'none', 'path' => null];
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return ['type' => $type, 'path' => $path];
        }

        // Sanitize path against directory traversal
        $sanitizedPath = str_replace(['../', '..\\'], '', $path);
        $webPath       = '/' . ltrim($sanitizedPath, '/');
        $projectRoot   = realpath(__DIR__ . '/../../') ?: __DIR__ . '/../..';
        $diskPath      = $projectRoot . $webPath;

        if (!file_exists($diskPath)) {
            error_log("HeroModel Notice: File missing on disk at " . $diskPath);
            return ['type' => 'none', 'path' => null];
        }

        return ['type' => $type, 'path' => $webPath];
    }

    // ==========================================
    // 1. HERO SECTION
    // ==========================================
    public function getHeroSettings(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM hero_settings ORDER BY id ASC LIMIT 1");
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
    }

    // ==========================================
    // 2. SDF SETTINGS & FEATURES
    // ==========================================
    public function getSdfSettings(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM sdf_settings ORDER BY id ASC LIMIT 1");
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error in getSdfSettings: " . $e->getMessage());
            return [];
        }
    }

    public function getSdfFeatures(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM sdf_features ORDER BY id ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error in getSdfFeatures: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // 3. LUXURY SECTION
    // ==========================================
    public function getLuxurySection(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM luxury_section ORDER BY id ASC LIMIT 1");
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($data) && !empty($data['image_path'])) {
                $data['image_path'] = '/' . ltrim($data['image_path'], '/');
            }
            return $data ?: [];
        } catch (PDOException $e) {
            error_log("Error in getLuxurySection: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // 4. PROMO BANNER
    // ==========================================
    public function getPromoBanner(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM promo_banner ORDER BY id DESC LIMIT 1");
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error in getPromoBanner: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // 5. BRAND SHOWCASE
    // ==========================================
    public function getBrandShowcase(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM brand_showcase LIMIT 1");
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error in getBrandShowcase: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // 6. DESTINATIONS
    // ==========================================
    public function getDestinations(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM destinations ORDER BY id ASC");
            $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
            foreach ($destinations as &$dest) {
                $imgPath = $dest['media_path'] ?? $dest['image_path'] ?? '';
                
                if (!empty($imgPath)) {
                    $dest['media_path'] = ltrim($imgPath, '/');
                    $dest['image_path'] = ltrim($imgPath, '/');
                } else {
                    $dest['media_path'] = 'assets/images/placeholder.jpg';
                    $dest['image_path'] = 'assets/images/placeholder.jpg';
                }

                if (isset($dest['highlights']) && is_string($dest['highlights'])) {
                    $decoded = json_decode($dest['highlights'], true);
                    $dest['highlights_json'] = json_encode($decoded ?: []);
                } else {
                    $dest['highlights_json'] = json_encode([]);
                }
            }
            unset($dest); // Prevent reference leaks

            return $destinations;
        } catch (PDOException $e) {
            error_log("Error in getDestinations: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // 7. EVENTS
    // ==========================================
    public function getEvents(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM events ORDER BY id ASC");
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($events as &$event) {
                if (!isset($event['date_range'])) {
                    $event['date_range'] = $event['date'] ?? $event['event_date'] ?? $event['season'] ?? 'Year-round';
                }

                if (isset($event['highlights'])) {
                    if (is_array($event['highlights'])) {
                        $event['highlights'] = json_encode($event['highlights']);
                    } elseif (is_string($event['highlights'])) {
                        $decoded = json_decode($event['highlights'], true);
                        $event['highlights'] = json_encode($decoded ?: []);
                    }
                } else {
                    $event['highlights'] = json_encode([]);
                }
            }
            unset($event);

            return $events;
        } catch (PDOException $e) {
            error_log("Error in getEvents: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get upcoming active events formatted specifically for the Hero Ticker Banner.
     */
    public function getUpcomingEventsForTicker(int $limit = 5): array {
        try {
            // Uses SELECT * to prevent schema mismatch exceptions
            $stmt = $this->pdo->prepare("SELECT * FROM events ORDER BY id ASC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $formattedEvents = [];
            foreach ($rows as $row) {
                $title = $row['title'] ?? $row['event_title'] ?? $row['name'] ?? null;
                if (!$title) {
                    continue;
                }

                // 1. Try formatted date range from start_date / end_date
                if (!empty($row['start_date'])) {
                    $startTs = strtotime((string) $row['start_date']);
                    if (!empty($row['end_date'])) {
                        $endTs = strtotime((string) $row['end_date']);
                        $dateString = (date('m Y', $startTs) === date('m Y', $endTs))
                            ? date('M j', $startTs) . '–' . date('j, Y', $endTs)
                            : date('M j', $startTs) . '–' . date('M j, Y', $endTs);
                    } else {
                        $dateString = date('M j, Y', $startTs);
                    }
                } 
                // 2. Fall back to alternative varchar fields ('date', 'event_date', 'season')
                else {
                    $dateString = !empty($row['date']) 
                        ? $row['date'] 
                        : ($row['event_date'] ?? $row['season'] ?? 'Upcoming');
                }

                $formattedEvents[] = [
                    'title'      => $title,
                    'date_range' => $dateString
                ];
            }

            return $formattedEvents;
        } catch (PDOException $e) {
            error_log("Error in getUpcomingEventsForTicker: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // 8. PLAN STEPS & RATES
    // ==========================================
    public function getPlanSteps(): array {
        $defaultSteps = [
            [
                'id'          => 1,
                'step_number' => 1,
                'title'       => 'Valid Passport',
                'content'     => '1. Obtain a Passport valid for at least 6 months.'
            ],
            [
                'id'          => 2,
                'step_number' => 2,
                'title'       => 'Booking Portal',
                'content'     => '2. Book with an authorized tour operator or self-apply via the official portal.'
            ],
            [
                'id'          => 3,
                'step_number' => 3,
                'title'       => 'Fee Payment',
                'content'     => '3. Pay the Sustainable Development Fee (SDF) & Visa processing fee.'
            ]
        ];

        try {
            $stmt = $this->pdo->query("SELECT * FROM plan_steps ORDER BY id ASC");
            $steps = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            if (empty($steps)) {
                return $defaultSteps;
            }

            return array_map(function ($step, $index) {
                return [
                    'id'          => (int) ($step['id'] ?? ($index + 1)),
                    'step_number' => (int) ($step['step_number'] ?? $step['sort_order'] ?? ($index + 1)),
                    'title'       => $step['title'] ?? $step['step_title'] ?? '',
                    'content'     => $step['content'] ?? $step['description'] ?? $step['step_text'] ?? $step['details'] ?? '',
                ];
            }, $steps, array_keys($steps));
        } catch (PDOException $e) {
            error_log("Error in getPlanSteps: " . $e->getMessage());
            return $defaultSteps;
        }
    }

    public function getPlanRates(): array {
        $defaultRates = [
            'sdf_international' => 100,
            'sdf_indian'        => 1200,
            'visa_fee'          => 40,
            'monument_fee'      => 12,
            'guide_rate'        => 25,
            'hotel_standard'    => 75,
            'hotel_luxury'      => 350,
        ];

        try {
            $stmt = $this->pdo->query("SELECT * FROM plan_rates ORDER BY id ASC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            if (empty($rows)) {
                return $defaultRates;
            }

            $rates = [];
            foreach ($rows as $row) {
                if (isset($row['rate_key'], $row['rate_value'])) {
                    $rates[$row['rate_key']] = is_numeric($row['rate_value']) 
                        ? (float) $row['rate_value'] 
                        : $row['rate_value'];
                } else {
                    $rates[] = $row;
                }
            }

            return array_merge($defaultRates, $rates);
        } catch (PDOException $e) {
            error_log("Error in getPlanRates: " . $e->getMessage());
            return $defaultRates;
        }
    }
}