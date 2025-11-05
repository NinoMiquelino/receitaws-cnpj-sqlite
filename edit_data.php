<?php
// edit_data.php — Atualiza dados de um cliente existente
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

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

// Lê dados JSON enviados via fetch
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['id'])) {
    http_response_code(400);
    echo json_encode(['error' => '❌ ID do cliente não informado ou dados inválidos.']);
    exit;
}

$id = (int)$input['id'];
$company_name = trim($input['company_name'] ?? '');
$fantasy_name = trim($input['fantasy_name'] ?? '');
$address = trim($input['address'] ?? '');
$city = trim($input['city'] ?? '');
$state = trim($input['state'] ?? '');
$cep = preg_replace('/\D/', '', $input['cep'] ?? '');

// Atualiza o registro
try {
    $stmt = $pdo->prepare("
        UPDATE clients 
        SET company_name = :company_name,
            fantasy_name = :fantasy_name,
            address = :address,
            city = :city,
            state = :state,
            cep = :cep
        WHERE id = :id
    ");
    $stmt->execute([
        ':company_name' => $company_name,
        ':fantasy_name' => $fantasy_name,
        ':address' => $address,
        ':city' => $city,
        ':state' => $state,
        ':cep' => $cep,
        ':id' => $id
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => '✅ Cliente atualizado com sucesso.']);
    } else {
        echo json_encode(['success' => false, 'message' => '⚠️ Nenhuma alteração realizada.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => '❌ Erro ao atualizar cliente.', 'detail' => $e->getMessage()]);
}
?>