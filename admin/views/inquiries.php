<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enforce login requirement
if (!isset($_SESSION['user_id'])) {
    header("Location: /admin/auth/login.php");
    exit();
}

// 1 level up from admin/views/ points to admin/
$adminDir = dirname(__DIR__); 

include __DIR__ . '/partials/header.php'; 
require_once $adminDir . '/config/db.php';
require_once $adminDir . '/models/InquiryModel.php';

$model = new InquiryModel($pdo);
$inquiries = $model->getAllInquiries();
$metrics = $model->getMetrics();
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.875rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Tour Inquiries & Leads</h1>
        <p style="color: #64748b; font-size: 0.95rem;">Review and manage incoming custom trip requests from travelers.</p>
    </div>
    <button onclick="exportTableToCSV('tour_inquiries.csv')" style="background: #0f172a; color: #ffffff; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem;">
        📥 Export CSV
    </button>
</div>

<!-- Quick Metrics Overview -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div style="background: #ffffff; border: 1px solid var(--border-color); padding: 1.25rem; border-radius: 8px;">
        <span style="font-size: 0.8rem; color: #64748b; font-weight: 600;">TOTAL INQUIRIES</span>
        <h3 id="metric-total" style="font-size: 1.75rem; color: #0f172a; margin-top: 0.25rem;"><?php echo (int)($metrics['total'] ?? 0); ?></h3>
    </div>
    <div style="background: #ffffff; border: 1px solid var(--border-color); padding: 1.25rem; border-radius: 8px;">
        <span style="font-size: 0.8rem; color: #d97706; font-weight: 600;">NEW LEADS</span>
        <h3 id="metric-new" style="font-size: 1.75rem; color: #d97706; margin-top: 0.25rem;"><?php echo (int)($metrics['new'] ?? 0); ?></h3>
    </div>
    <div style="background: #ffffff; border: 1px solid var(--border-color); padding: 1.25rem; border-radius: 8px;">
        <span style="font-size: 0.8rem; color: #15803d; font-weight: 600;">CONFIRMED TOURS</span>
        <h3 id="metric-confirmed" style="font-size: 1.75rem; color: #15803d; margin-top: 0.25rem;"><?php echo (int)($metrics['confirmed'] ?? 0); ?></h3>
    </div>
</div>

<!-- Inquiries Table -->
<div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; padding: 1rem;">
    <div style="padding-bottom: 1rem; border-bottom: 1px solid var(--border-color); margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
        <h2 style="font-size: 1.1rem; font-weight: 700; color: #0f172a;">Submitted Requests</h2>
    </div>

    <?php if (empty($inquiries)): ?>
        <p style="padding: 2rem; color: #64748b; font-size: 0.95rem; text-align: center;">No travel inquiries received yet.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table id="inquiriesTable" style="width: 100%; border-collapse: collapse; font-size: 0.875rem; text-align: left;">
                <thead>
                    <tr style="background: #f1f5f9; color: #475569;">
                        <th style="padding: 0.85rem 1rem;">Date</th>
                        <th style="padding: 0.85rem 1rem;">Traveler Name</th>
                        <th style="padding: 0.85rem 1rem;">Contact Email</th>
                        <th style="padding: 0.85rem 1rem;">Nationality</th>
                        <th style="padding: 0.85rem 1rem;">Trip Details</th>
                        <th style="padding: 0.85rem 1rem;">Group Breakdown</th>
                        <th style="padding: 0.85rem 1rem;">Status</th>
                        <th style="padding: 0.85rem 1rem; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inquiries as $inq): 
                        $status = $inq['status'] ?? 'new';
                        $statusStyles = [
                            'new'       => 'background: #fef3c7; color: #b45309;',
                            'contacted' => 'background: #dbeafe; color: #1d4ed8;',
                            'confirmed' => 'background: #dcfce7; color: #15803d;',
                            'archived'  => 'background: #f1f5f9; color: #64748b;'
                        ];
                        $style = $statusStyles[$status] ?? $statusStyles['new'];
                        $inquiryJson = htmlspecialchars(json_encode($inq), ENT_QUOTES, 'UTF-8');
                    ?>
                        <tr id="inquiry-row-<?php echo (int)$inq['id']; ?>" style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 0.85rem 1rem; color: #64748b; white-space: nowrap;">
                                <?php echo htmlspecialchars($inq['created_at'] ?? 'N/A'); ?>
                            </td>
                            <td style="padding: 0.85rem 1rem; font-weight: 600; color: #0f172a;">
                                <?php echo htmlspecialchars($inq['name']); ?>
                            </td>
                            <td style="padding: 0.85rem 1rem; color: #334155;">
                                <a href="mailto:<?php echo htmlspecialchars($inq['email']); ?>" style="color: var(--primary, #2563eb); text-decoration: none;">
                                    <?php echo htmlspecialchars($inq['email']); ?>
                                </a>
                            </td>
                            <td style="padding: 0.85rem 1rem; text-transform: capitalize;">
                                <?php echo htmlspecialchars($inq['nationality']); ?>
                            </td>
                            <td style="padding: 0.85rem 1rem;">
                                <div style="font-weight: 600; text-transform: capitalize;"><?php echo htmlspecialchars($inq['season'] ?? 'Any'); ?></div>
                                <div style="font-size: 0.75rem; color: #64748b;"><?php echo (int)($inq['duration'] ?? 0); ?> Days</div>
                            </td>
                            <td style="padding: 0.85rem 1rem; white-space: nowrap;">
                                <?php echo "Adults: " . (int)$inq['adults'] . " | Child: " . (int)$inq['children'] . " | Inf: " . (int)$inq['infants']; ?>
                            </td>
                            <td style="padding: 0.85rem 1rem;" data-search="<?php echo htmlspecialchars($status); ?>">
                                <select onchange="updateInquiryStatus(<?php echo (int)$inq['id']; ?>, this)" style="<?php echo $style; ?> border: none; padding: 0.35rem 0.6rem; border-radius: 4px; font-weight: 700; font-size: 0.75rem; cursor: pointer;">
                                    <option value="new" <?php echo $status === 'new' ? 'selected' : ''; ?>>New</option>
                                    <option value="contacted" <?php echo $status === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                    <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="archived" <?php echo $status === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                </select>
                            </td>
                            <td style="padding: 0.85rem 1rem; text-align: right; white-space: nowrap;">
                                <button onclick='openViewModal(<?php echo $inquiryJson; ?>)' style="background: #e2e8f0; color: #1e293b; border: none; padding: 0.4rem 0.6rem; border-radius: 4px; cursor: pointer; font-size: 0.8rem; margin-right: 0.25rem;">
                                    👁️ View
                                </button>
                                <button onclick="deleteInquiry(<?php echo (int)$inq['id']; ?>)" style="background: #fee2e2; color: #dc2626; border: none; padding: 0.4rem 0.6rem; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">
                                    🗑️ Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal View Component -->
<div id="inquiryModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; justify-content: center; align-items: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 600px; width: 100%; max-height: 90vh; overflow-y: auto; padding: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem; margin-bottom: 1rem;">
            <h3 style="font-size: 1.25rem; font-weight: 700; color: #0f172a;">Inquiry Details</h3>
            <button onclick="closeViewModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        <div id="modalContent" style="display: flex; flex-direction: column; gap: 0.85rem; font-size: 0.9rem; color: #334155;">
            <!-- Content dynamically rendered via JS -->
        </div>
        <div style="margin-top: 1.5rem; text-align: right; border-top: 1px solid #e2e8f0; padding-top: 1rem;">
            <button onclick="closeViewModal()" style="background: #0f172a; color: #ffffff; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 600;">Close</button>
        </div>
    </div>
</div>



<?php include __DIR__ . '/partials/footer.php'; ?>