<?php
// db/init_db.php — Cria ou recria o banco SQLite com detecção de ambiente (CLI ou Web)

$dir = __DIR__;
$dbFile = $dir . '/database.sqlite';
$isCli = (php_sapi_name() === 'cli');

function msg($text, $color = null) {
    global $isCli;
    if ($isCli) {
        // Modo terminal (cores ANSI)
        $colors = [
            'green' => "\033[32m",
            'red'   => "\033[31m",
            'yellow'=> "\033[33m",
            'blue'  => "\033[34m",
            'reset' => "\033[0m",
        ];
        $prefix = $color && isset($colors[$color]) ? $colors[$color] : '';
        $reset = $prefix ? $colors['reset'] : '';
        echo $prefix . $text . $reset . PHP_EOL;
    } else {
        // Modo navegador (HTML colorido)
        $colors = [
            'green' => 'style="color:green;font-weight:bold;"',
            'red'   => 'style="color:red;font-weight:bold;"',
            'yellow'=> 'style="color:orange;font-weight:bold;"',
            'blue'  => 'style="color:blue;font-weight:bold;"'
        ];
        $style = $colors[$color] ?? '';
        echo "<p $style>$text</p>";
    }
}

// Detecta se o usuário pediu recriação
$recriar = false;

if ($isCli) {
    if (file_exists($dbFile)) {
        msg("⚠️  O banco de dados já existe em: $dbFile", 'yellow');
        echo "Deseja recriar o banco? (s/n): ";
        $resp = strtolower(trim(fgets(STDIN)));
        $recriar = ($resp === 's');
    }
} else {
    $recriar = isset($_GET['recriar']) && $_GET['recriar'] === '1';
    if (file_exists($dbFile) && !$recriar) {
        echo "<h2>⚠️ Banco de dados já existe</h2>";
        echo "<p>Arquivo: <code>$dbFile</code></p>";
        echo '<p><a href="?recriar=1" style="color:red;">Recriar banco de dados</a></p>';
        exit;
    }
}

// Se o usuário pediu recriação, apaga o arquivo existente
if ($recriar && file_exists($dbFile)) {
    unlink($dbFile);
    msg("🗑️  Banco de dados antigo removido.", 'red');
}

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cria tabelas e índice
    $sql = "
        CREATE TABLE IF NOT EXISTS companies (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            cnpj TEXT UNIQUE,
            data TEXT,
            cached_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS clients (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            cnpj TEXT UNIQUE,
            company_name TEXT,
            fantasy_name TEXT,
            address TEXT,
            city TEXT,
            state TEXT,
            cep TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE UNIQUE INDEX IF NOT EXISTS idx_clients_cnpj ON clients (cnpj);
    ";

    $pdo->exec($sql);

    msg("✅ Banco de dados criado com sucesso!", 'green');
    msg("📁 Caminho: $dbFile", 'blue');

} catch (Exception $e) {
    msg("❌ Erro: " . $e->getMessage(), 'red');
    if (!$isCli) {
        http_response_code(500);
    }
    exit(1);
}
?>