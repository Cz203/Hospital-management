<?php
// Cấu hình Vonage API
class VonageConfig
{
    // Thông tin API từ file .env
    private $api_key;
    private $api_secret;
    private $from_number;

    public function __construct()
    {
        // Load thông tin từ file .env
        $this->loadEnvFile();

        // Lấy thông tin từ environment variables
        $this->api_key = $_ENV['API_KEY'] ?? getenv('API_KEY') ?? 'your_api_key';
        $this->api_secret = $_ENV['API_SECRET'] ?? getenv('API_SECRET') ?? 'your_api_secret';
        $this->from_number = $_ENV['FROM_NUMBER'] ?? getenv('FROM_NUMBER') ?? 'your_from_number';
    }

    // Load file .env
    private function loadEnvFile()
    {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value, '"\'');
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }

    public function getApiKey()
    {
        return $this->api_key;
    }

    public function getApiSecret()
    {
        return $this->api_secret;
    }

    public function getFromNumber()
    {
        return $this->from_number;
    }

    // Kiểm tra xem Vonage đã được cấu hình chưa
    public function isConfigured()
    {
        return $this->api_key !== 'your_api_key' &&
            $this->api_secret !== 'your_api_secret';
    }
}
