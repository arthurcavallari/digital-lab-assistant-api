<?php
if(!isset($pathCheck))
{	
	if($_SERVER['HTTP_HOST'] == "127.0.0.1")
	{
		$GLOBALS['base_url'] = "/mydla/";
	}
	else
	{
		$GLOBALS['base_url'] = "/~sentinus/";
	}
	header('Location: ' . $GLOBALS['base_url'] . 'index.php?src=' . $_SERVER['REQUEST_URI']);
}


/**
 * Modified DatabaseHandler, originally creates by Kabindra Bakey
 * @author Arthur Cavallari
 * @ref http://www.phpclasses.org/browse/file/44398.html
 */
class DatabaseHandler
{
	private $databaseName;
	private $host;
	private $user;
	private $password; 
	private $connection;
	private $errors;


	/**
	 * Initializes the db name, host, username and password to the values in globals.php
	 */
	public function __construct()
	{		
		$this->databaseName = $GLOBALS['db_name'];
		$this->host = $GLOBALS['db_server'];
		$this->user = $GLOBALS['db_username'];
		$this->password = $GLOBALS['db_password']; 
	}
	
		
	public function __destruct()
	{
		if(isset($this->connection) && is_resource($this->connection))
		{
			mysql_close($this->connection);
		}
	}
	
	/**
	 * Connects to the database and selected the given database name (located in globals.php)
	 * @return boolean
	 */
	public function initialize()
	{
		try 
		{
            //mysql conn
            $this->connection = mysql_connect($this->host, $this->user, $this->password) or die ("I cannot connect to the database because: " . mysql_error() . PHP_EOL);
            $db = mysql_select_db($this->databaseName, $this->connection) or die ("I cannot connect to the database because: " . mysql_error() . PHP_EOL);
			return true;
        } 
		catch (PDOException $e) 
		{
           //print "Error!: " . $e->getMessage() . "<br/>";
           return false;
        }
	}

	/**
	 * Returns the current mysql connection
	 * @return resource
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 * 
	 * @param string $strTable
	 * @param string $arFields
	 * @param string $strWhere
	 * @param string $strOrder
	 * @param number $intRecords
	 * @param number $intPage
	 * @return resource
	 */
	public function query($strTable, $arFields = '*', $strWhere = '', $strOrder = '',$intRecords=10, $intPage = 1 )
    {
		$limit = "";
        if($strWhere != '')
        {
            $strWhere = ' where '. $strWhere;
        }
        if($strOrder != '')
        {
            $strOrder = 'order by '.$strOrder;
        }
		if($intPage > 0)
		{
        	$limit = sprintf(' limit %d, %d', ($intPage-1) * $intRecords, $intRecords);
		}
		
        if( is_array( $arFields ))
		{
            $strFields = implode( ',', $arFields);
        }
		else
		{
            $strFields = $arFields;
            if( $strFields == '')
			{
				$strFields = '*';
			}
        }
        $strSQL = "SELECT $strFields FROM $strTable $strWhere $strOrder $limit";
		//var_dump($strSQL);
		//var_dump($this->connection);
        $objRes = mysql_query($strSQL, $this->connection) or die('Invalid SQL: '.mysql_error());
        return $objRes;
    }
	
	
    /**
     * 
     * @param unknown $strTable
     * @param unknown $strJoin
     * @param string $arFields
     * @param string $strWhere
     * @param string $strOrder
     * @param number $intRecords
     * @param number $intPage
     * @return resource
     */
	public function queryJoin($strTable, $strJoin, $arFields = '*', $strWhere = '', $strOrder = '',$intRecords=10, $intPage = 1 )
    {
		
		$limit = "";
        if($strWhere != '')
        {
            $strWhere = ' where '. $strWhere;
        }
        if($strOrder != '')
        {
            $strOrder = 'order by '.$strOrder;
        }
		if($intPage > 0)
		{
        	$limit = sprintf(' limit %d, %d', ($intPage-1) * $intRecords, $intRecords);
		}
		
        if( is_array( $arFields ))
		{
            $strFields = implode( ',', $arFields);
        }
		else
		{
            $strFields = $arFields;
            if( $strFields == '')
			{
				$strFields = '*';
			}
        }
        $strSQL = "SELECT $strFields FROM $strTable $strJoin $strWhere $strOrder $limit";
		//var_dump($strSQL);
		//var_dump($this->connection);
        $objRes = mysql_query($strSQL, $this->connection) or die('Invalid SQL: '.mysql_error());
        return $objRes;
    }
	
    /**
     * 
     * @param string $table A name of table to insert into
     * @param string $data An associative array
     * @return number|NULL
     */
	public function insert($table, $data)
    {      
		try
		{  
			foreach ($data as $key => $value) 
			{
				$data[$key] = mysql_escape_string($value);
			}
			$fieldNames = implode('`, `', array_keys($data));
			$fieldValues = implode('", "', array_values($data));
			$sth = mysql_query("INSERT INTO $table (`$fieldNames`) VALUES (\"$fieldValues\")", $this->connection) or die ("I cannot insert because: " . mysql_error());
			return mysql_insert_id($this->connection);
		} 
		catch (PDOException $e) 
		{
           $this->errors = "Error!: " . $e->getMessage() . "<br/>";
           return NULL;
        }

    }
	
    /**
     * Returns the current errors
     * @return array
     */
	public function getErrors()
	{
		return $this->errors;	
	}
	
	/**
	 * Creates an update string from a given associative array
	 * @param array $arrData
	 * @param string $strDelim
	 * @return string
	 */
	public function buildUpdateString( $arrData, $strDelim = ', ' )
	{
        $arrRows = array();
		foreach ($arrData as $key => $value) 
		{
			$arrData[$key] = mysql_escape_string($value);
		}
        foreach($arrData as $key=> $value) {
            $arrRows[] = "`$key`='$value'";
        }
        return implode( $strDelim, $arrRows );
    }
	
    /**
     * Creates a where statement from a given associative array
     * @param array $arr
     * @return string
     */
	public function buildWhereString($arr)
	{
		$QueryString = "";
		foreach ($arr as $key => $value)
		{
			if(is_array($value))
			{
				$QueryString .= "(" . buildWhereString($value) . ")";
			}
			else
			{
				if(is_numeric($key))
				{
					$QueryString .= $value . " ";
				}
				else
				{
					$QueryString .= $key . ' = "' . $value . '" ';
				}
			}       
		}		
		return $QueryString;
	}
	
	/**
     * update
     * @param string $table A name of table to insert into
     * @param string $data An associative array
     * @param string $where the WHERE query part
     */
    public function update($table, $data, $where)
    {
        //ksort($data);
        
        $fieldDetails = NULL;
        $fieldDetails = $this->buildUpdateString( $data );
        $sqlStr="UPDATE $table SET $fieldDetails WHERE $where";
        $sth = mysql_query($sqlStr, $this->connection) or die('SQL Error: '.mysql_error());
        return $sth;
    }
	
    /**
     * 
     * @param string $strTable
     * @param string $arFields
     * @param string $strWhere
     * @param string $strOrder
     * @param number $intRecords
     * @param number $intPage
     * @return Ambigous <multitype:, boolean>
     */
	public function QueryArray($strTable, $arFields = '*', $strWhere = '', $strOrder = '',$intRecords=10, $intPage = 1 )
    {
        $objRes = $this->query( $strTable, $arFields, $strWhere, $strOrder, $intRecords, $intPage);
        return $this->MySQL_to_Array($objRes);
    }
	
    /**
     * 
     * @param string $strTable
     * @param string $strJoin
     * @param string $arFields
     * @param string $strWhere
     * @param string $strOrder
     * @param number $intRecords
     * @param number $intPage
     * @return Ambigous <multitype:, boolean>
     */
	public function QueryJoinArray($strTable, $strJoin, $arFields = '*', $strWhere = '', $strOrder = '',$intRecords=10, $intPage = 1 )
    {
        $objRes = $this->queryJoin( $strTable, $strJoin, $arFields, $strWhere, $strOrder, $intRecords, $intPage);
        return $this->MySQL_to_Array($objRes);
    }
	
    /**
     * Public function to make database query
     * @param unknown $arResult
     * @param string $arFields
     * @return Ambigous <multitype:, boolean>
     */
    public function MySQL_to_Array( $arResult, $arFields = '' )
    {
        
        if( !is_array( $arFields ))
        {
            $totFields = mysql_num_fields( $arResult );
            $i = 0;
            while( $i<$totFields)
            {
                $arFields[] = mysql_field_name( $arResult, $i++ );
            }
        }
       
        $content = array();
        $i = 0;
        if(mysql_num_rows($arResult)>0)
        {
            while( $row =  mysql_fetch_assoc($arResult))
            {
                foreach($arFields as $field)
                {
                    $content[ $i ][ $field ] = $row[ $field ] ;
                    $content[ $i ][ $field ] = $content[ $i ][ $field ];
                }
                $i++;
            }
        }
		else
		{
            $content = false;
        }
        return $content;
    }
	
	/**
     * delete
     * 
     * @param string $table
     * @param string $where
     * @param integer $limit
     * @return integer Affected Rows
     */
    public function delete($table, $where='')
    {
        if ($where=='')
            return false;
        $strSql="DELETE FROM $table WHERE $where ";
        //echo $strSql;
        return mysql_query($strSql, $this->connection);
    }

}
?>