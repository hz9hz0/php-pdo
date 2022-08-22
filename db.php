<?php

class db
{
    protected $Pdo;
    private $dbUser;
    private $dbPass;
    private $dbName;
    private $dbHost;
    // creat new connection
    public function __construct($dbHost, $dbName, $dbUser, $dbPass){

        $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=UTF8";

        try {
            $Pdo = new PDO($dsn, $dbUser, $dbPass);
            $Pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->Pdo = $Pdo;
            //if ($pdo) {
            //    echo "Connected to the $dbName database successfully!";
            //}
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

    }
    //
    function __destruct() {
        $this->Pdo = null;
    }
    // get Where for sql
    private function creatWhere($values=[])
        {
            $where = '';
            $valueString = [];
            $fieldString = null;
            if(is_array($values)){
                foreach($values as $value){
                    if($where!=''){
                        $where.=" AND";
                    }
                    if(count($value)==3){
                        $name = $value[0];
                        $opreator = $value[1];
                        $val = $value[2];
                        $valueString[":".$name.""]="{$val}";
                        $fieldString =" `{$name}`";
                        $where.=$fieldString." ".$opreator." :".$name;
                    }
                }
            }
            if($where == ''){
                $where = ' 0';// not thing for where
                if($values=='all'){
                    $where = ' 1';// not thing for where
                }
            }
            $result = [$where,$valueString];
            return $result;
        }
    // get Connection
    public function getmyDB()
        {
        if ($this->Pdo instanceof PDO)
            {
            return $this->Pdo;
            }
        }
    // set Connection
    public function setmyDB(PDO $data)
    {
        $this->Pdo = $data;
    }
    // insert
    public function insert($options=[],$table){
        global $pdo;
        
        $queryString = "";
        $p    = count($options);
        $start   = 0;
        $fieldString = null;
        $valueString = array();
        // creat sql query
        foreach($options as $key=>$val){
            $fieldString.=" `{$key}`";
            $valueString[":".$key.""]="{$val}";
            $vs.=":".$key;
            if($start<$p-1){
                $fieldString.=",";
                $vs.=",";
            }
            $start++;
        }
        $queryString = "INSERT INTO `{$table}` ({$fieldString}) VALUES ({$vs}) ";
        try {
            $stmt = $this->Pdo->prepare($queryString);
            $stmt->execute($valueString);
            return $this->Pdo->lastInsertId();
        }catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    
    // update
    public function update($options=[],$table,$where=[]){
        
        $queryString = "";
        $fieldString = ""; 
        $valueString = array();  
        $p           = count($options);
        $start       = 0;
        foreach($options as $key=>$val){
            $vs=":".$key;
            $fieldString.=" `{$key}`={$vs}";
            $valueString[":".$key.""]="{$val}";

            if($start<$p-1){
                $fieldString.=",";
            }
            $start++;
        }
        // get where 
        $creatWhere = $this->creatWhere($where);
        $queryString = "UPDATE `{$table}` SET {$fieldString} WHERE".$creatWhere[0];
        try {
             
            $stmt = $this->Pdo->prepare($queryString);
            $valueString = array_merge($valueString,$creatWhere[1]);
            $stmt->execute($valueString);
        }catch (PDOException $e) {
            
            echo $e->getMessage();
        }
    }
    //---------
    public function select($options=[],$table,$where=[]){
        $queryString = "";
        $fieldString = ""; 
        $valueString = array();  
        $p           = count($options);
        $start       = 0;
        // select name1, name2, ...
        foreach($options as $key){
            $fieldString.=" `{$key}`";

            if($start<$p-1){
                $fieldString.=",";
            }
            $start++;
        }
        // select *
        if($fieldString==""){
            $fieldString = '*';
        }
        //-----
        $creatWhere = $this->creatWhere($where);
        $queryString="SELECT {$fieldString} FROM `{$table}` WHERE".$creatWhere[0];
        //-----
        try {
            $stmt = $this->Pdo->prepare($queryString);
            $valueString = array_merge($valueString,$creatWhere[1]);
            $stmt->execute($valueString);
            return $stmt;
        }catch (PDOException $e) {
            
            echo $e->getMessage();
        }
    }
    //---------
    public function fetch($respons_query){

        return $respons_query->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
