// Profile2.js
function toggleProfile() {
    const popup = document.getElementById('profilePopup');
    popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
}

function signOut() {
    // Send to logout.php which will handle the session destruction
    window.location.href = 'logout.php';
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

    alert('Profile updated successfully!');
    toggleProfile();
});

// Add automatic logout on page close
window.addEventListener('beforeunload', function(e) {
    // Make synchronous request to logout
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'auto_logout.php', false); // false makes it synchronous
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send();
});

// Also logout on tab close
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden') {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'auto_logout.php', false);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send();
    }
});