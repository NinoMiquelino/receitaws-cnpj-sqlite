<?php
// view_data.php — Visualização e edição dos registros
// Desativa qualquer cache armazenado no navegador ou em proxies intermediários
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// Compatibilidade com navegadores antigos (como Internet Explorer)
header("Cache-Control: post-check=0, pre-check=0", false);
// Para conexões HTTP/1.0
header("Pragma: no-cache");
// Define a expiração imediata
header("Expires: 0");

$dir = __DIR__;
$dbFile = $dir . '/db/database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $rows = $pdo->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Visualizar Dados</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
  <div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow">
  
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-semibold">📋 Dados ERP/CRM</h1>
      <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Voltar</a>
    </div>    

    <!-- Campo de Busca -->
    <div class="mb-4">
      <input type="text" id="busca" placeholder="🔍 Buscar por CNPJ, Razão Social, Nome Fantasia ou Cidade..."
             class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" />
    </div>

    <div class="overflow-x-auto">
      <table id="tabela" class="min-w-full border border-gray-300 rounded-lg overflow-hidden">
        <thead class="bg-gray-200 text-gray-700">
          <tr>
            <th class="px-3 py-2 text-left text-sm font-semibold text-gray-700">ID</th>
            <th class="px-3 py-2 text-left text-sm font-semibold text-gray-700">CNPJ</th>
            <th class="px-3 py-2 text-left text-sm font-semibold text-gray-700">Razão Social</th>
            <th class="px-3 py-2 text-left text-sm font-semibold text-gray-700">Nome Fantasia</th>
            <th class="px-3 py-2 text-left text-sm font-semibold text-gray-700">Cidade</th>
            <th class="px-3 py-2 text-left text-sm font-semibold text-gray-700">UF</th>
            <th class="px-3 py-2 text-left text-sm font-semibold text-gray-700">CEP</th>
            <th class="p-2 text-left">Ações</th>
          </tr>
        </thead>
        <tbody id="corpoTabela">
          <?php foreach ($rows as $r): ?>
            <tr class="border-t hover:bg-gray-50 transition">
              <td class="px-3 py-2 text-sm text-gray-600"><?= htmlspecialchars($r['id']) ?></td>
              <td class="px-3 py-2 text-sm text-gray-600"><?= htmlspecialchars($r['cnpj']) ?></td>
              <td class="px-3 py-2 text-sm text-gray-600"><?= htmlspecialchars($r['company_name']) ?></td>
              <td class="px-3 py-2 text-sm text-gray-600"><?= htmlspecialchars($r['fantasy_name']) ?></td>
              <td class="px-3 py-2 text-sm text-gray-600"><?= htmlspecialchars($r['city']) ?></td>
              <td class="px-3 py-2 text-sm text-gray-600"><?= htmlspecialchars($r['state']) ?></td>
              <td class="px-3 py-2 text-sm text-gray-600"><?= htmlspecialchars($r['cep']) ?></td>
              <td class="p-2 flex gap-2">
                <button onclick='abrirModal(<?= json_encode($r) ?>)'
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-sm">Editar</button>
               <button data-id="<?= htmlspecialchars($r['id'], ENT_QUOTES) ?>" data-name="<?= htmlspecialchars($r['company_name'], ENT_QUOTES) ?>" onclick="excluir(this)" 
                        class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-sm">Excluir</button>                                                     
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal de Edição -->
  <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
      <h2 class="text-lg font-semibold mb-4">Editar Cliente</h2>
      <form id="editForm" class="space-y-3">
        <input type="hidden" id="edit_id">

        <div>
          <label class="block text-sm font-medium">Razão Social</label>
          <input id="edit_company_name" class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="block text-sm font-medium">Nome Fantasia</label>
          <input id="edit_fantasy_name" class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="block text-sm font-medium">Endereço</label>
          <input id="edit_address" class="w-full border rounded px-3 py-2">
        </div>

        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="block text-sm font-medium">Cidade</label>
            <input id="edit_city" class="w-full border rounded px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium">UF</label>
            <input id="edit_state" class="w-full border rounded px-3 py-2">
          </div>
          <div>
            <label class="block text-sm font-medium">CEP</label>
            <input id="edit_cep" class="w-full border rounded px-3 py-2">
          </div>
        </div>

        <div class="flex justify-end gap-2 mt-4">
          <button type="button" onclick="fecharModal()" class="px-4 py-2 bg-gray-400 text-white rounded">Cancelar</button>
          <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Salvar</button>
        </div>
      </form>
    </div>
  </div>
  
  <div id="spinner" class="fixed inset-0 bg-gray-700 bg-opacity-40 flex items-center justify-center hidden z-50">
  <div class="bg-white p-4 rounded-lg shadow text-center">
    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600 mx-auto mb-2"></div>
    <p class="text-blue-700 font-medium">Aguarde... salvando alterações</p>
  </div>
</div>

<!-- Modal de confirmação -->
<div id="modalExcluir" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-2xl shadow-lg p-6 w-80 text-center">
    <h2 class="text-lg font-semibold text-gray-800 mb-3">Confirmar Exclusão</h2>
    <p id="modalExcluirTexto" class="text-gray-600 mb-5"></p>
    <p id="modalNomeTexto" class="text-gray-600 mb-5"></p>
    <div class="flex justify-center gap-3 mt-4">
      <button id="btnCancelar" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 text-gray-800">Cancelar</button>
      <button id="btnConfirmar" class="px-4 py-2 bg-red-600 rounded-lg text-white hover:bg-red-700">Excluir</button>
    </div>
  </div>
</div>

  <script>
  const modal = document.getElementById('editModal');
  const form = document.getElementById('editForm');
  const spinner = document.getElementById('spinner');

  function abrirModal(data) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_company_name').value = data.company_name;
    document.getElementById('edit_fantasy_name').value = data.fantasy_name;
    document.getElementById('edit_address').value = data.address;
    document.getElementById('edit_city').value = data.city;
    document.getElementById('edit_state').value = data.state;
    document.getElementById('edit_cep').value = data.cep;
  }

  function fecharModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    spinner.classList.remove('hidden'); // mostra o overlay

    const data = {
      id: document.getElementById('edit_id').value,
      company_name: document.getElementById('edit_company_name').value,
      fantasy_name: document.getElementById('edit_fantasy_name').value,
      address: document.getElementById('edit_address').value,
      city: document.getElementById('edit_city').value,
      state: document.getElementById('edit_state').value,
      cep: document.getElementById('edit_cep').value
    };
    
    try {
  const res = await fetch('edit_data.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  });
  
  const text = await res.text();
  //alert('Retorno bruto: '+text);
  
  try {
    const result = JSON.parse(text); // converte string JSON  objeto
     alert(result.message || result.error || 'Sem mensagem retornada.');
   } catch (e) {
     alert('Erro ao interpretar o JSON retornado: ' + e.message);
   }
  
   } catch (error) {
     console.error('Erro: ', error);
     alert('Erro ao atualizar: ' + error);
   } finally {
    spinner.classList.add('hidden'); // esconde o overlay
    fecharModal();
    location.reload();
  }
    
  });

async function excluir(buttonElement) {
  const id = buttonElement.dataset.id;
  const name = buttonElement.dataset.name || 'este registro';
  const ok = confirm(`🚨 Deseja realmente excluir o cliente:\n\n${name}?`);
  if (!ok) return;

  try {
    const res = await fetch('delete_data.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const result = await res.json();
    alert(result.message || result.error || 'Operação concluída.');
    if (result.success || result.status === 'success') location.reload();
  } catch (err) {
    console.error(err);
    alert('Erro ao excluir: ' + err.message);
  }
}

  // 🔍 Filtro de busca instantâneo
  document.getElementById('busca').addEventListener('input', function () {
    const termo = this.value.toLowerCase();
    const linhas = document.querySelectorAll('#corpoTabela tr');

    linhas.forEach(linha => {
      const texto = linha.textContent.toLowerCase();
      linha.style.display = texto.includes(termo) ? '' : 'none';
    });
  });
  
let idParaExcluir = null;
let nomeParaExcluir = null;

function excluir(buttonElement) {
  idParaExcluir = buttonElement.dataset.id;
  nomeParaExcluir = buttonElement.dataset.name || 'este registro';
  document.getElementById('modalExcluirTexto').textContent =
    `🚨 Deseja realmente excluir o registro?`;    
  document.getElementById('modalNomeTexto').textContent =`${nomeParaExcluir}`;
  document.getElementById('modalExcluir').classList.remove('hidden');
}

// Fecha o modal
document.getElementById('btnCancelar').addEventListener('click', () => {
  document.getElementById('modalExcluir').classList.add('hidden');
  idParaExcluir = null;
});

// Confirma exclusão
document.getElementById('btnConfirmar').addEventListener('click', async () => {
  if (!idParaExcluir) return;

  try {
    const res = await fetch('delete_data.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: idParaExcluir })
    });
        
  const text = await res.text();
  //alert('Retorno bruto: '+text);
  
  try {
  const result = JSON.parse(text); // converte string JSON  objeto
  alert(result.message || result.error || 'Operação concluída.');
  if (result.success || result.status === 'success') location.reload();
  } catch (e) {
    alert('Erro ao interpretar o JSON retornado: ' + e.message);
  }
    
  } catch (err) {
    console.error(err);
    alert('Erro ao excluir: ' + err.message);
  } finally {
    document.getElementById('modalExcluir').classList.add('hidden');
    idParaExcluir = null;
  }
});  
</script>
</body>
</html>