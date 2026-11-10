DROP DATABASE IF EXISTS petcare;
CREATE DATABASE petcare;
USE petcare;

CREATE TABLE perfil (
    perfil_id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE usuarios (
    id_usuario INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome_usuario VARCHAR(255) NOT NULL,
    email_usuario VARCHAR(255) NOT NULL UNIQUE,
    telefone VARCHAR(20) NULL,
    senha_hash VARCHAR(255) NOT NULL,
    id_perfil INTEGER NOT NULL,
    FOREIGN KEY (id_perfil) REFERENCES perfil(perfil_id) ON DELETE RESTRICT
);

CREATE TABLE pets (
    id_pet INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome_pet VARCHAR(255) NOT NULL,
    especie VARCHAR(100) NOT NULL,
    raca VARCHAR(100) NULL,
    data_nascimento DATE NOT NULL,
    peso_kg DECIMAL(5,2) NOT NULL ,
    usuario_id INTEGER NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT
);

CREATE TABLE tokens (
    id_token INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(255) NOT NULL UNIQUE,
    expira_em DATETIME NOT NULL,
    usuario_id INTEGER NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- perfis
INSERT INTO perfil (tipo) VALUES ('admin'), ('tutor');

-- usuários: 1 admin (login do professor) + 5 tutores
INSERT INTO usuarios (nome_usuario, email_usuario, telefone, senha_hash, id_perfil) VALUES
('Professor Teste', 'professor@petcare.com', NULL,         'COLE_AQUI_O_HASH_GERADO_NO_PHP', 1),
('João Silva',      'joao@email.com',        '61999990001', 'COLE_AQUI_O_HASH_GERADO_NO_PHP', 2),
('Maria Souza',     'maria@email.com',       '61999990002', 'COLE_AQUI_O_HASH_GERADO_NO_PHP', 2),
('Pedro Santos',    'pedro@email.com',       '61999990003', 'COLE_AQUI_O_HASH_GERADO_NO_PHP', 2),
('Ana Costa',       'ana@email.com',         '61999990004', 'COLE_AQUI_O_HASH_GERADO_NO_PHP', 2),
('Lucas Oliveira',  'lucas@email.com',       '61999990005', 'COLE_AQUI_O_HASH_GERADO_NO_PHP', 2);

-- pets (usuario_id 2 a 6 = tutores)
INSERT INTO pets (nome_pet, especie, raca, data_nascimento, peso_kg, usuario_id) VALUES
('Rex',     'cachorro', 'Labrador',      '2020-05-10', 25.50, 2),
('Mel',     'gato',     'Siamês',        '2021-03-15',  4.20, 2),
('Thor',    'cachorro', 'Pastor Alemão', '2019-11-20', 32.00, 3),
('Luna',    'gato',     'Persa',         '2022-01-12',  3.80, 3),
('Pipoca',  'roedor',   'Hamster',       '2023-02-10',  0.25, 4),
('Bob',     'cachorro', 'Poodle',        '2018-07-30',  8.50, 4),
('Nina',    'ave',      'Calopsita',     '2021-09-05',  0.15, 5),
('Max',     'cachorro', 'Vira-lata',     '2017-04-22', 18.30, 5),
('Belinha', 'gato',     'Angorá',        '2020-12-01',  4.90, 6),
('Toddy',   'outro',    NULL,            '2022-06-18',  2.10, 6);