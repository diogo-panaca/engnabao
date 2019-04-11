<?php
declare(strict_types=1);
include 'prepend.php';
require_once 'DynamicQuery.php';

# Fç auxiliar para garantir strings seguras
function parseInput($dbconn, $dados) {
    $dados = trim($dados);
    $dados = htmlspecialchars($dados);
    $dados = mysqli_real_escape_string($dbconn, $dados);
    return $dados;
}

function parseInputArray($dbconn, $arr) {
    foreach($arr as $k => $v) {
       $arr[$k] = parseInput($dbconn, $v);
    }
    return $arr;
}

# Se a condição for verdadeira, continua execução normalmente
# Caso contrário, retorna mensagem de erro e termina execução do script
function imporCondicao($dbconn, $dados, $condicao, $dbstmt = FALSE, $dbresult = FALSE) {
    if(!$condicao){
        $dados['erro'] = mysqli_error($dbconn);
        if($dbstmt){
            $dados['erro_stmt'] = mysqli_stmt_error($dbstmt);
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

$dados_recebidos = json_decode($_POST['strJson'], TRUE);

if(!array_key_exists('atleta', $dados_recebidos)
    # || !array_key_exists('competicao', $dados_recebidos)
    || !array_key_exists('louvor', $dados_recebidos)
    || !array_key_exists('ano_min', $dados_recebidos)
    || !array_key_exists('ano_max', $dados_recebidos)
)
{
    $dados_retorno['erro'] = 'Argumentos insuficientes. Devem ser os seguintes {atleta,louvor,ano_min,ano_max}.';
    die(json_encode($dados_retorno));
}

if(!is_numeric($dados_recebidos['ano_min'])) {
    $dados_retorno['erro'] = 'Argumento (ano_min) deve ser numérico.';
    die(json_encode($dados_retorno));
}

if(!is_numeric($dados_recebidos['ano_max'])) {
    $dados_retorno['erro'] = 'Argumento (ano_max) deve ser numérico.';
    die(json_encode($dados_retorno));
}

# Ligar à BD

$dbconn = mysqli_connect();
if(!$dbconn) {
    $dados_retorno['erro'] = mysqli_connect_error();
    die(json_encode($dados_retorno));
}

$aux = mysqli_select_db($dbconn, 'id9004398_test');
imporCondicao($dbconn, $dados_retorno, $aux !== FALSE);


# Verificar integridade dos argumentos recebidos do cliente

$filtro_atleta = parseInputArray($dbconn, $dados_recebidos['atleta']);
$filtro_louvor = parseInputArray($dbconn, $dados_recebidos['louvor']);
# $filtro_competicao = parseInput($dbconn, $dados_recebidos['competicao']);
$filtro_ano_min = (int) $dados_recebidos['ano_min'];
$filtro_ano_max = (int) $dados_recebidos['ano_max'];


# Criar query parametrizada

$path = DIR_SCRIPTS_PHP . 'query_atletas_json.sql';
$sqlstr = file_get_contents($path);
# 
# Nao se pode usar a fç auxiliar porque perde-se o erro 
# "file not found (atletas_json.sql)"
# importCondicao($dbconn, $dados_retorno, $sqlstr !== FALSE);
if($sqlstr === FALSE) {
    $dados_retorno['erro'] = "Ficheiro SQL nao foi encontrado!";
    mysqli_close($dbconn);
    die(json_encode($dados_retorno));
}

$dynamic_query = new DynamicQuery($sqlstr);


# Vincular os parametros à query

$dynamic_query->regArrayParams($filtro_atleta, 's');
$dynamic_query->regParam($filtro_ano_min, 'i');
$dynamic_query->regParam($filtro_ano_max, 'i');
$dynamic_query->regArrayParams($filtro_louvor, 's');

$dbstmt = $dynamic_query->bindMysqli($dbconn);
imporCondicao($dbconn, $dados_retorno, !is_null($dbstmt));
$aux = $dynamic_query->error('stmt_bind');
imporCondicao($dbconn, $dados_retorno, $aux, /**/$dbstmt);


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

# não fechar com "? >" . De acordo com o manual PHP:
# «
#   Se o ficheiro só contém código PHP, é preferível omitir marca de fecho
# no fim do ficheiro. Desta forma previne-se acrescentar acidentalmente
# espaço branco ou linhas depois desta marca de fecho.
# »

