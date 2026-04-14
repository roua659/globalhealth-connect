<?php

abstract class BaseController {
    protected $model;

    public function __construct($modelClass = null) {
        if ($modelClass) {
            $this->model = new $modelClass();
        }
    }

    public function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function handleGet() {
        $id = $_GET['id'] ?? null;

        if ($id) {
            $this->model->findById($id);
            return $this->jsonResponse([
                'success' => true,
                'data' => $this->model->toArray()
            ]);
        }

        $limit = $_GET['limit'] ?? 100;
        $offset = $_GET['offset'] ?? 0;
        $data = $this->model->getAll($limit, $offset);

        return $this->jsonResponse([
            'success' => true,
            'data' => $data
        ]);
    }

    public function handlePost() {
        $action = $_GET['action'] ?? 'create';
        $payload = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        try {
            switch ($action) {
                case 'create':
                    $this->model->fill($payload);
                    $result = $this->model->save();
                    return $this->jsonResponse([
                        'success' => $result['success'] ?? false,
                        'message' => $result['success'] ? 'Created successfully' : 'Error',
                        'id' => $result['id'] ?? null,
                        'error' => $result['error'] ?? null
                    ]);

                case 'update':
                    $id = $_GET['id'] ?? null;
                    if (!$id) {
                        return $this->jsonResponse(['success' => false, 'error' => 'ID required'], 400);
                    }
                    $this->model->fill($payload);
                    $this->model->setId($id);
                    $result = $this->model->update();
                    return $this->jsonResponse($result);

                case 'delete':
                    $id = $_GET['id'] ?? null;
                    if (!$id) {
                        return $this->jsonResponse(['success' => false, 'error' => 'ID required'], 400);
                    }
                    $this->model->setId($id);
                    $result = $this->model->delete();
                    return $this->jsonResponse($result);

                default:
                    return $this->jsonResponse(['success' => false, 'error' => 'Unknown action'], 400);
            }
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function route() {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            return $this->handleGet();
        } elseif ($method === 'POST') {
            return $this->handlePost();
        } else {
            return $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }
    }
}
