<?php

# Fç auxiliar para garantir strings seguras
function parseInput($dbconn, $dados) {
    $dados = trim($dados);
    $dados = htmlspecialchars($dados);
    $dados = mysqli_real_escape_string($dbconn, $dados);
    return $dados;
}

# Se a condição for verdadeira, continua execução normalmente
# Caso contrário, retorna mensagem de erro e termina execução do script
function imporCondicao($dbconn, $dados, $condicao, $dbstmt = FALSE, $dbresult = FALSE) {
    if(!$condicao){
        $dados['erro'] = mysqli_error($dbconn);
        if($dbstmt){
            $dados['erro_stmt'] = mysqli_error($dbstmt);
            mysqli_stmt_close($dbstmt);
        }
        if($dbresult) {
            mysqli_free_result($dbresult);
        }
        mysqli_close($dbconn);
        die(json_encode($dados));
    }
}


# Cabeçalho HTTP
header('Content-Type: application/json; charset=UTF-8');

# Só há sucesso se chegarmos ao fim deste script
$dados_retorno['sucesso'] = FALSE;

# Verificar dados recebidos do cliente
# Todos os campos são obrigatórios
# Se houver algum em falta, retorna-se erro e termina execução

if(!array_key_exists('atleta', $_GET)
    || !array_key_exists('competicao', $_GET)
    || !array_key_exists('ano', $_GET)
    || !array_key_exists('louvor', $_GET)
)
{
    $dados_retorno['erro'] = 'Argumentos GET insuficientes. Devem ser os seguintes (atleta,competicao,ano,louvor)';
    die(json_encode($dados_retorno));
}

$filtro_atleta = parseInput($_GET['atleta']);
$filtro_competicao = parseInput($_GET['competicao']);
$filtro_louvor = parseInput($_GET['louvor']);

if(!is_numeric($_GET['ano'])) {
    $dados_retorno['erro'] = 'Argumento (ano) GET deve ser numérico ';
    die(json_encode($dados_retorno));
}
$filtro_ano = (int) $_GET['ano'];


# Ligar à BD

$dbconn = mysqli_connect();
if(!$dbconn) {
    $dados_retorno['erro'] = mysqli_connect_error();
    die(json_encode($dados_retorno));
}

$aux = mysqli_select_db($dbconn, 'id9004398_test');
imporCondicao($dbconn, $dados_retorno, $aux);

# Criar query parametrizada

$sqlstr = file_get_contents('query_atletas_json.sql');
$dbstmt = mysqli_prepare($dbconn, $sqlstr);
imporCondicao($dbconn, $dados_retorno, $dbstmt !== FALSE);

$aux = mysqli_stmt_bind_params($dbstmt, 'sssi', 
    $filtro_atleta, $filtro_louvor, $filtro_competicao, $filtro_ano);
imporCondicao($dbconn, $dados_retorno, $aux, $dbstmt);

# Exectuar query

$aux = mysqli_stmt_execute($dbstmt);
imporCondicao($dbconn, $dados_retorno, $aux, $dbstmt);

$dbresult = mysqli_stmt_get_result($dbstmt);
imporCondicao($dbconn, $dados_retorno, $dbresult !== FALSE, $dbstmt);

# Tratar registos obtidos

$i = 0;
$dados_retorno['registos'] = array();
foreach($dbresult as $row) {
    $dados_retorno['registos'][$i++] = $row;
}

mysqli_free_result($dbresult);
mysqli_stmt_close($dbstmt);
mysqli_close($dbconn);

$dados_retorno['sucesso'] = TRUE;

echo json_encode($dados_retorno);

?>

