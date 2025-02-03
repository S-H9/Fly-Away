# Fly Away Wep Application
## Fly Away Overview:
 - Fly Away software is designed to simplify and enhance the travel experience, enabling
 users to explore the world with ease. The system primarily targets travelers and tourists.
 Our software aims to reduce the complexity of travel arrangements to provide an intuitive
 platform that saves time.
## Installation
- Download **"XAMPP"** according to the device version, Windows, Mac, etc 
Version (8.2.12 / PHP 8.2.12) Size (149 Mb)
First Method: Using XAMPP
### 1-Download XAMPP:
- Download the **"XAMPP"** version suitable for your device (Windows, Mac, etc.).
- Version: 8.2.12 / PHP 8.2.12
- Size: 149 MB
- Link: **https://www.apachefriends.org/download.html**
### 2-Start XAMPP Services:
- Open XAMPP and press the **"Start"** button for both **"Apache"** and **"MySQL"** services.
### 3-Create a Database:
- Press the **"Admin"** button under MySQL to open the **"phpMyAdmin"** page.
- Create a new database by clicking **"New"** and name it **"Fly_away"**.
- Paste the following code into the SQL editor and press the **"Start"** or **"Execute"** button:

Link: **https://www.apachefriends.org/download.html**
- Press the **"Start"** button in **"Apache"** and also in **"Mysql"**.
- Press the **"Admin"** button in **"Mysql"** to open a page for the database **"phpMyAdmin"** Create a database **"New"** Name the database **"Fly_away"** and place the sent code:
  
    CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP );
  
And press the **"Start or Execute"** button.
- Press the **"Explorer"** button and place the **"Fly away"** files in the **"htdocs"** file.
- Press the **"Admin"** button in **"“Apache"** and the web page will open with you.
### Add Project Files:
- Press the **"Explorer"** button in XAMPP.
- Place the project files (Fly Away) in the **"htdocs"** folder.
### Run the Application:
- Press the **"Admin"** button under Apache to open the application’s web page in your browser.
### Second Method: Using the Link
- Ensure the XAMPP server is running.
- Open the application directly using this link in any browser:
- Link: flyaway-juc.ddnsking.com
## Usage
## Features 
### 1-Registration:
- To create a new account, users navigate to the registration page and input required details such as username, password and Email. The system validates the data to ensure it meets criteria, such as unique usernames. Before storing, passwords are hashed using secure algorithms (e.g., bcrypt) to enhance security, ensuring that the actual password is not saved. The user data, including the hashed password, is then securely stored in a database.
### 2-Login:
- The "Login" feature allows users to access their accounts by entering their username and password. When the user submits their credentials, the system checks the database for a matching username. For security, passwords are not stored in plain text; instead, they are stored as hashed values During login, the system hashes the entered password and compares it with the stored hashed version in the database. If they match, the user is authenticated and granted access. This method ensures that even if the database is compromised, the actual passwords remain secure, as the hashes are computationally difficult to reverse.
### 3-Flight Booking:
- The "Flight Booking" feature allows users to search for flights, select their desired flight, and reserve an appropriate seat. Users input details such as travel dates, destination, and passenger information, which are validated by the system. Once a flight is selected, users can choose a seat from the available options displayed on a seat map. After confirming the reservation, the system stores the booking details, including the passenger's name, flight information, and seat number, in a secure database.This ensures a seamless and secure booking experience, with all details securely saved for future reference or modifications.
### 4-Flight History:
- The "Flight History" feature enables users to track and manage all their past flights in one convenient place. Each time a user completes a flight booking, the system automatically records details such as flight number, flight name, departure and arrival times, destinations, seat number, and ticket class. This data is securely stored in the database and can be accessed through the user's profile. Users can view their flight history to track their travel patterns.
### 5-Booking Management: 
- The "Booking Management" feature allows users to view, or cancel their flight reservations. Users can access their bookings through their profile, where the system displays all active reservations.Cancellation requests are processed according to the airline's policies, with refunds All changes are updated in real-time and saved in the database.
### 6-Flight Booking Filter:
- The "Flight Booking Filter" feature helps users streamline their flight search by applying various criteria, such as departure time, arrival time, airline, ticket class, price range, and layovers. After users enter their preferences, the system filters the available flights and displays the most relevant options. This feature enhances the user experience by making the flight search faster and more tailored to individual needs.
### 7-Customer Support: 
- The "Customer Support" feature offers users assistance with any issues or questions related to their accounts, bookings, or other services. Support is available through multiple channels, such as  email, or phone, All support interactions are logged securely for quality assurance and future reference.
### 8-Payment Options: 
- The "Payment Options" feature enables users to complete their transactions securely and conveniently. Users can choose from multiple payment methods, such as credit/debit cards, digital wallets. The system ensures all payment details are encrypted and processed securely using payment gateways compliant with industry standards. After a successful payment, a confirmation receipt is generated and sent to the user, while the booking details are updated in the database.
### 9-Flight Management: 
- The "Flight Management" feature is designed for administrators to manage the airline’s flight schedules and availability. It allows admins to add, update, or remove flights, configure seat availability, and adjust pricing dynamically. The system ensures real-time updates so users see the latest information when searching for flights. Flight data, including schedules, routes, and fare details, is stored securely in the database, enabling efficient operations and accurate user interactions.
### 10-Quick Searching for a Flight: 
- The "Quick Searching for a Flight" feature allows users to swiftly find flights based on minimal input. Users can enter essential details like departure and destination cities, travel dates. The system instantly processes the query. This feature prioritizes speed and simplicity, ensuring users can quickly find and book flights without navigating complex filters or forms.
### 11-User Profile:
- The "User Profile" feature provides users with a personalized space to manage their account and preferences. Users can view and update their personal information, such as name, email, and contact details. The profile also serves as a central hub for accessing flight history, active bookings, payment methods, and notification settings. All updates to the profile are securely validated and saved, ensuring data consistency and user convenience.
### 12-Flights Dashboard: 
- The "Flights Dashboard" feature gives users insights into their travel patterns and booking habits. Users can view data such as the total number of flights booked. This feature helps users plan future trips more effectively and track their travel history in a meaningful way.
## Contributing
## License
