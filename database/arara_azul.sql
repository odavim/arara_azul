-- ===============================
-- CRIAR BANCO
-- ===============================
DROP DATABASE IF EXISTS arara_azul;
CREATE DATABASE arara_azul
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE arara_azul;

-- ===============================
-- TABELA USUARIO
-- ===============================
CREATE TABLE usuario (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===============================
-- TABELA MATERIA
-- ===============================
CREATE TABLE materia (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- ===============================
-- TABELA TOPICO
-- ===============================
CREATE TABLE topico (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    materia_id INT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,

    INDEX (materia_id),

    CONSTRAINT fk_topico_materia
        FOREIGN KEY (materia_id)
        REFERENCES materia(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===============================
-- TABELA QUESTAO
-- ===============================
CREATE TABLE questao (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    topico_id INT UNSIGNED NOT NULL,
    enunciado TEXT NOT NULL,
    alternativa_a TEXT NOT NULL,
    alternativa_b TEXT NOT NULL,
    alternativa_c TEXT NOT NULL,
    alternativa_d TEXT NOT NULL,
    alternativa_e TEXT NOT NULL,
    resposta_correta CHAR(1) NOT NULL,

    INDEX (topico_id),

    CONSTRAINT fk_questao_topico
        FOREIGN KEY (topico_id)
        REFERENCES topico(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===============================
-- TABELA CADERNO
-- ===============================
CREATE TABLE caderno (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    topico_id INT UNSIGNED NOT NULL,
    total_questoes INT DEFAULT 10,
    total_acertos INT DEFAULT 0,
    percentual DECIMAL(5,2) DEFAULT 0,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    finalizado_em DATETIME NULL,

    INDEX (usuario_id),
    INDEX (topico_id),

    CONSTRAINT fk_caderno_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuario(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_caderno_topico
        FOREIGN KEY (topico_id)
        REFERENCES topico(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===============================
-- TABELA CADERNO_QUESTAO
-- ===============================
CREATE TABLE caderno_questao (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    caderno_id INT UNSIGNED NOT NULL,
    questao_id INT UNSIGNED NOT NULL,
    resposta_usuario CHAR(1) NULL,
    acertou BOOLEAN NULL,

    INDEX (caderno_id),
    INDEX (questao_id),

    CONSTRAINT fk_cadernoquestao_caderno
        FOREIGN KEY (caderno_id)
        REFERENCES caderno(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_cadernoquestao_questao
        FOREIGN KEY (questao_id)
        REFERENCES questao(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;
