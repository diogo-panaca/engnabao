<?php
declare(strict_types=1);
/*
 * Class that abstracts a parameterized SQL statement whose bind parametres can
 * be gradually registered.
 * At the end, all parametres must be registered, in the same order they appear
 * in the statement.
 *
 * In the statemenet, double question mark ?? indicates an "array" parametre,
 * which is replaced by a comma-separated list of bind parametres (?, ?, ... ?),
 * while single question marks are left alone.
 *
 * 
 * Usage example:
 * # Create a new instance
 * $query = new DynamicQuery(
 *   'SELECT * FROM players WHERE score < ? AND achievements IN(??)');
 *
 * # Register parametres IN ORDER
 * $query->regParam(10000, 'i');
 *
 * 
 */
class DynamicQuery {
    # A string containing the SQL statement that is to be executed.
    private $_sqlStr;

    # A string that keeps type of parametres registered so far.
    private $_paramStr;

    # An array with the values that will be bound to parametres.
    private $_paramArray;

    # An associative array consisting of error messages, or the value TRUE
    # It's empty until bindMysqli (futurely bindPDO too?...) is called.
    private $_errors;

    # Constructor, which receives the string containing the SQL statement
    public function __construct(string $sql) {
        $this->_sqlStr = $sql;
        $this->_errors = array();
        $this->_paramArray = array();
        $this->_paramStr = '';
    }

    # Method which returns one of the messages in the _errors array.
    # The array will be empty until bindMysqli is called; then, the
    # appropriate keys are set to TRUE on success, or to a string 
    # containing the error message.
    #
    # - When TRUE, the key 'stmt_prepare' tells that the mysqli_stmt object
    #   was successfully created ;
    # - When TRUE, the key 'stmt_bind' indicates the parametres were 
    # successfully bound to SQL statement.
    public function error($flag) {
        return $this->_errors[$flag];
    }

    # Method which registers a new parametre 
    public function regParam($value, string $type) {
        $this->_paramStr .= $type;
        array_push($this->_paramArray, $value);
    }

    # Method which registers an "array" parametre. All elements of the
    # array must be of the same type, or there'll be an error on binding
    
    public function regArrayParams(array $arr, string $type) {
        $this->_paramStr .= str_repeat($type, count($arr));
        $this->_paramArray = array_merge($this->_paramArray, $arr);

        # Replace (??) with (?, ?, ...?)
        $aux = implode(',', array_fill(0, count($arr), '?'));
        $this->_sqlStr = preg_replace(preg_quote('/??/'), $aux, $this->_sqlStr, 1);
    }

    # Method which creates a new mysqli statementt instance, with the provided `conn` mysqli 
    # instance, binds the parametres to that statement, and then returns it. After calling this
    # function, errorFlags contains keys 'stmt_prepare' and 'stmt_bind', which are both FALSE if
    # there was no error.
    # If there's an error creating the statement, returns null and sets appropriate error 
    # message; otherwise, the created statement object is returned.
    # If there's an error binding parametres, the appropriate error message is set
    public function bindMysqli(mysqli $conn): mysqli_stmt {

        $stmt = $conn->prepare($this->_sqlStr);
        if($stmt === FALSE) {
            $this->_errors['stmt_prepare'] = mysqli_error($conn);
            return NULL;
        }
        $this->_errors['stmt_prepare'] = TRUE;

        # Must array of references to use `call_user_func_array`
        $aux = array_merge(array($stmt, $this->_paramStr) , $this->_paramArray);
        foreach($aux as $k => $v) {
            $aux[$k] = &$aux[$k];
        }

        // print_r( $aux );
        
        $control = call_user_func_array('mysqli_stmt_bind_param', $aux);
        if(!$control) {
            $this->_errors['stmt_bind'] = mysqli_stmt_error($stmt);
        } else {
            $this->_errors['stmt_bind'] = TRUE;
        }
        return $stmt;
    }
}

# end of file
