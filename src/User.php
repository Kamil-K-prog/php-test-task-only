<?php

class User
{
    // private ?int $id = null;
    private ?array $data = null; // Все данные текущего пользователя

    private DBWorker $db;


    public function __construct($db, ?int $id = null)
    {
        $this->db = $db;
        // $this->id = $id;

        // Если у пользователя уже есть активная сессия
        if (isset($id)) {
            $sql = "SELECT * FROM users WHERE id = ?";

            $userData = $this->db->execute($sql, [$id])->fetch(PDO::FETCH_ASSOC);
            // Если записи в БД не будет (пользователя с таким id нет в базе)
            if ($userData) {
                $this->data = $userData;
            }
        }
    }

    /**
     * Авторизован ли текущий пользователь
     */
    public function isLogged(): bool
    {
        return is_array($this->data);
    }

    /**
     * Возвращает данные пользователя
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * Регистрирует пользователя, добавляет запись в БД.
     * Возвращает ID нового пользователя в случае успеха, иначе false.
     */
    public function register(string $name, string $phone, string $email, string $password): int|false
    {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)";

        try {
            $this->db->execute($sql, [$name, $phone, $email, $password_hash]);
            return $this->db->getConnection()->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Логинит пользователя. В случае успеха заполняет объект данными пользователя и возвращает его ID
     */
    public function login(string $phoneOrEmail, string $password): int|false
    {
        $sql = "SELECT * FROM users WHERE email = ? OR phone = ?";
        $stmt = $this->db->execute($sql, [$phoneOrEmail, $phoneOrEmail]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Если пользователь не найден
        if ($user === false) {
            return false;
        }

        // Если пользователь найден, то сверяем хеш пароля
        if (password_verify($password, $user['password'])) {
            $this->data = $user;
            return $user['id'];
        }

        return false;
    }


    /**
     * Обновляет запись в БД текущего пользователя, меняет поле
     */
    public function updateField(string $field, string $value): bool
    {
        if (!$this->isLogged()) {
            return false;
        }

        $sql = "UPDATE users SET $field = ? WHERE id = ?";
        try {
            $this->db->execute($sql, [$value, $this->data['id']]);
            $this->data[$field] = $value;
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }


    /**
     * Обновляет пароль текущего пользователя
     */
    public function changePassword(string $currentPassword, string $newPassword): bool
    {
        if (!$this->isLogged()) {
            return false;
        }

        if (!password_verify($currentPassword, $this->data['password'])) {
            return false;
        }

        $password_hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        try {
            $this->db->execute($sql, [$password_hash, $this->data['id']]);
            $this->data['password'] = $password_hash;
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}