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

-- ============================================================
-- ShopBR — Alterações v2
-- Execute apenas se já rodou o script inicial
-- ============================================================

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
