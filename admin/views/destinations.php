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
require_once __DIR__ . '/../models/DestinationModel.php';

$destModel    = new DestinationModel($pdo);
$destinations = $destModel->getAllDestinations();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.875rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Destinations Showcase</h1>
        <p style="color: #64748b; font-size: 0.95rem;">Add, edit, or remove featured destinations in the kingdom.</p>
    </div>
    <button type="button" id="btn-toggle-add-dest" style="background: var(--primary); color: #fff; border: none; padding: 0.75rem 1.25rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.9rem;">
        + Add New Destination
    </button>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="session-flash alert" style="padding: 0.85rem 1rem; margin-bottom: 1.5rem; border-radius: 6px; background: <?= ($_SESSION['flash_type'] ?? '') === 'error' ? '#fef2f2' : '#f0fdf4' ?>; color: <?= ($_SESSION['flash_type'] ?? '') === 'error' ? '#991b1b' : '#166534' ?>; border: 1px solid <?= ($_SESSION['flash_type'] ?? '') === 'error' ? '#fecaca' : '#bbf7d0' ?>; font-size: 0.9rem;">
        <?= htmlspecialchars($_SESSION['flash_message']) ?>
    </div>
    <?php 
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
    ?>
<?php endif; ?>

<!-- Inline Form: Add New Destination -->
<div id="add-destination-card" style="display: none; background: #ffffff; border: 2px dashed var(--primary); border-radius: 10px; padding: 1.5rem; margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--primary);">➕ Add New Destination</h2>
        <button type="button" class="btn-cancel-add-dest" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b;">&times;</button>
    </div>
    <form action="/admin/controllers/destinations_controller.php?action=add" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 0.85rem;">
        <div>
            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Title</label>
            <input type="text" name="title" required placeholder="e.g. Punakha Valley" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Badge Text</label>
                <input type="text" name="badge" required placeholder="e.g. Central Region" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
            </div>
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Region Data</label>
                <select name="region" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                    <option value="region-western">Western Region</option>
                    <option value="region-central">Central Region</option>
                    <option value="region-eastern">Eastern Region</option>
                </select>
            </div>
        </div>

        <div>
            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Activity Filter Keys (space separated)</label>
            <input type="text" name="activity" required placeholder="e.g. act-culture act-nature" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
        </div>

        <div>
            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Upload Picture or Video</label>
            <input type="file" name="media_file" accept="image/*,video/*" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem; background: #f8fafc;">
            <span style="font-size: 0.75rem; color: #64748b;">Supported formats: JPG, PNG, WEBP, MP4, WEBM</span>
        </div>

        <div>
            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Short Description</label>
            <textarea name="desc" rows="2" required placeholder="Card description..." style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"></textarea>
        </div>

        <div>
            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Modal Highlights (One per line)</label>
            <textarea name="highlights" rows="3" required placeholder="Highlight 1&#10;Highlight 2" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"></textarea>
        </div>

        <div>
            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Display Tags (comma separated)</label>
            <input type="text" name="tags" required placeholder="#Nature, #Heritage" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.5rem;">
            <button type="button" class="btn-cancel-add-dest" style="background: #cbd5e1; color: #334155; border: none; padding: 0.5rem 1rem; border-radius: 4px; font-size: 0.85rem; cursor: pointer;">Cancel</button>
            <button type="submit" style="background: var(--primary); color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px; font-size: 0.85rem; cursor: pointer; font-weight: 600;">Save Destination</button>
        </div>
    </form>
</div>

<!-- Grid of Editable Destination Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.5rem;">
    <?php foreach ($destinations as $item): ?>
        <?php 
            $id        = $item['id'];
            $mediaSrc  = $item['media_path'];
            $mediaType = $item['media_type'];
        ?>
        
        <!-- Display Mode -->
        <div id="dest-card-<?php echo $id; ?>" style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; display: flex; flex-direction: column;">
            <div style="position: relative; height: 180px; background: #000;">
                <?php if ($mediaType === 'video'): ?>
                    <video src="<?php echo htmlspecialchars($mediaSrc); ?>" style="width: 100%; height: 100%; object-fit: cover;" autoplay muted loop playsinline></video>
                <?php else: ?>
                    <img src="<?php echo htmlspecialchars($mediaSrc); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <?php endif; ?>
                <span style="position: absolute; top: 10px; left: 10px; background: rgba(15, 23, 42, 0.85); color: #fff; padding: 0.25rem 0.6rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                    <?php echo htmlspecialchars($item['badge']); ?>
                </span>
            </div>
            
            <div style="padding: 1.25rem; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <h3 style="font-size: 1.1rem; color: #0f172a; margin-bottom: 0.5rem; font-weight: 700;"><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p style="font-size: 0.875rem; color: #64748b; margin-bottom: 1rem; line-height: 1.4;"><?php echo htmlspecialchars($item['description']); ?></p>
                </div>

                <div style="display: flex; gap: 0.5rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                    <button type="button" class="btn-toggle-edit-dest" data-id="<?php echo $id; ?>" style="flex: 1; background: #e2e8f0; color: #334155; border: none; padding: 0.5rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.85rem;">
                        ✏️ Edit
                    </button>
                    <form action="/admin/controllers/destinations_controller.php?action=delete" method="POST" class="delete-dest-form">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <button type="submit" style="background: #fee2e2; color: #dc2626; border: none; padding: 0.5rem 0.75rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.85rem;">
                            🗑️
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Inline Edit Mode -->
        <div id="dest-edit-<?php echo $id; ?>" style="display: none; background: #ffffff; border: 2px solid var(--primary); border-radius: 10px; padding: 1.5rem; grid-column: span 1;">
            <form action="/admin/controllers/destinations_controller.php?action=edit" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 0.85rem;">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="existing_media" value="<?php echo htmlspecialchars($mediaSrc); ?>">
                <input type="hidden" name="existing_media_type" value="<?php echo htmlspecialchars($mediaType); ?>">

                <div>
                    <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($item['title']); ?>" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Badge Text</label>
                        <input type="text" name="badge" value="<?php echo htmlspecialchars($item['badge']); ?>" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Region Data</label>
                        <select name="region" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                            <option value="region-western" <?php echo $item['region'] === 'region-western' ? 'selected' : ''; ?>>Western Region</option>
                            <option value="region-central" <?php echo $item['region'] === 'region-central' ? 'selected' : ''; ?>>Central Region</option>
                            <option value="region-eastern" <?php echo $item['region'] === 'region-eastern' ? 'selected' : ''; ?>>Eastern Region</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Activity Filter Keys (space separated)</label>
                    <input type="text" name="activity" value="<?php echo htmlspecialchars($item['activity']); ?>" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                </div>

                <div>
                    <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Replace Picture or Video (Optional)</label>
                    <input type="file" name="media_file" accept="image/*,video/*" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem; background: #f8fafc;">
                </div>

                <div>
                    <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Short Description</label>
                    <textarea name="desc" rows="2" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($item['description']); ?></textarea>
                </div>

                <div>
                    <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Modal Highlights (One per line)</label>
                    <textarea name="highlights" rows="3" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($item['highlights']); ?></textarea>
                </div>

                <div>
                    <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Display Tags (comma separated)</label>
                    <input type="text" name="tags" value="<?php echo htmlspecialchars($item['tags']); ?>" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.5rem;">
                    <button type="button" class="btn-toggle-edit-dest" data-id="<?php echo $id; ?>" style="background: #cbd5e1; color: #334155; border: none; padding: 0.5rem 1rem; border-radius: 4px; font-size: 0.85rem; cursor: pointer;">Cancel</button>
                    <button type="submit" style="background: var(--primary); color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px; font-size: 0.85rem; cursor: pointer; font-weight: 600;">Save</button>
                </div>
            </form>
        </div>

    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>