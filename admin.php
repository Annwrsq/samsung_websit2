<?php
// admin.php
session_start();

// Підключення необхідних файлів
require_once 'includes/header.php';
require_once 'includes/footer.php';
require_once 'includes/database.php';
require_once 'config/config.php';

// Ініціалізація бази даних
$database = new Database();
$conn = $database->getConnection();

/**
 * Головна функція обробки
 */
function handleAdminPanel() {
    global $database;
    
    // Обробка виходу
    if (isset($_POST['logout'])) {
        handleLogout();
    }
    
    // Перевірка авторизації
    if (!isset($_SESSION['admin_logged_in'])) {
        handleLogin();
        return;
    }
    
    // Обробка дій адміна
    handleAdminActions($database);
    
    // Показ адмін панелі
    showAdminPanel($database);
}

/**
 * Обробка виходу
 */
function handleLogout() {
    session_destroy();
    header('Location: index.html');
    exit;
}

/**
 * Обробка авторизації
 */
function handleLogin() {
    $error = '';
    
    if (isset($_POST['login'])) {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            $_SESSION['login_time'] = time();
            
            // Перенаправлення на адмін панель
            header('Location: admin.php');
            exit;
        } else {
            $error = "Невірний логін або пароль!";
        }
    }
    
    showLoginForm($error);
}

/**
 * Обробка дій адміна
 */
function handleAdminActions($database) {
    // Видалення анкети
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        if ($database->deleteSurvey($id)) {
            $_SESSION['success_message'] = "Анкету успішно видалено!";
        } else {
            $_SESSION['error_message'] = "Помилка при видаленні анкети!";
        }
        header('Location: admin.php');
        exit;
    }
    
    // Експорт даних
    if (isset($_GET['export'])) {
        exportSurveysToJSON($database);
        exit;
    }
    
    // Масове видалення
    if (isset($_POST['bulk_delete']) && isset($_POST['selected_surveys'])) {
        $deleted_count = 0;
        foreach ($_POST['selected_surveys'] as $survey_id) {
            if ($database->deleteSurvey(intval($survey_id))) {
                $deleted_count++;
            }
        }
        $_SESSION['success_message'] = "Успішно видалено {$deleted_count} анкет!";
        header('Location: admin.php');
        exit;
    }
}

/**
 * Експорт анкет в JSON
 */
function exportSurveysToJSON($database) {
    $surveys = $database->getAllSurveys();
    
    // Форматуємо дані для експорту
    $export_data = [
        'export_time' => date('Y-m-d H:i:s'),
        'total_surveys' => count($surveys),
        'surveys' => []
    ];
    
    foreach ($surveys as $survey) {
        $export_data['surveys'][] = [
            'id' => $survey['id'],
            'survey_id' => $survey['survey_id'],
            'name' => $survey['name'],
            'email' => $survey['email'],
            'phone_usage' => $survey['phone_usage'],
            'preferred_brand' => $survey['preferred_brand'],
            'features' => json_decode($survey['features'], true),
            'suggestions' => $survey['suggestions'],
            'created_at' => $survey['created_at']
        ];
    }
    
    // Встановлюємо заголовки для завантаження файлу
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="surveys_export_' . date('Y-m-d_H-i-s') . '.json"');
    
    echo json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Показ форми входу
 */
function showLoginForm($error = '') {
    renderHeader("Вхід в адмін панель - Samsung");
    ?>
    <section class="section">
        <div class="login-container">
            <div class="login-form">
                <h2>🔐 Вхід в адмін панель</h2>
                
                <?php if ($error): ?>
                    <div class="error-message">
                        ❌ <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="username">👤 Ім'я користувача:</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Введіть логін" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">🔑 Пароль:</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Введіть пароль">
                    </div>
                    
                    <button type="submit" name="login" class="login-btn">🚀 Увійти</button>
                </form>
                
                <div class="back-link">
                    <a href="index.html" style="color: #bfe8ff;">← Повернутися на головну</a>
                </div>
                
                <div class="login-info">
                    <p><strong>Тестові дані для входу:</strong></p>
                    <p>Логін: <code>admin</code></p>
                    <p>Пароль: <code>admin123</code></p>
                </div>
            </div>
        </div>
    </section>
    
    <style>
        .login-container {
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }
        
        .login-form {
            background: linear-gradient(180deg, rgba(10,32,60,0.3), rgba(6,18,36,0.15));
            padding: 40px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            width: 100%;
            max-width: 400px;
        }
        
        .login-form h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #4a90e2;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #bfe8ff;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #4a90e2;
            background: rgba(255,255,255,0.15);
        }
        
        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #4a90e2, #357abd);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }
        
        .login-btn:hover {
            background: linear-gradient(135deg, #5a9ff2, #458acd);
            transform: translateY(-2px);
        }
        
        .error-message {
            background: rgba(255, 59, 48, 0.1);
            border: 1px solid rgba(255, 59, 48, 0.3);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #bfe8ff;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .back-link a:hover {
            color: #4a90e2;
        }
        
        .login-info {
            margin-top: 25px;
            padding: 15px;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
            font-size: 14px;
        }
        
        .login-info code {
            background: rgba(255,255,255,0.1);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
    </style>
    <?php
    renderFooter();
}

/**
 * Показ адмін панелі
 */
function showAdminPanel($database) {
    // Отримуємо дані з бази
    $surveys = $database->getAllSurveys();
    $stats = $database->getStats();
    
    // Повідомлення про успіх/помилку
    $success_message = $_SESSION['success_message'] ?? '';
    $error_message = $_SESSION['error_message'] ?? '';
    
    // Очищаємо повідомлення після показу
    unset($_SESSION['success_message']);
    unset($_SESSION['error_message']);
    
    renderHeader("Адмін Панель - Samsung");
    ?>
    <section class="section">
        <?php if ($success_message): ?>
            <div class="success-message">
                ✅ <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="error-message">
                ❌ <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-header">
            <div class="admin-welcome">
                <h2>👨‍💼 Адмін панель</h2>
                <p>Ласкаво просимо, <strong><?php echo $_SESSION['admin_username']; ?></strong>!</p>
            </div>
            <div class="admin-actions">
                <form method="POST" class="logout-form">
                    <button type="submit" name="logout" class="logout-btn">🚪 Вийти</button>
                </form>
            </div>
        </div>
        
        <!-- Статистика -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <h3>Всього анкет</h3>
                    <p class="stat-number"><?php echo $stats['total']; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🕒</div>
                <div class="stat-info">
                    <h3>Активність</h3>
                    <p class="stat-text">
                        <?php if (!empty($surveys)): ?>
                            <?php echo date('d.m.Y H:i', strtotime($surveys[0]['created_at'])); ?>
                        <?php else: ?>
                            Немає анкет
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏆</div>
                <div class="stat-info">
                    <h3>Топ бренд</h3>
                    <p class="stat-text">
                        <?php if (!empty($stats['brands'])): ?>
                            <?php echo $stats['brands'][0]['preferred_brand']; ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Дії з даними -->
        <div class="admin-toolbar">
            <div class="toolbar-actions">
                <a href="?export=1" class="toolbar-btn export-btn">
                    📥 Експорт в JSON
                </a>
                <button type="button" onclick="toggleBulkActions()" class="toolbar-btn bulk-btn">
                    🗑️ Масове видалення
                </button>
            </div>
            
            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <form method="POST" id="bulkForm" onsubmit="return confirm('Ви впевнені, що хочете видалити обрані анкети?')">
                    <button type="submit" name="bulk_delete" class="danger-btn">
                        ✅ Видалити обрані
                    </button>
                    <button type="button" onclick="toggleBulkActions()" class="secondary-btn">
                        ❌ Скасувати
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Список анкет -->
        <div class="surveys-list">
            <h3>📝 Список анкет (<?php echo count($surveys); ?>):</h3>
            
            <?php if (empty($surveys)): ?>
                <div class="empty-state">
                    <div class="empty-icon">😔</div>
                    <h4>Анкет ще немає</h4>
                    <p>Користувачі ще не заповнили жодної анкети.</p>
                    <a href="survey.php" class="cta-button">📝 Заповнити тестову анкету</a>
                </div>
            <?php else: ?>
                <div class="surveys-grid">
                    <?php foreach ($surveys as $survey): ?>
                        <div class="survey-card">
                            <div class="survey-header">
                                <div class="survey-meta">
                                    <h4><?php echo htmlspecialchars($survey['name']); ?></h4>
                                    <span class="survey-date">
                                        <?php echo date('d.m.Y H:i', strtotime($survey['created_at'])); ?>
                                    </span>
                                </div>
                                <div class="survey-actions">
                                    <span class="survey-id">ID: <?php echo $survey['survey_id']; ?></span>
                                    <a href="?delete=<?php echo $survey['id']; ?>" 
                                       class="delete-btn"
                                       onclick="return confirm('Ви впевнені, що хочете видалити цю анкету?')">
                                       🗑️
                                    </a>
                                    <label class="bulk-select" style="display: none;">
                                        <input type="checkbox" name="selected_surveys[]" value="<?php echo $survey['id']; ?>" form="bulkForm">
                                    </label>
                                </div>
                            </div>
                            
                            <div class="survey-content">
                                <div class="survey-field">
                                    <strong>📧 Email:</strong>
                                    <span><?php echo htmlspecialchars($survey['email']); ?></span>
                                </div>
                                
                                <div class="survey-field">
                                    <strong>📱 Використання:</strong>
                                    <span><?php echo htmlspecialchars($survey['phone_usage']); ?></span>
                                </div>
                                
                                <div class="survey-field">
                                    <strong>🏆 Улюблений бренд:</strong>
                                    <span class="brand-tag"><?php echo htmlspecialchars($survey['preferred_brand']); ?></span>
                                </div>
                                
                                <?php 
                                $features = json_decode($survey['features'], true);
                                if (!empty($features) && is_array($features)): 
                                ?>
                                    <div class="survey-field">
                                        <strong>⭐ Функції:</strong>
                                        <div class="features-list">
                                            <?php foreach ($features as $feature): ?>
                                                <span class="feature-tag"><?php echo htmlspecialchars($feature); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($survey['suggestions'])): ?>
                                    <div class="survey-field">
                                        <strong>💡 Пропозиції:</strong>
                                        <p class="suggestions-text"><?php echo htmlspecialchars($survey['suggestions']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <style>
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 25px;
            background: linear-gradient(180deg, rgba(10,32,60,0.3), rgba(6,18,36,0.15));
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .admin-welcome h2 {
            margin: 0 0 5px 0;
            color: #4a90e2;
        }
        
        .admin-welcome p {
            margin: 0;
            color: #bfe8ff;
        }
        
        .logout-form {
            margin: 0;
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #8E8E93, #6d6d72);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: linear-gradient(135deg, #9E9EA3, #7d7d82);
            transform: translateY(-2px);
        }
        
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #4a90e2, #357abd);
            padding: 25px;
            border-radius: 15px;
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
        }
        
        .stat-info h3 {
            margin: 0 0 8px 0;
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .stat-number {
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
        }
        
        .stat-text {
            margin: 0;
            font-size: 1.1rem;
            font-weight: bold;
        }
        
        .admin-toolbar {
            background: linear-gradient(180deg, rgba(10,32,60,0.2), rgba(6,18,36,0.1));
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 25px;
        }
        
        .toolbar-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .toolbar-btn, .bulk-actions button {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .export-btn {
            background: linear-gradient(135deg, #4CD964, #34C759);
            color: white;
        }
        
        .export-btn:hover {
            background: linear-gradient(135deg, #5CE974, #44D769);
            transform: translateY(-2px);
        }
        
        .bulk-btn {
            background: linear-gradient(135deg, #FF9500, #FF8A00);
            color: white;
        }
        
        .bulk-btn:hover {
            background: linear-gradient(135deg, #FFA520, #FF9A10);
            transform: translateY(-2px);
        }
        
        .bulk-actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .danger-btn {
            background: linear-gradient(135deg, #FF3B30, #FF2D55);
            color: white;
        }
        
        .danger-btn:hover {
            background: linear-gradient(135deg, #FF5B50, #FF4D65);
        }
        
        .secondary-btn {
            background: linear-gradient(135deg, #8E8E93, #6d6d72);
            color: white;
        }
        
        .secondary-btn:hover {
            background: linear-gradient(135deg, #9E9EA3, #7d7d82);
        }
        
        .surveys-grid {
            display: grid;
            gap: 20px;
        }
        
        .survey-card {
            background: linear-gradient(180deg, rgba(10,32,60,0.25), rgba(6,18,36,0.12));
            padding: 25px;
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }
        
        .survey-card:hover {
            border-color: rgba(74, 144, 226, 0.3);
            transform: translateY(-2px);
        }
        
        .survey-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .survey-meta h4 {
            margin: 0 0 5px 0;
            color: #bfe8ff;
            font-size: 1.2rem;
        }
        
        .survey-date {
            color: #8E8E93;
            font-size: 0.9rem;
        }
        
        .survey-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .survey-id {
            color: #8E8E93;
            font-size: 0.8rem;
            font-family: monospace;
        }
        
        .delete-btn {
            background: #FF3B30;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
            transition: background 0.3s ease;
        }
        
        .delete-btn:hover {
            background: #ff6b6b;
        }
        
        .bulk-select {
            margin-left: 10px;
        }
        
        .survey-content {
            display: grid;
            gap: 12px;
        }
        
        .survey-field {
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }
        
        .survey-field strong {
            min-width: 120px;
            color: #bfe8ff;
        }
        
        .brand-tag {
            background: rgba(74, 144, 226, 0.2);
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid rgba(74, 144, 226, 0.3);
        }
        
        .features-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .feature-tag {
            background: rgba(76, 217, 100, 0.2);
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid rgba(76, 217, 100, 0.3);
            font-size: 0.9rem;
        }
        
        .suggestions-text {
            margin: 0;
            padding: 10px;
            background: rgba(255,255,255,0.05);
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            background: linear-gradient(180deg, rgba(10,32,60,0.2), rgba(6,18,36,0.1));
            border-radius: 15px;
            border: 2px dashed rgba(255,255,255,0.1);
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .empty-state h4 {
            margin: 0 0 10px 0;
            color: #bfe8ff;
        }
        
        .empty-state p {
            margin: 0 0 25px 0;
            color: #8E8E93;
        }
        
        .success-message {
            background: rgba(76, 217, 100, 0.1);
            border: 1px solid rgba(76, 217, 100, 0.3);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            color: #4CD964;
        }
        
        .error-message {
            background: rgba(255, 59, 48, 0.1);
            border: 1px solid rgba(255, 59, 48, 0.3);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            color: #FF3B30;
        }
    </style>
    
    <script>
        function toggleBulkActions() {
            const bulkActions = document.getElementById('bulkActions');
            const bulkSelects = document.querySelectorAll('.bulk-select');
            const isVisible = bulkActions.style.display === 'block';
            
            bulkActions.style.display = isVisible ? 'none' : 'block';
            bulkSelects.forEach(select => {
                select.style.display = isVisible ? 'none' : 'block';
            });
        }
        
        // Додаємо обробник для виділення всіх
        document.addEventListener('DOMContentLoaded', function() {
            const bulkForm = document.getElementById('bulkForm');
            if (bulkForm) {
                const selectAllBtn = document.createElement('button');
                selectAllBtn.type = 'button';
                selectAllBtn.textContent = '☑️ Виділити всі';
                selectAllBtn.className = 'secondary-btn';
                selectAllBtn.style.marginLeft = '10px';
                selectAllBtn.onclick = function() {
                    const checkboxes = document.querySelectorAll('input[name="selected_surveys[]"]');
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    checkboxes.forEach(cb => cb.checked = !allChecked);
                };
                
                const bulkActions = document.getElementById('bulkActions');
                if (bulkActions) {
                    bulkActions.querySelector('form').appendChild(selectAllBtn);
                }
            }
        });
    </script>
    <?php
    renderFooter();
}

// Запуск головної функції
handleAdminPanel();
?>