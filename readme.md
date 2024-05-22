## Who is this for

### Developers that don't want to build a website on a website
Your frontend, Your backend, my CMS. Here you can build your website and attach my CMS like an appendage. No subscription fee. No account creation. Just code.

### Developers that want a simple onboarding with an approval system.
You give the client the request registration link. They fill the form and it sends an email to you. 

You approve? It sends an email to them and the email sends the client to register.




# Installation

### Prerequisites

- PHP >= 7.4
- Composer

### Steps

1. **Clone the repository:**

2. **composer install**

3. **Create an app password for the email you will be sending from**
https://knowledge.workspace.google.com/kb/how-to-create-app-passwords-000009237

4. **Add the following environment variables to you machine**
```sh
CMS_ADMIN_EMAIL = your-admin-email@example.com
CMS_EMAIL = your-email@example.com
CMS_EMAIL_PW = your-email-app-password
CMS_APPNAME = "Your App Name"
CMS_BASE_URL = http://localhost:8000 or http://yourdomain.com
```

5. **Run init_db.php** 
```sh
php cms/init_db.php
```
6. **Run add_tag.php for each post type the client will be able to upload to**
```sh
php add_tag.php "YourTagName"
```

6. **(development) Launch development server** 
```sh
php -S localhost:8000
```



