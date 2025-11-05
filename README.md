## 🙋‍♂️ Autor

<div align="center">
  <img src="https://avatars.githubusercontent.com/ninomiquelino" width="100" height="100" style="border-radius: 50%">
  <br>
  <strong>Onivaldo Miquelino</strong>
  <br>
  <a href="https://github.com/ninomiquelino">@ninomiquelino</a>
</div>

---

# 🏢 Integração ERP/CRM com API ReceitaWS e Banco SQLite (CNPJ Automático)

🔗 **Consulta, cadastro e gerenciamento de empresas com PHP + SQLite + Fetch API**

![PHP](https://img.shields.io/badge/PHP-8.2-blue)
![SQLite](https://img.shields.io/badge/SQLite-Database-orange)
![JavaScript](https://img.shields.io/badge/Frontend-Fetch_API-yellow)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-38B2AC.svg?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Responsive](https://img.shields.io/badge/Design-Responsive-FF6B6B.svg?style=for-the-badge)
![License](https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge)
![Version 1.0.0](https://img.shields.io/badge/Version-1.0.0-blue)
![GitHub stars](https://img.shields.io/github/stars/NinoMiquelino/receitaws-cnpj-sqlite?style=social)
![GitHub forks](https://img.shields.io/github/forks/NinoMiquelino/receitaws-cnpj-sqlite?style=social)
![GitHub issues](https://img.shields.io/github/issues/NinoMiquelino/receitaws-cnpj-sqlite)

---

## 📝 Sobre o Projeto

O **ReceitaWS CNPJ SQLite** é um sistema simples e funcional que permite consultar informações de CNPJs diretamente na **API pública ReceitaWS**, salvar os resultados em um banco **SQLite**, e gerenciá-los através de uma interface **PHP + JavaScript (Fetch API)** moderna, sem recarregar a página.

Ideal para estudos de integração com APIs REST, CRUD em PHP e uso do SQLite sem necessidade de servidor MySQL.

---

## 🚀 Funcionalidades

✅ Consulta automática de dados via **ReceitaWS**  
✅ Salvamento local em **SQLite**  
✅ CRUD completo:
- **Criar:** cadastro automático a partir da consulta  
- **Visualizar:** tabela dinâmica atualizada em tempo real  
- **Editar:** modal estilizado com validação  
- **Excluir:** modal de confirmação visual  
✅ Recriação do banco com um clique  
✅ Anti-cache habilitado (sempre mostra dados atuais)  
✅ Interface responsiva e leve com **TailwindCSS**

---

## 🧩 Estrutura do Projeto

```
receitaws-cnpj-sqlite/
├── index.php
├── api.php
├── view_data.php
├── edit_data.php
├── delete_data.php
├── 📁 db/
│      └── init_db.php
├── README.md
└── .gitignore                     
```

---

## ⚙️ Tecnologias Utilizadas

| Camada | Tecnologias |
|---------|--------------|
| **Backend** | PHP 8+, PDO, SQLite |
| **Frontend** | HTML5, TailwindCSS, JavaScript (Fetch API, modais, máscaras) |
| **API Externa** | [ReceitaWS](https://www.receitaws.com.br/) |
| **Segurança** | CSP, headers no-cache, sanitização de dados |

---

## 🧠 Fluxo de Funcionamento

1. O usuário informa um **CNPJ** e clica em **Buscar**.  
2. O sistema envia uma requisição `fetch()` para `api.php`.  
3. O PHP consulta a **ReceitaWS**, recebe os dados e grava no banco **SQLite**.  
4. O usuário pode então **visualizar**, **editar** ou **excluir** os cadastros diretamente em `view_data.php`.  
5. Tudo acontece de forma **assíncrona**, sem recarregar a página.

---

## 🖥️ Exemplo Visual

| Tela | Descrição |
|------|------------|
| 🧾 **Consulta e Cadastro** | Busca e armazena CNPJs automaticamente |
| 📋 **Listagem** | Mostra empresas cadastradas com botões de ação |
| ✏️ **Edição** | Modal estilizado com feedback visual |
| 🗑️ **Exclusão** | Modal de confirmação antes da remoção |

---

## 🔐 Segurança Implementada

```php
// nocache.php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
```

• Todas as requisições sensíveis usam POST.
• Dados validados no frontend e backend.
• Respostas formatadas em JSON para integração segura.

🧪 Como Executar Localmente

1 - Clone o repositório

```
git clone https://github.com/NinoMiquelino/receitaws-cnpj-sqlite.git
cd receitaws-cnpj-sqlite
```

2 - Crie o banco de dados

```
php db/init_db.php
```

3- Inicie o servidor PHP

```
php -S localhost:8000
```

4 - Acesse no navegador

```
http://localhost:8000
```

📚 Objetivo Educacional

• Este projeto foi criado para fins educacionais e demonstrativos, ideal para quem deseja aprender:
• Integração de APIs REST em PHP
• Uso do SQLite com PDO
• CRUD completo (Create, Read, Update, Delete)
• Requisições assíncronas com Fetch API
• Validação de formulários e UX moderno

🧭 Melhorias Futuras

• 🔍 Filtro e busca na listagem
• 📄 Exportação em CSV/JSON
• 👥 Sistema de login para uso multiusuário
• 📱 Melhorias de responsividade mobile
• 🌐 Cache inteligente da API ReceitaWS

💡 Aprenda, explore e contribua!

Este repositório mostra como unir simplicidade, segurança e integração de APIs em um projeto PHP moderno e funcional.

---

## 🤝 Contribuições
Contribuições são sempre bem-vindas!  
Sinta-se à vontade para abrir uma [*issue*](https://github.com/NinoMiquelino/receitaws-cnpj-sqlite/issues) com sugestões ou enviar um [*pull request*](https://github.com/NinoMiquelino/receitaws-cnpj-sqlite/pulls) com melhorias.

---

## 💬 Contato
📧 [Entre em contato pelo LinkedIn](https://www.linkedin.com/in/onivaldomiquelino/)  
💻 Desenvolvido por **Onivaldo Miquelino**

---
