<?php
require_once 'Controllers/AuthController.php';
require_once 'Models/Notification.php';

class NotificationController
{
    private $auth;
    private $model;

    public function __construct()
    {
        $this->auth = new AuthController();
        $this->model = new Notification();
    }

    public function list()
    {
        header('Content-Type: application/json');
        if (!$this->auth->isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        $ctx = $this->auth->resolveCurrentUserContext();
        $userId = (int)($ctx['id'] ?? 0);
        $role = (string)($ctx['role'] ?? '');
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $rows = $this->model->listForUser($role, $userId, $limit);
        echo json_encode(['success' => true, 'data' => $rows]);
        exit();
    }

    public function markAllRead()
    {
        header('Content-Type: application/json');
        if (!$this->auth->isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        $ctx = $this->auth->resolveCurrentUserContext();
        $userId = (int)($ctx['id'] ?? 0);
        $role = (string)($ctx['role'] ?? '');
        $ok = $this->model->markAllRead($role, $userId);
        echo json_encode(['success' => $ok]);
        exit();
    }
}
