<?php

class FormValidator
{
    /**
     * Проверяет форму регистрации. Возвращает пустой массив, или массив ошибок формата ['field' => 'error']
     */
    public static function validateRegisterForm(DBWorker $db, string $name, string $phone, string $email, string $password, string $password_confirm): array
    {
        $result = [
            'data' => [],
            'errors' => [],
        ];

        $name = static::normalizeSpaces($name);
        $result['data']['name'] = $name;
        if (empty($name)) {
            $result['errors']['username'] = 'Вы не ввели своё имя';
        } elseif (!preg_match(NAME_PATTERN, $name)) {
            $result['errors']['username'] = 'Введите корректное имя (допустимы буквы, пробелы и дефисы)';
        }


        $phone = static::normalizeSpaces($phone);
        $cleaned = static::cleanPhoneNumber($phone);
        $result['data']['phone'] = $cleaned;
        if (empty($phone)) {
            $result['errors']['phone'] = "Вы не ввели номер телефона";
        } elseif (!preg_match(PHONE_PATTERN, $cleaned)) {
            $result['errors']['phone'] = "Введите корректный номер телефона (например, +79991234567 или 89991234567)";
        } else {
            $sql = "SELECT EXISTS(SELECT 1 FROM users WHERE phone = ?)";
            $stmt = $db->execute($sql, [$cleaned]);
            if ((bool)$stmt->fetchColumn()) {
                $result['errors']['phone'] = 'Этот номер телефона уже зарегистрирован.';
            }
        }

        $email = static::normalizeSpaces($email);
        $result['data']['email'] = $email;
        if (empty($email)) {
            $result['errors']['email'] = 'Вы не ввели email';
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $result['errors']['email'] = "Введите корректный адрес электронной почты, например, example@gmail.com";
        } else {
            $sql = "SELECT EXISTS(SELECT 1 FROM users WHERE email = ?)";
            $stmt = $db->execute($sql, [$email]);
            if ((bool)$stmt->fetchColumn()) {
                $result['errors']['email'] = 'Этот email уже зарегистрирован.';
            }
        }

        $password = static::normalizeSpaces($password);
        $password_confirm = static::normalizeSpaces($password_confirm);
        $result['data']['password'] = $password;
        if ($password_confirm !== $password) {
            $result['errors']['password'] = 'Пароли не совпадают!';
        }

        return $result;
    }


    /**
     * Валидирует поля формы входа
     */
    public static function validateLoginForm(string $phoneOrEmail, string $password): array
    {
        $result = [
            'data' => [],
            'errors' => [],
        ];

        $phoneOrEmail = static::normalizeSpaces($phoneOrEmail);
        $result['data']['login'] = $phoneOrEmail;
        if (empty($phoneOrEmail)) {
            $result['errors']['login'] = 'Поле "Логин" не может быть пустым';
        }

        // Пароль не возвращаем для безопасности
        if (empty($password)) {
            $result['errors']['password'] = 'Поле "Пароль" не может быть пустым';
        }

        return $result;
    }

    /**
     * Валидирует все поля в дашборде. Возвращает массив ошибок
     */
    public static function validateDashboardForm(User   $user, DBWorker $db, string $name, string $email, string $phone, string $currentPassword,
                                                 string $newPassword, string $newPasswordConfirm): array
    {
        $result = [
            'data' => [],
            'errors' => [],
        ];

        $currentUserData = $user->getData();

        if (!empty($name)) {
            // Убираем пробелы
            $name = static::normalizeSpaces($name);
            $result['data']['name'] = $name;
            // Валидируем по regexp
            if (!preg_match(NAME_PATTERN, $name)) {
                $result['errors']['name'] = 'Введите корректное имя';
            }

        }

        if (!empty($phone)) {
            // Убираем пробелы
            $phone = static::normalizeSpaces($phone);
            $cleaned = static::cleanPhoneNumber($phone);
            $result['data']['phone'] = $cleaned;
            if (!preg_match(PHONE_PATTERN, $cleaned)) {
                $result['errors']['phone'] = 'Введите корректный номер телефона';
            } else {
                // Ищем ДРУГОГО пользователя с таким же телефоном
                $stmt = $db->execute("SELECT id FROM users WHERE phone = ? AND id != ?", [$cleaned, $currentUserData['id']]);
                if ($stmt->fetch()) {
                    $result['errors']['phone'] = 'Этот номер телефона уже занят.';
                }
            }
        }

        if (!empty($email)) {
            // Убираем пробелы
            $email = static::normalizeSpaces($email);
            $result['data']['email'] = $email;
            // Валидируем
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $result['errors']['email'] = 'Введите корректный email';
            } else {
                // Ищем ДРУГОГО пользователя с таким же email
                $stmt = $db->execute("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $currentUserData['id']]);
                if ($stmt->fetch()) {
                    $result['errors']['email'] = 'Этот email уже занят.';
                }
            }
        }


        // Проверяем блок смены пароля только если хотя бы одно из полей заполнено
        $passwordFieldsFilled = !empty($currentPassword) || !empty($newPassword) || !empty($newPasswordConfirm);

        if ($passwordFieldsFilled) {
            // Если начали менять пароль, то все поля обязательны
            if (empty($currentPassword)) {
                $result['errors']['current_password'] = 'Введите текущий пароль для смены.';
            }
            if (empty($newPassword)) {
                $result['errors']['new_password'] = 'Введите новый пароль.';
            }
            if (empty($newPasswordConfirm)) {
                $result['errors']['new_password_confirm'] = 'Подтвердите новый пароль.';
            }

            $currentPassword = static::normalizeSpaces($currentPassword);
            $newPassword = static::normalizeSpaces($newPassword);
            $newPasswordConfirm = static::normalizeSpaces($newPasswordConfirm);
            $result['data']['currentPassword'] = $currentPassword;
            $result['data']['newPassword'] = $newPassword;
            $result['data']['newPasswordConfirm'] = $newPasswordConfirm;

            // Если все поля заполнены, проверяем их корректность
            if (empty($result['current_password']) && !password_verify($currentPassword, $currentUserData['password'])) {
                $result['errors']['current_password'] = 'Введённый пароль не совпадает с текущим!';
            }
            if (empty($result['new_password']) && $newPassword !== $newPasswordConfirm) {
                $result['errors']['new_password'] = 'Пароли не совпадают!';
            }
        }

        return $result;
    }


    /**
     * Форматирует номер телефона в нужном формате.
     * Зачем нужно: в БД хранится очищенный номер телефона, а пользователю в вфырищфкв подставляется красиво форматированный
     */
    public static function formatPhoneNumber(string $phone): string
    {
        // Сначала очищаем номер от всего, кроме цифр
        $cleaned = preg_replace('/[^\d]/', '', $phone);

        // Если номер состоит из 11 цифр и начинается с 7 или 8, форматируем его
        if (strlen($cleaned) === 11 && ($cleaned[0] === '7' || $cleaned[0] === '8')) {
            // Заменяем первую 8 на 7 для единообразия
            $cleaned[0] = '7';
            // Используем регулярное выражение для расстановки скобок и дефисов
            return preg_replace('/(\d)(\d{3})(\d{3})(\d{2})(\d{2})/', '+7 ($2) $3-$4-$5', $cleaned);
        }

        // Если номер не соответствует формату, возвращаем его как есть
        return $phone;
    }

    private static function normalizeSpaces(string $input): string
    {
        return preg_replace(SPACES_PATTERN, ' ', trim($input));
    }

    private static function cleanPhoneNumber(string $phone): string
    {
        return preg_replace(PHONE_DELETE_PATTERN, '', $phone);
    }
}