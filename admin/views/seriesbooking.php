<?php include __DIR__ . '/partials/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.875rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Group Series Departures</h1>
        <p style="color: #64748b; font-size: 0.95rem;">Manage departure schedules, trip descriptions, capacity limits, and B2C customer bookings.</p>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_success'])): ?>
    <div style="background: #dcfce7; color: #166534; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem;">
        <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
    <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem;">
        <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">

    <!-- Left: Departures List -->
    <div style="background: #ffffff; border-radius: 10px; border: 1px solid #e2e8f0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569;">
                    <th style="padding: 0.85rem 1rem;">Departure Details</th>
                    <th style="padding: 0.85rem 1rem;">Dates</th>
                    <th style="padding: 0.85rem 1rem;">Status</th>
                    <th style="padding: 0.85rem 1rem;">Seats</th>
                    <th style="padding: 0.85rem 1rem;">Price</th>
                    <th style="padding: 0.85rem 1rem; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($departures)): ?>
                    <?php foreach ($departures as $dep): ?>
                        <?php 
                            $booked = (int)($dep['booked_seats'] ?? 0);
                            $capacity = (int)($dep['total_capacity'] ?? 0);
                            $minPass = (int)($dep['min_passengers'] ?? 0);
                            $remaining = max(0, $capacity - $booked);
                            $isGuaranteed = $booked >= $minPass;
                            $isSoldOut = $remaining <= 0;
                            // Cleanly encode JSON for data-attribute embedding
                            $bookingsJson = htmlspecialchars(json_encode($dep['bookings'] ?? [], JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.85rem 1rem;">
                                <strong style="font-size: 0.95rem; color: #0f172a;"><?= htmlspecialchars($dep['title'] ?? 'Untitled Series') ?></strong>
                                <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.2rem;"><?= htmlspecialchars($dep['description'] ?? '') ?></div>
                            </td>
                            <td style="padding: 0.85rem 1rem; white-space: nowrap;">
                                <div><?= date('M d, Y', strtotime($dep['start_date'])) ?></div>
                                <div style="font-size: 0.8rem; color: #64748b;">to <?= date('M d, Y', strtotime($dep['end_date'])) ?></div>
                            </td>
                            <td style="padding: 0.85rem 1rem;">
                                <?php if ($isSoldOut): ?>
                                    <span style="background: #fee2e2; color: #991b1b; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">Sold Out</span>
                                <?php elseif ($isGuaranteed): ?>
                                    <span style="background: #dcfce7; color: #166534; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">✓ Guaranteed</span>
                                <?php else: ?>
                                    <span style="background: #fef3c7; color: #92400e; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">Open</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.85rem 1rem;"><?= $booked ?>/<?= $capacity ?> (<?= $remaining ?> left)</td>
                            <td style="padding: 0.85rem 1rem; font-weight: 600;">$<?= number_format((float)$dep['base_price'], 2) ?></td>
                            <td style="padding: 0.85rem 1rem; text-align: right; white-space: nowrap;">
                                <button type="button" 
                                        class="btn-view-departure"
                                        data-title="<?= htmlspecialchars($dep['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-dates="<?= date('M d, Y', strtotime($dep['start_date'])) ?> to <?= date('M d, Y', strtotime($dep['end_date'])) ?>"
                                        data-capacity="<?= $booked ?>/<?= $capacity ?>"
                                        data-price="$<?= number_format((float)$dep['base_price'], 2) ?>"
                                        data-desc="<?= htmlspecialchars($dep['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-bookings='<?= $bookingsJson ?>'
                                        style="background: none; border: none; color: #0284c7; cursor: pointer; text-decoration: underline; margin-right: 0.5rem; font-size: 0.85rem; font-weight: 600;">
                                    View
                                </button>
                                <a href="DepartureController.php?edit_id=<?= (int)$dep['id'] ?>" style="color: #475569; text-decoration: none; margin-right: 0.5rem;">Edit</a>
                                <a href="DepartureController.php?action=delete&id=<?= (int)$dep['id'] ?>" onclick="return confirm('Delete this departure?')" style="color: #dc2626; text-decoration: none;">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="padding: 2rem; text-align: center; color: #64748b;">No departures configured.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Right: Add / Edit Form -->
    <div style="background: #ffffff; border-radius: 10px; border: 1px solid #e2e8f0; padding: 1.25rem; height: fit-content;">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem;">
            <?= isset($editDeparture) ? 'Edit Departure' : 'Add New Departure' ?>
        </h3>

        <form action="DepartureController.php?action=<?= isset($editDeparture) ? 'update' : 'store' ?>" method="POST">
            <?php if (isset($editDeparture)): ?>
                <input type="hidden" name="id" value="<?= (int)$editDeparture['id'] ?>">
            <?php endif; ?>

            <div style="margin-bottom: 0.85rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Departure Title / Tour Name</label>
                <input type="text" name="title" value="<?= htmlspecialchars($editDeparture['title'] ?? '') ?>" placeholder="e.g., 7-Day Cultural Experience" required style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div style="margin-bottom: 0.85rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Short Description</label>
                <textarea name="description" rows="3" placeholder="Brief summary of what's included..." style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; font-size: 0.85rem;"><?= htmlspecialchars($editDeparture['description'] ?? '') ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Start Date</label>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($editDeparture['start_date'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">End Date</label>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($editDeparture['end_date'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.85rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Capacity</label>
                    <input type="number" name="total_capacity" value="<?= (int)($editDeparture['total_capacity'] ?? 12) ?>" required style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Min. Pax</label>
                    <input type="number" name="min_passengers" value="<?= (int)($editDeparture['min_passengers'] ?? 4) ?>" required style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Price ($)</label>
                    <input type="number" step="0.01" name="base_price" value="<?= htmlspecialchars($editDeparture['base_price'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 0.25rem;">Booked Seats</label>
                    <input type="number" name="booked_seats" value="<?= (int)($editDeparture['booked_seats'] ?? 0) ?>" required style="width: 100%; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
            </div>

            <button type="submit" style="width: 100%; padding: 0.65rem; background: #6b1d2f; color: #ffffff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
                <?= isset($editDeparture) ? 'Update Departure' : 'Save Departure' ?>
            </button>

            <?php if (isset($editDeparture)): ?>
                <a href="DepartureController.php" style="display: block; text-align: center; font-size: 0.85rem; color: #64748b; margin-top: 0.5rem; text-decoration: none;">Cancel Editing</a>
            <?php endif; ?>
        </form>
    </div>

</div>

<!-- Admin View Modal -->
<div id="adminViewModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); align-items: center; justify-content: center; z-index: 1000; padding: 1rem;">
    <div style="background: #ffffff; width: 100%; max-width: 800px; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div>
                <h3 id="adminModalTitle" style="font-size: 1.2rem; font-weight: 700; color: #0f172a; margin: 0;">Departure Details</h3>
                <div id="adminModalDates" style="font-size: 0.85rem; color: #64748b; margin-top: 0.2rem;"></div>
            </div>
            <button onclick="closeAdminViewModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <div style="padding: 1.5rem; max-height: 70vh; overflow-y: auto;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; background: #f1f5f9; padding: 1rem; border-radius: 8px;">
                <div>
                    <span style="display: block; font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600;">Seats Filled</span>
                    <strong id="adminModalCapacity" style="font-size: 1.1rem; color: #0f172a;"></strong>
                </div>
                <div>
                    <span style="display: block; font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600;">Price / Seat</span>
                    <strong id="adminModalPrice" style="font-size: 1.1rem; color: #0f172a;"></strong>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <h4 style="font-size: 0.9rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem;">Description</h4>
                <p id="adminModalDesc" style="font-size: 0.85rem; color: #475569; margin: 0; line-height: 1.5;"></p>
            </div>

            <h4 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 0.75rem;">Customer Bookings</h4>
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569;">
                            <th style="padding: 0.6rem 0.85rem;">Reference</th>
                            <th style="padding: 0.6rem 0.85rem;">Customer</th>
                            <th style="padding: 0.6rem 0.85rem;">Passport</th>
                            <th style="padding: 0.6rem 0.85rem;">Seats</th>
                            <th style="padding: 0.6rem 0.85rem;">Total</th>
                            <th style="padding: 0.6rem 0.85rem;">Payment</th>
                        </tr>
                    </thead>
                    <tbody id="adminModalBookingsBody">
                        <!-- Populated via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <div style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc; text-align: right;">
            <button onclick="closeAdminViewModal()" style="padding: 0.5rem 1rem; background: #e2e8f0; color: #334155; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Close</button>
        </div>
    </div>
</div>

<script>
// Delegated click listener for View buttons using data attributes
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-view-departure');
    if (!btn) return;

    const title = btn.getAttribute('data-title');
    const dates = btn.getAttribute('data-dates');
    const capacity = btn.getAttribute('data-capacity');
    const price = btn.getAttribute('data-price');
    const desc = btn.getAttribute('data-desc');
    
    let bookings = [];
    try {
        bookings = JSON.parse(btn.getAttribute('data-bookings') || '[]');
    } catch (err) {
        console.error('Failed to parse bookings JSON:', err);
    }

    openAdminViewModal(title, dates, capacity, price, desc, bookings);
});

function openAdminViewModal(title, dates, capacity, price, desc, bookings) {
    document.getElementById('adminModalTitle').textContent = title || 'Departure Details';
    document.getElementById('adminModalDates').textContent = 'Scheduled: ' + dates;
    document.getElementById('adminModalCapacity').textContent = capacity;
    document.getElementById('adminModalPrice').textContent = price;
    document.getElementById('adminModalDesc').textContent = desc || 'No description available.';

    const tbody = document.getElementById('adminModalBookingsBody');
    tbody.innerHTML = '';

    if (bookings && bookings.length > 0) {
        bookings.forEach(b => {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #f1f5f9';
            
            const currentStatus = (b.payment_status || 'pending').toLowerCase();
            const email = b.customer_email || '';
            const hasPassport = b.passenger_passport_id !== null && b.passenger_passport_id !== undefined;

            const passportHtml = hasPassport 
                ? `<a href="DepartureController.php?action=download_passport&booking_id=${b.id}" 
                      style="color: #0284c7; font-weight: 600; text-decoration: underline;" 
                      target="_blank">
                      📄 View Scan
                   </a>`
                : `<span style="color: #94a3b8; font-style: italic;">None</span>`;

            tr.innerHTML = `
                <td style="padding: 0.6rem 0.85rem; font-weight: 600; color: #0284c7;">${b.booking_reference || 'N/A'}</td>
                <td style="padding: 0.6rem 0.85rem;">
                    <div style="font-weight: 600; color: #0f172a;">${b.customer_name || 'N/A'}</div>
                    ${email ? `<a href="mailto:${email}" style="font-size: 0.75rem; color: #0284c7; text-decoration: underline;">${email}</a>` : ''}
                </td>
                <td style="padding: 0.6rem 0.85rem;">${passportHtml}</td>
                <td style="padding: 0.6rem 0.85rem;">${b.seats_booked || 1}</td>
                <td style="padding: 0.6rem 0.85rem; font-weight: 600;">$${parseFloat(b.total_amount || 0).toFixed(2)}</td>
                <td style="padding: 0.6rem 0.85rem;">
                    <select onchange="updatePaymentStatus(this, ${b.id})" 
                            data-current="${currentStatus}"
                            style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; border: 1px solid #cbd5e1; cursor: pointer; background: #ffffff;">
                        <option value="pending" ${currentStatus === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="paid" ${currentStatus === 'paid' ? 'selected' : ''}>Paid</option>
                        <option value="cancelled" ${currentStatus === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                    </select>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } else {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="padding: 1rem; text-align: center; color: #64748b;">No customer bookings recorded for this departure.</td>
            </tr>
        `;
    }

    document.getElementById('adminViewModal').style.display = 'flex';
}

function closeAdminViewModal() {
    document.getElementById('adminViewModal').style.display = 'none';
}

async function updatePaymentStatus(selectElement, bookingId) {
    const newStatus = selectElement.value;
    const oldStatus = selectElement.getAttribute('data-current');
    
    selectElement.disabled = true;

    try {
        const response = await fetch('DepartureController.php?action=update_payment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ booking_id: bookingId, status: newStatus })
        });

        const result = await response.json();

        if (result.success) {
            selectElement.setAttribute('data-current', newStatus);
            location.reload(); 
        } else {
            alert(result.message || 'Failed to update payment status.');
            selectElement.value = oldStatus;
        }
    } catch (err) {
        console.error('Error updating status:', err);
        alert('Server communication error.');
        selectElement.value = oldStatus;
    } finally {
        selectElement.disabled = false;
    }
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>