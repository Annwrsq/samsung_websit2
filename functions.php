<?php
// includes/functions.php

/**
 * Валідація даних форми
 */
function validateSurveyData($data) {
    $errors = [];
    
    if (empty(trim($data['name']))) {
        $errors[] = "Ім'я обов'язкове для заповнення";
    }
    
    if (empty(trim($data['email']))) {
        $errors[] = "Email обов'язковий для заповнення";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Введіть коректний email адрес";
    }
    
    if (empty($data['phone_usage'])) {
        $errors[] = "Оберіть варіант використання телефону";
    }
    
    if (empty($data['preferred_brand'])) {
        $errors[] = "Оберіть улюблений бренд";
    }
    
    return $errors;
}

/**
 * Очищення даних
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Генерація унікального ID
 */
function generateSurveyId() {
    return date('Ymd_His') . '_' . uniqid();
}
?>