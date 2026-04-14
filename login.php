<?php
session_start();

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
}

require_once "config.php";

$username = $password = "";
$username_err = $password_err = $login_err = $captcha_err = "";

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

    if(empty(trim($_POST["username"]))){
        $username_err = "Required.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    if(empty(trim($_POST["password"]))){
        $password_err = "Required.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    if(empty($username_err) && empty($password_err) && empty($captcha_err)){
        $user = find_user_mysql('username', $username);
        
        if($user){
            if(password_verify($password, $user['password'])){
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $user['id'];
                $_SESSION["username"] = $user['username'];                            
                header("location: dashboard.php");
                exit;
            } else {
                $login_err = "Invalid.";
            }
        } else {
            $login_err = "Invalid.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in - SecureAuthX</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <header class="navbar">
        <div class="logo">SecureAuth<span>X</span></div>
        <nav class="nav-links">
            <a href="register.php" class="btn-login-nav">Register</a>
        </nav>
    </header>

    <main class="main-content">
        <div class="container login-container">
            <h2>Welcome Back</h2>

            <?php 
            if(!empty($login_err)) echo '<div class="alert alert-error">' . $login_err . '</div>';
            if(!empty($captcha_err)) echo '<div class="alert alert-error">' . $captcha_err . '</div>';
            if(isset($_GET['registered']) && $_GET['registered'] == 'true') echo '<div class="alert alert-success">Registration successful. Please login.</div>';
            ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="loginForm">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?php echo xss_clean($username); ?>" placeholder="Username" required class="secure-input">
                    <?php if(!empty($username_err)) echo '<span class="error-msg">'.xss_clean($username_err).'</span>'; ?>
                </div>    
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" placeholder="Password" required class="secure-input">
                        <span class="toggle-password" onclick="togglePassword('password')">Show</span>
                    </div>
                    <?php if(!empty($password_err)) echo '<span class="error-msg">'.xss_clean($password_err).'</span>'; ?>
                </div>

                <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>

                <button type="submit" class="btn-login">Log in</button>
                
                <div class="footer-link">
                    New user? <a href="register.php">Create an account &rsaquo;</a>
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
                // Block common link keywords
                const blocked = ['HYPERLINK', 'http', 'www.', '.com', '.net', '.org'];
                blocked.forEach(term => {
                    if (this.value.toUpperCase().includes(term.toUpperCase())) {
                        this.value = this.value.replace(new RegExp(term, 'gi'), '');
                    }
                });
            });
        });
    </script>
</body>
</html>
