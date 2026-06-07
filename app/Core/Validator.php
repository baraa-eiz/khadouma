<?php
/**
 * Validator.php
 * Khadomeh Core Input Validation Engine
 * 
 * Performs server-side validations on user inputs,
 * featuring pre-built rules for standard types, email addresses, and Syrian phone formats.
 */

namespace App\Core;

class Validator {
    private $errors = [];

    /**
     * Validate data against a set of rules.
     * Rules format:
     * [
     *    'email' => ['required', 'email'],
     *    'phone' => ['required', 'phone'],
     *    'name'  => ['required', 'min_length' => 3]
     * ]
     */
    public function validate($data, $rules) {
        foreach ($rules as $field => $fieldRules) {
            $value = isset($data[$field]) ? trim($data[$field]) : '';

            foreach ($fieldRules as $rule => $param) {
                // Normalize rule formats where rule is array index or key
                if (is_numeric($rule)) {
                    $rule = $param;
                    $param = null;
                }

                switch ($rule) {
                    case 'required':
                        if ($value === null || $value === '') {
                            $this->errors[$field][] = "هذا الحقل مطلوب.";
                        }
                        break;

                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->errors[$field][] = "البريد الإلكتروني المدخل غير صحيح.";
                        }
                        break;

                    case 'phone':
                        // Matches typical Syrian mobile numbers (e.g. +9639xxxxxxxx, 009639xxxxxxxx, 09xxxxxxxx, 9xxxxxxxx)
                        if (!empty($value) && !preg_match('/^(\+963|00963|0)?9\d{8}$/', $value)) {
                            $this->errors[$field][] = "رقم الهاتف غير صالح. يرجى إدخال رقم سوري صحيح (مثال: 09xxxxxxxx).";
                        }
                        break;

                    case 'min_length':
                        if (!empty($value) && mb_strlen($value) < $param) {
                            $this->errors[$field][] = "يجب ألا يقل طول النص عن {$param} أحرف.";
                        }
                        break;

                    case 'max_length':
                        if (!empty($value) && mb_strlen($value) > $param) {
                            $this->errors[$field][] = "يجب ألا يتجاوز طول النص {$param} حرفاً.";
                        }
                        break;
                }
            }
        }
        return empty($this->errors);
    }

    /**
     * Get list of validation errors.
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get the first error of a specific field.
     */
    public function getFirstError($field) {
        return isset($this->errors[$field][0]) ? $this->errors[$field][0] : null;
    }
}
