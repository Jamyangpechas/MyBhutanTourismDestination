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
require_once __DIR__ . '/../models/EventModel.php';

$eventModel = new EventModel($pdo);
$events = $eventModel->getAllEvents();
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.875rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Calendar & Festival Showcase</h1>
        <p style="color: #64748b; font-size: 0.95rem;">Manage sacred tshechus, eco-festivals, and seasonal events.</p>
    </div>
    <button type="button" id="btn-toggle-add-event" style="background: var(--primary); color: #fff; border: none; padding: 0.75rem 1.25rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.9rem;">
        + Add New Event
    </button>
</div>

<?php if (isset($_GET['success'])): ?>
    <div style="background: #dcfce7; color: #15803d; padding: 0.85rem 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: 0.9rem;">
        ✅ Calendar events updated successfully!
    </div>
<?php endif; ?>

<!-- Inline Form: Add New Event -->
<div id="add-event-card" style="display: none; background: #ffffff; border: 2px dashed var(--primary); border-radius: 10px; padding: 1.5rem; margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--primary);">➕ Add New Calendar Event</h2>
        <button type="button" class="btn-cancel-add-event" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b;">&times;</button>
    </div>
    <form action="/admin/controllers/events_controller.php?action=add" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 0.85rem;">
        <div>
            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Event Title</label>
            <input type="text" name="title" required placeholder="e.g. Thimphu Tshechu" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Start Date</label>
                <input type="date" name="start_date" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
            </div>
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">End Date (Optional)</label>
                <input type="date" name="end_date" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Season Filter</label>
                <select name="season" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                    <option value="spring">Spring</option>
                    <option value="summer">Summer</option>
                    <option value="autumn">Autumn</option>
                    <option value="winter">Winter</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Category Filter</label>
                <select name="category" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                    <option value="cat-tshechu">Sacred Tshechu</option>
                    <option value="cat-nature">Nature & Eco</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Display Tag</label>
                <input type="text" name="tag" required placeholder="e.g. #SacredTshechu" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
            </div>
            <div>
                <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Location</label>
                <input type="text" name="location" required placeholder="e.g. Tashichho Dzong, Thimphu" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
            </div>
        </div>

        <div>
            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Upload Optional Photo or Video</label>
            <input type="file" name="media_file" accept="image/*,video/*" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem; background: #f8fafc;">
        </div>

        <div>
            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Short Description</label>
            <textarea name="desc" rows="2" required placeholder="Brief festival overview..." style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"></textarea>
        </div>

        <div>
            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Modal Highlights (One per line)</label>
            <textarea name="highlights" rows="3" required placeholder="Witness mask dances&#10;Receive blessings" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.5rem;">
            <button type="button" class="btn-cancel-add-event" style="background: #cbd5e1; color: #334155; border: none; padding: 0.5rem 1rem; border-radius: 4px; font-size: 0.85rem; cursor: pointer;">Cancel</button>
            <button type="submit" style="background: var(--primary); color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px; font-size: 0.85rem; cursor: pointer; font-weight: 600;">Save Event</button>
        </div>
    </form>
</div>

<!-- Grid of Editable Event Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.5rem;">
    <?php foreach ($events as $id => $item): ?>
        
        <!-- Display Mode Card -->
        <div id="event-card-<?php echo $id; ?>" style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; display: flex; flex-direction: column;">
            <div style="padding: 1.25rem; background: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 0.75rem; font-weight: 700; color: var(--primary); text-transform: uppercase;"><?php echo htmlspecialchars($item['season'] ?? ''); ?></span>
                    <h3 style="font-size: 1.1rem; color: #0f172a; font-weight: 700; margin-top: 0.2rem;"><?php echo htmlspecialchars($item['title'] ?? ''); ?></h3>
                </div>
                <span style="background: #e2e8f0; color: #334155; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                    <?php echo htmlspecialchars($item['date'] ?? ''); ?>
                </span>
            </div>
            
            <div style="padding: 1.25rem; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <p style="font-size: 0.8rem; color: var(--accent); font-weight: 600; margin-bottom: 0.4rem;"><?php echo htmlspecialchars($item['tag'] ?? ''); ?></p>
                    <p style="font-size: 0.85rem; color: #475569; margin-bottom: 0.5rem;">📍 <?php echo htmlspecialchars($item['location'] ?? ''); ?></p>
                    <p style="font-size: 0.875rem; color: #64748b; margin-bottom: 1rem; line-height: 1.4;"><?php echo htmlspecialchars($item['description'] ?? $item['desc'] ?? ''); ?></p>
                </div>

                <div style="display: flex; gap: 0.5rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                    <button type="button" class="btn-toggle-edit-event" data-id="<?php echo $id; ?>" style="flex: 1; background: #e2e8f0; color: #334155; border: none; padding: 0.5rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.85rem;">
                        ✏️ Edit
                    </button>
                    <form action="/admin/controllers/events_controller.php?action=delete" method="POST" class="delete-event-form">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <button type="submit" style="background: #fee2e2; color: #dc2626; border: none; padding: 0.5rem 0.75rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.85rem;">
                            🗑️
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Inline Edit Form Mode -->
        <div id="event-edit-<?php echo $id; ?>" style="display: none; background: #ffffff; border: 2px solid var(--primary); border-radius: 10px; padding: 1.5rem; grid-column: span 1;">
            <form action="/admin/controllers/events_controller.php?action=edit" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 0.85rem;">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="existing_media" value="<?php echo htmlspecialchars($item['media'] ?? ''); ?>">
                <input type="hidden" name="existing_media_type" value="<?php echo htmlspecialchars($item['media_type'] ?? 'image'); ?>">

                <div>
                    <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($item['title'] ?? ''); ?>" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Start Date</label>
                        <input type="date" name="start_date" value="<?php echo htmlspecialchars($item['start_date'] ?? ''); ?>" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">End Date (Optional)</label>
                        <input type="date" name="end_date" value="<?php echo htmlspecialchars($item['end_date'] ?? ''); ?>" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Season</label>
                        <select name="season" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                            <option value="spring" <?php echo ($item['season'] ?? '') === 'spring' ? 'selected' : ''; ?>>Spring</option>
                            <option value="summer" <?php echo ($item['season'] ?? '') === 'summer' ? 'selected' : ''; ?>>Summer</option>
                            <option value="autumn" <?php echo ($item['season'] ?? '') === 'autumn' ? 'selected' : ''; ?>>Autumn</option>
                            <option value="winter" <?php echo ($item['season'] ?? '') === 'winter' ? 'selected' : ''; ?>>Winter</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Category</label>
                        <select name="category" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                            <option value="cat-tshechu" <?php echo ($item['category'] ?? '') === 'cat-tshechu' ? 'selected' : ''; ?>>Sacred Tshechu</option>
                            <option value="cat-nature" <?php echo ($item['category'] ?? '') === 'cat-nature' ? 'selected' : ''; ?>>Nature & Eco</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Tag</label>
                        <input type="text" name="tag" value="<?php echo htmlspecialchars($item['tag'] ?? ''); ?>" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Location</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($item['location'] ?? ''); ?>" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                    </div>
                </div>

                <div>
                    <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Replace Media (Optional)</label>
                    <input type="file" name="media_file" accept="image/*,video/*" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem; background: #f8fafc;">
                </div>

                <div>
                    <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Short Description</label>
                    <textarea name="desc" rows="2" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($item['description'] ?? $item['desc'] ?? ''); ?></textarea>
                </div>

                <div>
                    <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155;">Modal Highlights (One per line)</label>
                    <textarea name="highlights" rows="3" required style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($item['highlights'] ?? ''); ?></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.5rem;">
                    <button type="button" class="btn-toggle-edit-event" data-id="<?php echo $id; ?>" style="background: #cbd5e1; color: #334155; border: none; padding: 0.5rem 1rem; border-radius: 4px; font-size: 0.85rem; cursor: pointer;">Cancel</button>
                    <button type="submit" style="background: var(--primary); color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px; font-size: 0.85rem; cursor: pointer; font-weight: 600;">Save</button>
                </div>
            </form>
        </div>

    <?php endforeach; ?>
</div>

<script src="/public/js/events.js"></script>

<?php include __DIR__ . '/partials/footer.php'; ?>