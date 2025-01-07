## Fly Away Overview:
 - Fly Away software is designed to simplify and enhance the travel experience, enabling
 users to explore the world with ease. The system primarily targets travelers and tourists.
 Our software aims to reduce the complexity of travel arrangements to provide an intuitive
 platform that saves time.

## Features 

1. * registration*:
    - To create a new account, users navigate to the registration page and input required details such as username, password. The system validates the data to ensure it meets criteria, such as unique usernames. Before storing, passwords are hashed using secure algorithms (e.g., bcrypt) to enhance security, ensuring that the actual password is not saved. The user data, including the hashed password, is then securely stored in a database..

      
2. * Login*:
- The "Login" feature allows users to access their accounts by entering their username and password. When the user submits their credentials, the system checks the database for a matching username. For security, passwords are not stored in plain text; instead, they are stored as hashed values During login, the system hashes the entered password and compares it with the stored hashed version in the database. If they match, the user is authenticated and granted access. This method ensures that even if the database is compromised, the actual passwords remain secure, as the hashes are computationally difficult to reverse.
