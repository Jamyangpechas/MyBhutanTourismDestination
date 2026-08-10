<?php

declare(strict_types=1);

// 1. Require DB connection, HeroModel, and SeriesDepartureModel files
require_once __DIR__ . '/../../admin/config/db.php';
require_once __DIR__ . '/../model/HeroModel.php';
require_once __DIR__ . '/../model/SeriesDepartureModel.php';

class HomeController {
    private HeroModel $heroModel;
    private SeriesDepartureModel $seriesModel;

    public function __construct(?HeroModel $heroModel = null, ?SeriesDepartureModel $seriesModel = null) {
        global $pdo;

        if (!$pdo instanceof \PDO) {
            error_log('HomeController Warning: $pdo global is not a valid PDO instance.');
        }

        $this->heroModel   = $heroModel ?? new HeroModel($pdo);
        $this->seriesModel = $seriesModel ?? new SeriesDepartureModel($pdo);
    }

    /**
     * Aggregates and returns data payload for the homepage view.
     */
    public function index(): array {
        try {
            $destinations = method_exists($this->heroModel, 'getDestinations') ? $this->heroModel->getDestinations() : [];
            $sdfFeatures  = method_exists($this->heroModel, 'getSdfFeatures') ? $this->heroModel->getSdfFeatures() : [];

            // Process destinations: summarize 'description' into 'short_desc' (20 words max)
            foreach ($destinations as &$item) {
                $rawText = $item['description'] ?? $item['desc'] ?? '';
                $item['short_desc'] = $this->summarizeTo20Words($rawText);
            }
            unset($item);

            // Process SDF features: summarize 'desc' into 'short_desc' (20 words max)
            foreach ($sdfFeatures as &$feature) {
                $rawText = $feature['desc'] ?? $feature['description'] ?? '';
                $feature['short_desc'] = $this->summarizeTo20Words($rawText);
            }
            unset($feature);

            // Retrieve group series departures safely
            $departures = method_exists($this->seriesModel, 'getActiveDepartures') 
                ? $this->seriesModel->getActiveDepartures() 
                : [];

            return [
                'hero'          => method_exists($this->heroModel, 'getHeroSettings') ? $this->heroModel->getHeroSettings() : [],
                'sdfSettings'   => method_exists($this->heroModel, 'getSdfSettings') ? $this->heroModel->getSdfSettings() : [],
                'sdfFeatures'   => $sdfFeatures,
                'departures'    => $departures,
                'luxurySection' => method_exists($this->heroModel, 'getLuxurySection') ? $this->heroModel->getLuxurySection() : [],
                'promoBanner'   => method_exists($this->heroModel, 'getPromoBanner') ? $this->heroModel->getPromoBanner() : [],
                'brandShowcase' => method_exists($this->heroModel, 'getBrandShowcase') ? $this->heroModel->getBrandShowcase() : [],
                'destinations'  => $destinations,
                'events'        => method_exists($this->heroModel, 'getEvents') ? $this->heroModel->getEvents() : [],
                'tickerEvents'  => method_exists($this->heroModel, 'getUpcomingEventsForTicker') ? $this->heroModel->getUpcomingEventsForTicker() : [],
                'planSteps'     => method_exists($this->heroModel, 'getPlanSteps') ? $this->heroModel->getPlanSteps() : $this->getFallbackPlanSteps(),
                'planRates'     => method_exists($this->heroModel, 'getPlanRates') ? $this->heroModel->getPlanRates() : $this->getFallbackPlanRates()
            ];
        } catch (\Throwable $e) {
            error_log(sprintf(
                'HomeController index error: %s in %s on line %d',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));

            return [
                'hero'          => [],
                'sdfSettings'   => [],
                'sdfFeatures'   => [],
                'departures'    => [],
                'luxurySection' => [],
                'promoBanner'   => [],
                'brandShowcase' => [],
                'destinations'  => [],
                'events'        => [],
                'tickerEvents'  => [],
                'planSteps'     => $this->getFallbackPlanSteps(),
                'planRates'     => $this->getFallbackPlanRates()
            ];
        }
    }

    /**
     * Helper to summarize/truncate text strictly to 20 words or fewer.
     */
    private function summarizeTo20Words(string $text, int $limit = 20): string {
        $cleanText = trim(strip_tags($text));
        if (empty($cleanText)) {
            return '';
        }

        // Split by white spaces
        $words = preg_split('/\s+/', $cleanText);

        if (count($words) <= $limit) {
            return $cleanText;
        }

        // Slice first 20 words and append ellipsis
        return implode(' ', array_slice($words, 0, $limit)) . '...';
    }

    /**
     * Provides default travel steps if model resolution fails entirely.
     */
    private function getFallbackPlanSteps(): array {
        return [
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
    }

    /**
     * Provides default rates if model resolution fails entirely.
     */
    private function getFallbackPlanRates(): array {
        return [
            'sdf_international' => 100,
            'sdf_indian'        => 1200,
            'visa_fee'          => 40,
            'monument_fee'      => 12,
            'guide_rate'        => 25,
            'hotel_standard'    => 75,
            'hotel_luxury'      => 350,
        ];
    }
}