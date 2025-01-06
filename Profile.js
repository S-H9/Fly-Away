// Profile popup functionality
function toggleProfile()
{
    const popup = document.getElementById('profilePopup');
    popup.style.display = popup.style.display === 'none' ? 'block' : 'none';
}

function saveProfile()
{
    const name = document.getElementById('userName').value;
    const email = document.getElementById('userEmail').value;
    
    // Add your save logic here
    // For example, sending to a server or storing in localStorage
    localStorage.setItem('userName', name);
    localStorage.setItem('userEmail', email);
    
    alert('Profile updated successfully!');
    toggleProfile();
}

function logout()
{
    // Add your logout logic here
    localStorage.clear();
    window.location.href = 'index.php'; // Redirect to login page
}

// Load profile data when page loads
window.onload = function()
{
    const name = localStorage.getItem('userName');
    const email = localStorage.getItem('userEmail');
    
    if (name) document.getElementById('userName').value = name;
    if (email) document.getElementById('userEmail').value = email;
};