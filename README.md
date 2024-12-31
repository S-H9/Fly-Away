## Fly Away Overview:
 - Fly Away software is designed to simplify and enhance the travel experience, enabling
 users to explore the world with ease. The system primarily targets travelers and tourists.
 Our software aims to reduce the complexity of travel arrangements to provide an intuitive
 platform that saves time.

## Features 

1. *User Login*:
- Users provide their username and password to log in.

- Authentication is performed by validating the provided credentials against the stored hashed passwords in the database.

2. *Secure Password Handling*:
- Passwords are hashed being stored in the database.

- Salting ensures that even users with the same password have unique hash values.

- The system includes measures to prevent brute-force attacks, such as rate-limiting login attempts.

3. *Forgot Password*:
- Users can request a password reset link by entering their registered email address.

- A secure, time-sensitive token is generated and sent to the user’s email.

- The user can reset their password via the link.

4. *Account Recovery*:
- Provides an alternate recovery mechanism, such as answering security questions or multi-factor authentication.
