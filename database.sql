-- ============================================================
-- ShopBR — Script de criação do banco de dados
-- Compatível com MySQL 5.7+ / MariaDB 10.3+ (XAMPP)
-- Execute este script no phpMyAdmin ou via linha de comando:
--   mysql -u root -p < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS shopbr
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE shopbr;

-- ------------------------------------------------------------
-- Tabela: usuarios
-- Armazena tanto compradores quanto vendedores.
-- A coluna `tipo` distingue os perfis.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nome          VARCHAR(100)    NOT NULL,
    email         VARCHAR(254)    NOT NULL,
    senha_hash    VARCHAR(255)    NOT NULL,
    tipo          ENUM('comprador','vendedor') NOT NULL DEFAULT 'comprador',
    criado_em     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabela: vendedores
-- Dados extras exclusivos do perfil vendedor.
-- Relacionado 1:1 com usuarios.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS vendedores (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    usuario_id    INT UNSIGNED    NOT NULL,
    nome_loja     VARCHAR(100)    NOT NULL,
    cpf_cnpj      VARCHAR(18)     NOT NULL,
    telefone      VARCHAR(15)     NOT NULL,
    ativo         TINYINT(1)      NOT NULL DEFAULT 1,
    criado_em     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_vendedores_usuario   (usuario_id),
    UNIQUE KEY uq_vendedores_cpf_cnpj  (cpf_cnpj),

    CONSTRAINT fk_vendedores_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabela: categorias
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug      VARCHAR(60)  NOT NULL,
    nome      VARCHAR(80)  NOT NULL,
    icone     VARCHAR(10)  NOT NULL DEFAULT '🗂️',

    PRIMARY KEY (id),
    UNIQUE KEY uq_categorias_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categorias iniciais (mesmas do frontend)
INSERT IGNORE INTO categorias (slug, nome, icone) VALUES
    ('eletronicos',  'Eletrônicos',  '📱'),
    ('roupas',       'Roupas',       '👗'),
    ('casa',         'Casa e Jardim','🏠'),
    ('esportes',     'Esportes',     '⚽'),
    ('livros',       'Livros',       '📚'),
    ('beleza',       'Beleza',       '💄'),
    ('brinquedos',   'Brinquedos',   '🧸'),
    ('informatica',  'Informática',  '💻');

-- ------------------------------------------------------------
-- Tabela: produtos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS produtos (
    id            INT UNSIGNED       NOT NULL AUTO_INCREMENT,
    vendedor_id   INT UNSIGNED       NOT NULL,
    categoria_id  INT UNSIGNED       NOT NULL,
    nome          VARCHAR(150)       NOT NULL,
    descricao     TEXT,
    preco         DECIMAL(10, 2)     NOT NULL,
    estoque       INT UNSIGNED       NOT NULL DEFAULT 0,
    imagem_url    VARCHAR(512),
    ativo         TINYINT(1)         NOT NULL DEFAULT 1,
    criado_em     DATETIME           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_produtos_vendedor  (vendedor_id),
    KEY idx_produtos_categoria (categoria_id),
    KEY idx_produtos_ativo     (ativo),

    CONSTRAINT fk_produtos_vendedor
        FOREIGN KEY (vendedor_id)  REFERENCES vendedores(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_produtos_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adiciona coluna de visualizações em produtos (ignora se já existir)
ALTER TABLE produtos
    ADD COLUMN IF NOT EXISTS visualizacoes INT UNSIGNED NOT NULL DEFAULT 0;

-- ------------------------------------------------------------
-- Tabela: ofertas
-- Desconto aplicado a um produto por tempo determinado.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ofertas (
    id             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    produto_id     INT UNSIGNED    NOT NULL,
    desconto_pct   DECIMAL(5,2)    NOT NULL COMMENT 'Percentual de desconto: 0.00 a 100.00',
    preco_oferta   DECIMAL(10,2)   NOT NULL COMMENT 'Preço já calculado com desconto',
    data_inicio    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_fim       DATETIME        NOT NULL,
    ativa          TINYINT(1)      NOT NULL DEFAULT 1,
    criado_em      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_ofertas_produto (produto_id),
    KEY idx_ofertas_ativa   (ativa),
    KEY idx_ofertas_fim     (data_fim),

    CONSTRAINT fk_ofertas_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabela: carrinho_itens
-- Carrinho persistente por usuário (comprador).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS carrinho_itens (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id   INT UNSIGNED NOT NULL,
    produto_id   INT UNSIGNED NOT NULL,
    quantidade   INT UNSIGNED NOT NULL DEFAULT 1,
    adicionado_em DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_carrinho_usuario_produto (usuario_id, produto_id),
    KEY idx_carrinho_usuario (usuario_id),

    CONSTRAINT fk_carrinho_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_carrinho_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- ShopBR — Dados de Teste (Seed)
-- Senha de todos os usuários: senha123
-- Hash gerado com password_hash('senha123', PASSWORD_BCRYPT)
-- ============================================================

-- ------------------------------------------------------------
-- Usuários
-- 1 comprador comum | 1 admin/vendedor | 3 vendedores
-- ------------------------------------------------------------
INSERT IGNORE INTO usuarios (id, nome, email, senha_hash, tipo) VALUES
-- Comprador comum
(1,  'Carlos Comprador',  'carlos@teste.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'comprador'),
-- Vendedor admin (também é vendedor)
(2,  'Admin',   'admin@gmail.com',    '$2y$12$gqXnbVFHfm1v39RfbKXPm.lmLMmtCFAWgjDK5OsuYTkEfSDeTy6mC',   'comprador'),
-- Vendedores
(3,  'Ana Eletronics',    'ana@eletronics.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendedor'),
(4,  'Bruno Moda',        'bruno@moda.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendedor'),
(5,  'Admin Vendedor', 'AdminV@informatica.com', '$2y$12$bScscpFaD3UtXRD7tGXuQ.Tpp1ZgTnk2F5suQjIy2k4WwTK5CGAaG', 'vendedor'),
(6,  'Carla Casa',        'carla@casa.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendedor');

-- ------------------------------------------------------------
-- Perfis de vendedor
-- ------------------------------------------------------------
INSERT IGNORE INTO vendedores (id, usuario_id, nome_loja, cpf_cnpj, telefone, ativo) VALUES
(1, 2, 'ShopBR Admin',       '00.000.000/0001-00', '(11) 90000-0000', 1),
(2, 3, 'Ana Eletronics',     '111.111.111-11',     '(11) 91111-1111', 1),
(3, 4, 'Bruno Moda Store',   '222.222.222-22',     '(21) 92222-2222', 1),
(4, 6, 'Carla Casa & Cia',   '333.333.333-33',     '(31) 93333-3333', 1),
(5, 5, 'Loja Teste',  '00.000.000/0000-00',     '(11) 11111-1111', 1);

-- ------------------------------------------------------------
-- Produtos
-- vendedor 1 (Admin): mix de categorias
-- vendedor 2 (Ana):   eletrônicos / informática
-- vendedor 3 (Bruno): roupas / esportes
-- vendedor 4 (Carla): casa / beleza
-- ------------------------------------------------------------
INSERT IGNORE INTO produtos (id, vendedor_id, categoria_id, nome, descricao, preco, estoque, imagem_url, ativo, visualizacoes) VALUES
-- Admin (vendedor_id = 1)
(1,  1, 1, 'Smartphone XPro 128GB',    'Tela AMOLED 6.5", câmera 108MP, bateria 5000mAh.',        1299.90, 50, 'https://placehold.co/400x300?text=Smartphone', 1, 120),
(2,  1, 8, 'Notebook UltraSlim 15"',   'Processador i5 11ª geração, 8GB RAM, SSD 256GB.',         3499.00, 20, 'https://placehold.co/400x300?text=Notebook',   1, 85),
(3,  1, 5, 'Kit 3 Livros de Programação', 'Clean Code, Design Patterns e The Pragmatic Programmer.', 189.90, 30, 'https://placehold.co/400x300?text=Livros',   1, 60),

-- Ana Eletronics (vendedor_id = 2)
(4,  2, 1, 'Fone Bluetooth NC-300',    'Cancelamento de ruído ativo, 30h de bateria.',              349.90, 80, 'https://placehold.co/400x300?text=Fone',        1, 200),
(5,  2, 1, 'Smartwatch FitPro 2',      'Monitor cardíaco, GPS, resistente à água 50m.',             599.00, 45, 'https://placehold.co/400x300?text=Smartwatch',  1, 150),
(6,  2, 8, 'Teclado Mecânico RGB',     'Switch Red, layout ABNT2, iluminação por tecla.',           279.90, 60, 'https://placehold.co/400x300?text=Teclado',     1, 90),
(7,  2, 8, 'Mouse Gamer 16000 DPI',    'Sensor óptico de alta precisão, 7 botões programáveis.',   189.90, 75, 'https://placehold.co/400x300?text=Mouse',       1, 110),

-- Bruno Moda Store (vendedor_id = 3)
(8,  3, 2, 'Camiseta Dry-Fit Masculina','Tecido antisuor, disponível em P/M/G/GG.',                  59.90, 200, 'https://placehold.co/400x300?text=Camiseta',   1, 300),
(9,  3, 2, 'Jaqueta Corta-Vento',      'Impermeável, capuz removível, bolsos com zíper.',           229.90,  80, 'https://placehold.co/400x300?text=Jaqueta',   1, 175),
(10, 3, 4, 'Tênis Running Air 2024',   'Solado de amortecimento, cabedal em mesh respirável.',      389.90,  60, 'https://placehold.co/400x300?text=Tenis',     1, 220),
(11, 3, 4, 'Bermuda de Compressão',    'Alta performance para corrida e academia.',                   89.90, 150, 'https://placehold.co/400x300?text=Bermuda',   1, 130),

-- Carla Casa & Cia (vendedor_id = 4)
(12, 4, 3, 'Conjunto de Panelas Inox', '5 peças com tampa de vidro, fundo triplo.',                 299.90,  40, 'https://placehold.co/400x300?text=Panelas',   1, 95),
(13, 4, 3, 'Luminária de Mesa LED',    'Luz regulável 3 tons, braço flexível, USB.',                 99.90,  70, 'https://placehold.co/400x300?text=Luminaria', 1, 80),
(14, 4, 6, 'Kit Skincare Hidratante',  'Sérum vitamina C + hidratante facial + protetor solar.',    189.90,  90, 'https://placehold.co/400x300?text=Skincare',  1, 260),
(15, 4, 6, 'Perfume Floral 100ml',     'Fragrância floral amadeirada, duração 12h.',                249.00,  55, 'https://placehold.co/400x300?text=Perfume',   1, 180);

-- ------------------------------------------------------------
-- Ofertas ativas para alguns produtos
-- ------------------------------------------------------------
INSERT IGNORE INTO ofertas (produto_id, desconto_pct, preco_oferta, data_inicio, data_fim, ativa) VALUES
(1,  10.00, 1169.91, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 1),  -- Smartphone 10% off
(4,  15.00,  297.42, NOW(), DATE_ADD(NOW(), INTERVAL 15 DAY), 1),  -- Fone 15% off
(8,  20.00,   47.92, NOW(), DATE_ADD(NOW(), INTERVAL 7  DAY), 1),  -- Camiseta 20% off
(12,  5.00,  284.91, NOW(), DATE_ADD(NOW(), INTERVAL 10 DAY), 1);  -- Panelas 5% off

-- ------------------------------------------------------------
-- Tabela: pedidos
-- Cabeçalho do pedido (um por compra finalizada).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pedidos (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    usuario_id    INT UNSIGNED    NOT NULL,
    total         DECIMAL(10,2)   NOT NULL,
    status        ENUM('concluido','cancelado') NOT NULL DEFAULT 'concluido',
    criado_em     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_pedidos_usuario (usuario_id),

    CONSTRAINT fk_pedidos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabela: pedido_itens
-- Itens individuais de cada pedido (snapshot do preço pago).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pedido_itens (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    pedido_id     INT UNSIGNED    NOT NULL,
    produto_id    INT UNSIGNED    NOT NULL,
    nome_produto  VARCHAR(150)    NOT NULL COMMENT 'Snapshot do nome no momento da compra',
    preco_unitario DECIMAL(10,2)  NOT NULL COMMENT 'Preço efetivamente pago (com oferta se houver)',
    quantidade    INT UNSIGNED    NOT NULL DEFAULT 1,
    subtotal      DECIMAL(10,2)   NOT NULL COMMENT 'preco_unitario * quantidade',

    PRIMARY KEY (id),
    KEY idx_pedido_itens_pedido  (pedido_id),
    KEY idx_pedido_itens_produto (produto_id),

    CONSTRAINT fk_pedido_itens_pedido
        FOREIGN KEY (pedido_id)  REFERENCES pedidos(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_pedido_itens_produto
        FOREIGN KEY (produto_id) REFERENCES produtos(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
