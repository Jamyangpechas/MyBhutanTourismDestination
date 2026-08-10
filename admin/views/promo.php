<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enforce login requirement
if (!isset($_SESSION['user_id'])) {
    header("Location: /admin/auth/login.php");
    exit();
}

$adminDir = dirname(__DIR__);

include __DIR__ . '/partials/header.php'; 
require_once $adminDir . '/config/db.php';
require_once $adminDir . '/models/PromoModel.php';

$model = new PromoModel($pdo);
$promoData = $model->getSettings();
?>

<!-- Header & Description -->
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.875rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Promotional Banner Settings</h1>
    <p style="color: #64748b; font-size: 0.95rem;">Manage the headline, descriptive paragraph, and action button link for the promotional banner.</p>
</div>

<!-- Alert Box -->
<div id="promo-alert-box" style="display: none; padding: 0.85rem 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: 0.9rem;"></div>

<!-- Form Container -->
<div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 10px; padding: 1.75rem;">
    <h3 style="font-size: 1.1rem; color: #0f172a; font-weight: 600; margin-bottom: 1.25rem;">Banner Content</h3>

    <form id="promo-banner-form" action="/admin/controllers/promo_controller.php" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
        
        <!-- Main Headline -->
        <div>
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem; color: #334155;">Banner Title (H2)</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($promoData['title']); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem;">
        </div>

        <!-- Description Paragraph -->
        <div>
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem; color: #334155;">Banner Description</label>
            <textarea name="description" rows="3" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; resize: vertical;"><?php echo htmlspecialchars($promoData['description']); ?></textarea>
        </div>

        <!-- Button Label & Link Fields -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem; color: #334155;">Button Text</label>
                <input type="text" name="btn_text" value="<?php echo htmlspecialchars($promoData['btn_text']); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem;">
            </div>

            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem; color: #334155;">Button URL Link</label>
                <input type="text" name="btn_url" value="<?php echo htmlspecialchars($promoData['btn_url']); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem;">
            </div>
        </div>

        <div style="padding-top: 0.5rem; text-align: right;">
            <button type="submit" style="background: var(--primary); color: #ffffff; border: none; padding: 0.85rem 2rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.9rem;">
                Save Banner Settings
            </button>
        </div>

    </form>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>