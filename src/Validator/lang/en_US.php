<?php

return [
    'default' => 'Validation failed',
    'required' => 'Please fill in {label}',
    'enum' => '{label} is not within the allowed range',
    'regex' => '{label} format is incorrect',
    'type' => '{label} type error',
    'confirm' => '{label} does not match {confirm_label}, please re-enter',
    'confirm_not_found' => '{confirm_label} field not found！',
    'pwd' => '{label} must be 6–18 characters and cannot be all letters, numbers, or special characters (!@#$%^&_.*?)',
    'weak_pwd' => '{label} can only contain letters, numbers, or special characters (!@#$%^&_.*?), length 6–18 characters',
    'strong_pwd' => '{label} must be at least 8 characters and include at least one uppercase letter, one lowercase letter, one number, and one special character (!@#$%^&_.*?)',
    'min' => '{label} must not be less than {min}',
    'max' => '{label} must not be greater than {max}',
    'between' => '{label} must be between {min} and {max}',
];