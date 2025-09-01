<?php

require_once 'src/init.php';
require_once 'src/FormValidator.php';
require_once 'src/CaptchaValidator.php';

$error_message = '';

// Если сюда попал уже авторизованный пользователь
// Без проверки метода, редирект в любом случае
if ($user->isLogged()) {
    header("Location: /dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phoneOrEmail = $_POST['login'];
    $password = $_POST['password'];
    $smart_token = $_POST['smart-token'];

    if (!CaptchaValidator::validateCaptcha($_POST['smart-token'])) {
        $error_message = 'Пожалуйста, подтвердите, что вы не робот.';
    }
    else {
        $result = FormValidator::validateLoginForm($phoneOrEmail, $password);
        $data = $result['data'];
        $errors = $result['errors'];

        $userId = $user->login($data['login'], $_POST['password']);

        if ($userId !== false) {
            $_SESSION['uid'] = $userId;
            header("Location: /dashboard.php");
            exit;
        } else {
            $error_message = 'Неверный логин или пароль.';
        }
    }
}

ob_start();
?>

<!-- Подключение капчи -->
<script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>


<h2 class="mb-4">Вход</h2>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $error_message; ?>
    </div>
<?php endif; ?>

<form method="post" action="/login.php">
    <div class="mb-3">
        <label for="login" class="form-label">Логин</label>
        <input type="text" class="form-control" id="login" name="login"
               value="<?php echo htmlspecialchars($_POST['login'] ?? '') ?>">
        <?php if (isset($errors['login'])): ?>
            <div class="invalid-feedback">
                <?php echo htmlspecialchars($errors['login']); ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Пароль</label>
        <input type="password" class="form-control" id="password" name="password">
        <?php if (isset($errors['password'])): ?>
            <div class="invalid-feedback">
                <?php echo htmlspecialchars($errors['password']); ?>
            </div>
        <?php endif; ?>
    </div>
    <div
            id="captcha-container"
            class="smart-captcha"
            data-sitekey="<?= YANDEX_SMARTCAPTCHA_CLIENT_KEY ?>"
            style="height: 100px"
    >
    </div>

    <button type="submit" class="btn btn-primary">Войти</button>
</form>

<?php
$content = ob_get_clean();

require_once 'main.php';
?>
