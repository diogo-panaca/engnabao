-- -- -- -- -- -- -- -- -- -- -- -- -- --
-- Criar tabelas, se não existirem
-- -- -- -- -- -- -- -- -- -- -- -- -- --

CREATE TABLE IF NOT EXISTS atletas (
    id_atleta INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome_atleta VARCHAR(128) CHARACTER SET utf8 NOT NULL
);

CREATE TABLE IF NOT EXISTS competicoes (
    id_competicao INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome_competicao VARCHAR(128) CHARACTER SET utf8 NOT NULL,
    ano_competicao SMALLINT NOT NULL,
    local_competicao VARCHAR(128) CHARACTER SET utf8 NOT NULL
);

-- Criar tabela com chave estrangeira apenas depois das tabelas referidas
CREATE TABLE IF NOT EXISTS louvores (
    id_atleta INT NOT NULL,
    id_competicao INT NOT NULL,
    lugar TINYINT NOT NULL,
    modalidade VARCHAR(128) CHARACTER SET utf8 NOT NULL,
    designacao VARCHAR(128) CHARACTER SET utf8 NOT NULL,
    CONSTRAINT PRIMARY KEY (id_atleta, id_competicao, modalidade),
    -- possivelmente: índice com (id_atleta, lugar)
    CONSTRAINT FOREIGN KEY (id_atleta)
        REFERENCES atletas (id_atleta)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT,
    CONSTRAINT FOREIGN KEY (id_competicao)
        REFERENCES competicoes (id_competicao)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT
);

-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
-- Popular tabelas; se já existirem, atualizar dados
-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --

INSERT INTO atletas (id_atleta, nome_atleta)
VALUES (1, 'Francis'), (2, 'Nélson'), (3, 'Telma')
ON DUPLICATE KEY UPDATE nome_atleta = VALUES(nome_atleta);

INSERT INTO competicoes 
    (id_competicao, nome_competicao, ano_competicao, local_competicao)
VALUES 
    -- Olimpíadas
    ( 1, 'Jogos Olímpicos', 2004, 'Atenas'),
    ( 2, 'Jogos Olímpicos', 2008, 'Pequim'),
    ( 3, 'Jogos Olímpicos', 2012, 'Londres'),
    ( 4, 'Jogos Olímpicos', 2016, 'Rio de Janeiro'),
    -- Atletismo
    ( 5, 'Campeonato Europeu de Atletismo', 2002, 'Munique'),
    ( 6, 'Campeonato Europeu de Atletismo', 2006, 'Gotemburgo'),
    ( 7, 'Campeonato Mundial de Atletismo', 1997, 'Atenas'),
    ( 8, 'Campeonato Mundial de Atletismo', 1999, 'Sevilha'),
    ( 9, 'Campeonato Mundial de Atletismo', 2007, 'Osaka'),
    (10, 'Campeonato Mundial de Atletismo', 2009, 'Berlim'),
    (11, 'Campeonato Mundial de Atletismo', 2015, 'Pequim'),
    (12, 'Campeonato Mundial de Atletismo', 2017, 'Londres'),
    (13, 'Campeonato Europeu de Atletismo', 2018, 'Berlim'),
    -- Mundial de Judo
    (14, 'Campeonato Mundial de Judo', 2014, 'Chelyabinsk'),
    (15, 'Campeonato Mundial de Judo', 2010, 'Tóquio'),
    (16, 'Campeonato Mundial de Judo', 2009, 'Roterdão'),
    (17, 'Campeonato Mundial de Judo', 2007, 'Rio de Janeiro'),
    (18, 'Campeonato Mundial de Judo', 2005, 'Cairo'),
    -- Euro de Judo
    (19, 'Campeonato Europeu de Judo', 2014, 'Varsóvia'),
    (20, 'Campeonato Europeu de Judo', 2012, 'Cheliabinsk'),
    (21, 'Campeonato Europeu de Judo', 2009, 'Tbilisi')
ON DUPLICATE KEY UPDATE
    id_competicao = VALUES(id_competicao),
    nome_competicao = VALUES(nome_competicao),
    ano_competicao = VALUES(ano_competicao),
    local_competicao = VALUES(local_competicao)
;


-- Inserir chave estrangeira apenas depois das tabelas referidas

INSERT INTO louvores
    (id_atleta, id_competicao, lugar, modalidade, designacao)
VALUES
    -- Francis
    ( 1,  1, 2, '100m velocidade', 'Prata'),
    ( 1,  5, 2, '200m velocidade', 'Prata'),
    ( 1,  5, 1, '100m velocidade', 'Ouro'),
    ( 1,  6, 1, '100m velocidade', 'Ouro'),
    ( 1,  6, 1, '200m velocidade', 'Ouro'),
    ( 1,  7, 2, '4x100m estafetas', 'Prata'),
    ( 1,  8, 3, '200m velocidade', 'Bronze'),
    -- Nélson
    ( 2,  2, 1, 'Triplo salto', 'Ouro'),
    ( 2,  9, 1, 'Triplo salto', 'Ouro'),
    ( 2, 10, 2, 'Triplo salto', 'Prata'),
    ( 2, 11, 3, 'Triplo salto', 'Bronze'),
    ( 2, 12, 3, 'Triplo salto', 'Bronze'),
    ( 2, 13, 1, 'Triplo salto', 'Ouro'),
    -- Telma
    ( 3,  3, 3, 'Judo -57kg', 'Bronze'),
    ( 3, 14, 2, 'Judo -57kg', 'Prata'),
    ( 3, 15, 2, 'Judo -57kg', 'Prata'),
    ( 3, 16, 2, 'Judo -57kg', 'Prata'),
    ( 3, 17, 2, 'Judo -52kg', 'Prata'),
    ( 3, 18, 3, 'Judo -52kg', 'Bronze'),
    ( 3, 19, 1, 'Judo -57kg', 'Ouro'),
    ( 3, 20, 1, 'Judo -57kg', 'Ouro'),
    ( 3, 21, 1, 'Judo -57kg', 'Ouro')
ON DUPLICATE KEY UPDATE
    id_atleta = VALUES(id_atleta),
    id_competicao = VALUES(id_competicao),
    lugar = VALUES(lugar),
    modalidade = VALUES(modalidade),
    designacao = VALUES(designacao)
;

COMMIT;
