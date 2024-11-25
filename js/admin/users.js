// File: js/admin/users.js

// URL Parameters Helper
function updateUrlParams(params) {
    const url = new URL(window.location.href);
    Object.keys(params).forEach(key => {
        if (params[key]) {
            url.searchParams.set(key, params[key]);
        } else {
            url.searchParams.delete(key);
        }
    });
    window.history.pushState({}, '', url);
    window.location.reload();
}

// Search functionality
let searchTimeout;
function updateSearch(value) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        updateUrlParams({ search: value, page: 1 });
    }, 500);
}

// Filter and Sort
function updateFilter(value) {
    updateUrlParams({ filter: value, page: 1 });
}

function updateSort(value) {
    updateUrlParams({ sort: value, page: 1 });
}

// View user details
async function viewUser(userId) {
    try {
        const response = await fetch(`../includes/admin/get_user_view.inc.php?user_id=${userId}`);
        if (!response.ok) throw new Error('Failed to fetch user details');
        
        const modalContent = document.querySelector('#userViewModal .modal-content');
        modalContent.innerHTML = await response.text();
        
        document.getElementById('userViewModal').classList.add('show');
        document.body.style.overflow = 'hidden';
        
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

// Delete user
async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch('../includes/admin/user_operations.inc.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete',
                user_id: userId
            })
        });

        if (!response.ok) {
            throw new Error('Failed to delete user');
        }

        const data = await response.json();
        
        if (data.success) {
            showAlert('User deleted successfully', 'success');
            
            // Find and remove the user card with animation
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
        } else {
            throw new Error(data.error || 'Failed to delete user');
        }
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

// Alert functionality
function showAlert(message, type = 'success') {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type}`;
    
    alertContainer.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.getElementById('alertContainer').appendChild(alertContainer);
    
    setTimeout(() => {
        alertContainer.classList.add('fade-out');
        setTimeout(() => alertContainer.remove(), 300);
    }, 3000);
}

// Modal functionality
function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
    document.body.style.overflow = 'auto';
}

// Close modals when clicking outside
document.addEventListener('click', (e) => {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (e.target === modal) {
            closeModal(modal.id);
        }
    });
});