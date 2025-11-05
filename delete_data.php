<?php
// delete_data.php — Exclui um cliente do banco

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');

$dbFile = __DIR__ . '/db/database.sqlite';
if (!file_exists($dbFile)) {
    http_response_code(500);
    echo json_encode(['error' => '❌ Banco de dados não encontrado.']);
    exit;
}

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => '❌ Falha ao conectar ao banco', 'detail' => $e->getMessage()]);
    exit;
}

// Recebe o ID via GET ou JSON
$id = $_GET['id'] ?? null;

if (!$id) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
}

if (!$id || !is_numeric($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido.']);
    exit;
}

// Exclui o cliente
try {
    $stmt = $pdo->prepare('DELETE FROM clients WHERE id = :id');
    $stmt->execute([':id' => (int)$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => '✅ Cliente excluído com sucesso.']);
    } else {
        echo json_encode(['success' => false, 'message' => '⚠️ Cliente não encontrado.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => '❌ Erro ao excluir cliente.', 'detail' => $e->getMessage()]);
}
?>