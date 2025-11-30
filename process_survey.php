<?php
// process_survey.php

require_once 'includes/functions.php';
require_once 'includes/database.php';
require_once 'includes/header.php';
require_once 'includes/footer.php';

// Ініціалізація бази даних
$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    $database->createTables();
}

function processAjaxSurvey() {
    global $database;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Очищення та валідація даних
        $name = cleanInput($_POST['name']);
        $email = cleanInput($_POST['email']);
        $phone_usage = $_POST['phone_usage'];
        $preferred_brand = $_POST['preferred_brand'];
        $features = isset($_POST['features']) ? $_POST['features'] : [];
        $suggestions = cleanInput($_POST['suggestions']);
        
        $data = [
            'name' => $name,
            'email' => $email,
            'phone_usage' => $phone_usage,
            'preferred_brand' => $preferred_brand,
            'features' => $features,
            'suggestions' => $suggestions
        ];
        
        // Валідація
        $errors = validateSurveyData($data);
        
        if (empty($errors)) {
            // Додаємо ID анкети
            $data['survey_id'] = generateSurveyId();
            
            // Зберігаємо в базу даних
            $db_success = $database->saveSurvey($data);
            
            // Додатково зберігаємо в файл (для бекапу)
            $file_success = saveSurveyToFile($data);
            
            if ($db_success || $file_success) {
                showAjaxConfirmation($data['survey_id']);
            } else {
                showAjaxError("Не вдалося зберегти дані. Спробуйте ще раз.");
            }
        } else {
            showAjaxFormWithErrors($errors);
        }
    }
}

function showAjaxConfirmation($survey_id) {
    $current_time = date('d.m.Y о H:i:s');
    ?>
    <section class="section">
        <div class="confirmation-message">
            <h2>✅ Дякуємо за участь в анкетуванні!</h2>
            <div class="confirmation-details">
                <p><strong>Час заповнення форми:</strong> <?php echo $current_time; ?></p>
                <p><strong>ID анкети:</strong> <?php echo $survey_id; ?></p>
            </div>
            <p>Ваші відповіді були успішно збережені в базу даних!</p>
            
            <div class="confirmation-buttons">
                <a href="survey.php" class="cta-button">📝 Заповнити ще одну анкету</a>
                <a href="index.html" class="cta-button secondary">🏠 На головну</a>
                <a href="jokes.html" class="cta-button">🎭 Посміятися з жартами</a>
            </div>
        </div>
    </section>
    <?php
}

function showAjaxError($message) {
    ?>
    <section class="section">
        <div class="error-message">
            <h2>❌ Сталася помилка</h2>
            <p><?php echo $message; ?></p>
            <a href="survey.php" class="cta-button">🔄 Спробувати знову</a>
        </div>
    </section>
    <?php
}

function showAjaxFormWithErrors($errors) {
    ?>
    <section class="section">
        <h2>📝 Анкета про використання смартфонів</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <strong>❌ Виправте наступні помилки:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php showSurveyFormHTML(); ?>
    </section>
    
    <script>
    // Додаємо обробник подій для оновленої форми
    document.addEventListener('DOMContentLoaded', function() {
        const surveyForm = document.querySelector('.survey-form');
        
        if (surveyForm) {
            surveyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const xhr = new XMLHttpRequest();
                
                xhr.open('POST', 'process_survey.php', true);
                
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        document.querySelector('main').innerHTML = xhr.responseText;
                    } else {
                        alert('Помилка при відправці форми. Спробуйте ще раз.');
                    }
                };
                
                xhr.onerror = function() {
                    alert('Помилка при відправці форми. Спробуйте ще раз.');
                };
                
                xhr.send(formData);
            });
        }
    });
    </script>
    <?php
}

// Запуск обробки
processAjaxSurvey();
?>