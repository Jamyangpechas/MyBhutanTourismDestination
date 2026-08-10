<?php 
// 1. Enforce authentication first (MUST be before any HTML/headers are sent)
require_once __DIR__ . '/auth/auth_check.php';

// 2. Load the database connection
require_once __DIR__ . '/config/db.php'; 

// 3. Load UI layout header
include __DIR__ . '/views/partials/header.php'; 

// Query stats counts safely with default fallbacks
try {
    $destCount = $pdo->query("SELECT COUNT(*) FROM destinations")->fetchColumn() ?? 0;
    $eventCount = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn() ?? 0;
    $inquiryCount = $pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn() ?? 0;
    $departureCount = $pdo->query("SELECT COUNT(*) FROM departures WHERE start_date >= CURDATE()")->fetchColumn() ?? 0;
} catch (Exception $e) {
    // Fallback if tables are not yet created
    $destCount = $eventCount = $inquiryCount = $departureCount = 0;
}
?>

<!-- Admin Landing Header -->
<div style="background: linear-gradient(135deg, #6b1d2f 0%, #3b0f19 100%); color: white; padding: 2.5rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 4px 15px rgba(107, 29, 47, 0.15);">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">Welcome to Bhutan Destination Admin</h1>
    <p style="color: #f1f5f9; font-size: 1.05rem; max-width: 650px;">Manage travel itineraries, group series departures, regional destinations, cultural events, homepage hero content, and prospective traveler requests from a single portal.</p>
</div>

<!-- Core Sections Overview Grid -->
<h3 style="margin-bottom: 1.25rem; font-size: 1.2rem; color: #1e293b;">Management Sections</h3>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">

    <!-- Dashboard Quick Card -->
    <a href="/admin/views/dashboard.php" style="text-decoration: none; color: inherit;">
        <div style="background: #fff; border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; padding: 1.5rem; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; height: 100%;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.06)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
            <div style="width: 42px; height: 42px; background: #fee2e2; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; color: #b91c1c; font-weight: bold;">📊</div>
            <h4 style="font-size: 1.1rem; margin-bottom: 0.4rem; color: var(--text-dark, #0f172a);">Analytics & Metrics</h4>
            <p style="color: #64748b; font-size: 0.875rem; line-height: 1.4;">View platform usage, system stats, and high-level site metrics.</p>
        </div>
    </a>

    <!-- Series Departures Card -->
    <a href="/admin/controllers/DepartureController.php" style="text-decoration: none; color: inherit;">
        <div style="background: #fff; border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; padding: 1.5rem; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; height: 100%; position: relative;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.06)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div style="width: 42px; height: 42px; background: #e0f2fe; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #0284c7; font-weight: bold;">🚌</div>
                <span style="background: #e0f2fe; color: #0369a1; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 12px;"><?= $departureCount ?> Active</span>
            </div>
            <h4 style="font-size: 1.1rem; margin-bottom: 0.4rem; color: var(--text-dark, #0f172a);">Series Departures</h4>
            <p style="color: #64748b; font-size: 0.875rem; line-height: 1.4;">Schedule group departure dates, manage pricing, and track booking capacities.</p>
        </div>
    </a>

    <!-- Hero Banner Section Card -->
    <a href="/admin/views/hero.php" style="text-decoration: none; color: inherit;">
        <div style="background: #fff; border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; padding: 1.5rem; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; height: 100%;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.06)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
            <div style="width: 42px; height: 42px; background: #fae8ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; color: #a21caf; font-weight: bold;">🖼️</div>
            <h4 style="font-size: 1.1rem; margin-bottom: 0.4rem; color: var(--text-dark, #0f172a);">Hero Section</h4>
            <p style="color: #64748b; font-size: 0.875rem; line-height: 1.4;">Update the main headline slogan and eyebrow text on the homepage hero banner.</p>
        </div>
    </a>

    <!-- Destinations Card -->
    <a href="/admin/views/destinations.php" style="text-decoration: none; color: inherit;">
        <div style="background: #fff; border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; padding: 1.5rem; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; height: 100%; position: relative;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.06)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div style="width: 42px; height: 42px; background: #fef3c7; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #b45309; font-weight: bold;">🏔️</div>
                <span style="background: #fef3c7; color: #92400e; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 12px;"><?= $destCount ?> Added</span>
            </div>
            <h4 style="font-size: 1.1rem; margin-bottom: 0.4rem; color: var(--text-dark, #0f172a);">Destinations</h4>
            <p style="color: #64748b; font-size: 0.875rem; line-height: 1.4;">Add new locations, upload destination photos, and edit region details.</p>
        </div>
    </a>

    <!-- Events Card -->
    <a href="/admin/views/events.php" style="text-decoration: none; color: inherit;">
        <div style="background: #fff; border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; padding: 1.5rem; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; height: 100%; position: relative;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.06)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div style="width: 42px; height: 42px; background: #e0f2fe; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #0284c7; font-weight: bold;">📅</div>
                <span style="background: #e0f2fe; color: #0369a1; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 12px;"><?= $eventCount ?> Events</span>
            </div>
            <h4 style="font-size: 1.1rem; margin-bottom: 0.4rem; color: var(--text-dark, #0f172a);">Festival Calendar</h4>
            <p style="color: #64748b; font-size: 0.875rem; line-height: 1.4;">Schedule upcoming Tshechus, cultural celebrations, and seasonal activities.</p>
        </div>
    </a>

    <!-- Inquiries Card -->
    <a href="/admin/views/inquiries.php" style="text-decoration: none; color: inherit;">
        <div style="background: #fff; border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; padding: 1.5rem; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; height: 100%; position: relative;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.06)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div style="width: 42px; height: 42px; background: #dcfce7; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #166534; font-weight: bold;">📩</div>
                <span style="background: #dcfce7; color: #15803d; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 12px;"><?= $inquiryCount ?> Requests</span>
            </div>
            <h4 style="font-size: 1.1rem; margin-bottom: 0.4rem; color: var(--text-dark, #0f172a);">Traveler Requests</h4>
            <p style="color: #64748b; font-size: 0.875rem; line-height: 1.4;">Review trip planning inquiries and booking messages sent from visitors.</p>
        </div>
    </a>

</div>

<?php include __DIR__ . '/views/partials/footer.php'; ?>