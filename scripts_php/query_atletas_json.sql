SELECT
    atletas.nome_atleta AS atleta,
    louvores.modalidade AS modalidade,
    competicoes.nome_competicao AS competicao,
    CONCAT_WS(' ', competicoes.local_competicao, competicoes.ano_competicao) AS edicao,
    louvores.designacao AS louvor
FROM
    atletas
    INNER JOIN louvores ON atletas.id_atleta = louvores.id_atleta
    INNER JOIN competicoes ON louvores.id_competicao = competicoes.id_competicao
WHERE
    atletas.nome_atleta = ? AND
    (competicoes.ano_competicao between ? and ?)

