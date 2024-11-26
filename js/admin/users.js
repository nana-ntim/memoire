// File: js/admin/users.js

// Base paths configuration
const BASE_URL = '/Memoire'; // Update this to match your installation path
const ADMIN_INCLUDES = `${BASE_URL}/includes/admin`;

// View user details
async function viewUserDetails(userId) {
    console.log('Viewing user:', userId); // Debug log
    
    try {
        const response = await fetch(`${ADMIN_INCLUDES}/get_user_view.inc.php?user_id=${userId}`);
        console.log('Response status:', response.status); // Debug log
        
        const contentType = response.headers.get('content-type');
        console.log('Content type:', contentType); // Debug log

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const content = await response.text();
        console.log('Response content:', content); // Debug log
        
        const modalContent = document.querySelector('#userViewModal .modal-content');
        modalContent.innerHTML = content;
        
        const modal = document.getElementById('userViewModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
    } catch (error) {
        console.error('Error viewing user:', error); // Debug log
        showAlert('Failed to load user details: ' + error.message, 'error');
    }
}

// Delete user confirmation
function deleteUserConfirm(userId, userName) {
    console.log('Delete confirmation for user:', userId, userName); // Debug log
    
    if (confirm(`Are you sure you want to delete ${userName}? This action cannot be undone.`)) {
        deleteUser(userId, userName);
    }
}

// Delete user
async function deleteUser(userId, userName) {
    console.log('Deleting user:', userId); // Debug log
    
    try {
        const response = await fetch(`${ADMIN_INCLUDES}/user_operations.inc.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete',
                user_id: userId
            })
        });

        console.log('Delete response status:', response.status); // Debug log
        const data = await response.json();
        console.log('Delete response data:', data); // Debug log

        if (data.success) {
            showAlert('User deleted successfully', 'success');
            
            // Remove the user card with animation
            const userCard = document.querySelector(`[data-user-id="${userId}"]`);
            if (userCard) {
                userCard.style.opacity = '0';
                userCard.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    userCard.remove();
                    
                    // Check if we need to reload
                    const remainingCards = document.querySelectorAll('.user-card');
                    if (remainingCards.length === 0) {
                        window.location.reload();
                    }
                }, 300);
            }

            // Close modal if open
            closeModal('userViewModal');
        } else {
            throw new Error(data.error || 'Failed to delete user');
        }
    } catch (error) {
        console.error('Error deleting user:', error); // Debug log
        showAlert(error.message, 'error');
    }
}

// Alert functionality
function showAlert(message, type = 'success') {
    console.log('Showing alert:', type, message); // Debug log
    
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) {
        console.error('Alert container not found!');
        return;
    }

    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    alertContainer.appendChild(alert);
    
    setTimeout(() => {
        alert.classList.add('fade-out');
        setTimeout(() => alert.remove(), 300);
    }, 3000);
}

// Modal functionality
function closeModal(modalId) {
    console.log('Closing modal:', modalId); // Debug log
    
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    } else {
        console.error('Modal not found:', modalId);
    }
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Loaded - Initializing event listeners'); // Debug log

    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            closeModal(event.target.id);
        }
    };

    // Handle automatic alert dismissal
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade-out');
            setTimeout(() => alert.remove(), 300);
        }, 3000);
    });
});