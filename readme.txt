# Turf Management System

## Overview
The **Turf Management System** is a web-based application designed to streamline the management of sports turfs. It allows turf owners to manage bookings, view and edit turf details, and provide a seamless experience for customers to book turfs for various sports and activities. This system is built to enhance the user experience while simplifying administrative tasks for turf owners.

## Features

### For Customers
- **Browse Available Turfs**: View detailed information about turfs, including location, capacity, and available sports.
- **Online Booking**: Book a turf for specific sports, dates, and times.
- **Amenity Selection**: Choose additional amenities like parking, lighting, and water facilities during booking.
- **Responsive Design**: Access the system on mobile, tablet, or desktop seamlessly.
- **Invoice Generation**: Can download invoice after payment
- **Booking History**: Can View the booking history

### For Turf Owners/Admins
- **Add and Manage Turfs**: Add new turfs, update details, and manage time slots.
- **Booking Management**: View and handle customer bookings.
- **Customizable Amenities**: Set and update available amenities and their prices.
- **Detailed Reporting**: Track bookings and usage statistics.

## Technologies Used
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap
- **Backend**: PHP
- **Database**: MySQL
- **Other Tools**: Font Awesome (Icons), JS Libraries for interactivity

## Installation

1. **Set Up the Environment:**
   - Install a local server (e.g., XAMPP, WAMP, or MAMP).

2. **Set Up the Database:**
   - Import the SQL file provided in the `database` folder into your MySQL server.
   - Update the database connection settings in the `config.php` file.

3. **Run the Application:**
   - Place the project folder in your web server directory (e.g., `htdocs` for XAMPP).
   - Start your web server and open the application in your browser.

## Project Structure
```
Turf-Management-System/
|-- fpdf/        		# Invoice generation Tool
|-- assets/         		# Images and static assets
|-- css                 	# Stylesheets
|-- js                  	# JavaScript files
|-- database/            	# SQL file for database setup
|-- includes/            	# Reusable PHP components
|-- index.php            	# Homepage
|-- about.html         	# About Us page
|-- booking.php          	# Booking management
|-- adminedit.php        	# Admin editing turfs
|-- adminadd.php        	# Admin adding turfs
|-- admin.php        	# Admin management interface
|-- config.php           	# Database configuration
```

## Future Enhancements
- Add payment gateway integration for online payments.
- Implement user profiles management.
- Introduce rating and reviews for turfs.
- Add a mobile application for a better user experience.


## Team Members
- **Name1**: Project Lead
- **Name2**: Backend Developer
- **Name3**: Frontend Developer

## Acknowledgments
- Faculty Advisor: Dr. [Advisor Name]
- Institution: [College]

---
Thank you for reviewing our project. We hope you find the **Turf Management System** helpful and innovative.

