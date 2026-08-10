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

// Optional: If auth_check.php contains additional logic, import it using the corrected path:
// require_once $adminDir . '/auth/auth_check.php';

include __DIR__ . '/partials/header.php'; 
require_once $adminDir . '/config/db.php';
require_once $adminDir . '/models/SdfModel.php';

$model = new SdfModel($pdo);
$sdfHeader = $model->getHeadings();
$sdfFeatures = $model->getFeatures();
?>

<!-- Header & Description -->
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.875rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">SDF Impact Section Settings</h1>
    <p style="color: #64748b; font-size: 0.95rem;">Manage section headers, intro copy, feature cards, and closing messages for the Sustainable Development Fee area.</p>
</div>

<!-- Dynamic Alert Box -->
<div id="sdf-alert-box" style="display: none; padding: 0.85rem 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: 0.9rem;"></div>

<div style="display: flex; flex-direction: column; gap: 2rem;">

    <!-- Section Intro & Closing Settings Form -->
    <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 10px; padding: 1.75rem;">
        <h3 style="font-size: 1.1rem; color: #0f172a; font-weight: 600; margin-bottom: 1.25rem;">1. Section Text & Headings</h3>

        <form id="sdf-headings-form" action="/admin/controllers/sdf_controller.php?action=update_headings" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
            
            <!-- Eyebrow Text -->
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem; color: #334155;">Section Eyebrow</label>
                <input type="text" name="sdf_eyebrow" value="<?php echo htmlspecialchars($sdfHeader['eyebrow']); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem;">
            </div>

            <!-- Intro Paragraph -->
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem; color: #334155;">Intro Description</label>
                <input type="text" name="sdf_intro" value="<?php echo htmlspecialchars($sdfHeader['intro']); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem;">
            </div>

            <!-- Closing Header -->
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem; color: #334155;">Closing Title (H4)</label>
                <input type="text" name="sdf_closing_title" value="<?php echo htmlspecialchars($sdfHeader['closing_title']); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem;">
            </div>

            <!-- Closing Subtitle -->
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem; color: #334155;">Closing Subtitle Paragraph</label>
                <input type="text" name="sdf_closing_desc" value="<?php echo htmlspecialchars($sdfHeader['closing_desc']); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem;">
            </div>

            <div style="grid-column: 1 / -1; text-align: right;">
                <button type="submit" style="background: var(--primary); color: #ffffff; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.9rem;">
                    Update Section Headings
                </button>
            </div>

        </form>
    </div>

    <!-- Feature Cards Manager Layout -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">

        <!-- Card Creation/Editing Form -->
        <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 10px; padding: 1.75rem;">
            <h3 id="card-form-title" style="font-size: 1.1rem; color: #0f172a; font-weight: 600; margin-bottom: 1.25rem;">2. Add Feature Card</h3>

            <form id="sdf-card-form" action="/admin/controllers/sdf_controller.php?action=save_card" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.25rem;">
                
                <input type="hidden" name="card_id" id="card_id" value="">

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem; color: #334155;">Card Title (H4)</label>
                    <input type="text" name="title" id="card_title" placeholder="e.g., Free Healthcare & Education" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem;">
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem; color: #334155;">Card Image</label>
                    <input type="file" name="image" id="card_image" accept="image/png, image/jpeg, image/webp" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.85rem; background: #f8fafc;">
                    <span id="card-image-note" style="display: block; font-size: 0.75rem; color: #94a3b8; margin-top: 0.3rem;">Accepted formats: JPG, PNG, WEBP</span>
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem; color: #334155;">Card Body Text</label>
                    <textarea name="desc" id="card_desc" rows="3" required placeholder="Card description text..." style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; resize: vertical;"></textarea>
                </div>

                <div style="display: flex; gap: 0.5rem; padding-top: 0.5rem;">
                    <button type="submit" id="card-submit-btn" style="flex: 1; background: var(--primary); color: #ffffff; border: none; padding: 0.85rem 1.75rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.9rem;">
                        Add Card to Grid
                    </button>
                    <button type="button" id="card-cancel-btn" style="display: none; background: #e2e8f0; color: #334155; border: none; padding: 0.85rem 1rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.9rem;">
                        Cancel
                    </button>
                </div>

            </form>
        </div>

        <!-- Active Cards Grid Preview -->
        <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 10px; padding: 1.75rem;">
            <h3 style="font-size: 1.1rem; color: #0f172a; font-weight: 600; margin-bottom: 1.25rem;">Active Grid Cards</h3>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php if (!empty($sdfFeatures)): ?>
                    <?php foreach ($sdfFeatures as $item): ?>
                        <div class="feature-card-item" style="display: flex; gap: 1rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; background: #f8fafc; align-items: center;">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;">
                            <div style="flex: 1;">
                                <h4 style="font-size: 0.95rem; font-weight: 600; color: #0f172a; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($item['title']); ?></h4>
                                <p style="font-size: 0.8rem; color: #64748b; margin: 0; line-height: 1.3;"><?php echo htmlspecialchars($item['desc']); ?></p>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <button type="button" class="btn-edit-card" 
                                        data-id="<?php echo $item['id']; ?>" 
                                        data-title="<?php echo htmlspecialchars($item['title']); ?>" 
                                        data-desc="<?php echo htmlspecialchars($item['desc']); ?>" 
                                        style="background: #e2e8f0; border: none; padding: 0.4rem 0.6rem; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">
                                    ✏️ Edit
                                </button>
                                <button type="button" class="btn-delete-card" 
                                        data-id="<?php echo $item['id']; ?>" 
                                        style="background: #fee2e2; color: #dc2626; border: none; padding: 0.4rem 0.6rem; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">
                                    🗑️
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #94a3b8; font-size: 0.9rem; text-align: center; padding: 2rem 0;">No feature cards added yet.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<?php include __DIR__ . '/partials/footer.php'; ?>