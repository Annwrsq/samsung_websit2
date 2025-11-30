<?php
// survey.php - Головний файл анкети

// Підключення необхідних файлів
require_once 'includes/header.php';
require_once 'includes/footer.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

// Ініціалізація бази даних
$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    $database->createTables();
}

/**
 * Головна функція обробки
 */
function handleSurvey() {
    global $database;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        processSurvey($database);
    } else {
        showSurveyForm();
    }
}

/**
 * Обробка даних форми
 */
function processSurvey($database) {
    require_once 'includes/functions.php';
    
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
            showConfirmation($data['survey_id']);
        } else {
            showError("Не вдалося зберегти дані. Спробуйте ще раз.");
        }
    } else {
        showSurveyForm($errors);
    }
}

/**
 * Збереження в файл (додатково)
 */
function saveSurveyToFile($data) {
    if (!file_exists('survey')) {
        mkdir('survey', 0777, true);
    }
    
    $filename = "survey/survey_{$data['survey_id']}.txt";
    $content = "=== АНКЕТА SAMSUNG ===\n";
    $content .= "ID: {$data['survey_id']}\n";
    $content .= "Час заповнення: " . date('d.m.Y H:i:s') . "\n";
    $content .= "Ім'я: {$data['name']}\n";
    $content .= "Email: {$data['email']}\n";
    $content .= "Використання телефону: {$data['phone_usage']}\n";
    $content .= "Улюблений бренд: {$data['preferred_brand']}\n";
    $content .= "Важливі функції: " . implode(', ', $data['features']) . "\n";
    $content .= "Пропозиції: {$data['suggestions']}\n";
    
    return file_put_contents($filename, $content);
}

/**
 * Показ форми анкети
 */
function showSurveyForm($errors = []) {
    renderHeader("Анкета про смартфони - Samsung");
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
    <?php
    renderFooter();
}

/**
 * HTML форма анкети
 */
function showSurveyFormHTML() {
    ?>
    <form method="POST" action="survey.php" class="survey-form">
        <!-- Ім'я -->
        <div class="form-group">
            <label for="name">👤 Ваше ім'я:</label>
            <input type="text" id="name" name="name" required 
                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                   placeholder="Введіть ваше ім'я">
        </div>
        
        <!-- Email -->
        <div class="form-group">
            <label for="email">📧 Ваш Email:</label>
            <input type="email" id="email" name="email" required 
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                   placeholder="example@email.com">
        </div>
        
        <!-- Використання телефону -->
        <div class="form-group">
            <label>📱 Як часто ви використовуєте телефон?</label>
            <select name="phone_usage" required>
                <option value="">-- Оберіть варіант --</option>
                <option value="Постійно" <?php echo (isset($_POST['phone_usage']) && $_POST['phone_usage'] == 'Постійно') ? 'selected' : ''; ?>>📞 Постійно</option>
                <option value="Декілька разів на день" <?php echo (isset($_POST['phone_usage']) && $_POST['phone_usage'] == 'Декілька разів на день') ? 'selected' : ''; ?>>🕒 Декілька разів на день</option>
                <option value="Рідко" <?php echo (isset($_POST['phone_usage']) && $_POST['phone_usage'] == 'Рідко') ? 'selected' : ''; ?>>⏰ Рідко</option>
            </select>
        </div>
        
        <!-- Улюблений бренд -->
        <div class="form-group">
            <label>🏆 Який бренд смартфонів ви вважаєте найкращим?</label>
            <div class="radio-group">
                <?php
                $brands = [
                    'Samsung' => '📱 Samsung',
                    'Apple' => '🍎 Apple', 
                    'Xiaomi' => '🔴 Xiaomi',
                    'Google' => '🔵 Google Pixel',
                    'Інший' => '🔶 Інший'
                ];
                
                $selected_brand = $_POST['preferred_brand'] ?? '';
                foreach ($brands as $value => $label): ?>
                    <label>
                        <input type="radio" name="preferred_brand" value="<?php echo $value; ?>" 
                               <?php echo ($selected_brand == $value) ? 'checked' : ''; ?> required>
                        <?php echo $label; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Важливі функції -->
        <div class="form-group">
            <label>⭐ Які функції для вас найважливіші? (оберіть декілька)</label>
            <div class="checkbox-group">
                <?php
                $features_list = [
                    'Камера' => '📸 Камера',
                    'Батарея' => '🔋 Батарея', 
                    'Швидкість' => '⚡ Швидкість',
                    'Екран' => '🖥️ Якість екрану',
                    'Ціна' => '💰 Ціна',
                    'Дизайн' => '🎨 Дизайн'
                ];
                
                $selected_features = $_POST['features'] ?? [];
                foreach ($features_list as $value => $label): ?>
                    <label>
                        <input type="checkbox" name="features[]" value="<?php echo $value; ?>"
                               <?php echo in_array($value, $selected_features) ? 'checked' : ''; ?>>
                        <?php echo $label; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Пропозиції -->
        <div class="form-group">
            <label for="suggestions">💡 Ваші пропозиції для покращення смартфонів:</label>
            <textarea id="suggestions" name="suggestions" rows="4" 
                      placeholder="Що б ви хотіли бачити в майбутніх смартфонах?"><?php echo isset($_POST['suggestions']) ? htmlspecialchars($_POST['suggestions']) : ''; ?></textarea>
        </div>
        
        <!-- Кнопка відправки -->
        <button type="submit" class="cta-button">🚀 Надіслати анкету</button>
    </form>
    <?php
}

/**
 * Показ підтвердження
 */
function showConfirmation($survey_id) {
    $current_time = date('d.m.Y о H:i:s');
    
    renderHeader("Дякуємо! - Samsung");
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
            </div>
        </div>
    </section>
    <?php
    renderFooter();
}

/**
 * Показ помилки
 */
function showError($message) {
    renderHeader("Помилка - Samsung");
    ?>
    <section class="section">
        <div class="error-message">
            <h2>❌ Сталася помилка</h2>
            <p><?php echo $message; ?></p>
            <a href="survey.php" class="cta-button">🔄 Спробувати знову</a>
        </div>
    </section>
    <?php
    renderFooter();
}

// Запуск головної функції
handleSurvey();
?>

<script>
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
                    // Показуємо повідомлення про успіх
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