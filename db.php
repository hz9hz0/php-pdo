<?php

class db
{
    public $Pdo;

    public function __construct ($dbName, $dbUser, $dbPass)
    {
        $dsn = "mysql:host=localhost;dbname=$this->dbName;charset=UTF8";

        try {
            $Pdo = new PDO($dsn, $this->dbUser, $this->dbPass);
            $Pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->Pdo = $Pdo;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    //===========================
    function __destruct() {
        unset($this->Pdo);
    }
    //============================
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
            $where = ' 0';
            if($values=='all'){
                $where = ' 1';
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
    public function insert(array $options,string $table){
        global $pdo;

        $queryString = "";
        $p    = count($options);
        $start   = 0;
        $fieldString = null;
        $valueString = array();
        // creat sql query
        $vs = '';
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
    public function update(array $options,string $table,array $where){

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
            $result = $stmt->execute($valueString);
            return $result;
        }catch (PDOException $e) {
            return $e->getMessage();
        }
    }
    //---------
    // delete
    public function DELETE(string $table, array $where){
        $valueString = array();
        // get where
        $creatWhere = $this->creatWhere($where);
        if($creatWhere == '1')
            $creatWhere = '0';
        $queryString = "DELETE FROM `{$table}` WHERE".$creatWhere[0];
        try {

            $stmt = $this->Pdo->prepare($queryString);
            $valueString = array_merge($valueString,$creatWhere[1]);
            $result = $stmt->execute($valueString);
        }catch (PDOException $e) {
            return $e->getMessage();
        }
    }
    //---------
    public function select(array $options, string $table, array|string $where){
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
    //--------
    public function query($sql){
        return $this->Pdo->query($sql);
    }
}
