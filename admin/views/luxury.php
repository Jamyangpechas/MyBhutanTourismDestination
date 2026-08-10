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
require_once $adminDir . '/models/LuxuryModel.php';

$model = new LuxuryModel($pdo);
$luxuryData = $model->getSettings();
?>

<!-- Header & Page Description -->
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.875rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Luxury Section Settings</h1>
    <p style="color: #64748b; font-size: 0.95rem;">Update the copy, labels, and imagery for the "A Different Definition of Luxury" layout.</p>
</div>

<!-- Dynamic JavaScript Alert Box Container -->
<div id="alert-box" style="display: none; padding: 0.85rem 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: 0.9rem;"></div>

<div style="background: #ffffff; border: 1px solid var(--border-color, #e2e8f0); border-radius: 10px; padding: 1.75rem;">
    
    <form id="luxury-form" action="/admin/controllers/luxury_controller.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.5rem;">

        <!-- Section 1: Main Copy Fields -->
        <div>
            <h3 style="font-size: 1.05rem; color: #0f172a; font-weight: 600; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color, #e2e8f0); padding-bottom: 0.5rem;">
                Center Content Copy
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.4rem; font-size: 0.875rem; color: #334155;">Eyebrow Tagline</label>
                    <input type="text" name="eyebrow" value="<?php echo htmlspecialchars($luxuryData['eyebrow'] ?? 'THE BHUTANESE WAY'); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 6px; font-size: 0.9rem;">
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.4rem; font-size: 0.875rem; color: #334155;">Main Title (H2)</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($luxuryData['title'] ?? 'A Different Definition of Luxury'); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 6px; font-size: 0.9rem;">
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.4rem; font-size: 0.875rem; color: #334155;">First Paragraph</label>
                    <textarea name="paragraph_1" rows="3" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 6px; font-size: 0.9rem; resize: vertical;"><?php echo htmlspecialchars($luxuryData['paragraph_1'] ?? 'In Bhutan, luxury is not measured by excess. It is found in silence, untouched landscapes, meaningful encounters, ancient wisdom, and the rare privilege of experiencing a kingdom that has chosen balance over haste.'); ?></textarea>
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.4rem; font-size: 0.875rem; color: #334155;">Second Paragraph</label>
                    <textarea name="paragraph_2" rows="3" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 6px; font-size: 0.9rem; resize: vertical;"><?php echo htmlspecialchars($luxuryData['paragraph_2'] ?? 'Here, your journey becomes part of something greater—helping preserve a sanctuary where nature thrives, traditions endure, and happiness remains at the heart of development.'); ?></textarea>
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.4rem; font-size: 0.875rem; color: #334155;">Divider Quote (Bottom Paragraph)</label>
                    <input type="text" name="divider_quote" value="<?php echo htmlspecialchars($luxuryData['divider_quote'] ?? 'Your journey becomes part of something greater.'); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 6px; font-size: 0.9rem;">
                </div>
            </div>
        </div>

        <!-- Section 2: Left & Right Cards Media -->
        <div>
            <h3 style="font-size: 1.05rem; color: #0f172a; font-weight: 600; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color, #e2e8f0); padding-bottom: 0.5rem;">
                Side Image Cards
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                
                <!-- Left Card -->
                <div style="background: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px solid var(--border-color, #e2e8f0);">
                    <h4 style="font-size: 0.95rem; font-weight: 600; color: #1e293b; margin-bottom: 0.75rem;">Left Image Card</h4>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.3rem; font-size: 0.8rem; color: #334155;">Image Overlay Label</label>
                        <input type="text" name="card_1_label" value="<?php echo htmlspecialchars($luxuryData['card_1_label'] ?? 'BHUTAN, UNRUSHED'); ?>" required style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.85rem;">
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.3rem; font-size: 0.8rem; color: #334155;">Upload Photo or File URL</label>
                        <input type="file" name="card_1_image" accept="image/*" style="width: 100%; padding: 0.4rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.8rem; background: #fff; margin-bottom: 0.5rem;">
                        <input type="text" name="card_1_image_url" value="<?php echo htmlspecialchars($luxuryData['card_1_image'] ?? 'https://images.unsplash.com/photo-1578637387939-43c525550085'); ?>" placeholder="Or enter image URL" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.85rem;">
                    </div>
                </div>

                <!-- Right Card -->
                <div style="background: #f8fafc; padding: 1.25rem; border-radius: 8px; border: 1px solid var(--border-color, #e2e8f0);">
                    <h4 style="font-size: 0.95rem; font-weight: 600; color: #1e293b; margin-bottom: 0.75rem;">Right Image Card</h4>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: 600; margin-bottom: 0.3rem; font-size: 0.8rem; color: #334155;">Image Overlay Label</label>
                        <input type="text" name="card_2_label" value="<?php echo htmlspecialchars($luxuryData['card_2_label'] ?? 'AUTHENTIC CUISINE'); ?>" required style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.85rem;">
                    </div>

                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.3rem; font-size: 0.8rem; color: #334155;">Upload Photo or File URL</label>
                        <input type="file" name="card_2_image" accept="image/*" style="width: 100%; padding: 0.4rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.8rem; background: #fff; margin-bottom: 0.5rem;">
                        <input type="text" name="card_2_image_url" value="<?php echo htmlspecialchars($luxuryData['card_2_image'] ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c'); ?>" placeholder="Or enter image URL" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.85rem;">
                    </div>
                </div>

            </div>
        </div>

        <!-- Submit Controls -->
        <div style="padding-top: 0.5rem; text-align: right;">
            <button type="submit" style="background: var(--primary, #0284c7); color: #ffffff; border: none; padding: 0.85rem 2rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.95rem;">
                Save Luxury Section
            </button>
        </div>

    </form>

</div>

<?php include __DIR__ . '/partials/footer.php'; ?>