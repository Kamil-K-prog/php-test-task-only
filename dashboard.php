<?php
require_once 'src/init.php';

require_once 'src/FormValidator.php';

// Выкидывает всех неавторизованных пользователей
if (!$user->isLogged()) {
    header('Location: /index.php');
}

$userData = $user->getData();
$success = true;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Получаем новые данные для обновления
    $newName = $_POST['name'];
    $newEmail = $_POST['email'];
    $newPhone = $_POST['phone'];

    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $newPasswordConfirm = $_POST['new_password_confirm'];

    $result = FormValidator::validateDashboardForm($user, $db, $newName, $newEmail, $newPhone, $currentPassword, $newPassword, $newPasswordConfirm);
    $errors = $result['errors'];
    $newData = $result['data']; // Валидированные данные


    // Здесь нет проверок и true/false, потому что проверки были пройдены при валидации (уникальность полей, соответствие паролей, т.д.)
    if (empty($errors)) {
        if (!empty($newData['name']) && $newData['name'] !== $userData['name']) {
            $success = $success && $user->updateField('name', $newData['name']);
        }
        if (!empty($newData['email']) && $newData['email'] !== $userData['email']) {
            $success = $success && $user->updateField('email', $newData['email']);
        }
        if (!empty($newData['phone']) && $newData['phone'] !== $userData['phone']) {
            $success = $success && $user->updateField('phone', $newData['phone']);
        }
        if (!empty($newData['currentPassword']) && !empty($newData['newPassword']) && !empty($newData['newPasswordConfirm'])) {
            $success = $success && $user->changePassword($newData['currentPassword'], $newData['newPassword']);
        }
    }

}

ob_start();
?>
<h2 class="mb-4">Настройки профиля</h2>
<?php if (!$success): ?>
    <div class="alert alert-danger" role="alert">
        Что-то пошло не так. Попробуйте позже.
    </div>
<?php endif; ?>

<form method="post" action="/dashboard.php">
    <!-- Имя -->
    <div class="mb-3">
        <label for="name" class="form-label">Имя</label>
        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
               id="name" name="name"
               value="<?php echo htmlspecialchars($_POST['name'] ?? ($userData['name'] ?? '')); ?>">
        <?php if (isset($errors['name'])): ?>
            <div class="invalid-feedback">
                <?php echo htmlspecialchars($errors['name']); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Телефон -->
    <div class="mb-3">
        <label for="phone" class="form-label">Телефон</label>
        <input type="text" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>"
               id="phone" name="phone"
               value="<?php echo htmlspecialchars(FormValidator::formatPhoneNumber($_POST['phone'] ?? ($userData['phone'] ?? ''))); ?>">
        <?php if (isset($errors['phone'])): ?>
            <div class="invalid-feedback">
                <?php echo htmlspecialchars($errors['phone']); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Почта -->
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
               id="email" name="email"
               value="<?php echo htmlspecialchars($_POST['email'] ?? ($userData['email'] ?? '')); ?>">
        <?php if (isset($errors['email'])): ?>
            <div class="invalid-feedback">
                <?php echo htmlspecialchars($errors['email']); ?>
            </div>
        <?php endif; ?>
    </div>

    <hr class="my-4">

    <h4 class="mb-3">Смена пароля (вам потребуется войти заново)</h4>

    <!-- Текущий пароль -->
    <div class="mb-3">
        <label for="current_password" class="form-label">Текущий пароль</label>
        <input type="password"
               class="form-control <?php echo isset($errors['current_password']) ? 'is-invalid' : ''; ?>"
               id="current_password" name="current_password">
        <?php if (isset($errors['current_password'])): ?>
            <div class="invalid-feedback">
                <?php echo htmlspecialchars($errors['current_password']); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Новый пароль -->
    <div class="mb-3">
        <label for="new_password" class="form-label">Новый пароль</label>
        <input type="password" class="form-control <?php echo isset($errors['new_password']) ? 'is-invalid' : ''; ?>"
               id="new_password" name="new_password">
        <?php if (isset($errors['new_password'])): ?>
            <div class="invalid-feedback">
                <?php echo htmlspecialchars($errors['new_password']); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Подтверждение нового пароля -->
    <div class="mb-3">
        <label for="new_password_confirm" class="form-label">Подтверждение нового пароля</label>
        <input type="password"
               class="form-control <?php echo isset($errors['new_password_confirm']) ? 'is-invalid' : ''; ?>"
               id="new_password_confirm" name="new_password_confirm">
        <?php if (isset($errors['new_password_confirm'])): ?>
            <div class="invalid-feedback">
                <?php echo htmlspecialchars($errors['new_password_confirm']); ?>
            </div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
</form>
<?php
$content = ob_get_clean();

require_once 'main.php';
?>
