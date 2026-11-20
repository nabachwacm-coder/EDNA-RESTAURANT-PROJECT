PERISSOS EVENTS LTD. - INTEGRATED PUBLIC WEBSITE AND PHP/MYSQL APPLICATION
============================================================================

MAIN PROJECT FOLDER
-------------------
Perissos_Public_Website_HTML_Stage

This single folder contains the public informational website and its PHP/MySQL
application backend. The supplied public HTML pages, existing visual design,
logo and responsive styles are retained. The CSS file includes additional
application styles appended for login, registration and dashboards.

PUBLIC WEBSITE PAGES
--------------------
index.html       Home page, organisation overview, mission, banner and links to app
about.html       Organisational background and examination case-study narrative
services.html    Catering and event services
leadership.html  Leadership and functional team roles
contact.html     Contact channels and public inquiry form

FRONTEND ASSETS
---------------
assets/css/style.css       Public site styles plus dashboard/application styles
assets/js/script.js        Responsive menu, current year, image fallback and
                           client-side contact form validation
assets/images/             Perissos logo

PHP APPLICATION
---------------
login.php                  Login with password_verify() and PHP sessions
register.php               Customer and supplier registration with password_hash()
supplier_application_status.php  Supplier approval status and next steps
admin/users.php             Existing administrator creates additional admin accounts
logout.php                 Secure POST logout
process_contact.php        Server-side validation and inquiry storage
contact_success.php        Contact form success page
setup_admin.php            One-time first administrator creation page
create_database.php        Creates the database (lecture demonstration)
create_tables.php          Creates the application tables (lecture demonstration)

customer/                  Customer dashboard, request CRUD and quotation responses
admin/                     Administrator dashboard, request CRUD, quotations,
                           contact messages, reports and CSV export
config/                    MySQL connection and application/session configuration
includes/                  Shared PHP functions and dashboard header/footer

database/                  SQL database import file
uploads/                    Reserved for future user file uploads

database/perissos_events_exam_db.sql is the database script used by this project.
It creates/selects perissos_events_exam_db and creates the required tables.

INSTALLATION WITH XAMPP
-----------------------
1. Extract this folder into:
   C:\xampp\htdocs\Perissos_Public_Website_HTML_Stage

2. Start Apache and MySQL in the XAMPP Control Panel.

3. Open http://localhost/phpmyadmin/

4. Click Import, select:
   C:\xampp\htdocs\Perissos_Public_Website_HTML_Stage\database\perissos_events_exam_db.sql
   then click Go.

   Alternative (to demonstrate the lecture steps):
   Visit http://localhost/Perissos_Public_Website_HTML_Stage/create_database.php
   then visit http://localhost/Perissos_Public_Website_HTML_Stage/create_tables.php

5. Open the public website:
   http://localhost/Perissos_Public_Website_HTML_Stage/

6. Create the first administrator:
   http://localhost/Perissos_Public_Website_HTML_Stage/setup_admin.php
   Enter your own full name, email, phone and a strong password.

7. IMPORTANT: after successful administrator creation, delete setup_admin.php
   from the project folder. Do not leave the one-time setup page on a live server.

8. Log in:
   http://localhost/Perissos_Public_Website_HTML_Stage/login.php

DEFAULT LOCAL MYSQL SETTINGS
----------------------------
Server: localhost
Username: root
Password: empty (usual default XAMPP configuration)
Database: perissos_events_exam_db

If your local MySQL root account has a password, update config/connect.php and
config/conn.php. For public hosting, do not use the root account; create a
separate database user with a strong password and configure HTTPS/backups.

HOW TO NAVIGATE THE APPLICATION
-------------------------------
PUBLIC VISITOR:
1. Open the homepage.
2. Navigate to About & Case Study, Services, Leadership and Contact & Support.
3. Use Login or Request a quote to enter the web application.
4. The public inquiry form submits to process_contact.php and stores messages
   in the contact_messages table after server-side validation.

CUSTOMER:
1. Open register.php and choose Customer.
2. Complete registration. The customer is signed in automatically and redirected to customer/dashboard.php.
3. Use New event request to submit a catering request.
4. Submit an event request (CREATE).
5. View your requests and their status (READ).
6. Edit a request while it is Pending review (UPDATE).
7. Delete a pending request after a confirmation prompt (DELETE).
8. View quotations and accept, decline or request a revision.

ADMINISTRATOR:
1. Open setup_admin.php to create the first administrator. After creation, the system signs the administrator in and opens admin/dashboard.php.
2. Delete setup_admin.php immediately after the first administrator is created.
3. Log in through login.php on later visits.
3. Open the administrator dashboard.
4. Open Requests to search, filter and view customer requests (READ).
5. Edit request details/status (UPDATE).
6. Delete obsolete requests after confirmation (DELETE).
7. Prepare and send a quotation to the customer's dashboard.
8. Open Messages to review and update public inquiry statuses.
9. Open Reports, filter by status/date and export CSV.

SUPPLIER:
1. Open register.php and choose Supplier.
2. Submit the business name, supply category and business location.
3. The application is saved as pending and the browser opens supplier_application_status.php.
4. An administrator approves the supplier in admin/suppliers.php.
5. After approval, the supplier logs in and is redirected to supplier/dashboard.php.
6. Pending suppliers cannot access the supplier dashboard.

ADMIN ACCOUNT POLICY
--------------------
- Public registration is only for customers and suppliers.
- The first administrator is created through setup_admin.php.
- Existing administrators can create additional administrator accounts in admin/users.php.

IMPORTANT NOTES
---------------
- Use the website through http://localhost/... and not by double-clicking files.
- The public pages are HTML5. PHP is used for authentication, database handling,
  request management, quotations, messages and reports.
- The system uses procedural MySQLi, as shown in the supplied PHP-MySQL lecture
  slides. User-entered values are handled with prepared statements.
- Customer passwords are stored using password_hash() and checked with
  password_verify().
- Customers can only access their own requests and quotations.
- The first-release app includes customer and administrator roles. Supplier and
  employee dashboards are future extensions.
- The contact form has JavaScript validation and server-side PHP validation.
- Remote Unsplash food/event images require internet access. Replace them with
  approved local Perissos photographs before public production deployment.
- The content avoids claiming an unverified founding date, company history,
  named leadership biographies or awards. Add verified company details as supplied.
