document.addEventListener('DOMContentLoaded', () => {
    const planForm = document.getElementById('trip-plan-form');
    if (!planForm) return;

    planForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitBtn = planForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn ? submitBtn.textContent : 'Submit Request';

        // Basic front-end validation check
        const nameInput = document.getElementById('traveler-name');
        const emailInput = document.getElementById('traveler-email');

        if (!nameInput?.value.trim() || !emailInput?.value.trim()) {
            alert('Please fill out all required contact fields (Name and Email).');
            return;
        }

        // Collect checked interests
        const checkedBoxes = planForm.querySelectorAll('input[name="interests"]:checked');
        const selectedInterests = Array.from(checkedBoxes).map(cb => cb.value);

        // Convert duration range (e.g., "5-7") to lower bound INT for DB compatibility
        const rawDuration = document.getElementById('duration')?.value || '5';
        const parsedDuration = parseInt(rawDuration, 10) || 1;

        const grandTotalDisplay = document.getElementById('grand-total-display');

        const payload = {
            name: nameInput.value.trim(),
            email: emailInput.value.trim(),
            nationality: document.getElementById('traveler-nationality')?.value || 'international',
            season: document.getElementById('travel-dates')?.value || 'Any',
            duration: parsedDuration,
            adults: parseInt(document.getElementById('traveler-adults')?.value, 10) || 1,
            children: parseInt(document.getElementById('traveler-children')?.value, 10) || 0,
            infants: parseInt(document.getElementById('traveler-infants')?.value, 10) || 0,
            interests: selectedInterests,
            estimated_total: grandTotalDisplay ? grandTotalDisplay.textContent : 'N/A'
        };

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting Request...';
        }

        try {
            const response = await fetch('frontend/controller/inquiry_controller.php?action=create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            // Read raw text response first to handle unexpected PHP notices or warnings cleanly
            const textResponse = await response.text();
            let result = null;

            try {
                // Attempt standard JSON parse
                result = JSON.parse(textResponse);
            } catch (jsonErr) {
                // If extra whitespace/HTML was printed before/after JSON, extract valid JSON string
                const jsonMatch = textResponse.match(/\{[\s\S]*\}/);
                if (jsonMatch) {
                    result = JSON.parse(jsonMatch[0]);
                }
            }

            // Verify both HTTP status and response payload state
            if (response.ok && result && result.success) {
                alert(`Thank you, ${payload.name}!\n\nYour travel request has been successfully submitted. Our team will contact you shortly at ${payload.email}.`);
                
                planForm.reset();

                // Trigger change event to sync the calculator values back to baseline defaults
                const travelerNationality = document.getElementById('traveler-nationality');
                if (travelerNationality) {
                    travelerNationality.dispatchEvent(new Event('change'));
                }
            } else {
                alert(result?.message || 'There was an issue submitting your request. Please try again.');
            }
        } catch (error) {
            console.error('Submission error:', error);
            alert('A network error occurred. Please check your connection and try again.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            }
        }
    });
});