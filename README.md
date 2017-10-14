Simple web site
=================================

This code is educational example a simplistic website with dynamic content, AJAX, CSS animations and session keeping (for authorization of users).

Shown here is a concept of for web-site that could be used by Croatian producers of home-grown produces to advertise their products. The idea is to provide a simplistic UI that allows new users (producers) to register and add their own products to the page, then have the customers browse the page for whatever they need.

Implementation of this concept encompases both front-end, which should be simple and intuitive for a user to interact with the page, but also a back-end which provides and holds content such as information about registered users or their products.


##Front-end
----------------------
Front end was entirely realized through combination of HTML/CSS/JavaScript. However content was dynamic, controlled by PHP snippets embedded in HTML backbone. Use of JavaScript was minimized to almost exclusively AJAX calls. Few animations that are present were instead implemented through CSS. Design of page is also split in desktop and mobile, and each one is loaded based on the size of the screen of a device rendering the page. Although small, difference is only in appearance of drop-down menus and tool-tips which, on desktops expand on hovering above them, while on phones they are automatically expanded.

![alt tag](https://hsr.duckdns.org/images/etrznica/desktop.png)
![alt tag](https://hsr.duckdns.org/images/etrznica/phone.png)

###Functionality
Last version of web-site offered following functionality:

* **_Registration of new users:_** Registration form asks users for email and password together with some contact information used for customers when browsing their products
* **_Authorization of users:_** Authorization is required so that user can add new products to system, edit its own (and only its own) product infos and change account settings
* **_Adding and editing products:_** Once registered and authorized, user is able to submit new products to the system (product consists of 'name', 'description' and 'picture')
* **_Change account information:_** Once registered and authorized, user is able of changing its account information (being 'email', 'password' and various contact information)
* **_Report inappropriate products:_** Each user(registered or not) is able to report certain product as inappropriate, which logs that product in DB for administrator to inspect
* **_Account password reset:_** Registered users can issue a password-reset email in case they are unable to login with their existing password.


##Back-end
----------------------
Back end portion of this project consists of a MySQL database and a number PHP scripts to manipulate content of database and manage user sessions. A session for user is generated once user logs in and is kept alive for a limited amount of time or until user requests logout.



###Security consideration

* **_Encryption:_** Website is supposed to handle sensitive user data which reacquired a web server hosting it to support TLS encryption

* **_Encryption/hashing of sensitive data:_** To ensure security of user data, sensitive parts were never stored in plain text. Email was encrypted using AES with a key stored on the server, and in encrypted form stored in cookies or DB. Password, on the other hand, was salted and hashed using PHP's password_hash() function and in that form stored in DB. 

* **_Session management:_** Instead of using standard session module of PHP, session management is implemented through use of cookies. As suggested by php.net manual, cookies are saved with 'secure' and 'httponly' flags set TRUE so that they are only accessible through HTTP protocol over secure connection. After successful login, a session is generated for user. It consists of an AES-encrypted username (email) cookie, and MD5 hash of current time, used as unique identifier of session(SUID). SUID is stored in a database, associated with belonging email. As cookies can be tampered with, each time user requests a content that requires authorization, server reads cookie content and verifies encrypted email and cookie hash against entry in DB. In order for user to hijack someone else's session it would need to know AES-encrypted version of other person's email and the exact time of login (in order to produce matching MD5 hash), being extremely unlikely.

* **_File upload:_** Users are allowed to upload pictures to the web site. This is a possible entry point for unwanted files(scripts) to reach server and compromise it. To ensure files are indeed pictures PHP script first verifies extension of uploaded file and then tries to read image information through getimagesize() function which should fail in case file is not image content. Lastly, uploaded image is stored with randomly generated filename in a folder on the server which has no execution permission.

* **_Password reset:_** Once user requests password reset, server generates a token (hash) associated with email of account. This token is embedded in a link being emailed to user who can open that link for next 10 minutes (otherwise it expires and new one needs to be requested). Once link is opened, server validates token from link and offers 3 fields to user, one for repeating e-mail and two for password (input & repeat). User is supposed to enter email associated with this link/token and new password which is saved only if email and token combination match.

* **_Data verification:_** User input data is checked for unsupported values on both front-end (JavaScript form verification) and back-end (PHP input parsing).

* **_Prepared statements:_** All database transactions are handled exclusively through prepared statement to mitigate possibility of SQL injection


