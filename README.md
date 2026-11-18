# TechStore – E-commerce de Acessórios de Tecnologia

Loja virtual desenvolvida como Atividade Final do 4º Bimestre, com o fluxo completo de venda: vitrine, carrinho, cadastro/login, checkout com baixa de estoque e painel administrativo.

## Tecnologias

- HTML5, CSS3, Bootstrap 5 e JavaScript
- PHP 8 (Apache/XAMPP)
- MySQL/MariaDB com a extensão **PDO**

## Como rodar o projeto

1. Instale o **XAMPP** e ligue o **Apache** e o **MySQL**.
2. Coloque a pasta do projeto em `C:\xampp\htdocs\loja`.
3. Abra `http://localhost/phpmyadmin`, vá em **Importar** e importe o arquivo `database/schema.sql`. Ele cria o banco `loja` com as tabelas e os dados de teste.
4. Faça uma **cópia** do arquivo `.env.example`, renomeie para `.env` e preencha com os dados do seu banco (no XAMPP padrão: usuário `root` e senha vazia).
5. Acesse `http://localhost/loja`.

## Usuários de teste

| Perfil | E-mail | Senha |
|---|---|---|
| Administrador | admin@loja.com | admin123 |
| Cliente | cliente@loja.com | cliente123 |

## Funcionalidades

**Loja**
- Vitrine com produtos vindos do banco, **filtro por categoria** e **campo de busca**
- Página de cada produto com nome, código, preço, descrição, imagem e **indicador de estoque**
- **Carrinho** com `$_SESSION`: adicionar, alterar quantidade (sem passar do estoque), remover, subtotal e total
- **Cadastro e login** de clientes

**Checkout**
- Só usuários logados podem finalizar a compra
- Pagamento simulado (PIX, Boleto ou Cartão) com confirmação imediata
- Ao finalizar: cria o pedido, grava cada item com o preço da compra, **baixa o estoque** e limpa o carrinho, tudo dentro de uma **transação** (se algo falhar, nada é salvo)

**Painel administrativo (`/admin`)**
- Acesso restrito ao perfil **admin**
- CRUD de produtos: cadastrar, listar, editar preço/estoque, ativar/desativar e excluir
- Lista de pedidos com cliente, itens e valor total

## Segurança

- **Prepared Statements** do PDO em todas as consultas com dados do usuário (proteção contra SQL Injection)
- Senhas salvas com `password_hash()` e conferidas com `password_verify()`
- Rotas do `/admin` protegidas por **validação de sessão no servidor** (`admin/proteger.php`)
- Textos exibidos com `htmlspecialchars()` (proteção contra XSS)
- Credenciais do banco no arquivo `.env`, que é ignorado pelo Git (`.gitignore`) e bloqueado no navegador (`.htaccess`)

## Banco de dados

O script `database/schema.sql` recria o banco completo. Tabelas:

| Tabela | Conteúdo |
|---|---|
| `usuarios` | Clientes e administradores (`perfil`), e-mail único e senha criptografada |
| `categorias` | Categorias dos produtos |
| `produtos` | Preço (`DECIMAL`), estoque (`INT`), imagem e situação (ativo/desativado), ligados a uma categoria (FK) |
| `pedidos` | Cliente (FK), data, forma de pagamento e total |
| `itens_pedido` | Liga pedidos e produtos (relação N:N), com a quantidade e o preço unitário congelado no momento da compra |

## Estrutura de pastas

```
loja/
├── admin/          → painel administrativo
├── config/         → conexão com o banco (lê o .env)
├── css/            → estilos
├── database/       → schema.sql
├── img/produtos/   → fotos dos produtos
├── includes/       → cabeçalho e rodapé das páginas
├── .env.example    → modelo das variáveis de ambiente
└── *.php           → páginas da loja
```

## Autora

**Maria Eduarda** · [GitHub](https://github.com/dudagi64-art)