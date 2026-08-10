<?php
$current_uri = $_SERVER['REQUEST_URI'] ?? '';
?>
<aside class="admin-sidebar">
    <!-- Brand Title (Clickable Link to Index) -->
    <a href="/admin/index.php" class="sidebar-brand-link">
        <div class="sidebar-brand">
            <h2>Bhutan Believe</h2>
            <span class="sub-brand">Destination Admin</span>
        </div>
    </a>

    <!-- Navigation Links -->
    <nav class="sidebar-nav">
        <ul>
            <li class="<?= strpos($current_uri, 'dashboard.php') !== false ? 'active' : '' ?>">
             <a href="/admin/views/dashboard.php">    
                    <span class="icon">📊</span> Dashboard
                </a>
            </li>
            <li class="<?= strpos($current_uri, 'DepartureController.php') !== false ? 'active' : '' ?>">
                <a href="/admin/controllers/DepartureController.php">
                    <span class="icon">🚌</span> Series Departures
                </a>
            </li>
            <li class="<?= strpos($current_uri, 'hero.php') !== false ? 'active' : '' ?>">
                <a href="/admin/views/hero.php">
                    <span class="icon">🖼️</span> Hero Section
                </a>
            </li>
            <li class="<?= strpos($current_uri, 'sdf.php') !== false ? 'active' : '' ?>">
                <a href="/admin/views/sdf.php">
                    <span class="icon">🌱</span> SDF Impact
                </a>
            </li>
            <li class="<?= strpos($current_uri, 'luxury.php') !== false ? 'active' : '' ?>">
                <a href="/admin/views/luxury.php">
                    <span class="icon">✨</span> Luxury Definition
                </a>
            </li>
            <li class="<?= strpos($current_uri, 'promo.php') !== false ? 'active' : '' ?>">
                <a href="/admin/views/promo.php">
                    <span class="icon">📢</span> Promo Banner
                </a>
            </li>
            <li class="<?= strpos($current_uri, 'brand.php') !== false ? 'active' : '' ?>">
                <a href="/admin/views/brand.php">
                    <span class="icon">🏷️</span> Brand Showcase
                </a>
            </li>
            <li class="<?= strpos($current_uri, 'destinations.php') !== false ? 'active' : '' ?>">
                <a href="/admin/views/destinations.php">
                    <span class="icon">🏔️</span> Destinations
                </a>
            </li>
            <li class="<?= strpos($current_uri, 'events.php') !== false ? 'active' : '' ?>">
                <a href="/admin/views/events.php">
                    <span class="icon">📅</span> Calendar Events
                </a>
            </li>
            <li class="<?= strpos($current_uri, 'plan.php') !== false ? 'active' : '' ?>">
                <a href="/admin/views/plan.php">
                    <span class="icon">✈️</span> Trip Planner
                </a>
            </li>
            <li class="<?= strpos($current_uri, 'inquiries.php') !== false ? 'active' : '' ?>">
                <a href="/admin/views/inquiries.php">
                    <span class="icon">📩</span> Inquiries & Leads
                </a>
            </li>
        </ul>
    </nav>

    <!-- Footer Action -->
    <div class="sidebar-footer">
        <a href="/" target="_blank" class="live-site-btn">View Live Site ↗</a>
    </div>
</aside>