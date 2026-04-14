<?php
require_once "config.php";

$username = $password = $confirm_password = $email = "";
$first_name = $last_name = $address1 = $address2 = $mobile = $country_code = $gender = "";
$username_err = $password_err = $confirm_password_err = $captcha_err = $general_err = "";
$first_name_err = $last_name_err = $email_err = $mobile_err = $address_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate reCAPTCHA
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array(
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $recaptcha_response
    );
    $options = array(
        'http' => array (
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $verify = file_get_contents($url, false, $context);
    $captcha_success = json_decode($verify);

    if (!$captcha_success || !$captcha_success->success) {
        $captcha_err = "Please complete the CAPTCHA correctly.";
    }

    // Validate First Name
    if(empty(trim($_POST["first_name"]))){
        $first_name_err = "Required.";
    } elseif(!preg_match("/^[a-zA-Z]{2,30}$/", trim($_POST["first_name"]))){
        $first_name_err = "Invalid.";
    } else {
        $first_name = trim($_POST["first_name"]);
    }

    // Validate Last Name
    if(empty(trim($_POST["last_name"]))){
        $last_name_err = "Required.";
    } elseif(!preg_match("/^[a-zA-Z]{1,30}$/", trim($_POST["last_name"]))){
        $last_name_err = "Invalid.";
    } else {
        $last_name = trim($_POST["last_name"]);
    }

    // Validate Username
    if(empty(trim($_POST["username"]))){
        $username_err = "Required.";
    } elseif(!preg_match("/^[a-zA-Z0-9_]{3,20}$/", trim($_POST["username"]))){
        $username_err = "Invalid.";
    } else {
        if(find_user_mysql('username', trim($_POST["username"]))){
            $username_err = "Taken.";
        } else {
            $username = trim($_POST["username"]);
        }
    }

    // Validate Email
    if(empty(trim($_POST["email"]))){
        $email_err = "Required.";
    } elseif(!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL) || !preg_match("/\.(com|in|org|net|edu)$/i", trim($_POST["email"]))){
        $email_err = "Invalid.";
    } else {
        if(find_user_mysql('email', trim($_POST["email"]))){
            $email_err = "Registered.";
        } else {
            $email = trim($_POST["email"]);
        }
    }

    // Validate Mobile
    $country_code = $_POST["country_code"];
    if(empty(trim($_POST["mobile_no"]))){
        $mobile_err = "Required.";
    } elseif(!preg_match("/^[0-9]{10,15}$/", trim($_POST["mobile_no"]))){
        $mobile_err = "Invalid.";
    } else {
        $mobile = trim($_POST["mobile_no"]);
    }

    // Validate Address
    if(empty(trim($_POST["address_line1"]))){
        $address_err = "Required.";
    } elseif(!preg_match("/^[a-zA-Z0-9\s,'.-]*$/", trim($_POST["address_line1"]))){
        $address_err = "Invalid.";
    } else {
        $address1 = trim($_POST["address_line1"]);
    }
    $address2 = trim($_POST["address_line2"]);

    // Validate Password
    if(empty(trim($_POST["password"]))){
        $password_err = "Required.";     
    } elseif(strlen(trim($_POST["password"])) < 8){
        $password_err = "Invalid.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Required.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Mismatch.";
        }
    }

    $gender = $_POST["gender"];
    
    // Check input errors before inserting
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($captcha_err) && 
       empty($first_name_err) && empty($last_name_err) && empty($email_err) && empty($mobile_err) && empty($address_err)){
        
        try {
            // 1. Save to MySQL
            $sql = "INSERT INTO users (email, username, password, first_name, last_name, address_line1, address_line2, mobile_no, country_code, gender) 
                    VALUES (:email, :username, :password, :first_name, :last_name, :address1, :address2, :mobile, :country_code, :gender)";
             
            $stmt = $pdo->prepare($sql);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt->execute([
                ':email' => $email,
                ':username' => $username,
                ':password' => $hashed_password,
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':address1' => $address1,
                ':address2' => $address2,
                ':mobile' => $mobile,
                ':country_code' => $country_code,
                ':gender' => $gender
            ]);
            
            $new_id = $pdo->lastInsertId();
            $created_at = date('Y-m-d H:i:s');

            // 2. Save to CSV
            $csv_data = [
                $new_id, $email, $username, $hashed_password, $first_name, $last_name, $address1, $address2, $mobile, $country_code, $gender, $created_at
            ];
            save_to_csv($csv_data);

            header("location: login.php?registered=true");
            exit;

        } catch (PDOException $e) {
            $general_err = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account - SecureAuthX</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <header class="navbar">
        <div class="logo">SecureAuth<span>X</span></div>
        <nav class="nav-links">
            <a href="login.php" class="btn-login-nav">Log in</a>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <h2>Create an Account</h2>

            <?php 
            if(!empty($general_err)) echo '<div class="alert alert-error">' . $general_err . '</div>';
            if(!empty($captcha_err)) echo '<div class="alert alert-error">' . $captcha_err . '</div>';
            ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="registerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="<?php echo xss_clean($first_name); ?>" placeholder="First Name" required class="secure-input">
                        <?php if(!empty($first_name_err)) echo '<span class="error-msg">'.xss_clean($first_name_err).'</span>'; ?>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="<?php echo xss_clean($last_name); ?>" placeholder="Last Name" required class="secure-input">
                        <?php if(!empty($last_name_err)) echo '<span class="error-msg">'.xss_clean($last_name_err).'</span>'; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo xss_clean($email); ?>" placeholder="Email" required class="secure-input">
                        <?php if(!empty($email_err)) echo '<span class="error-msg">'.xss_clean($email_err).'</span>'; ?>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo xss_clean($username); ?>" placeholder="Username" required class="secure-input">
                        <?php if(!empty($username_err)) echo '<span class="error-msg">'.xss_clean($username_err).'</span>'; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" placeholder="Password" required class="secure-input">
                            <span class="toggle-password" onclick="togglePassword('password')">Show</span>
                        </div>
                        <?php if(!empty($password_err)) echo '<span class="error-msg">'.xss_clean($password_err).'</span>'; ?>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required class="secure-input">
                            <span class="toggle-password" onclick="togglePassword('confirm_password')">Show</span>
                        </div>
                        <?php if(!empty($confirm_password_err)) echo '<span class="error-msg">'.xss_clean($confirm_password_err).'</span>'; ?>
                    </div>
                </div>

                <div class="form-group full">
                    <label>Address Line 1</label>
                    <input type="text" name="address_line1" value="<?php echo xss_clean($address1); ?>" placeholder="Address Line 1" required class="secure-input">
                    <?php if(!empty($address_err)) echo '<span class="error-msg">'.xss_clean($address_err).'</span>'; ?>
                </div>

                <div class="form-group full">
                    <label>Address Line 2 (optional)</label>
                    <input type="text" name="address_line2" value="<?php echo xss_clean($address2); ?>" placeholder="Address Line 2" class="secure-input">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <div class="mobile-input-group" style="display: flex; gap: 5px;">
                            <select name="country_code" style="width: 80px;">
                                <option value="+91" <?php echo ($country_code == '+91') ? 'selected' : ''; ?>>+91 (IN)</option>
                                <option value="+1" <?php echo ($country_code == '+1') ? 'selected' : ''; ?>>+1 (US)</option>
                                <option value="+44" <?php echo ($country_code == '+44') ? 'selected' : ''; ?>>+44 (UK)</option>
                                <option value="+61" <?php echo ($country_code == '+61') ? 'selected' : ''; ?>>+61 (AU)</option>
                                <option value="+971" <?php echo ($country_code == '+971') ? 'selected' : ''; ?>>+971 (UAE)</option>
                            </select>
                            <input type="tel" name="mobile_no" value="<?php echo xss_clean($mobile); ?>" placeholder="Mobile Number" required style="flex: 1;" class="secure-input">
                        </div>
                        <?php if(!empty($mobile_err)) echo '<span class="error-msg">'.xss_clean($mobile_err).'</span>'; ?>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <div class="gender-options">
                            <label class="gender-option">
                                <input type="radio" name="gender" value="male" <?php echo ($gender == 'male' || $gender == '') ? 'checked' : ''; ?>> Male
                            </label>
                            <label class="gender-option">
                                <input type="radio" name="gender" value="female" <?php echo ($gender == 'female') ? 'checked' : ''; ?>> Female
                            </label>
                            <label class="gender-option">
                                <input type="radio" name="gender" value="prefer_not_to_say" <?php echo ($gender == 'prefer_not_to_say') ? 'checked' : ''; ?>> Prefer not to say
                            </label>
                        </div>
                    </div>
                </div>

                <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>

                <button type="submit" class="btn-register">Register</button>

                <div class="footer-link">
                    Already have an account? <a href="login.php">Log in &rsaquo;</a>
                </div>
            </form>
        </div>
    </main>
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const btn = input.nextElementSibling;
            if (input.type === "password") {
                input.type = "text";
                btn.textContent = "Hide";
            } else {
                input.type = "password";
                btn.textContent = "Show";
            }
        }

        // Prevent formula/link injection as user types
        document.querySelectorAll('.secure-input').forEach(input => {
            input.addEventListener('input', function(e) {
                // Block Excel-style formulas starting with =, +, -, @
                if (this.value.startsWith('=') || this.value.startsWith('+') || this.value.startsWith('-') || this.value.startsWith('@')) {
                    this.value = this.value.substring(1);
                }
                // Block common link keywords (except for email field)
                if (this.name !== 'email') {
                    const blocked = ['HYPERLINK', 'http', 'www.', '.com', '.net', '.org'];
                    blocked.forEach(term => {
                        if (this.value.toUpperCase().includes(term.toUpperCase())) {
                            this.value = this.value.replace(new RegExp(term, 'gi'), '');
                        }
                    });
                }

                // Specific restrictions for First Name and Last Name (Alphabets only)
                if (this.name === 'first_name' || this.name === 'last_name') {
                    this.value = this.value.replace(/[^a-zA-Z]/g, '');
                }

                // Specific restrictions for Username (Letters, numbers, underscore only)
                if (this.name === 'username') {
                    this.value = this.value.replace(/[^a-zA-Z0-9_]/g, '');
                }

                // Specific restrictions for Mobile Number (Digits only)
                if (this.name === 'mobile_no') {
                    this.value = this.value.replace(/[^0-9]/g, '');
                }
            });
        });
    </script>
</body>
</html>
