SET NAMES utf8mb4;

DROP DATABASE IF EXISTS loja;
CREATE DATABASE loja CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE loja;


-- ---------- USUÁRIOS (clientes e administradores) ----------
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('cliente', 'admin') NOT NULL DEFAULT 'cliente',
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ---------- CATEGORIAS ----------
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL
);

-- ---------- PRODUTOS ----------
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    estoque INT NOT NULL DEFAULT 0,
    imagem VARCHAR(255),
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- ---------- PEDIDOS ----------
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    forma_pagamento ENUM('PIX', 'Boleto', 'Cartao') NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- ---------- ITENS DO PEDIDO (liga pedidos e produtos) ----------
CREATE TABLE itens_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);


-- =========================================================
-- DADOS DE TESTE
-- =========================================================

-- Senhas criptografadas com password_hash():
--   admin@loja.com   -> senha: admin123
--   cliente@loja.com -> senha: cliente123
INSERT INTO usuarios (nome, email, senha, perfil) VALUES
('Administrador', 'admin@loja.com', '$2y$10$SsfYPttUGqGVxj8CcfWM0OAMghebBLhEDQ/57uXNJCBk/f3zM3gVO', 'admin'),
('Cliente Teste', 'cliente@loja.com', '$2y$10$Xveu/zEDRORydfrnsBRh4On7OYqZFADDrmIry48QWLZgKpSAL/BTy', 'cliente');

INSERT INTO categorias (nome) VALUES
('Periféricos'),
('Áudio'),
('Cabos e Carregadores');

INSERT INTO produtos (categoria_id, nome, descricao, preco, estoque, imagem) VALUES
(1, 'Mouse Gamer RGB', 'Mouse com 6 botões, sensor de 7200 DPI e iluminação RGB.', 89.90, 15, 'mouse-gamer.jpg'),
(1, 'Teclado Mecânico', 'Teclado mecânico ABNT2 com switches azuis e iluminação LED.', 199.90, 8, 'teclado-mecanico.jpg'),
(1, 'Mousepad Grande', 'Mousepad 80x30 cm com base antiderrapante e bordas costuradas.', 49.90, 20, 'mousepad.jpg'),
(2, 'Headset Gamer', 'Headset com microfone removível e som estéreo.', 159.90, 5, 'headset.jpg'),
(2, 'Fone Bluetooth', 'Fone sem fio com estojo de carregamento e até 20 horas de bateria.', 129.90, 12, 'fone-bluetooth.jpg'),
(2, 'Caixa de Som Portátil', 'Caixa de som Bluetooth resistente à água.', 99.90, 0, 'caixa-de-som.jpg'),
(3, 'Cabo USB-C 2m', 'Cabo USB-C reforçado com carregamento rápido.', 29.90, 30, 'cabo-usb-c.jpg'),
(3, 'Carregador Turbo 20W', 'Carregador de parede com saída USB-C e carregamento rápido.', 69.90, 10, 'carregador.jpg'),
(3, 'Power Bank 10.000 mAh', 'Bateria portátil com duas saídas USB.', 119.90, 3, 'power-bank.jpg');

-- Um pedido de exemplo, para o painel administrativo já ter o que mostrar
INSERT INTO pedidos (usuario_id, forma_pagamento, total) VALUES
(2, 'PIX', 149.70);

INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES
(1, 1, 1, 89.90),
(1, 7, 2, 29.90);