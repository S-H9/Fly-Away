document.addEventListener('DOMContentLoaded', function() {
    const profilePic = document.querySelector('.profile-pic');
    const profilePopup = document.createElement('div');
    
    // Fetch user details from the backend
    function fetchUserDetails() {
        fetch('get_user_details.php')
            .then(response => response.json())
            .then(user => {
                // Update popup with user details
                profilePopup.innerHTML = `
                    <div class="profile-popup-header">
                        <img src="${user.profile_image || 'imges/img4.jpeg'}" alt="Profile Picture">
                        <div class="profile-popup-header-info">
                            <h3>${user.name || 'User'}</h3>
                            <p>${user.email || 'user@example.com'}</p>
                        </div>
                    </div>
                    <div class="profile-popup-actions">
                        <button class="edit-profile" onclick="location.href='edit_profile.php'">Edit Profile</button>
                        <button class="sign-out" onclick="location.href='logout.php'">Sign Out</button>
                    </div>
                `;
            })
            .catch(error => {
                console.error('Error fetching user details:', error);
                profilePopup.innerHTML = `
                    <div class="profile-popup-header">
                        <img src="imges/img4.jpeg" alt="Profile Picture">
                        <div class="profile-popup-header-info">
                            <h3>User</h3>
                            <p>user@example.com</p>
                        </div>
                    </div>
                    <div class="profile-popup-actions">
                        <button class="edit-profile" onclick="location.href='edit_profile.php'">Edit Profile</button>
                        <button class="sign-out" onclick="location.href='logout.php'">Sign Out</button>
                    </div>
                `;
            });
    }

    // Setup profile popup
    profilePopup.id = 'profilePopup';
    profilePopup.classList.add('profile-popup');
    document.body.appendChild(profilePopup);

    // Toggle popup visibility
    profilePic.addEventListener('click', function(event) {
        event.stopPropagation(); // Prevent immediate closing
        
        // Toggle display
        if (profilePopup.style.display === 'block') {
            profilePopup.style.display = 'none';
        } else {
            fetchUserDetails(); // Refresh user details
            profilePopup.style.display = 'block';
        }
    });

    // Close popup when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target !== profilePopup && 
            !profilePopup.contains(event.target) && 
            event.target !== profilePic) {
            profilePopup.style.display = 'none';
        }
    });
});