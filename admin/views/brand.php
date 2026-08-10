<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Ensure user is logged in before anything else
if (!isset($_SESSION['user_id'])) {
    header("Location: /admin/auth/login.php");
    exit();
}

// 2. Load header and dependencies
include __DIR__ . '/partials/header.php'; 
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/BrandModel.php';

$brandModel = new BrandModel($pdo);
$brandData  = $brandModel->getBrandData();
?>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="session-flash alert" style="padding: 1rem; margin-bottom: 1.5rem; border-radius: 6px; background: <?= ($_SESSION['flash_type'] ?? '') === 'error' ? '#fef2f2' : '#f0fdf4' ?>; color: <?= ($_SESSION['flash_type'] ?? '') === 'error' ? '#991b1b' : '#166534' ?>; border: 1px solid <?= ($_SESSION['flash_type'] ?? '') === 'error' ? '#fecaca' : '#bbf7d0' ?>;">
        <?= htmlspecialchars($_SESSION['flash_message']) ?>
    </div>
    <?php 
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
    ?>
<?php endif; ?>

<!-- Header & Page Description -->
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.875rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Brand Showcase Settings</h1>
    <p style="color: #64748b; font-size: 0.95rem;">Manage the Brand Manifesto quote and the 6 core "BHUTAN Believe" brand pillar cards.</p>
</div>

<div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 10px; padding: 1.75rem;">
    <form action="/admin/controllers/brand_controller.php" method="POST" style="display: flex; flex-direction: column; gap: 2rem;">

        <!-- 1. Brand Header & Manifesto -->
        <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem;">
            <h3 style="font-size: 1.1rem; color: #0f172a; font-weight: 600; margin-bottom: 1.25rem;">Section Header & Manifesto</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.4rem; font-size: 0.875rem; color: #334155;">Eyebrow Title</label>
                    <input type="text" name="eyebrow" value="<?php echo htmlspecialchars($brandData['eyebrow'] ?? 'An Anatomy of the Brand'); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem;">
                </div>

                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.4rem; font-size: 0.875rem; color: #334155;">Main Section Heading (H2)</label>
                    <input type="text" name="heading" value="<?php echo htmlspecialchars($brandData['heading'] ?? 'BHUTAN Believe'); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem;">
                </div>
            </div>

            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.4rem; font-size: 0.875rem; color: #334155;">Brand Manifesto Text</label>
                <textarea name="manifesto" rows="3" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; resize: vertical;"><?php echo htmlspecialchars($brandData['manifesto'] ?? 'We see a bright future. And we believe in our ability and responsibility to realise it together, and shine as a beacon of possibility in the world.'); ?></textarea>
            </div>
        </div>

        <!-- 2. Brand Blocks (6 Pillars) -->
        <div>
            <h3 style="font-size: 1.1rem; color: #0f172a; font-weight: 600; margin-bottom: 1.25rem;">Brand Pillar Blocks</h3>

            <div style="display: flex; flex-direction: column; gap: 1.5rem;">

                <!-- Block 1: Policy -->
                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--primary); margin-bottom: 1rem;">1. High-Value, Low-Volume Policy & Border Reopening</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Card Title (H3)</label>
                            <input type="text" name="block1_title" value="<?php echo htmlspecialchars($brandData['block1_title'] ?? 'High-Value, Low-Volume Policy & Border Reopening'); ?>" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Subline Tagline</label>
                            <input type="text" name="block1_subline" value="<?php echo htmlspecialchars($brandData['block1_subline'] ?? 'BHUTAN Believe: Believe in Meaningful Travel'); ?>" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Theme Description</label>
                            <textarea name="block1_theme" rows="2" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($brandData['block1_theme'] ?? 'Bhutan reopened to inspire a new vision, maintaining its long-practiced "High Value, Low Volume" tourism policy to protect its sacred places, peace, and wildernesses.'); ?></textarea>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Traveler Experience Description</label>
                            <textarea name="block1_exp" rows="2" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($brandData['block1_exp'] ?? 'Exclusive, Uncrowded Journeys. Step into an untouched sanctuary where you won\'t compete with tour buses or crowds. Walk through sacred dzongs, serene mountain monasteries, and peaceful valleys in quiet contemplation.'); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Block 2: Youth -->
                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--primary); margin-bottom: 1rem;">2. Youth at the Heart of the Brand</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Card Title (H3)</label>
                            <input type="text" name="block2_title" value="<?php echo htmlspecialchars($brandData['block2_title'] ?? 'Youth at the Heart of the Brand'); ?>" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Subline Tagline</label>
                            <input type="text" name="block2_subline" value="<?php echo htmlspecialchars($brandData['block2_subline'] ?? 'BHUTAN Believe: Believe in the Future'); ?>" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Theme Description</label>
                            <textarea name="block2_theme" rows="2" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($brandData['block2_theme'] ?? 'Focuses on Bhutanese youth and professionals as the primary conduits and front-line hosts for the country\'s future and transformation.'); ?></textarea>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Traveler Experience Description</label>
                            <textarea name="block2_exp" rows="2" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($brandData['block2_exp'] ?? 'Warm, Authentic Local Connections. Travel alongside articulate, educated young guides and local hosts who represent modern Bhutan.'); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Block 3: Nature -->
                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--primary); margin-bottom: 1rem;">3. Pristine Nature & Wilderness Guardianship</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Card Title (H3)</label>
                            <input type="text" name="block3_title" value="<?php echo htmlspecialchars($brandData['block3_title'] ?? 'Pristine Nature & Wilderness Guardianship'); ?>" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Subline Tagline</label>
                            <input type="text" name="block3_subline" value="<?php echo htmlspecialchars($brandData['block3_subline'] ?? 'BHUTAN Believe: Believe in Harmony with Nature'); ?>" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Theme Description</label>
                            <textarea name="block3_theme" rows="2" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($brandData['block3_theme'] ?? 'Bhutan is a carbon-negative nation with around 70% forest cover and over 50% land declared as protected wildlife areas.'); ?></textarea>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Traveler Experience Description</label>
                            <textarea name="block3_exp" rows="2" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($brandData['block3_exp'] ?? 'Trek Through Pure, Carbon-Negative Wilderness. Hike ancient trails surrounded by raw biodiversity and dense evergreen forests.'); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Block 4: Sustainable Development Fee -->
                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--primary); margin-bottom: 1rem;">4. Sustainable Development Fee (SDF)</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Card Title (H3)</label>
                            <input type="text" name="block4_title" value="<?php echo htmlspecialchars($brandData['block4_title'] ?? 'The Sustainable Development Fee (SDF)'); ?>" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Subline Tagline</label>
                            <input type="text" name="block4_subline" value="<?php echo htmlspecialchars($brandData['block4_subline'] ?? 'BHUTAN Believe: Believe in Shared Value'); ?>" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Theme Description</label>
                            <textarea name="block4_theme" rows="2" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($brandData['block4_theme'] ?? 'The daily SDF directly funds social, environmental, and cultural development, including free healthcare, education, and infrastructure upgrades for citizens.'); ?></textarea>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Traveler Experience Description</label>
                            <textarea name="block4_exp" rows="2" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($brandData['block4_exp'] ?? 'Purpose-Driven Travel. Enjoy a deeply rewarding journey knowing your trip directly pays for free medical care and schooling for local families.'); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Block 5: Conscious Travelers -->
                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--primary); margin-bottom: 1rem;">5. Conscious Travelers as Transformation Partners</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Card Title (H3)</label>
                            <input type="text" name="block5_title" value="<?php echo htmlspecialchars($brandData['block5_title'] ?? 'Conscious Travelers as Transformation Partners'); ?>" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Subline Tagline</label>
                            <input type="text" name="block5_subline" value="<?php echo htmlspecialchars($brandData['block5_subline'] ?? 'BHUTAN Believe: Believe in Stillness & Transformation'); ?>" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Theme Description</label>
                            <textarea name="block5_theme" rows="2" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($brandData['block5_theme'] ?? 'The brand positions travelers not as passive tourists, but as "conscious eyes" and dedicated partners in Bhutan\'s ongoing transformation.'); ?></textarea>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Traveler Experience Description</label>
                            <textarea name="block5_exp" rows="2" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($brandData['block5_exp'] ?? 'A Meaningful, Transformative Encounter. Engage in a travel experience designed to help you slow down, discover deeper details, and reconnect with yourself.'); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Block 6: Heritage & Crafts -->
                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--primary); margin-bottom: 1rem;">6. Heritage & Crafts (Contemporary Constellations)</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Card Title (H3)</label>
                            <input type="text" name="block6_title" value="<?php echo htmlspecialchars($brandData['block6_title'] ?? 'Contemporary Constellations (Heritage & Crafts)'); ?>" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Subline Tagline</label>
                            <input type="text" name="block6_subline" value="<?php echo htmlspecialchars($brandData['block6_subline'] ?? 'BHUTAN Believe: Believe in Living Heritage'); ?>" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;">
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Theme Description</label>
                            <textarea name="block6_theme" rows="2" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($brandData['block6_theme'] ?? 'Traditional 13 crafts (Zorig Chusum), 8 auspicious symbols (Tashi Tagye), and mythical guardian animals are visually reimagined with digital art.'); ?></textarea>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.3rem;">Traveler Experience Description</label>
                            <textarea name="block6_exp" rows="2" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($brandData['block6_exp'] ?? 'Immersive Artisan Encounters. Experience living history firsthand through visits to active artisan workshops, traditional painting schools, and local studios.'); ?></textarea>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Submit Controls -->
        <div style="padding-top: 0.5rem; text-align: right;">
            <button type="submit" style="background: var(--primary); color: #ffffff; border: none; padding: 0.85rem 2rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.95rem;">
                Save Brand Showcase Settings
            </button>
        </div>

    </form>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>