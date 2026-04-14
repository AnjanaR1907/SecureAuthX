# SecureAuthX - Modern PHP Authentication System

SecureAuthX is a premium, cybersecurity-focused web application built with PHP and MySQL. It features a modern "glassmorphism" UI, robust security measures, and a simplified setup process designed for XAMPP environments.

## 🚀 Key Features

- **Modern UI/UX**: A clean, professional interface with a blurred white card layout, bold typography, and responsive design.
- **Dual Storage (MySQL + CSV)**: Data is stored in a MySQL database for primary operations and mirrored to a local `users.csv` file for backup.
- **Real-time Input Protection**:
    - **Formula Injection Block**: Prevents Excel-style formulas (`=`, `+`, `-`, `@`) in all fields.
    - **Link/URL Blocking**: Automatically strips common link keywords (`http`, `www.`, etc.) from inputs to prevent spam and phishing.
    - **Character Enforcement**: 
        - **First/Last Name**: Alphabets only (real-time restriction).
        - **Mobile Number**: Digits only (real-time restriction).
        - **Username**: Alphanumeric and underscores only.
- **Strict Server-side Validation**:
    - **Names**: 2-30 characters.
    - **Email**: Proper domain validation (.com, .in, .org, .net, .edu).
    - **Mobile**: 10-15 length, includes country code selection.
    - **Address**: Sanitized text with minimal special characters (`,`, `.`, `'`, `-`).
- **Bot Protection**: Integrated **Google reCAPTCHA v2** on both **Registration** and **Login** pages.
- **Advanced Security**: Uses `password_hash()` with `PASSWORD_DEFAULT` for industry-standard credential protection and `xss_clean()` for output sanitization.

## 🛠️ Setup Instructions

1.  **Install XAMPP**: Download and install XAMPP from [apachefriends.org](https://www.apachefriends.org/).
2.  **Start Services**: Open the **XAMPP Control Panel** and start **Apache** and **MySQL**.
3.  **Move Files**: Copy all project files into a new folder named `SecureAuthX` inside your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\SecureAuthX`).
4.  **Configure reCAPTCHA**:
    - Go to the [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin).
    - Register a new site (reCAPTCHA v2 "I'm not a robot" Checkbox).
    - Add `localhost` to the domains.
    - Copy your **Site Key** and **Secret Key**.
    - Open `config.php` and replace the placeholders (`YOUR_SITE_KEY_HERE` and `YOUR_SECRET_KEY_HERE`) with your actual keys.
5.  **Run the App**: Open your browser and navigate to `http://localhost/SecureAuthX/register.php`. 
    - **Note**: The application will automatically create the `secureauth_db` database and `users` table on the first run. No manual SQL import is required.
    - The `users.csv` file will be generated automatically in the project folder upon the first successful registration.

## 📁 Project Structure

- `config.php`: Database connection, table auto-initialization, and reCAPTCHA configuration.
- `register.php`: User registration with real-time validation and CAPTCHA.
- `login.php`: Secure user login with bot protection.
- `dashboard.php`: Protected user area displaying profile information.
- `logout.php`: Session termination.
- `style.css`: Modern UI styling (Glassmorphism).
- `users.csv`: CSV backup file (generated automatically).

## 🔒 Security Notes

- **Database Access**: The `users.csv` file is a plain text file. For production use, ensure this file is protected from direct web access (e.g., via `.htaccess`) or moved outside the public web root.
- **Permissions**: Ensure the `SecureAuthX` folder has write permissions so PHP can create the `users.csv` file.
- **XSS Protection**: All user outputs are passed through `xss_clean()` to prevent Cross-Site Scripting.
- **CSRF Protection**: Form actions use `htmlspecialchars($_SERVER["PHP_SELF"])` to mitigate basic injection risks.
