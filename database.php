<?php
// includes/database.php
require_once __DIR__ . '/../config/config.php';

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    // Отримання підключення
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            return false;
        }
        
        return $this->conn;
    }

    /**
     * Створення таблиці для анкет
     */
    public function createTables() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS surveys (
                id INT AUTO_INCREMENT PRIMARY KEY,
                survey_id VARCHAR(50) UNIQUE NOT NULL,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                phone_usage VARCHAR(50) NOT NULL,
                preferred_brand VARCHAR(50) NOT NULL,
                features TEXT,
                suggestions TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            $this->conn->exec($sql);
            return true;
        } catch(PDOException $exception) {
            error_log("Table creation error: " . $exception->getMessage());
            return false;
        }
    }

    /**
     * Збереження анкети в базу даних
     */
    public function saveSurvey($data) {
        try {
            $sql = "INSERT INTO surveys (
                survey_id, name, email, phone_usage, preferred_brand, features, suggestions
            ) VALUES (
                :survey_id, :name, :email, :phone_usage, :preferred_brand, :features, :suggestions
            )";
            
            $stmt = $this->conn->prepare($sql);
            
            $features_json = !empty($data['features']) ? json_encode($data['features']) : '[]';
            
            $stmt->bindParam(':survey_id', $data['survey_id']);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':phone_usage', $data['phone_usage']);
            $stmt->bindParam(':preferred_brand', $data['preferred_brand']);
            $stmt->bindParam(':features', $features_json);
            $stmt->bindParam(':suggestions', $data['suggestions']);
            
            return $stmt->execute();
        } catch(PDOException $exception) {
            error_log("Save survey error: " . $exception->getMessage());
            return false;
        }
    }

    /**
     * Отримання всіх анкет
     */
    public function getAllSurveys() {
        try {
            $sql = "SELECT * FROM surveys ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $exception) {
            error_log("Get surveys error: " . $exception->getMessage());
            return [];
        }
    }

    /**
     * Видалення анкети
     */
    public function deleteSurvey($id) {
        try {
            $sql = "DELETE FROM surveys WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch(PDOException $exception) {
            error_log("Delete survey error: " . $exception->getMessage());
            return false;
        }
    }

    /**
     * Отримання статистики
     */
    public function getStats() {
        try {
            $stats = [];
            
            // Загальна кількість
            $sql = "SELECT COUNT(*) as total FROM surveys";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats['total'] = $stmt->fetch()['total'];
            
            // Популярні бренди
            $sql = "SELECT preferred_brand, COUNT(*) as count 
                    FROM surveys 
                    GROUP BY preferred_brand 
                    ORDER BY count DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats['brands'] = $stmt->fetchAll();
            
            return $stats;
        } catch(PDOException $exception) {
            error_log("Get stats error: " . $exception->getMessage());
            return ['total' => 0, 'brands' => []];
        }
    }
}
?>