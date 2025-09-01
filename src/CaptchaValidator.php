<?php

class CaptchaValidator
{
    public static function validateCaptcha($token): bool
    {
        $ch = curl_init("https://smartcaptcha.yandexcloud.net/validate");
        $args = [
            "secret" => YANDEX_SMARTCAPTCHA_SERVER_KEY,
            "token" => $token,
            "ip" => $_SERVER['REMOTE_ADDR'] ?? null, // Нужно передать IP-адрес пользователя.
            // Способ получения IP-адреса пользователя зависит от вашего прокси.
        ];
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($args));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        /* Отладка */
//        if ($server_output === false) {
//            echo 'Ошибка curl: ' . curl_error($ch);
//            echo 'HTTP code: ' . $httpcode;
//        } else {
//            var_dump($server_output);
//        }

        if ($httpcode !== 200) {
            echo "Allow access due to an error: code=$httpcode; message=$server_output\n";
            return false;
        }

        $resp = json_decode($server_output);

        /* Отладка */
//        var_dump($resp);

        return $resp->status === "ok";
    }
}