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
require_once $adminDir . '/models/PlanModel.php';

$model = new PlanModel($pdo);
$stepsData = $model->getSteps();
$ratesData = $model->getRates();
?>

<div class="admin-page-container" style="width: 100%; max-width: 100%; box-sizing: border-box; padding: 1.5rem 2rem;">
    
    <!-- Header Title -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 700; color: #0f172a; margin: 0 0 0.25rem 0;">Trip Planner Settings</h1>
            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Manage entry steps and dynamic rate rules for the travel calculator.</p>
        </div>
    </div>

    <!-- Alert Message Container -->
    <div id="plan-alert-box" style="display: none; padding: 0.85rem 1rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: 0.9rem;"></div>

    <!-- Two Column Grid -->
    <div style="display: grid; grid-template-columns: minmax(350px, 1.2fr) minmax(300px, 0.8fr); gap: 1.5rem; width: 100%;">
        
        <!-- Form 1: Essential Travel Steps Table -->
        <div style="background: #ffffff; border: 1px solid var(--border-color, #e2e8f0); border-radius: 10px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0;">Essential Travel Steps</h2>
                <button type="button" id="btn-add-step" style="background: #f1f5f9; color: #334155; border: 1px solid var(--border-color, #cbd5e1); padding: 0.4rem 0.8rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.8rem;">+ Add Row</button>
            </div>
            
            <form id="plan-steps-form" action="/admin/controllers/plan_controller.php?action=save_steps" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                
                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
                            <th style="padding: 0.6rem 0.5rem; width: 30px; color: #475569; font-weight: 600;">#</th>
                            <th style="padding: 0.6rem 0.5rem; width: 35%; color: #475569; font-weight: 600;">Step Title</th>
                            <th style="padding: 0.6rem 0.5rem; color: #475569; font-weight: 600;">Step Description</th>
                            <th style="padding: 0.6rem 0.5rem; width: 60px; text-align: center; color: #475569; font-weight: 600;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="steps-table-body">
                        <?php foreach ($stepsData as $idx => $step): ?>
                            <tr class="step-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td class="step-label" style="padding: 0.6rem 0.5rem; font-weight: 600; color: #64748b; vertical-align: top; text-align: center; font-size: 0.85rem;">
                                    <?php echo $idx + 1; ?>
                                </td>
                                <td style="padding: 0.5rem; vertical-align: top;">
                                    <input type="text" name="step_titles[]" value="<?php echo htmlspecialchars($step['title']); ?>" placeholder="e.g. Visa & Entry Permit" style="width: 100%; box-sizing: border-box; padding: 0.5rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.85rem;" required />
                                </td>
                                <td style="padding: 0.5rem; vertical-align: top;">
                                    <textarea name="step_descriptions[]" rows="2" placeholder="Description details..." style="width: 100%; box-sizing: border-box; padding: 0.5rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.85rem;" required><?php echo htmlspecialchars($step['description']); ?></textarea>
                                </td>
                                <td style="padding: 0.5rem; text-align: center; vertical-align: top;">
                                    <button type="button" class="btn-remove-step" style="background: none; border: none; color: #ef4444; font-size: 0.8rem; cursor: pointer; font-weight: 600; padding: 0.25rem;">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div>
                    <button type="submit" style="background: #701a2b; color: #fff; border: none; padding: 0.6rem 1.25rem; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.85rem;">Save Steps</button>
                </div>
            </form>
        </div>

        <!-- Form 2: Calculator Base Rates -->
        <div style="background: #ffffff; border: 1px solid var(--border-color, #e2e8f0); border-radius: 10px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); height: fit-content;">
            <h2 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 1rem;">Calculator Base Rates</h2>
            
            <form id="plan-rates-form" action="/admin/controllers/plan_controller.php?action=save_rates" method="POST" style="display: flex; flex-direction: column; gap: 0.85rem;">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.25rem;">Intl SDF ($/night)</label>
                        <input type="number" step="any" name="sdf_intl" value="<?php echo htmlspecialchars($ratesData['sdf_intl']); ?>" required style="width: 100%; box-sizing: border-box; padding: 0.5rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.85rem;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.25rem;">Indian SDF (₹/night)</label>
                        <input type="number" step="any" name="sdf_indian" value="<?php echo htmlspecialchars($ratesData['sdf_indian']); ?>" required style="width: 100%; box-sizing: border-box; padding: 0.5rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.85rem;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.25rem;">Visa Fee ($ USD)</label>
                        <input type="number" step="any" name="visa_fee" value="<?php echo htmlspecialchars($ratesData['visa_fee']); ?>" required style="width: 100%; box-sizing: border-box; padding: 0.5rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.85rem;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.25rem;">Monument Avg Fee ($ USD)</label>
                        <input type="number" step="any" name="monument_rate" value="<?php echo htmlspecialchars($ratesData['monument_rate']); ?>" required style="width: 100%; box-sizing: border-box; padding: 0.5rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.85rem;">
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px dashed var(--border-color, #cbd5e1); margin: 0.5rem 0;">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.25rem;">Accommodation ($/night)</label>
                        <input type="number" step="any" name="accommodation_rate" value="<?php echo htmlspecialchars($ratesData['accommodation_rate']); ?>" required style="width: 100%; box-sizing: border-box; padding: 0.5rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.85rem;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.25rem;">Guide Fee ($/day)</label>
                        <input type="number" step="any" name="guide_rate" value="<?php echo htmlspecialchars($ratesData['guide_rate']); ?>" required style="width: 100%; box-sizing: border-box; padding: 0.5rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.85rem;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.25rem;">Transport ($/day)</label>
                        <input type="number" step="any" name="transport_rate" value="<?php echo htmlspecialchars($ratesData['transport_rate']); ?>" required style="width: 100%; box-sizing: border-box; padding: 0.5rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.85rem;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; font-size: 0.8rem; color: #334155; margin-bottom: 0.25rem;">Miscellaneous ($/person)</label>
                        <input type="number" step="any" name="misc_rate" value="<?php echo htmlspecialchars($ratesData['misc_rate']); ?>" required style="width: 100%; box-sizing: border-box; padding: 0.5rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.85rem;">
                    </div>
                </div>

                <button type="submit" style="background: #701a2b; color: #fff; border: none; padding: 0.6rem 1.25rem; border-radius: 6px; font-weight: 600; cursor: pointer; align-self: flex-start; margin-top: 0.5rem; font-size: 0.85rem;">Update Rates</button>
            </form>
        </div>

    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>