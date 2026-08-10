<?php

declare(strict_types=1);

// ==========================================================================
// 1. Site Metadata & General Content
// ==========================================================================
$pageTitle = "Kingdom of Bhutan | Land of the Thunder Dragon";
$year      = (int) date("Y");

// ==========================================================================
// 2. Load Controller Dependency & Retrieve Page Data
// ==========================================================================
require_once __DIR__ . '/frontend/controller/HomeController.php';

$homeController = new HomeController();
$pageData       = $homeController->index();

// ==========================================================================
// 3. Extract Structured Data with Fallbacks
// ==========================================================================
$hero          = $pageData['hero'] ?? [];
$mediaType     = !empty($hero['media_type']) ? $hero['media_type'] : 'video';
$mediaPath     = !empty($hero['media_path']) ? $hero['media_path'] : 'assets/videos/hero.mp4';
$eyebrow       = $hero['eyebrow'] ?? 'BHUTAN BELIEVE';
$title         = $hero['title'] ?? 'Experience Stillness. Make an Impact.';

// Series Departures Array
$departures    = $pageData['departures'] ?? [];

// Core Homepage Data Arrays
$sdfSettings   = $pageData['sdfSettings'] ?? [];
$sdfFeatures   = $pageData['sdfFeatures'] ?? [];
$luxurySection = $pageData['luxurySection'] ?? [];
$promoBanner   = $pageData['promoBanner'] ?? [];
$brandShowcase = $pageData['brandShowcase'] ?? [];
$destinations  = $pageData['destinations'] ?? [];
$events        = $pageData['events'] ?? [];
$tickerEvents  = $pageData['tickerEvents'] ?? [];
$planSteps     = $pageData['planSteps'] ?? [];
$planRates     = $pageData['planRates'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Base & Component Styles -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/headernav.css">
    <link rel="stylesheet" href="css/herosection.css">
    <link rel="stylesheet" href="css/luxurybrand.css">
    <link rel="stylesheet" href="css/sdfimpact.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/bhutanbelieve.css">
    <link rel="stylesheet" href="css/destinations.css">
    <link rel="stylesheet" href="css/calendar.css">
    <link rel="stylesheet" href="css/map.css">
    <link rel="stylesheet" href="css/plantrip.css">
    <link rel="stylesheet" href="css/chatbot.css">
 <link rel="stylesheet" href="css/series-widget.css">

    <!-- Application Controller Scripts -->
    <script src="app.js" defer></script>
    <script src="frontend/js/inquiry.js" defer></script>
    <script src="frontend/js/chatbot.js" defer></script>

    <!-- Responsive Style Overrides -->
    <link rel="stylesheet" href="css/responsive.css">
</head>

<body>

    <!-- Header Navigation -->
    <header class="app-navbar">
        <div class="nav-container">
            <a href="#home" class="brand-logo nav-link">
                <span class="logo-title">BHUTAN</span>
                <span class="logo-sub">KINGDOM OF HAPPINESS</span>
            </a>

            <button type="button" class="mobile-nav-toggle" id="mobile-toggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="main-nav">
                <span class="hamburger-bar"></span>
                <span class="hamburger-bar"></span>
                <span class="hamburger-bar"></span>
            </button>

            <nav class="main-nav-links" id="main-nav">
                <a href="#believe" class="nav-link" id="believe-nav-btn">Bhutan Believe</a>
                <a href="#destinations" class="nav-link">Destinations</a>
                <a href="#calendar" class="nav-link">Bhutan Calendar</a>
                <a href="#interactive-map-view" class="nav-link highlight-link" id="map-nav-btn">Interactive Map</a>
                <a href="#plan" class="nav-link">Plan Your Trip</a>
            </nav>

            <div class="nav-tools">
                <div class="search-box">
                    <span class="search-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" placeholder="Search destination..." aria-label="Search destination" />
                </div>
                <button type="button" class="lang-btn" id="lang-menu-btn"><i class="fa-solid fa-globe"></i> EN</button>
                <a href="#plan" class="btn btn-outline nav-link">Get Your Visa</a>
                <a href="/admin/auth/login.php" class="btn btn-primary">Sign In</a>
            </div>
        </div>
    </header>

    <!-- Main Home Wrapper -->
    <div id="home-view" class="active-view">

     
    


    <!-- Hero Section -->
        <section class="hero" id="home">
            <?php if (!empty($mediaPath) && $mediaType !== 'none'): ?>
                <div class="hero-media-container">
                    <?php if ($mediaType === 'video'): ?>
                        <video 
                            id="heroVideo" 
                            class="hero-bg-media" 
                            autoplay 
                            muted 
                            loop 
                            playsinline 
                            src="<?= htmlspecialchars($mediaPath, ENT_QUOTES, 'UTF-8') ?>">
                        </video>
                        
                        <div class="hero-controls" role="toolbar" aria-label="Media Controls">
                            <button type="button" id="playBtn" class="hero-btn" aria-label="Pause Video" aria-pressed="false">
                                <span id="playIcon" aria-hidden="true"><i class="fa-solid fa-pause"></i></span>
                            </button>
                            
                            <button type="button" id="muteBtn" class="hero-btn" aria-label="Unmute Video" aria-pressed="true">
                                <span id="muteIcon" aria-hidden="true"><i class="fa-solid fa-volume-xmark"></i></span>
                            </button>
                            
                            <div class="hero-volume-wrapper">
                                <input 
                                    type="range" 
                                    id="volumeSlider" 
                                    class="hero-volume-slider" 
                                    min="0" 
                                    max="1" 
                                    step="0.05" 
                                    value="0" 
                                    aria-label="Volume Slider"
                                >
                            </div>
                        </div>
                    <?php elseif ($mediaType === 'image'): ?>
                        <img 
                            class="hero-bg-media" 
                            src="<?= htmlspecialchars($mediaPath, ENT_QUOTES, 'UTF-8') ?>" 
                            alt="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
                            loading="eager"
                        >
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="hero-content">
                <h1 class="hero-eyebrow"><?= htmlspecialchars($eyebrow, ENT_QUOTES, 'UTF-8') ?></h1>
                <h2 class="hero-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
            </div>

<!-- Animated Bottom Event Banner -->
<div class="hero-event-ticker">
    <div class="hero-ticker-badge">
        <span class="pulse-dot"></span> UPCOMING EVENTS
    </div>
    
    <div class="hero-ticker-track">
        <?php if (!empty($tickerEvents)): ?>
            <!-- Track 1 -->
            <div class="hero-ticker-content">
                <?php foreach ($tickerEvents as $event): ?>
                    <?php 
                        $eventId     = $event['id'] ?? '';
                        $calendarUrl = !empty($eventId) ? "#calendar?event_id=" . urlencode((string)$eventId) : "#calendar"; 
                    ?>
                    <a href="<?= htmlspecialchars($calendarUrl, ENT_QUOTES, 'UTF-8') ?>" class="ticker-calendar-link">
                        <span>
                            📅 <strong><?= htmlspecialchars($event['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>:</strong> 
                            <?= htmlspecialchars($event['date_range'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Track 2 (Duplicate for Seamless Infinite Scroll) -->
            <div class="hero-ticker-content" aria-hidden="true">
                <?php foreach ($tickerEvents as $event): ?>
                    <?php 
                        $eventId     = $event['id'] ?? '';
                        $calendarUrl = !empty($eventId) ? "#calendar?event_id=" . urlencode((string)$eventId) : "#calendar"; 
                    ?>
                    <a href="<?= htmlspecialchars($calendarUrl, ENT_QUOTES, 'UTF-8') ?>" class="ticker-calendar-link" tabindex="-1">
                        <span>
                            📅 <strong><?= htmlspecialchars($event['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>:</strong> 
                            <?= htmlspecialchars($event['date_range'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="hero-ticker-content">
                <a href="#calendar" class="ticker-calendar-link">
                    <span>🌟 <strong>Welcome:</strong> Explore our curated travel experiences and seasonal packages!</span>
                </a>
            </div>
            <div class="hero-ticker-content" aria-hidden="true">
                <a href="#calendar" class="ticker-calendar-link" tabindex="-1">
                    <span>🌟 <strong>Welcome:</strong> Explore our curated travel experiences and seasonal packages!</span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
        </section>





        <!-- SDF Contribution Impact Section -->
        <section class="sdf-section" id="sdf">
            <div class="sdf-intro">
                <span class="sdf-eyebrow"><?= htmlspecialchars($sdfSettings['eyebrow'] ?? 'Sustainable Tourism', ENT_QUOTES, 'UTF-8') ?></span>
                <p><?= htmlspecialchars($sdfSettings['intro'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <div class="sdf-grid">
                <?php foreach ($sdfFeatures as $item): ?>
                    <article class="sdf-card">
                        <div class="sdf-card-header">
                            <h4><?= htmlspecialchars($item['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h4>
                        </div>
                        <div class="sdf-image-container">
                            <img 
                                src="<?= htmlspecialchars($item['image'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                alt="<?= htmlspecialchars($item['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                loading="lazy"
                            >
                        </div>
                        <div class="sdf-card-body">
                            <span class="sdf-card-accent">◆</span>
                            <p><?= htmlspecialchars($item['desc'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="sdf-closing">
                <div class="diamond-divider">─────── ◆ ───────</div>   
                <h4><?= htmlspecialchars($sdfSettings['closing_title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h4>
                <p><?= htmlspecialchars($sdfSettings['closing_desc'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </section>

        <!-- Standalone Luxury Section -->
        <section class="luxury">
            <div class="hero-philosophy three-col-layout">
                <div class="luxury-card">
                    <div class="luxury-image-wrapper">
                        <img src="<?= htmlspecialchars($luxurySection['card_1_image'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($luxurySection['card_1_label'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <span class="image-label"><?= htmlspecialchars($luxurySection['card_1_label'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <div class="luxury-content">
                    <span class="eyebrow-catchy"><?= htmlspecialchars($luxurySection['eyebrow'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    <h2><?= htmlspecialchars($luxurySection['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($luxurySection['paragraph_1'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    <p><?= htmlspecialchars($luxurySection['paragraph_2'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="diamond-divider">─────── ◆ ───────</div>           
                    <p><?= htmlspecialchars($luxurySection['divider_quote'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="luxury-card">
                    <div class="luxury-image-wrapper">
                        <img src="<?= htmlspecialchars($luxurySection['card_2_image'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($luxurySection['card_2_label'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <span class="image-label"><?= htmlspecialchars($luxurySection['card_2_label'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
        </section>



        <!-- Promotional Banner -->
        <section class="section">
            <div class="banner">
                <h2><?= htmlspecialchars($promoBanner['title'] ?? 'Plan Your High-Value Travel Experience', ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= htmlspecialchars($promoBanner['description'] ?? 'Bhutan welcomes guests through a sustainable tourism model...', ENT_QUOTES, 'UTF-8') ?></p>
                <a href="<?= htmlspecialchars($promoBanner['btn_url'] ?? '#plan', ENT_QUOTES, 'UTF-8') ?>" class="btn-permits"><?= htmlspecialchars($promoBanner['btn_text'] ?? 'Apply for Visa & SDF', ENT_QUOTES, 'UTF-8') ?></a>
            </div>
        </section>

    </div>

  



<!-- Bhutan Believe Showcase View -->
<section id="brand-details-view" class="brand-showcase">
    <div class="container">
        <button id="back-to-home-btn" type="button" class="btn btn-outline back-to-home-btn">
            ← Back to Home
        </button>
        
        <header class="section-header">
            <span class="eyebrow"><?= htmlspecialchars($brandShowcase['eyebrow'] ?? 'An Anatomy of the Brand', ENT_QUOTES, 'UTF-8') ?></span>
            <h2><?= htmlspecialchars($brandShowcase['heading'] ?? 'BHUTAN Believe', ENT_QUOTES, 'UTF-8') ?></h2>
        </header>

        <blockquote class="brand-manifesto">
            <p><strong>Brand Manifesto:</strong></p>
            <p><em><?= htmlspecialchars($brandShowcase['manifesto'] ?? '', ENT_QUOTES, 'UTF-8') ?></em></p>
        </blockquote>

        <?php 
        // Theme color classes array
        $themeClasses = [
            'policy-block',    // Royal Slate
            'youth-block',     // Golden Sunshine
            'nature-block',    // Emerald Forest
            'sdf-block',       // Vibrant Teal
            'traveler-block',  // Mindful Indigo
            'crafts-block'     // Terracotta Ochre
        ];
        $totalThemes = count($themeClasses);

        // Dynamic loop: check for blocks 1 up to 20 automatically
        for ($i = 1; $i <= 20; $i++): 
            if (!empty($brandShowcase["block{$i}_title"])): 
                // Modulo operator (%) auto-cycles through the 6 themes for block 7, 8, 9, etc.
                $blockClass = $themeClasses[($i - 1) % $totalThemes]; 
        ?>
                <div class="brand-block <?= $blockClass ?>">
                    <div class="card-header">
                        <h3><?= htmlspecialchars($brandShowcase["block{$i}_title"], ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php if (!empty($brandShowcase["block{$i}_subline"])): ?>
                            <span class="banner-subline"><?= htmlspecialchars($brandShowcase["block{$i}_subline"], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-content">
                        <?php if (!empty($brandShowcase["block{$i}_theme"])): ?>
                            <p><strong>Theme:</strong> <?= htmlspecialchars($brandShowcase["block{$i}_theme"], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($brandShowcase["block{$i}_exp"])): ?>
                            <p><strong>Traveler Experience:</strong> <?= htmlspecialchars($brandShowcase["block{$i}_exp"], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
        <?php 
            endif; 
        endfor; 
        ?>
    </div>
</section>





<!-- Destinations & Products Showcase View -->
    <section id="destinations-view" class="destinations-showcase" style="display: none;">
        <div class="container">
            <button type="button" class="btn-back back-to-home-btn">
                ← Back to Home
            </button>

            <header class="section-header">
                <span class="eyebrow">EXPLORE THE KINGDOM</span>
                <h2>Sacred Valleys & Curated Experiences</h2>
            </header>

            <!-- Horizontal Filter Bar Row -->
            <div class="dest-filter-bar-container">
                <!-- Standalone Button -->
                <button class="filter-btn active" data-filter="all">All Experiences</button>

                <!-- Divider 1 -->
                <div class="filter-divider"></div>

                <!-- DESTINATIONS GROUP (Label on top, buttons in row) -->
                <div class="filter-column-group">
                    <span class="filter-group-label">DESTINATIONS</span>
                    <div class="filter-buttons-row">
                        <button class="filter-btn" data-filter="region-western">Western Bhutan</button>
                        <button class="filter-btn" data-filter="region-central">Central Bhutan</button>
                        <button class="filter-btn" data-filter="region-eastern">Eastern Bhutan</button>
                    </div>
                </div>

                <!-- Divider 2 -->
                <div class="filter-divider"></div>

                <!-- PRODUCTS GROUP (Label on top, buttons in row) -->
                <div class="filter-column-group">
                    <span class="filter-group-label">PRODUCTS</span>
                    <div class="filter-buttons-row">
                        <button class="filter-btn" data-filter="act-culture">Culture & Festivals</button>
                        <button class="filter-btn" data-filter="act-trekking">Trekking & Nature</button>
                        <button class="filter-btn" data-filter="act-wellness">Wellness & Heritage</button>
                    </div>
                </div>
            </div>

            <div class="destinations-grid">
                <?php if (empty($destinations)): ?>
                    <p class="no-data-msg">No destinations found in database.</p>
                <?php else: ?>
                    <?php foreach ($destinations as $dest): ?>
                        <?php 
                            $title = $dest['title'] ?? $dest['name'] ?? 'Bhutan Destination';
                            $image = $dest['media_path'] ?? $dest['image_path'] ?? 'assets/images/placeholder.jpg';
                            $imageSrc = ltrim($image, '/'); 
                            
                            $region = strtolower(trim($dest['region'] ?? ''));
                            $formattedRegion = (strncmp($region, 'region-', 7) === 0) ? $region : 'region-' . $region;

                            $activity = strtolower(trim($dest['activity'] ?? ''));
                            $formattedActivity = (strncmp($activity, 'act-', 4) === 0) ? $activity : 'act-' . $activity;

                            $rawTags = explode(',', $dest['tags'] ?? ''); 
                            $highlights = $dest['highlights_json'] ?? $dest['highlights'] ?? '[]';
                            if (is_array($highlights)) {
                                $highlightsJson = json_encode($highlights);
                            } else {
                                $decoded = json_decode((string)$highlights, true);
                                $highlightsJson = json_encode($decoded ?: []);
                            }
                        ?>
                        <article class="destination-card" 
                                 data-region="<?= htmlspecialchars($formattedRegion, ENT_QUOTES, 'UTF-8') ?>" 
                                 data-activity="<?= htmlspecialchars($formattedActivity, ENT_QUOTES, 'UTF-8') ?>"
                                 data-title="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
                                 data-badge="<?= htmlspecialchars($dest['badge'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                 data-img="<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>"
                                 data-desc="<?= htmlspecialchars($dest['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                 data-highlights='<?= htmlspecialchars($highlightsJson, ENT_QUOTES, 'UTF-8') ?>'
                                 onclick="handleCardClick(this)">
                            
                            <div class="dest-image-wrapper">
                                <img src="<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>" 
                                     alt="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>" 
                                     loading="lazy">
                                <?php if (!empty($dest['badge'])): ?>
                                    <span class="dest-badge"><?= htmlspecialchars($dest['badge'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="dest-card-body">
                                <h3><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
                                <p><?= htmlspecialchars($dest['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                                
                                <div class="dest-tags">
                                    <?php foreach ($rawTags as $tag): ?>
                                        <?php if (!empty(trim($tag))): ?>
                                            <span class="tag"><?= htmlspecialchars(trim($tag), ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Destination Lightbox Modal Overlay -->
    <div class="dest-modal" id="destModal" aria-hidden="true">
        <div class="dest-modal-overlay" onclick="closeDestModal()"></div>
        <div class="dest-modal-content">
            <button type="button" class="dest-modal-close" onclick="closeDestModal()" aria-label="Close modal">&times;</button>
            <div class="dest-modal-hero">
                <img id="modalImg" src="" alt="Destination Image">
                <span class="dest-modal-badge" id="modalBadge"></span>
            </div>
            <div class="dest-modal-body">
                <h3 id="modalTitle"></h3>
                <p id="modalDesc"></p>
                <div class="dest-modal-highlights">
                    <h4>Top Highlights & Experiences</h4>
                    <ul id="modalHighlights"></ul>
                </div>
                <button type="button" class="dest-modal-btn" onclick="closeDestModal()">Back to Destinations</button>
            </div>
        </div>
    </div>




    <!-- Bhutan Calendar Showcase View -->
    <section id="calendar-view" class="calendar-showcase" style="display: none;">
        <div class="container">
            <button type="button" class="btn-back back-to-home-btn">
                ← Back to Home
            </button>

            <header class="section-header">
                <span class="eyebrow">FESTIVALS & CELEBRATIONS</span>
                <h2>Bhutan Cultural Calendar</h2>
            </header>

            <!-- Interactive Season & Festival Filter Bar -->
            <div class="cal-filter-bar">
                <button class="cal-filter-btn active" data-cal-filter="all">All Events</button>
                <div class="filter-divider"></div>
                <button class="cal-filter-btn" data-cal-filter="spring">Spring (Mar - May)</button>
                <button class="cal-filter-btn" data-cal-filter="summer">Summer (Jun - Aug)</button>
                <button class="cal-filter-btn" data-cal-filter="autumn">Autumn (Sep - Nov)</button>
                <button class="cal-filter-btn" data-cal-filter="winter">Winter (Dec - Feb)</button>
                <div class="filter-divider"></div>
                <button class="cal-filter-btn" data-cal-filter="cat-tshechu">Sacred Tshechus</button>
                <button class="cal-filter-btn" data-cal-filter="cat-nature">Nature & Eco</button>
            </div>

            <!-- Event Schedule Grid -->
            <div class="calendar-grid">
                <?php if (empty($events)): ?>
                    <p class="no-data-msg">No upcoming events scheduled.</p>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <?php 
                            $eventTitle      = $event['title'] ?? 'Bhutan Event';
                            $eventSeason     = strtolower(trim($event['season'] ?? 'spring'));
                            $eventCategory   = strtolower(trim($event['category'] ?? 'tshechu'));
                            $eventTag        = $event['tag'] ?? $event['category'] ?? 'Festival';
                            $eventDate       = $event['date_range'] ?? $event['event_date'] ?? $event['season'] ?? 'Year-round';
                            $eventLocation   = $event['location'] ?? 'Bhutan';
                            $eventDesc       = $event['description'] ?? '';

                            $eventHighlights = $event['highlights'] ?? '[]';
                            if (is_array($eventHighlights)) {
                                $eventHighlightsJson = json_encode($eventHighlights);
                            } else {
                                $decoded = json_decode((string)$eventHighlights, true);
                                $eventHighlightsJson = json_encode($decoded ?: []);
                            }
                        ?>
                        <article class="event-card" 
                                 data-season="<?= htmlspecialchars($eventSeason, ENT_QUOTES, 'UTF-8') ?>" 
                                 data-cat="<?= htmlspecialchars($eventCategory, ENT_QUOTES, 'UTF-8') ?>"
                                 data-title="<?= htmlspecialchars($eventTitle, ENT_QUOTES, 'UTF-8') ?>"
                                 data-date="<?= htmlspecialchars($eventDate, ENT_QUOTES, 'UTF-8') ?>"
                                 data-tag="<?= htmlspecialchars($eventTag, ENT_QUOTES, 'UTF-8') ?>"
                                 data-location="<?= htmlspecialchars($eventLocation, ENT_QUOTES, 'UTF-8') ?>"
                                 data-desc="<?= htmlspecialchars($eventDesc, ENT_QUOTES, 'UTF-8') ?>"
                                 data-highlights='<?= htmlspecialchars($eventHighlightsJson, ENT_QUOTES, 'UTF-8') ?>'
                                 onclick="handleEventClick(this)">
                            <div class="event-date-badge">
                                <span class="month"><?= htmlspecialchars($eventDate, ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="type"><?= strtoupper(htmlspecialchars($eventSeason, ENT_QUOTES, 'UTF-8')) ?></span>
                            </div>
                            <div class="event-card-body">
                                <span class="event-tag"><?= htmlspecialchars($eventTag, ENT_QUOTES, 'UTF-8') ?></span>
                                <h3><?= htmlspecialchars($eventTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="event-location"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($eventLocation, ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="event-desc"><?= htmlspecialchars($eventDesc, ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Calendar Lightbox Modal Overlay -->
    <div id="calModal" class="cal-modal" aria-hidden="true">
        <div class="cal-modal-overlay" onclick="closeCalModal()"></div>
        <div class="cal-modal-content">
            <button class="cal-modal-close" onclick="closeCalModal()" aria-label="Close modal">&times;</button>
            <div class="cal-modal-header">
                <span id="calModalDate" class="cal-modal-date">DATE</span>
                <span id="calModalTag" class="cal-modal-tag">#TAG</span>
            </div>
            <div class="cal-modal-body">
                <h3 id="calModalTitle">Event Title</h3>
                <p id="calModalLocation" class="cal-modal-location"><i class="fa-solid fa-location-dot"></i> Location</p>
                <p id="calModalDesc">Event description text goes here...</p>
                
                <div class="cal-modal-highlights">
                    <h4>Festival Highlights</h4>
                    <ul id="calModalHighlights"></ul>
                </div>

                <button class="cal-modal-btn" onclick="closeCalModal()">Close Overview</button>
            </div>
        </div>
    </div>



    <!-- Interactive Map View -->
    <section id="interactive-map-view" class="map-showcase" style="display: none;">
        <div class="container">
            <button type="button" class="btn-back back-to-home-btn">← Back to Home</button>
            
            <header class="section-header">
                <span class="eyebrow">GEOGRAPHIC EXPLORER</span>
                <h2>Dzongkhags & Sacred Valleys</h2>
            </header>

            <div class="map-layout-grid">
                <div id="leaflet-map" style="width: 100%; height: 500px; border-radius: 20px;"></div>
                <div class="map-info-panel">
                    <div class="district-info-card active">
                        <span class="district-region" id="info-region">WESTERN REGION</span>
                        <h3 id="info-title">Paro Dzongkhag</h3>
                        <p class="district-desc" id="info-desc">Home to Paro International Airport...</p>
                        <div class="district-stats">
                            <div class="stat-item"><strong>Elevation:</strong> <span id="info-elevation">2,200m</span></div>
                            <div class="stat-item"><strong>Attraction:</strong> <span id="info-attraction">Tiger's Nest & Rinpung Dzong</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <!-- Plan Your Trip Showcase View -->
    <section id="plan-view" class="plan-showcase" style="display: none;">
        <div class="container">
            <button type="button" class="btn-back back-to-home-btn">
                ← Back to Home
            </button>

            <header class="section-header">
                <span class="eyebrow">YOUR JOURNEY BEGINS</span>
                <h2>Plan Your Trip to Bhutan</h2>
            </header>

            <div class="plan-grid">
                <!-- Travel Requirements Info Card -->
                <div class="plan-info-card" id="plan-info-card">
                    <h3 class="card-title">Essential Travel Steps</h3>
                    <ul class="plan-steps">
                        <?php if (empty($planSteps)): ?>
                            <li><strong>1. Passport:</strong> Obtain a Passport valid for at least 6 months.</li>
                            <li><strong>2. Booking:</strong> Book with an authorized tour operator or self-apply via the official portal.</li>
                            <li><strong>3. Payment:</strong> Pay the Sustainable Development Fee (SDF) & Visa processing fee.</li>
                        <?php else: ?>
                            <?php foreach ($planSteps as $index => $step): ?>
                                <?php 
                                    $title   = is_array($step) ? ($step['title'] ?? '') : '';
                                    $content = is_array($step) ? ($step['content'] ?? '') : $step;
                                    $stepId  = is_array($step) ? ($step['id'] ?? ($index + 1)) : ($index + 1);
                                ?>
                                <li data-step-id="<?= htmlspecialchars((string)$stepId, ENT_QUOTES, 'UTF-8') ?>">
                                    <?php if (!empty($title)): ?>
                                        <strong><?= htmlspecialchars(rtrim($title, ':') . ':', ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php endif; ?>
                                    <?= htmlspecialchars((string)$content, ENT_QUOTES, 'UTF-8') ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Real-Time Trip Cost Estimator Card -->
                <div class="plan-calculator-card">
                    <h3><i class="fa-solid fa-calculator"></i> Trip Cost Estimator</h3>
                    <form id="cost-calculator-form" class="plan-form" onsubmit="return false;"
                          data-rate-sdf-intl="<?= htmlspecialchars((string)($planRates['sdf_international'] ?? 100), ENT_QUOTES, 'UTF-8') ?>"
                          data-rate-sdf-inr="<?= htmlspecialchars((string)($planRates['sdf_indian'] ?? 1200), ENT_QUOTES, 'UTF-8') ?>"
                          data-rate-visa="<?= htmlspecialchars((string)($planRates['visa_fee'] ?? 40), ENT_QUOTES, 'UTF-8') ?>"
                          data-rate-monument="<?= htmlspecialchars((string)($planRates['monument_fee'] ?? 12), ENT_QUOTES, 'UTF-8') ?>"
                          data-rate-guide="<?= htmlspecialchars((string)($planRates['guide_rate'] ?? 25), ENT_QUOTES, 'UTF-8') ?>"
                          data-rate-hotel-std="<?= htmlspecialchars((string)($planRates['hotel_standard'] ?? 75), ENT_QUOTES, 'UTF-8') ?>"
                          data-rate-hotel-lux="<?= htmlspecialchars((string)($planRates['hotel_luxury'] ?? 350), ENT_QUOTES, 'UTF-8') ?>">
                        
                        <div class="form-group-row">
                            <div class="form-group">
                                <label for="calc-nationality">Nationality Category</label>
                                <select id="calc-nationality" required>
                                    <option value="international">International Tourist ($<?= htmlspecialchars((string)($planRates['sdf_international'] ?? 100), ENT_QUOTES, 'UTF-8') ?> SDF/night)</option>
                                    <option value="indian">Indian Passport Holder (₹<?= htmlspecialchars(number_format((float)($planRates['sdf_indian'] ?? 1200)), ENT_QUOTES, 'UTF-8') ?> SDF/night)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="calc-nights">Duration (Nights)</label>
                                <input type="number" id="calc-nights" min="1" value="5" required />
                            </div>
                        </div>

                        <!-- Traveler Age Breakdown Row -->
                        <div class="form-group-row three-cols">
                            <div class="form-group">
                                <label for="calc-adults">Adults (12+ yrs)</label>
                                <input type="number" id="calc-adults" min="1" value="1" required />
                            </div>
                            <div class="form-group">
                                <label for="calc-children">Child (6-11 yrs, 50% SDF)</label>
                                <input type="number" id="calc-children" min="0" value="0" />
                            </div>
                            <div class="form-group">
                                <label for="calc-infants">Infant (&lt;6 yrs, Free)</label>
                                <input type="number" id="calc-infants" min="0" value="0" />
                            </div>
                        </div>

                        <div class="form-group-row">
                            <div class="form-group">
                                <label for="calc-tier">Travel Comfort Tier</label>
                                <select id="calc-tier">
                                    <option value="standard">Standard (3★ Hotel & Guide)</option>
                                    <option value="luxury">Luxury (5★ Resort & Private Escort)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="calc-monuments">Monument Visits / Sites</label>
                                <select id="calc-monuments">
                                    <option value="0">None / Skip Sites</option>
                                    <option value="1">1 Site (e.g., Tiger's Nest)</option>
                                    <option value="3" selected>3 Sites (Tiger's Nest, Dzongs)</option>
                                    <option value="5">5+ Key Heritage Sites</option>
                                </select>
                            </div>
                        </div>

                        <!-- Dynamic Cost Summary Output -->
                        <div class="cost-summary-box">
                            <div class="cost-line">
                                <span>Sustainable Development Fee (SDF):</span>
                                <strong id="sdf-total-display">$500 USD</strong>
                            </div>
                            <div class="cost-line">
                                <span>Visa Processing Fee:</span>
                                <strong id="visa-total-display">$40 USD</strong>
                            </div>
                            <div class="cost-line">
                                <span>Monument Entry Fees:</span>
                                <strong id="monument-total-display">$36 USD</strong>
                            </div>
                            <div class="cost-line">
                                <span>Licensed Guide, Driver & Logistics:</span>
                                <strong id="guide-total-display">$125 USD</strong>
                            </div>
                            <div class="cost-line">
                                <span>Accommodation & Full Board Meals:</span>
                                <strong id="services-total-display">$375 USD</strong>
                            </div>
                            <div class="cost-line total-line">
                                <span>Estimated Ground Total:</span>
                                <strong id="grand-total-display">$1,076 USD</strong>
                            </div>
                            <p class="cost-note">*Children (6-11 yrs) get 50% SDF discount; infants under 6 yrs are exempt. Monument entry (Nu. 500 - 1,000/site) payable on-site or via tour operators.</p>
                        </div>
                    </form>
                </div>

                <!-- Interactive Trip Inquiry Form -->
                <div class="plan-form-card">
                    <h3>Inquire / Request Tour Package</h3>
                    <form id="trip-plan-form" class="plan-form" method="POST" action="api/submit_inquiry.php">
                        <div class="form-group">
                            <label for="traveler-name">Full Name</label>
                            <input type="text" id="traveler-name" name="full_name" placeholder="John Doe" required />
                        </div>
                        <div class="form-group">
                            <label for="traveler-email">Email Address</label>
                            <input type="email" id="traveler-email" name="email" placeholder="john@example.com" required />
                        </div>

                        <div class="form-group-row">
                            <div class="form-group">
                                <label for="traveler-nationality">Nationality</label>
                                <select id="traveler-nationality" name="nationality" required>
                                    <option value="international">International</option>
                                    <option value="indian">Indian Passport Holder</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="travel-dates">Preferred Season</label>
                                <select id="travel-dates" name="season">
                                    <option value="spring">Spring (Mar - May)</option>
                                    <option value="summer">Summer (Jun - Aug)</option>
                                    <option value="autumn">Autumn (Sep - Nov)</option>
                                    <option value="winter">Winter (Dec - Feb)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group-row">
                            <div class="form-group">
                                <label for="duration">Duration</label>
                                <select id="duration" name="duration">
                                    <option value="5-7">5 - 7 Days</option>
                                    <option value="8-12">8 - 12 Days</option>
                                    <option value="14+">14+ Days</option>
                                </select>
                            </div>
                            <div class="form-group-row three-cols" style="margin: 0;">
                                <div class="form-group">
                                    <label for="traveler-adults">Adults (12+)</label>
                                    <input type="number" id="traveler-adults" name="adults" min="1" value="1" required />
                                </div>
                                <div class="form-group">
                                    <label for="traveler-children">Child (6-11)</label>
                                    <input type="number" id="traveler-children" name="children" min="0" value="0" />
                                </div>
                                <div class="form-group">
                                    <label for="traveler-infants">Infant (&lt;6)</label>
                                    <input type="number" id="traveler-infants" name="infants" min="0" value="0" />
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Travel Interests (Select Multiple)</label>
                            <div class="interest-chips-group">
                                <label class="chip-item">
                                    <input type="checkbox" name="interests[]" value="culture" checked>
                                    <span>Culture & Festivals</span>
                                </label>
                                <label class="chip-item">
                                    <input type="checkbox" name="interests[]" value="trekking">
                                    <span>Trekking & Nature</span>
                                </label>
                                <label class="chip-item">
                                    <input type="checkbox" name="interests[]" value="wellness">
                                    <span>Wellness & Heritage</span>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Submit Request</button>
                    </form>
                </div>
            </div>
        </div>
    </section>


    


    

    <!-- Group Series Departures Widget -->
<div class="series-booking-widget">
    <h3>Upcoming Group Series Departures</h3>
    <p class="widget-subtitle">Select a guaranteed departure date to reserve your seat directly.</p>

    <div class="departures-list">
        <?php if (empty($departures)): ?>
            <p class="no-data-msg">No upcoming departures scheduled at this time.</p>
        <?php else: ?>
            <?php foreach ($departures as $dep): ?>
                <?php 
                    $seatsRemaining = max(0, (int)($dep['total_capacity'] ?? 0) - (int)($dep['booked_seats'] ?? 0));
                    $status         = strtolower($dep['status'] ?? 'open');
                    $isGuaranteed   = ($status === 'guaranteed' || (int)($dep['booked_seats'] ?? 0) >= (int)($dep['min_passengers'] ?? 1));
                    $isSoldOut      = ($status === 'sold_out' || $seatsRemaining <= 0);
                    $pricePerSeat   = (float)($dep['base_price'] ?? 0);
                    $dateStr        = !empty($dep['start_date']) ? date('M d, Y', strtotime($dep['start_date'])) : 'TBD';
                    $endDateStr     = !empty($dep['end_date']) ? date('M d, Y', strtotime($dep['end_date'])) : '';
                    
                    $titleText      = !empty($dep['title']) ? $dep['title'] : (!empty($dep['name']) ? $dep['name'] : 'Group Departure');
                    $descText       = !empty($dep['description']) ? $dep['description'] : (!empty($dep['subtitle']) ? $dep['subtitle'] : '');
                ?>
                <div class="departure-row <?= $isSoldOut ? 'sold-out' : '' ?>">
                    <div class="info-col">
                        <strong class="departure-title" style="display: block; font-size: 1.05em; margin-bottom: 2px;">
                            <a href="javascript:void(0);" 
                               class="btn-trigger-details"
                               data-title="<?= htmlspecialchars($titleText, ENT_QUOTES, 'UTF-8') ?>"
                               data-desc="<?= htmlspecialchars($descText, ENT_QUOTES, 'UTF-8') ?>"
                               data-start="<?= htmlspecialchars($dateStr, ENT_QUOTES, 'UTF-8') ?>"
                               data-end="<?= htmlspecialchars($endDateStr, ENT_QUOTES, 'UTF-8') ?>"
                               title="Click to view complete details"
                               style="color: #059669; text-decoration: underline; cursor: pointer; font-weight: 700;">
                                <?= htmlspecialchars($titleText) ?> ℹ️
                            </a>
                        </strong>
                        <?php if (!empty($descText)): ?>
                            <span class="departure-desc" style="display: block; font-size: 0.85em; color: #6b7280; line-height: 1.3;">
                                <?= htmlspecialchars(substr($descText, 0, 85)) ?><?= strlen($descText) > 85 ? '...' : '' ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="date-col">
                        <strong><?= htmlspecialchars($dateStr) ?></strong>
                        <?php if ($endDateStr): ?>
                            <span>to <?= htmlspecialchars($endDateStr) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="status-col">
                        <?php if ($isSoldOut): ?>
                            <span class="badge badge-soldout">Sold Out</span>
                        <?php elseif ($isGuaranteed): ?>
                            <span class="badge badge-guaranteed">✓ Guaranteed Departure</span>
                        <?php else: ?>
                            <span class="badge badge-open">Open Departure</span>
                        <?php endif; ?>
                    </div>

                    <div class="seats-col">
                        <span class="seats-count"><?= $isSoldOut ? '0' : $seatsRemaining ?> seats left</span>
                    </div>

                    <div class="price-col">
                        <strong class="departure-price">$<?= number_format($pricePerSeat, 2) ?></strong>
                    </div>

                    <div class="action-col">
                        <?php if ($isSoldOut): ?>
                            <button type="button" class="btn btn-disabled" disabled>Unavailable</button>
                        <?php else: ?>
                            <button type="button" 
                                    class="btn btn-primary btn-trigger-booking" 
                                    data-id="<?= (int)$dep['id'] ?>"
                                    data-date="<?= htmlspecialchars($dateStr, ENT_QUOTES, 'UTF-8') ?>"
                                    data-seats="<?= (int)$seatsRemaining ?>"
                                    data-price="<?= $pricePerSeat ?>">
                                Book Spot
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal CSS Rules -->
<style>
.series-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
}
.series-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(2px);
}
.series-modal-content {
    position: relative;
    z-index: 10;
    width: 90%;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>

<!-- Tour Description Details Modal -->
<div id="seriesDetailsModal" class="series-modal" style="display: none;">
    <div class="series-modal-overlay" onclick="window.closeDetailsModal()"></div>
    <div class="series-modal-content" style="max-width: 550px; padding: 24px; border-radius: 12px; background: #ffffff;">
        <button type="button" class="series-modal-close" onclick="window.closeDetailsModal()" style="font-size: 24px; cursor: pointer; float: right; border: none; background: transparent;">&times;</button>
        <h3 id="modalDetailTitle" style="margin-bottom: 6px; color: #111827; font-size: 1.3em;">Tour Details</h3>
        <p id="modalDetailDates" style="font-size: 0.9em; color: #059669; font-weight: 600; margin-bottom: 14px;"></p>
        <hr style="border: 0; border-top: 1px solid #e5e7eb; margin-bottom: 16px;" />
        <div id="modalDetailDescription" style="font-size: 0.95em; color: #374151; line-height: 1.6; white-space: pre-wrap; max-height: 300px; overflow-y: auto;"></div>
        <div style="margin-top: 20px; text-align: right;">
            <button type="button" class="btn btn-secondary" onclick="window.closeDetailsModal()" style="padding: 8px 16px; cursor: pointer;">Close</button>
        </div>
    </div>
</div>

<!-- Booking Modal Container -->
<div id="seriesBookingModal" class="series-modal" style="display: none;">
    <div class="series-modal-overlay" onclick="window.closeBookingModal()"></div>
    <div class="series-modal-content" style="max-width: 550px; max-height: 90vh; overflow-y: auto; background: #ffffff; padding: 24px; border-radius: 12px;">
        <button type="button" class="series-modal-close" onclick="window.closeBookingModal()" style="font-size: 24px; cursor: pointer; float: right; border: none; background: transparent;">&times;</button>
        <h3 style="margin-bottom: 4px; color: #111827;">Reserve Your Seat</h3>
        <p class="modal-sub" style="font-size: 0.9em; color: #6b7280; margin-bottom: 16px;">Departure Date: <strong id="modalDepDate" style="color: #059669;"></strong></p>

        <form id="seriesBookingForm" enctype="multipart/form-data">
            <input type="hidden" id="modalDepId" name="departure_id">
            <input type="hidden" id="modalPricePerSeat" name="price_per_seat">

            <div class="form-group" style="margin-bottom: 16px;">
                <label for="custSeats" style="font-weight: 600; display: block; margin-bottom: 4px;">Number of Seats</label>
                <select id="custSeats" name="cust_seats" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;"></select>
            </div>

            <div id="passengersContainer"></div>

            <div class="booking-summary" style="margin-top: 16px; padding: 12px; background: #f9fafb; border-radius: 6px; font-size: 1.1em; display: flex; justify-content: space-between; align-items: center;">
                <span>Total Amount:</span>
                <strong id="modalTotalAmount" style="color: #059669; font-size: 1.2em;">$0.00</strong>
            </div>

            <div id="bookingAlert" class="booking-alert" style="display: none; margin-top: 12px; padding: 10px; border-radius: 6px; font-size: 0.9em;"></div>

            <button type="submit" id="btnConfirmBooking" class="btn btn-primary btn-block" style="margin-top: 16px; width: 100%; padding: 12px; background: #059669; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Confirm & Book Spot</button>
        </form>
    </div>
</div>






    <!-- Chatbot Widget UI -->
    <div id="bhutan-chat-widget" class="chat-widget">
        <button id="chat-toggle-btn" class="chat-toggle-btn" aria-label="Open Chat">
            <span class="chat-icon">💬</span>
            <span class="close-icon">✕</span>
        </button>

        <div id="chat-box" class="chat-box">
            <div class="chat-header">
                <div class="chat-title">
                    <h4>Bhutan Travel Assistant</h4>
                    <span class="online-status">Online</span>
                </div>
                <button type="button" id="chat-close-btn" class="chat-panel-close-btn" aria-label="Close Chat">✕</button>
            </div>
            <div id="chat-messages" class="chat-messages">
                <div class="message bot-message">
                    Hello! Tashi Delek! 🇧🇹 How can I help you plan your journey to Bhutan today?
                </div>
            </div>
            <form id="chat-form" class="chat-form">
                <input type="text" id="chat-input" placeholder="Ask about visas, SDF, or destinations..." required autocomplete="off">
                <button type="submit" id="chat-submit-btn">Send</button>
            </form>
        </div>
    </div>



    
   <!-- Footer -->
<footer class="site-footer">
    <div class="container footer-content">
        <!-- Brand / Intro Section -->
        <div class="footer-col footer-brand">
            <h3 class="footer-logo"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="footer-tagline">Kingdom of Happiness</p>
            <p class="footer-desc">Discover majestic landscapes, timeless heritage, and transformative travel experiences.</p>
        </div>

        <!-- Navigation Links -->
        <div class="footer-col">
            <h4>Quick Links</h4>
            <ul class="footer-links-list">
                <li><a href="#home" class="nav-link">Home</a></li>
                <li><a href="#destinations" class="nav-link">Destinations</a></li>
                <li><a href="#plan" class="nav-link">Plan Your Trip</a></li>
                <li><a href="#calendar" class="nav-link">Events Calendar</a></li>
            </ul>
        </div>

        <!-- Social Channels -->
        <div class="footer-col">
            <h4>Connect With Us</h4>
            <ul class="footer-social-links">
                <li><a href="#" target="_blank" rel="noopener"><i class="fa-brands fa-instagram"></i> Instagram</a></li>
                <li><a href="#" target="_blank" rel="noopener"><i class="fa-brands fa-x-twitter"></i> Twitter / X</a></li>
                <li><a href="#" target="_blank" rel="noopener"><i class="fa-brands fa-youtube"></i> YouTube</a></li>
            </ul>
        </div>

        <!-- Direct Contact Actions (WhatsApp & Email) -->
        <div class="footer-col">
            <h4>Get In Touch</h4>
            <div class="footer-contact-pills">
                <a href="https://wa.me/97517000000" class="contact-pill whatsapp-pill" target="_blank" rel="noopener">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>Chat on WhatsApp</span>
                </a>
                <a href="mailto:info@bhutan.travel" class="contact-pill email-pill">
                    <i class="fa-regular fa-envelope"></i>
                    <span>info@bhutan.travel</span>
                </a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?= $year ?> <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>. All rights reserved.</p>
    </div>
</footer>

    <!-- Pass Backend Data Safely to JS Window Context -->
    <script>
        window.BHUTAN_RATES = <?= json_encode($planRates, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>
</body>
</html>