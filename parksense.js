/**
 * ParkSense System - Core JavaScript Integration (With Persistent Frontend Notifications)
 * Consolidates Student, Admin, Unregistered, Violation, and Archive scripts.
 */

document.addEventListener('DOMContentLoaded', function() {
    
    /* ==========================================================================
       0. PERSISTENT GLOBAL SYSTEM ENABLER / DISABLE SWITCH RULE
       ========================================================================== */
    const systemToggle = document.querySelector('.toggle-switch input[type="checkbox"]');
    // FIXED: Broadened selector hook to ensure it successfully grabs the main content area tag across all views
    const mainContent = document.querySelector('.main-content') || document.querySelector('main');
    const downloadBtn = document.querySelector('.download-btn');

    if (systemToggle && mainContent) {
        const systemState = localStorage.getItem('parksense_activated');
        // Target .system-status h2 directly — works on all pages without requiring id attribute
        const systemStatusLabel = document.querySelector('.system-status h2');

        function applySystemState(isActivated) {
            if (isActivated) {
                systemToggle.checked = true;
                mainContent.classList.remove('system-disabled');
                if (systemStatusLabel) systemStatusLabel.textContent = 'System Activated';
                if (downloadBtn) {
                    downloadBtn.classList.remove('system-disabled');
                    const saved = downloadBtn.dataset.href;
                    if (saved) downloadBtn.setAttribute('href', saved);
                }
            } else {
                systemToggle.checked = false;
                mainContent.classList.add('system-disabled');
                if (systemStatusLabel) systemStatusLabel.textContent = 'System Deactivated';
                if (downloadBtn) {
                    downloadBtn.classList.add('system-disabled');
                    if (!downloadBtn.dataset.href) {
                        downloadBtn.dataset.href = downloadBtn.getAttribute('href') || '';
                    }
                    downloadBtn.setAttribute('href', 'javascript:void(0)');
                }
            }
        }

        if (systemState === 'false') {
            applySystemState(false);
        } else {
            applySystemState(true);
        }

        systemToggle.addEventListener('change', function() {
            if (this.checked) {
                localStorage.setItem('parksense_activated', 'true');
                applySystemState(true);
            } else {
                localStorage.setItem('parksense_activated', 'false');
                applySystemState(false);
            }
        });
    }

    /* ==========================================================================
       1. GLOBAL NOTIFICATION ENGINE & SHARED UTILITIES
       ========================================================================== */
    const notificationContainer = document.getElementById('notification-container');
    const notificationPopup = document.getElementById('notification-popup');

    // Load and render persistent notifications from localStorage
    function loadPersistentNotifications() {
        if (!notificationPopup) return;

        let stored = localStorage.getItem('parksense_notifications');
        let items = stored ? JSON.parse(stored) : [];

        // FIXED: Removed the mock data seeding block completely so it remains unpopulated on refreshes
        renderNotificationDOM(items);
    }

    // Rebuilds the inner dropdown items with correct classes, layout, and Clear All actions
    function renderNotificationDOM(items) {
        if (!notificationPopup) return;
        
        notificationPopup.innerHTML = '';
        const badge = notificationContainer ? notificationContainer.querySelector('.notification-badge') : null;

        if (items.length === 0) {
            notificationPopup.innerHTML = `
                <div class="notification-empty">
                    <p>No new notifications</p>
                </div>`;
            if (badge) badge.style.display = 'none';
            return;
        }

        // Sticky header bar with "Notifications" title and "Clear All" action
        const clearHeader = document.createElement('div');
        clearHeader.className = 'notification-clear-header';
        clearHeader.innerHTML = `
            <span class="notif-header-title">Notifications</span>
            <span id="clear-all-notifications"><i class="fas fa-trash-alt"></i> Clear All</span>`;
        notificationPopup.appendChild(clearHeader);

        // Render each notification split into a bold label and a lighter detail sentence
        items.forEach(item => {
            // Split on the first period to separate "Action sentence." from "Email sentence."
            const splitIndex = item.text.indexOf('. An email');
            const label  = splitIndex !== -1 ? item.text.slice(0, splitIndex + 1) : item.text;
            const detail = splitIndex !== -1 ? item.text.slice(splitIndex + 2) : '';

            const entry = document.createElement('div');
            entry.className = 'popup-content';
            entry.innerHTML = `
                <p class="notif-label">${label}</p>
                ${detail ? `<p class="notif-detail">${detail}</p>` : ''}`;
            notificationPopup.appendChild(entry);
        });

        // Attach Clear All listener after elements are in the DOM
        document.getElementById('clear-all-notifications').addEventListener('click', function(e) {
            e.stopPropagation();
            localStorage.setItem('parksense_notifications', JSON.stringify([]));
            renderNotificationDOM([]);
        });

        if (badge) {
            badge.textContent = items.length;
            badge.style.display = 'flex';
        }
    }

    // Interactive toggle and strict click propagation safety checks
    if (notificationContainer && notificationPopup) {
        // Initialize notifications immediately on page load
        loadPersistentNotifications();

        // Toggle visibility when clicking the bell element container directly
        notificationContainer.addEventListener('click', function(event) {
            event.stopPropagation();
            const isDisplayed = notificationPopup.style.display === 'block';
            notificationPopup.style.display = isDisplayed ? 'none' : 'block';
        });

        // Halt event bubbling so clicking inside the dropdown content container doesn't shut it
        notificationPopup.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    }

    // Helper hook engine used to programmatically register new alerts from actions
    function triggerFrontendNotification(plateNumber, alertMessage) {
        let stored = localStorage.getItem('parksense_notifications');
        let items = stored ? JSON.parse(stored) : [];
        
        // Push the fresh payload to the beginning of the feed stack array
        items.unshift({ text: alertMessage, plate: plateNumber });
        localStorage.setItem('parksense_notifications', JSON.stringify(items));
        
        // Refresh active layout matrix if visible on screen
        renderNotificationDOM(items);
    }

    // Live Clock Widget Engine
    const dateEl = document.getElementById('date');
    const timeEl = document.getElementById('time');
    if (dateEl && timeEl) {
        function updateDateTime() {
            const now = new Date();
            const options = { month: 'long', day: 'numeric', year: 'numeric' };
            dateEl.textContent = now.toLocaleDateString('en-US', options);
            let hours = now.getHours();
            let minutes = now.getMinutes().toString().padStart(2, '0');
            let seconds = now.getSeconds().toString().padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;
            timeEl.textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);
    }

/* ==========================================================================
       2. UNREGISTERED VEHICLES PAGE LOGIC (unregistered.php)
       ========================================================================== */
    const resolveModal = document.getElementById('resolve-modal');
    const cancelResolveBtn = document.getElementById('cancel-resolve-btn');
    const confirmResolveBtn = document.getElementById('confirm-resolve-btn');
    const successModal = document.getElementById('success-modal');
    
    let rowToResolve = null;

    async function archiveViolation(violationId) {
        try {
            const response = await fetch('archive_violation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `violation_id=${encodeURIComponent(violationId)}`
            });
            const data = await response.json();
            return data.success;
        } catch (error) {
            console.error('Error:', error);
            return false;
        }
    }

    document.querySelectorAll('.resolved-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            rowToResolve = this.closest('tr');
            if (resolveModal) resolveModal.style.display = 'block';
        });
    });

    if (confirmResolveBtn) {
        confirmResolveBtn.addEventListener('click', async function() {
            if (rowToResolve) {
                const plate = rowToResolve.cells[1]?.textContent.trim() || 'Unknown';
                const originalText = confirmResolveBtn.textContent;
                confirmResolveBtn.textContent = 'Processing...';
                confirmResolveBtn.disabled = true;

                const violationId = rowToResolve.dataset.id;
                const success = await archiveViolation(violationId);

                confirmResolveBtn.textContent = originalText;
                confirmResolveBtn.disabled = false;

                if (success) {
                    // REMOVED EMAIL NOTIFICATION SENTENCE FROM RESOLVE ACTION
                    triggerFrontendNotification(plate, `Unregistered vehicle record for ${plate} has been resolved.`);
                    if (resolveModal) resolveModal.style.display = 'none';
                    if (successModal) successModal.style.display = 'block';
                }
            }
        });
    }

    if (cancelResolveBtn) {
        cancelResolveBtn.addEventListener('click', function() {
            if (resolveModal) resolveModal.style.display = 'none';
            rowToResolve = null;
        });
    }

    /* ==========================================================================
       3. VIOLATION HISTORY PAGE LOGIC (violation.php)
       ========================================================================== */
    const confirmModal = document.getElementById('confirm-modal');
    const deleteModal = document.getElementById('delete-modal');
    const cancelConfirmBtn = document.getElementById('cancel-confirm-btn');
    const confirmConfirmBtn = document.getElementById('confirm-confirm-btn');
    const cancelDeleteBtn = document.getElementById('cancel-delete-btn');
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');

    let rowToConfirm = null;
    let rowToDelete = null;

    document.querySelectorAll('.confirm-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            rowToConfirm = this.closest('tr');
            if (confirmModal) confirmModal.style.display = 'block';
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            rowToDelete = this.closest('tr');
            if (deleteModal) deleteModal.style.display = 'block';
        });
    });

    if (confirmConfirmBtn) {
        confirmConfirmBtn.addEventListener('click', async function() {
            if (rowToConfirm) {
                const plate = rowToConfirm.cells[1]?.textContent.trim() || 'Unknown';
                const originalText = confirmConfirmBtn.textContent;
                confirmConfirmBtn.textContent = 'Processing...';
                confirmConfirmBtn.disabled = true;

                const violationId = rowToConfirm.dataset.id;
                const success = await archiveViolation(violationId);

                confirmConfirmBtn.textContent = originalText;
                confirmConfirmBtn.disabled = false;

                if (success) {
                    triggerFrontendNotification(plate, `Violation record for ${plate} has been confirmed. An email notification has been sent to the registered owner.`);
                    if (confirmModal) confirmModal.style.display = 'none';
                    if (successModal) successModal.style.display = 'block';
                }
            }
        });
    }

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', async function() {
            if (rowToDelete) {
                const plate = rowToDelete.cells[1]?.textContent.trim() || 'Unknown';
                const originalText = confirmDeleteBtn.textContent;
                confirmDeleteBtn.textContent = 'Processing...';
                confirmDeleteBtn.disabled = true;

                const violationId = rowToDelete.dataset.id;
                const success = await archiveViolation(violationId);

                confirmDeleteBtn.textContent = originalText;
                confirmDeleteBtn.disabled = false;

                if (success) {
                    triggerFrontendNotification(plate, `Violation record for ${plate} has been deleted.`);
                    if (deleteModal) deleteModal.style.display = 'none';
                    if (successModal) successModal.style.display = 'block';
                }
            }
        });
    }

    if (cancelConfirmBtn) {
        cancelConfirmBtn.addEventListener('click', function() {
            if (confirmModal) confirmModal.style.display = 'none';
            rowToConfirm = null;
        });
    }

    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', function() {
            if (deleteModal) deleteModal.style.display = 'none';
            rowToDelete = null;
        });
    }

/* ==========================================================================
       4. ARCHIVES PAGE LOGIC (archive.php)
       ========================================================================== */
    const restoreModal = document.getElementById('restore-modal');
    const confirmRestoreBtn = document.getElementById('confirm-restore-btn');
    const cancelRestoreBtn = document.getElementById('cancel-restore-btn');
    
    let rowToRestore = null;

    async function restoreViolation(archiveId) {
        try {
            const response = await fetch('restore_violation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `archive_id=${encodeURIComponent(archiveId)}`
            });
            const data = await response.json();
            return data.success;
        } catch (error) {
            console.error('Error:', error);
            return false;
        }
    }

    document.querySelectorAll('.restore-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            rowToRestore = this.closest('tr');
            if (restoreModal) restoreModal.style.display = 'block';
        });
    });

    if (confirmRestoreBtn) {
        confirmRestoreBtn.addEventListener('click', async function() {
            if (rowToRestore) {
                const plate = rowToRestore.cells[1]?.textContent.trim() || 'Unknown';
                const originalText = confirmRestoreBtn.textContent;
                confirmRestoreBtn.textContent = 'Processing...';
                confirmRestoreBtn.disabled = true;

                const archiveId = rowToRestore.dataset.id;
                const success = await restoreViolation(archiveId);

                confirmRestoreBtn.textContent = originalText;
                confirmRestoreBtn.disabled = false;

                if (success) {
                    triggerFrontendNotification(plate, `Archived record for ${plate} has been restored.`);
                    if (restoreModal) restoreModal.style.display = 'none';
                    if (successModal) successModal.style.display = 'block';
                    rowToRestore.remove();
                }
            }
        });
    }

    if (cancelRestoreBtn) {
        cancelRestoreBtn.addEventListener('click', function() {
            if (restoreModal) restoreModal.style.display = 'none';
            rowToRestore = null;
        });
    }

/* ==========================================================================
       5. UNIVERSAL DISMISSAL & MODAL OVERLAY COMPLETION MAP
       ========================================================================== */
    // Helper function to check if any operational process is ongoing
    function isSystemProcessing() {
        const buttons = [confirmResolveBtn, confirmConfirmBtn, confirmDeleteBtn, confirmRestoreBtn];
        return buttons.some(btn => btn && btn.disabled && btn.textContent.includes('Processing'));
    }

    window.addEventListener('click', function(event) {
        if (notificationPopup && notificationPopup.style.display === 'block') {
            if (!notificationContainer.contains(event.target)) {
                notificationPopup.style.display = 'none';
            }
        }

        // GUARD RULE 1: If a network action is processing, reject all backdrop click dismissals completely
        if (isSystemProcessing()) {
            return;
        }

        // GUARD RULE 2: Disabled backdrop clicking entirely for confirmation modals.
        // The user must explicitly click the "Cancel" button to go back.
        /* 
        if (resolveModal && event.target === resolveModal) resolveModal.style.display = 'none';
        if (confirmModal && event.target === confirmModal) confirmModal.style.display = 'none';
        if (deleteModal && event.target === deleteModal) deleteModal.style.display = 'none';
        if (restoreModal && event.target === restoreModal) restoreModal.style.display = 'none';
        */
    });

    // Cross-tab synchronization event engine for real-time memory rendering
    window.addEventListener('storage', function(event) {
        if (event.key === 'parksense_notifications') {
            renderNotificationDOM(JSON.parse(event.newValue || '[]'));
        }
    });

    // BIND SUCCESS DISMISSAL EXCLUSIVELY TO THE UNDERSTOOD ACTION BUTTON
    const successDismissBtn = document.getElementById('success-dismiss-btn');
    if (successDismissBtn && successModal) {
        successDismissBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // Stop propagation bubbles
            successModal.style.display = 'none';
            window.location.reload();
        });
    }
});