<?php 
// 1. Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Protect page: redirect unauthenticated users to login
if (!isset($_SESSION['user_id'])) {
    header("Location: /admin/auth/login.php");
    exit();
}

// 3. Include layout partials
include __DIR__ . '/partials/header.php'; 

// Load database connection from admin/config/db.php
require_once __DIR__ . '/../config/db.php'; 

// Default fallbacks
$destCount = 0;
$eventCount = 0;
$inquiryCount = 0;
$sdfRate = 100;
$recentInquiries = [];

if (isset($pdo) && $pdo instanceof PDO) {
    // 1. Destinations Count
    try {
        $destCountQuery = $pdo->query("SELECT COUNT(*) FROM destinations");
        $destCount = $destCountQuery ? $destCountQuery->fetchColumn() : 0;
    } catch (PDOException $e) {}

    // 2. Events Count
    try {
        $eventCountQuery = $pdo->query("SELECT COUNT(*) FROM events");
        $eventCount = $eventCountQuery ? $eventCountQuery->fetchColumn() : 0;
    } catch (PDOException $e) {}

    // 3. New Inquiries Count
    try {
        $inquiryCountQuery = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'");
        $inquiryCount = $inquiryCountQuery ? $inquiryCountQuery->fetchColumn() : 0;
    } catch (PDOException $e) {}

    // 4. Fetch SDF Rate from key-value `plan_rates` table
    try {
        $sdfStmt = $pdo->prepare("SELECT rate_value FROM plan_rates WHERE rate_key = :key LIMIT 1");
        $sdfStmt->execute([':key' => 'sdf_intl']);
        $fetchedRate = $sdfStmt->fetchColumn();
        if ($fetchedRate !== false) {
            $sdfRate = $fetchedRate;
        }
    } catch (PDOException $e) {
        $sdfRate = 100;
    }

    // 5. Recent Inquiries
    try {
        $recentInquiriesStmt = $pdo->query("SELECT id, name, interests, adults, children, infants, status, created_at FROM inquiries ORDER BY created_at DESC LIMIT 5");
        $recentInquiries = $recentInquiriesStmt ? $recentInquiriesStmt->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (PDOException $e) {}
}
?>

<!-- Page Welcome Banner -->
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.875rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Dashboard Overview</h1>
    <p style="color: #64748b; font-size: 0.95rem;">Monitor key metrics, update content cards, and handle new traveler inquiries.</p>
</div>

<!-- Stat Cards Row -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    
    <div style="background: #ffffff; padding: 1.25rem; border-radius: 10px; border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; justify-content: space-between;">
        <div>
            <span style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Destinations</span>
            <div style="font-size: 1.75rem; font-weight: 700; color: #6b1d2f; margin-top: 0.2rem;"><?= htmlspecialchars((string)$destCount) ?></div>
        </div>
        <div style="width: 44px; height: 44px; background: #fee2e2; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">🏔️</div>
    </div>

    <div style="background: #ffffff; padding: 1.25rem; border-radius: 10px; border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; justify-content: space-between;">
        <div>
            <span style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Events</span>
            <div style="font-size: 1.75rem; font-weight: 700; color: #1e293b; margin-top: 0.2rem;"><?= htmlspecialchars((string)$eventCount) ?></div>
        </div>
        <div style="width: 44px; height: 44px; background: #eff6ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">📅</div>
    </div>

    <div style="background: #ffffff; padding: 1.25rem; border-radius: 10px; border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; justify-content: space-between;">
        <div>
            <span style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase;">New Inquiries</span>
            <div style="font-size: 1.75rem; font-weight: 700; color: #16a34a; margin-top: 0.2rem;"><?= htmlspecialchars((string)$inquiryCount) ?></div>
        </div>
        <div style="width: 44px; height: 44px; background: #dcfce7; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">📩</div>
    </div>

    <div style="background: #ffffff; padding: 1.25rem; border-radius: 10px; border: 1px solid var(--border-color, #e2e8f0); display: flex; align-items: center; justify-content: space-between;">
        <div>
            <span style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase;">SDF Daily Fee</span>
            <div style="font-size: 1.75rem; font-weight: 700; color: #b45309; margin-top: 0.2rem;">$<?= htmlspecialchars((string)$sdfRate) ?></div>
        </div>
        <div style="width: 44px; height: 44px; background: #fef3c7; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">🏷️</div>
    </div>

</div>

<!-- Full Width Recent Inquiries Panel -->
<div style="background: #ffffff; border-radius: 10px; border: 1px solid var(--border-color, #e2e8f0); padding: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 style="font-size: 1.1rem; color: #0f172a; font-weight: 600;">Recent Inquiries</h3>
        <a href="/admin/views/inquiries.php" style="color: #6b1d2f; font-size: 0.85rem; text-decoration: none; font-weight: 600;">View All →</a>
    </div>
    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
        <?php if (!empty($recentInquiries)): ?>
            <?php foreach ($recentInquiries as $inquiry): ?>
                <?php 
                    $totalTravelers = (int)($inquiry['adults'] ?? 1) + (int)($inquiry['children'] ?? 0) + (int)($inquiry['infants'] ?? 0);
                    $interests = !empty($inquiry['interests']) ? implode(', ', json_decode($inquiry['interests'], true) ?? []) : 'General Inquiry';
                    $isNew = strtolower($inquiry['status'] ?? '') === 'new';
                ?>
                <div style="padding: 0.75rem 1rem; background: #f8fafc; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="font-size: 0.9rem; color: #1e293b;"><?= htmlspecialchars($inquiry['name']) ?></strong>
                        <div style="font-size: 0.8rem; color: #64748b;"><?= htmlspecialchars($interests) ?> • <?= $totalTravelers ?> Traveler<?= $totalTravelers > 1 ? 's' : '' ?></div>
                    </div>
                    <span style="background: <?= $isNew ? '#dcfce7' : '#f1f5f9' ?>; color: <?= $isNew ? '#166534' : '#475569' ?>; font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600;">
                        <?= htmlspecialchars(ucfirst($inquiry['status'] ?? 'Pending')) ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="padding: 1rem; color: #64748b; font-size: 0.9rem; text-align: center;">No inquiries submitted yet.</div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>