<?php
declare(strict_types=1);
include 'prepend.php';
require_once 'DynamicQuery.php';

# Fç auxiliar para garantir strings seguras
function parseInput($dbconn, $data) {
    $data = trim($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($dbconn, $data);
    return $data;
}

function parseInputArray($dbconn, $arr) {
    foreach($arr as $k => $v) {
       $arr[$k] = parseInput($dbconn, $v);
    }
    return $arr;
}

# Se a condição for verdadeira, continua execução normalmente
# Caso contrário, retorna mensagem de erro e termina execução do script
function enforceCondition($dbconn, $data, $condicao, $dbstmt = FALSE, $dbresult = FALSE) {
    if(!$condicao){
        $data['error'] = mysqli_error($dbconn);
        if($dbstmt){
            $data['error_stmt'] = mysqli_stmt_error($dbstmt);
            mysqli_stmt_close($dbstmt);
        }
        if($dbresult) {
            mysqli_free_result($dbresult);
        }
        mysqli_close($dbconn);
        die(json_encode($data));
    }
}


# Cabeçalho HTTP
header('Content-Type: application/json; charset=UTF-8');

# Só há sucesso se chegarmos ao fim deste script
$data_to_return['success'] = FALSE;

# Verificar dados recebidos do cliente
# Todos os campos são obrigatórios
# Se houver algum em falta, retorna-se erro e termina execução

$data_received = json_decode($_POST['strJson'], TRUE);

if(!array_key_exists('atleta', $data_received)
    # || !array_key_exists('competicao', $data_received)
    || !array_key_exists('louvor', $data_received)
    || !array_key_exists('ano_min', $data_received)
    || !array_key_exists('ano_max', $data_received)
)
{
    $data_to_return['error'] = 'Argumentos insuficientes. Devem ser os seguintes {atleta,louvor,ano_min,ano_max}.';
    die(json_encode($data_to_return));
}

if(!is_numeric($data_received['ano_min'])) {
    $data_to_return['error'] = 'Argumento (ano_min) deve ser numérico.';
    die(json_encode($data_to_return));
}

if(!is_numeric($data_received['ano_max'])) {
    $data_to_return['error'] = 'Argumento (ano_max) deve ser numérico.';
    die(json_encode($data_to_return));
}

# Ligar à BD

$dbconn = mysqli_connect();
if(!$dbconn) {
    $data_to_return['error'] = mysqli_connect_error();
    die(json_encode($data_to_return));
}

$aux = mysqli_select_db($dbconn, 'id9004398_test');
enforceCondition($dbconn, $data_to_return, $aux !== FALSE);


# Verificar integridade dos argumentos recebidos do cliente

$filtro_atleta = parseInputArray($dbconn, $data_received['atleta']);
$filtro_louvor = parseInputArray($dbconn, $data_received['louvor']);
# $filtro_competicao = parseInput($dbconn, $data_received['competicao']);
$filtro_ano_min = (int) $data_received['ano_min'];
$filtro_ano_max = (int) $data_received['ano_max'];


# Criar query parametrizada

$path = DIR_SCRIPTS_PHP . 'query_atletas_json.sql';
$sqlstr = file_get_contents($path);
# 
# Nao se pode usar a fç auxiliar porque perde-se o erro 
# "file not found (atletas_json.sql)"
# enforceCondition($dbconn, $data_to_return, $sqlstr !== FALSE);
if($sqlstr === FALSE) {
    $data_to_return['error'] = "Ficheiro SQL nao foi encontrado!";
    mysqli_close($dbconn);
    die(json_encode($data_to_return));
}

$dynamic_query = new DynamicQuery($sqlstr);


# Vincular os parametros à query

$dynamic_query->regArrayParams($filtro_atleta, 's');
$dynamic_query->regParam($filtro_ano_min, 'i');
$dynamic_query->regParam($filtro_ano_max, 'i');
$dynamic_query->regArrayParams($filtro_louvor, 's');

$dbstmt = $dynamic_query->bindMysqli($dbconn);
enforceCondition($dbconn, $data_to_return, !is_null($dbstmt));
$aux = $dynamic_query->error('stmt_bind');
enforceCondition($dbconn, $data_to_return, $aux, /**/$dbstmt);


# Exectuar query

$aux = mysqli_stmt_execute($dbstmt);
enforceCondition($dbconn, $data_to_return, $aux, $dbstmt);

$dbresult = mysqli_stmt_get_result($dbstmt);
enforceCondition($dbconn, $data_to_return, $dbresult !== FALSE, $dbstmt);


# Tratar registos obtidos

$i = 0;
$data_to_return['data_rows'] = array();
foreach($dbresult as $row) {
    $data_to_return['data_rows'][$i++] = $row;
}

mysqli_free_result($dbresult);
mysqli_stmt_close($dbstmt);
mysqli_close($dbconn);

$data_to_return['success'] = TRUE;

echo json_encode($data_to_return);

# não fechar com "? >" . De acordo com o manual PHP:
# «
#   Se o ficheiro só contém código PHP, é preferível omitir marca de fecho
# no fim do ficheiro. Desta forma previne-se acrescentar acidentalmente
# espaço branco ou linhas depois desta marca de fecho.
# »

