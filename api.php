<?php
// api.php
// Endpoint simples para lookup (ReceitaWS) e salvar cliente no SQLite.
// Uso:
// GET  api.php?action=lookup&cnpj=00000000000191
// POST api.php?action=save  (JSON no corpo)

// Desativa qualquer cache armazenado no navegador ou em proxies intermediários
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// Compatibilidade com navegadores antigos (como Internet Explorer)
header("Cache-Control: post-check=0, pre-check=0", false);
// Para conexões HTTP/1.0
header("Pragma: no-cache");
// Define a expiração imediata
header("Expires: 0");
/** Configurações básicas */
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$baseDir = __DIR__;
$dbFile = $baseDir . '/db/database.sqlite';

if (!file_exists($dbFile)) {
    http_response_code(500);
    echo json_encode(['error' => 'Banco de dados não encontrado. Execute db/init_db.php', 'detail' => '']);
    exit;
}

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Falha ao conectar ao banco: ' . $e->getMessage(), 'detail' => '']);
    exit;
}

/** Helpers */
function onlyDigits($s){ return preg_replace('/\D/', '', (string)$s); }

if ($action === 'lookup') {
    $cnpj = onlyDigits($_GET['cnpj'] ?? '');
    if (strlen($cnpj) !== 14) {
        http_response_code(400);
        echo json_encode(['error' => 'CNPJ inválido.', 'detail' => 'Quantidade de digitos.']);
        exit;
    }
    
    if (!validarCNPJ($cnpj)) {	
       echo json_encode(['error' => 'Número de CNPJ inválido.', 'detail' => 'Não validado.']);
       exit;		
    }   

    // Verifica cache local
    $stmt = $pdo->prepare('SELECT data, cached_at FROM companies WHERE cnpj = :cnpj');
    $stmt->execute([':cnpj' => $cnpj]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // TTL de cache opcional: aqui usaremos 24 horas (86400 segundos)
    $useCache = false;
    if ($row) {
        $cachedAt = strtotime($row['cached_at']);
        if ($cachedAt !== false && (time() - $cachedAt) < 86400) {
            $useCache = true;
        }
    }

    if ($useCache) {
        $data = json_decode($row['data'], true);
        echo json_encode(['source' => 'cache', 'data' => $data]);
        exit;
    }

    // Consulta ReceitaWS
    $url = 'https://www.receitaws.com.br/v1/cnpj/' . $cnpj;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-ReceitaWS-Client/1.0');
    // Opcional: forçar HTTPS e verificar certificado (padrão cURL faz verificação)
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false || $httpCode !== 200) {
        http_response_code(502);
        echo json_encode(['error' => 'Falha ao consultar ReceitaWS', 'detail' => $err, 'http_code' => $httpCode]);
        exit;
    }

    $data = json_decode($resp, true);
    if (!$data || (isset($data['status']) && strtoupper($data['status']) === 'ERROR')) {
        http_response_code(404);
        echo json_encode(['error' => 'Empresa não encontrada ou API retornou erro', 'detail' => $data]);
        exit;
    }

    // Salva/atualiza cache local
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare('INSERT INTO companies (cnpj, data, cached_at) VALUES (:cnpj, :data, :cached_at)
                          ON CONFLICT(cnpj) DO UPDATE SET data = :data2, cached_at = :cached_at2');
    $now = date('Y-m-d H:i:s');
    $stmt->execute([
        ':cnpj' => $cnpj,
        ':data' => $jsonData,
        ':cached_at' => $now,
        ':data2' => $jsonData,
        ':cached_at2' => $now
    ]);

    echo json_encode(['source' => 'receitaws', 'data' => $data]);
    exit;
}

if ($action === 'save') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Payload inválido', 'detail' => '']);
        exit;
    }

    $cnpj = onlyDigits($input['cnpj'] ?? '');
    if (strlen($cnpj) !== 14) {
        http_response_code(400);
        echo json_encode(['error' => 'CNPJ inválido no payload', 'detail' => '']);
        exit;
    }

    $company_name = substr(trim($input['company_name'] ?? ''), 0, 255);
    $fantasy_name = substr(trim($input['fantasy_name'] ?? ''), 0, 255);
    $address = substr(trim($input['address'] ?? ''), 0, 255);
    $city = substr(trim($input['city'] ?? ''), 0, 100);
    $state = substr(trim($input['state'] ?? ''), 0, 10);
    $cep = onlyDigits($input['cep'] ?? '');

try {
    // Verifica se já existe o CNPJ no banco
    $check = $pdo->prepare('SELECT id FROM clients WHERE cnpj = :cnpj LIMIT 1');
    $check->execute([':cnpj' => $cnpj]);
    if ($check->fetch()) {
        http_response_code(409); // 409 Conflict
        echo json_encode(['error' => 'Este CNPJ já está cadastrado no sistema.', 'detail' => '']);
        exit;
    }
    // Se não existir, insere normalmente
    $stmt = $pdo->prepare('INSERT INTO clients (cnpj, company_name, fantasy_name, address, city, state, cep)
                           VALUES (:cnpj, :company_name, :fantasy_name, :address, :city, :state, :cep)');
    $stmt->execute([
        ':cnpj' => $cnpj,
        ':company_name' => $company_name,
        ':fantasy_name' => $fantasy_name,
        ':address' => $address,
        ':city' => $city,
        ':state' => $state,
        ':cep' => $cep
    ]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Falha ao salvar cliente', 'detail' => $e->getMessage()]);
    exit;
}    

    
}

// Ação inválida
http_response_code(400);
echo json_encode(['error' => 'Ação inválida', 'detail' => '']);
exit;

function validarCNPJ($cnpj) {
    // Remove caracteres não numéricos
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    // Verifica se o CNPJ tem 14 dígitos
    if (strlen($cnpj) != 14) {
        return false;
    }
    // Elimina CNPJs inválidos conhecidos
    if (preg_match('/(\d)\1{13}/', $cnpj)) {
        return false;
    }
    // Validação do primeiro dígito verificador
    $tamanho = strlen($cnpj) - 2;
    $numeros = substr($cnpj, 0, $tamanho);
    $digitos = substr($cnpj, $tamanho);
    $soma = 0;
    $pos = $tamanho - 7;
    for ($i = $tamanho; $i >= 1; $i--) {
        $soma += $numeros[$tamanho - $i] * $pos--;
        if ($pos < 2) {
            $pos = 9;
        }
    }
    $resultado = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
    if ($resultado != $digitos[0]) {
        return false;
    }
    // Validação do segundo dígito verificador
    $tamanho = $tamanho + 1;
    $numeros = substr($cnpj, 0, $tamanho);
    $soma = 0;
    $pos = $tamanho - 7;
    for ($i = $tamanho; $i >= 1; $i--) {
        $soma += $numeros[$tamanho - $i] * $pos--;
        if ($pos < 2) {
            $pos = 9;
        }
    }
    $resultado = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
    if ($resultado != $digitos[1]) {
        return false;
    }
    return true;
}
?>