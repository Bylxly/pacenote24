<?php
const passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d])[A-Za-z\d\S]{8,128}$/';

class PasswordValidator{
    public static function validate($password) {
        return preg_match(passwordRegex, $password);
    }
}