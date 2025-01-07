# Fly Away Wep Application
## Fly Away Overview:
 - Fly Away software is designed to simplify and enhance the travel experience, enabling
 users to explore the world with ease. The system primarily targets travelers and tourists.
 Our software aims to reduce the complexity of travel arrangements to provide an intuitive
 platform that saves time.
## Installation
## Usage
## Features 
**1- Registration:**
- To create a new account, users navigate to the registration page and input required details such as username, password and Email. The system validates the data to ensure it meets criteria, such as unique usernames. Before storing, passwords are hashed using secure algorithms (e.g., bcrypt) to enhance security, ensuring that the actual password is not saved. The user data, including the hashed password, is then securely stored in a database..
 2- Login:
- The "Login" feature allows users to access their accounts by entering their username and password. When the user submits their credentials, the system checks the database for a matching username. For security, passwords are not stored in plain text; instead, they are stored as hashed values During login, the system hashes the entered password and compares it with the stored hashed version in the database. If they match, the user is authenticated and granted access. This method ensures that even if the database is compromised, the actual passwords remain secure, as the hashes are computationally difficult to reverse.
## 3- Flight Booking:
- The "Flight Booking" feature allows users to search for flights, select their desired flight, and reserve an appropriate seat. Users input details such as travel dates, destination, and passenger information, which are validated by the system. Once a flight is selected, users can choose a seat from the available options displayed on a seat map. After confirming the reservation, the system stores the booking details, including the passenger's name, flight information, and seat number, in a secure database.This ensures a seamless and secure booking experience, with all details securely saved for future reference or modifications.
## 4- Flight History:
- The "Flight History" feature enables users to track and manage all their past flights in one convenient place. Each time a user completes a flight booking, the system automatically records details such as flight number, flight name, departure and arrival times, destinations, seat number, and ticket class. This data is securely stored in the database and can be accessed through the user's profile. Users can view their flight history to track their travel patterns.
## Contributing
## License
