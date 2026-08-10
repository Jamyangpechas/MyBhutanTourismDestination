<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enforce login requirement
if (!isset($_SESSION['user_id'])) {
    header("Location: /admin/auth/login.php");
    exit();
}

include __DIR__ . '/partials/header.php'; 

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/HeroModel.php';

global $pdo; 

$heroModel = new HeroModel($pdo);
$hero = $heroModel->getHeroSettings();

// Keep raw for input values, escape for HTML rendering
$rawEyebrow = $hero['eyebrow'] ?? 'BHUTAN Believe';
$rawTitle   = $hero['title'] ?? 'Experience Stillness. Make an Impact.';

$eyebrow   = htmlspecialchars($rawEyebrow, ENT_QUOTES, 'UTF-8');
$title     = htmlspecialchars($rawTitle, ENT_QUOTES, 'UTF-8');
$mediaType = $hero['media_type'] ?? 'none';
$mediaPath = !empty($hero['media_path']) ? htmlspecialchars($hero['media_path'], ENT_QUOTES, 'UTF-8') : '';
?>

<!-- Header & Page Description -->
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.875rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Hero Section Settings</h1>
    <p style="color: #64748b; font-size: 0.95rem;">Manage hero banner text and optionally upload either a background video or a picture.</p>
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div style="margin-top: 1rem; padding: 0.75rem 1rem; background: #dcfce7; color: #15803d; border-radius: 6px; font-weight: 600;">
            <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div style="margin-top: 1rem; padding: 0.75rem 1rem; background: #fee2e2; color: #b91c1c; border-radius: 6px; font-weight: 600;">
            <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
</div>

<!-- Main Layout Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">

    <!-- Form Section -->
    <div style="background: #ffffff; border: 1px solid var(--border-color, #e2e8f0); border-radius: 10px; padding: 1.75rem;">
        <h3 style="font-size: 1.1rem; color: #0f172a; font-weight: 600; margin-bottom: 1.25rem;">Hero Content & Optional Media</h3>

        <form action="/admin/controllers/hero_controller.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.25rem;">
            
            <!-- Eyebrow Text -->
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem; color: #334155;">Hero Eyebrow</label>
                <input type="text" id="inputEyebrow" name="eyebrow" value="<?= $eyebrow ?>" placeholder="e.g., BHUTAN Believe" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 6px; font-size: 0.9rem;">
            </div>

            <!-- Title Text -->
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem; color: #334155;">Hero Main Title</label>
                <textarea id="inputTitle" name="title" rows="2" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 6px; font-size: 0.9rem; resize: vertical;"><?= $title ?></textarea>
            </div>

            <hr style="border: 0; border-top: 1px dashed var(--border-color, #cbd5e1); margin: 0.5rem 0;">

            <!-- Optional Picture Upload -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <label style="font-weight: 600; font-size: 0.875rem; color: #334155;">Option A: Background Photo</label>
                    <span style="font-size: 0.75rem; color: #64748b; background: #f1f5f9; padding: 0.1rem 0.4rem; border-radius: 4px;">Optional</span>
                </div>
                <input type="file" id="imageInput" name="hero_image" accept="image/png, image/jpeg, image/webp" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 6px; font-size: 0.85rem; background: #f8fafc;">
            </div>

            <!-- Optional Video Upload -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <label style="font-weight: 600; font-size: 0.875rem; color: #334155;">Option B: Background Video</label>
                    <span style="font-size: 0.75rem; color: #64748b; background: #f1f5f9; padding: 0.1rem 0.4rem; border-radius: 4px;">Optional</span>
                </div>
                <input type="file" id="videoInput" name="hero_video" accept="video/mp4, video/webm" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 6px; font-size: 0.85rem; background: #f8fafc;">
            </div>

            <div style="padding-top: 0.5rem;">
                <button type="submit" style="background: var(--primary, #2563eb); color: #ffffff; border: none; padding: 0.85rem 1.75rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.9rem; width: 100%;">
                    Save Hero Settings
                </button>
            </div>

        </form>
    </div>

    <!-- Dynamic Live Banner Preview -->
    <div style="background: #ffffff; border: 1px solid var(--border-color, #e2e8f0); border-radius: 10px; padding: 1.75rem;">
        <h3 style="font-size: 1.1rem; color: #0f172a; font-weight: 600; margin-bottom: 1.25rem;">Live Banner Preview</h3>

        <div id="previewContainer" style="position: relative; border-radius: 8px; overflow: hidden; min-height: 280px; display: flex; flex-direction: column; justify-content: center; align-items: center; border: 1px solid #334155; background: #0f172a; text-align: center; padding: 2rem 1.5rem;">
            
            <img id="imgPreview" src="<?= ($mediaType === 'image') ? $mediaPath : '' ?>" alt="Hero Image Preview" style="display: <?= ($mediaType === 'image') ? 'block' : 'none' ?>; position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;">
            <video id="vidPreview" src="<?= ($mediaType === 'video') ? $mediaPath : '' ?>" autoplay loop muted playsinline style="display: <?= ($mediaType === 'video') ? 'block' : 'none' ?>; position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;"></video>

            <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(15,23,42,0.4) 0%, rgba(15,23,42,0.85) 100%); z-index: 1;"></div>
            
            <div style="position: relative; z-index: 2; color: #ffffff;">
                <span id="previewEyebrow" style="letter-spacing: 3px; text-transform: uppercase; font-size: 0.75rem; color: var(--accent, #f59e0b); font-weight: 700; margin-bottom: 0.75rem; display: block;"><?= $eyebrow ?></span>
                <h2 id="previewTitle" style="font-size: 1.5rem; font-weight: 700; line-height: 1.3;"><?= $title ?></h2>
            </div>

            <div style="position: absolute; bottom: 12px; left: 12px; z-index: 3; display: flex; gap: 0.5rem;">
                <span id="badgeImage" style="display: <?= ($mediaType === 'image') ? 'inline-block' : 'none' ?>; background: rgba(0,0,0,0.7); color: #22c55e; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 4px;">🖼️ Photo Active</span>
                <span id="badgeVideo" style="display: <?= ($mediaType === 'video') ? 'inline-block' : 'none' ?>; background: rgba(0,0,0,0.7); color: #38bdf8; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 4px;">📹 Video Active</span>
                <span id="badgeDefault" style="display: <?= ($mediaType === 'none') ? 'inline-block' : 'none' ?>; background: rgba(0,0,0,0.7); color: #94a3b8; font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 4px;">🎨 Solid Theme</span>
            </div>
        </div>
    </div>

</div>

<?php include __DIR__ . '/partials/footer.php'; ?>