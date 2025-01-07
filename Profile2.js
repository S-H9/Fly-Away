// Function to toggle profile popup
function toggleProfile() {
    const popup = document.getElementById('profilePopup');
    popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
}

// Function to handle sign out
function signOut() {
    // Add sign out logic here
    alert('Signing out...');
    window.location.href = 'home.html';
}

// Close popup when clicking outside
window.onclick = function(event) {
    const popup = document.getElementById('profilePopup');
    if (event.target === popup) {
        popup.style.display = 'none';
    }
}

// Handle form submission
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const name = document.getElementById('name').value;
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (newPassword !== confirmPassword) {
        alert('New passwords do not match!');
        return;
    }

    // Add your save logic here
    alert('Profile updated successfully!');
    toggleProfile();
});