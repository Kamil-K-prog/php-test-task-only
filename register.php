<?php
require_once 'src/init.php';

require_once 'src/FormValidator.php';


$result = true;


// Если сюда попал уже авторизованный пользователь
// Без проверки метода, редирект в любом случае
if ($user->isLogged()) {
    header("Location: /dashboard.php");
    exit;
}

// Проверка формы, регистрация пользователя
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Получение данных с формы
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    $validationResult = FormValidator::validateRegisterForm($db, $name, $phone, $email, $password, $password_confirm);
    $data = $validationResult['data']; // Валидированные данные: прошли проверку и без лишних пробелов
    $errors = $validationResult['errors'];
    if (empty($errors)) {
        // Регистрируем пользователя
        $newUserId = $user->register($data['name'], $data['phone'], $data['email'], $data['password']);

        if ($newUserId) {
            session_regenerate_id(true);
            $_SESSION['uid'] = $newUserId;

            // Редирект на страницу профиля
            header("Location: /dashboard.php");
            exit;
        }

        // Что-то пошло не так
        $result = false;
    }
}


ob_start();
?>
    <h2 class="mb-4">Регистрация</h2>
    <form method="post" action="/register.php">
        <div class="mb-3">
            <label for="name" class="form-label">Имя</label>
            <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                   id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? '') ?>">
            <?php if (isset($errors['name'])): ?>
                <div class="invalid-feedback">
                    <?php echo htmlspecialchars($errors['name']); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Номер телефона (Российский, +7 или 8)</label>
            <input type="phone" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>"
                   id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? '') ?>">
            <?php if (isset($errors['phone'])): ?>
                <div class="invalid-feedback">
                    <?php echo htmlspecialchars($errors['phone']); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Адрес электронной почты</label>
            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                   id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>">
            <?php if (isset($errors['email'])): ?>
                <div class="invalid-feedback">
                    <?php echo htmlspecialchars($errors['email']); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Пароль</label>
            <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                   id="password" name="password">
            <?php if (isset($errors['password'])): ?>
                <div class="invalid-feedback">
                    <?php echo htmlspecialchars($errors['password']); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="password_confirm" class="form-label">Подтверждение пароля</label>
            <input type="password"
                   class="form-control <?php echo isset($errors['password_confirm']) ? 'is-invalid' : ''; ?>"
                   id="password_confirm" name="password_confirm">
            <?php if (isset($errors['password_confirm'])): ?>
                <div class="invalid-feedback">
                    <?php echo htmlspecialchars($errors['password_confirm']); ?>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Зарегистрироваться</button>

        <div class="mb-3">
            <div class="invalid-feedback">
                <?php echo $result ? "" : "Что-то пошло не так. Попробуйте снова позже." ?>
            </div>
        </div>

    </form>
<?php

$content = ob_get_clean();

require_once 'main.php';