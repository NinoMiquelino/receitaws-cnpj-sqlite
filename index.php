<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Cadastro por CNPJ — Demo</title>
  <meta name="author" content="Onivaldo Miquelino">  
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-start justify-center p-6 overflow-auto">
<div class="max-w-2xl w-full bg-white rounded-xl shadow p-6">
    <h1 class="text-2xl font-semibold mb-4">🔗⚙️🌐 Integração ERP/CRM com ReceitaWS</h1>
    <p class="text font-semibold mb-4">Cadastro Automático de Clientes ou Fornecedores por CNPJ</p>
<div class="mb-4 overflow-hidden">
  <label class="block text-sm font-medium text-gray-700">CNPJ</label>
  <div class="flex gap-2 mt-2">
    <input id="cnpj" type="text" placeholder="00.000.000/0000-00"
           class="flex-1 min-w-0 border rounded px-3 py-2" />
    <button id="buscar" class="flex-shrink-0 bg-blue-600 text-white px-4 py-2 rounded">
      Buscar
    </button>
  </div>
  <p id="msg" class="text text-black-600 mt-2"></p>
</div>

    <form id="form" class="space-y-3" onsubmit="return false;">
      <div>
        <label class="block text-sm">Razão Social</label>
        <input id="company_name" name="company_name" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm">Nome Fantasia</label>
        <input id="fantasy_name" name="fantasy_name" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm">Endereço</label>
        <input id="address" name="address" class="w-full border rounded px-3 py-2" />
      </div>
      <div class="grid grid-cols-3 gap-3">
        <div>
          <label class="block text-sm">Cidade</label>
          <input id="city" name="city" class="w-full border rounded px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm">UF</label>
          <input id="state" name="state" class="w-full border rounded px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm">CEP</label>
          <input id="cep" name="cep" class="w-full border rounded px-3 py-2" />
        </div>
      </div>

<div class="flex justify-between items-center mt-4">
  <!-- Botão à esquerda -->
  <a href="view_data.php" 
     class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded">
     🔍 Visualizar Dados 
  </a>

  <!-- Botões à direita -->
  <div class="flex gap-2">
    <button type="button" id="salvar" 
            class="bg-green-600 text-white px-4 py-2 rounded hidden">
      Salvar
    </button>
    <button type="button" id="novo" 
            class="bg-blue-600 text-white px-4 py-2 rounded hidden">
      Novo
    </button>
  </div>
</div>        
      
    </form>

    <div id="result" class="mt-4 text-sm text-gray-600"></div>
  </div>

<script>
        // Configuração para máscara de CNPJ e limpeza automática
        document.getElementById('cnpj').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 14) value = value.substring(0, 14);
            
            if (value.length > 12) {
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5");
            } else if (value.length > 8) {
                value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{0,4})/, "$1.$2.$3/$4");
            } else if (value.length > 5) {
                value = value.replace(/^(\d{2})(\d{3})(\d{0,3})/, "$1.$2.$3");
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{0,3})/, "$1.$2");
            }
            e.target.value = value;
        });


function onlyDigits(v){return v.replace(/\D/g,'');}

async function lookupCnpj(cnpj){
  const url = `api.php?action=lookup&cnpj=${encodeURIComponent(cnpj)}`;
  const res = await fetch(url);
  return res.json();
}

document.getElementById('buscar').addEventListener('click', async ()=>{
  const cnpjEl = document.getElementById('cnpj');
  const msgEl = document.getElementById('msg');
  const cnpj = onlyDigits(cnpjEl.value);
  msgEl.textContent = '';

  if (cnpj.length !== 14){
    msgEl.textContent = '❌ CNPJ inválido';
    return;
  }
  
  if (!validarCNPJ(document.getElementById("cnpj").value)) {
    msgEl.textContent = '❌ Número de CNPJ inválido';
    return;
  }	

  msgEl.textContent = '🔄 Consultando dados na ReceitaWS...';
  try {
    const data = await lookupCnpj(cnpj);
    if (data.error){
      msgEl.textContent = '❌ '+data.error;
      return;
    }

    const d = data.data || {};
    document.getElementById('company_name').value = d.nome || '';
    document.getElementById('fantasy_name').value = d.fantasia || '';
    const address = [d.logradouro || '', d.numero || '', d.complemento || ''].filter(Boolean).join(', ');
    document.getElementById('address').value = address;
    document.getElementById('city').value = d.municipio || '';
    document.getElementById('state').value = d.uf || '';
    document.getElementById('cep').value = d.cep || '';
    msgEl.textContent = data.source === 'cache'
      ? '✅ Dados carregados do cache local.🛢'
      : '✅ Dados obtidos da ReceitaWS com sucesso!';      
      document.getElementById('buscar').classList.add('hidden');
      document.getElementById('salvar').classList.remove('hidden');
  } catch(e){
    msgEl.textContent = '❌ Erro ao consultar CNPJ.';
    console.error(e);
  }
});

document.getElementById('salvar').addEventListener('click', async ()=>{
  const payload = {
    cnpj: onlyDigits(document.getElementById('cnpj').value),
    company_name: document.getElementById('company_name').value,
    fantasy_name: document.getElementById('fantasy_name').value,
    address: document.getElementById('address').value,
    city: document.getElementById('city').value,
    state: document.getElementById('state').value,
    cep: onlyDigits(document.getElementById('cep').value)
  };

  try {
    const res = await fetch('api.php?action=save', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    });

    const json = await res.json();
    const resultEl = document.getElementById('result');

    if (json.success){
      resultEl.textContent = `✅ Cliente cadastrado com sucesso (ID ${json.id})`;      
      document.getElementById('salvar').classList.add('hidden');
      document.getElementById('novo').classList.remove('hidden');
    } else {
      resultEl.textContent = '❌ Erro ao salvar o cadastro: '+json.error+' '+json.detail;
      console.error(json);
    }
  } catch (e) {
    document.getElementById('result').textContent = '❌ Erro na requisição de salvamento.';
    console.error(e);
  }
});

function validarCNPJ(cnpj) {
    cnpj = cnpj.replace(/[^\d]+/g, '');
    if (cnpj.length !== 14) return false;
    // Elimina CNPJs inválidos conhecidos
    if (/^(\d)\1{13}$/.test(cnpj)) return false;
    // Validação do primeiro dígito verificador
    let tamanho = cnpj.length - 2;
    let numeros = cnpj.substring(0, tamanho);
    let digitos = cnpj.substring(tamanho);
    let soma = 0;
    let pos = tamanho - 7;
    for (let i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2) pos = 9;
    }
    let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado !== parseInt(digitos.charAt(0))) return false;
    // Validação do segundo dígito verificador
    tamanho = tamanho + 1;
    numeros = cnpj.substring(0, tamanho);
    soma = 0;
    pos = tamanho - 7;
    for (let i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2) pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado !== parseInt(digitos.charAt(1))) return false;
    return true;
}

document.getElementById('novo').addEventListener('click', async ()=>{
novoCadastro();
});

function novoCadastro() {
document.getElementById('novo').classList.add('hidden');
document.getElementById('buscar').classList.remove('hidden');
document.getElementById('cnpj').value = '';
clearPreviousResults();
}

function clearPreviousResults() {
document.getElementById('company_name').value = '';
document.getElementById('fantasy_name').value = ''; 
document.getElementById('address').value = '';
document.getElementById('city').value = '';
document.getElementById('state').value = '';
document.getElementById('cep').value = '';
document.getElementById('msg').textContent = '';
document.getElementById('result').textContent = '';
}

function newInicio() {
document.getElementById('salvar').classList.add('hidden');	
document.getElementById('novo').classList.add('hidden');
document.getElementById('buscar').classList.remove('hidden');
clearPreviousResults();
}

// Limpar consulta anterior quando o usuário interagir com o campo
document.getElementById('cnpj').addEventListener('focus', function() {
newInicio();
});

document.getElementById('cnpj').addEventListener('click', function() {
    newInicio();
});
</script>
</body>
</html>