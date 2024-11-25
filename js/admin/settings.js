// File: js/admin/settings.js

async function cleanUnusedFiles() {
    if (!confirm('Clean unused files? This will remove any files not referenced in the database.')) {
        return;
    }

    const button = event.target;
    const originalText = button.textContent;

    try {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cleaning...';

        const response = await fetch('../includes/admin/maintenance.inc.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clean_files'
        });

        const data = await response.json();

        if (data.success) {
            showAlert(`Cleanup completed: ${data.cleaned} files removed (${data.space_freed} freed)`, 'success');
        } else {
            throw new Error(data.error || 'Operation failed');
        }

    } catch (error) {
        showAlert(error.message, 'error');
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

async function optimizeDatabase() {
    if (!confirm('Optimize database? This may take a while for large databases.')) {
        return;
    }

    const button = event.target;
    const originalText = button.textContent;

    try {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Optimizing...';

        const response = await fetch('../includes/admin/maintenance.inc.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=optimize_db'
        });

        const data = await response.json();

        if (data.success) {
            showAlert(`Optimization completed: ${data.optimized} tables optimized`, 'success');
        } else {
            throw new Error(data.error || 'Operation failed');
        }

    } catch (error) {
        showAlert(error.message, 'error');
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

async function createBackup() {
    if (!confirm('Create system backup? This may take a while.')) {
        return;
    }

    const button = event.target;
    const originalText = button.textContent;

    try {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Backup...';

        const response = await fetch('../includes/admin/maintenance.inc.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=create_backup'
        });

        const data = await response.json();

        if (data.success) {
            showAlert(`Backup created successfully at ${data.backup_location}`, 'success');
        } else {
            throw new Error(data.error || 'Operation failed');
        }

    } catch (error) {
        showAlert(error.message, 'error');
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

async function confirmReset() {
    const adminPassword = prompt('WARNING: This will reset all system data. Enter your admin password to confirm:');
    if (!adminPassword) return;

    const button = event.target;
    const originalText = button.textContent;

    try {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting...';

        const response = await fetch('../includes/admin/maintenance.inc.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=reset_system&admin_password=${encodeURIComponent(adminPassword)}`
        });

        const data = await response.json();

        if (data.success) {
            showAlert('System reset completed successfully. Backup created at: ' + data.backup_location, 'success');
            setTimeout(() => window.location.reload(), 2000);
        } else {
            throw new Error(data.error || 'Operation failed');
        }

    } catch (error) {
        showAlert(error.message, 'error');
    } finally {
        button.disabled = false;
        button.textContent = originalText;
    }
}

function showAlert(message, type = 'success') {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type}`;
    
    alertContainer.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(alertContainer);
    
    setTimeout(() => {
        alertContainer.classList.add('fade-out');
        setTimeout(() => alertContainer.remove(), 300);
    }, 3000);
}