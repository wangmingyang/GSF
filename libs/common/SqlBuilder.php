<?php
/**
 * PHPProject
 * SqlBuilder.php Created by usher.yue.
 * User: usher.yue
 * Date: 16/7/7
 * Time: 下午12:32
 * 心怀教育梦－烟台网格软件技术有限公司
 */

class SqlBuilder
{
    const SQL_INSERT = 0;
    const SQL_UPDATE = 1;
    const SQL_SELECT = 2;
    protected $_sql = "";
    protected $_select = "";
    protected $_from = "";
    protected $_where = "";
    protected $_update = "";
    protected $_insert = "";
    protected $_join = "";
    protected $_value = "";
    protected $_limit = "";
    protected $_set = "";
    protected $_method;
    //执行方式
    protected $_order = "";
    protected $_group = "";
    protected $_having = "";

    /**set产生语句
     * @param $table
     * @param $data
     * @param $prms
     * @return string
     */
    public static function GenericUpdateSql($table, $data, $prms)
    {
        $sql = "update {$table} set ";
        if (is_string($data)) {
            $sql .= $data;
        } elseif (is_array($data)) {
            //拼接expr数组情况下
            if (array_key_exists('expr', $data)) {
                $sql .= $data['expr'];
                unset($data['expr']);
            }
            $arrSetExpr = [];
            //判断是关联数组还是索引数组
            if (is_assoc($data)) {
                foreach ($data as $k => $v) {
                    $arrSetExpr[] = "$k='$v'";
                }
            } else {
                foreach ($data as $v) {
                    $arrSetExpr[] = $v;
                }
            }
            $condition = implode(',', $arrSetExpr);
            $sql = ($condition == '' ? $sql : $sql . $condition);
        }
        if (is_string($prms)) {
            $sql .= " where $prms";
        } elseif (is_array($prms)) {
            $arrSetExpr = [];
            //判断是关联数组还是索引数组
            if (is_assoc($prms)) {
                foreach ($prms as $k => $v) {
                    $arrSetExpr[] = "$k='$v'";
                }
            } else {
                foreach ($prms as $v) {
                    $arrSetExpr[] = $v;
                }
            }
            //var_dump($prms);
            $condition = implode(' and ', $arrSetExpr);
            $sql = ($condition == '' ? $sql : $sql . '  where ' . $condition);
        }
        return $sql;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function select($fields = array())
    {
        $this->clear();
        $this->_method = SqlBuilder::SQL_SELECT;
        if (is_array($fields)) {
            $selectFields = implode(' , ', $fields);
            $this->_select = ($selectFields == "") ? " * " : $selectFields;
        } else if (is_string($fields)) {
            $this->_select = $fields;
        }
        return $this;
    }

    /**
     * clear sql
     */
    public function clear()
    {
        $this->_select = "";
        $this->_from = "";
        $this->_where = "";
        $this->_update = "";
        $this->_insert = "";
        $this->_method = "";
        $this->_value = "";
        $this->_set = "";
        $this->_limit = "";
        $this->_order = "";
        $this->_having = "";
        $this->_group = "";
    }

    /**
     * @param array $tables
     * @return $this
     */
    public function  from($tables = array())
    {
        if (is_array($tables)) {
            $from = implode(',', $tables);
            $this->_from = ' ' . $from . '  ';
        } else if (is_string($tables)) {
            $this->_from = $tables;
        }
        return $this;
    }

    /**
     * @param $prm
     * @return $this
     */
    public function groupby($prm)
    {
        $this->_group .= " group by $prm  ";
        return $this;
    }

    /**配合表达式
     * @param array $conditionExpr
     * @return $this
     */
    public function wheres($conditionExpr = array())
    {
        //拼接expr
        if (array_key_exists('expr', $conditionExpr)) {
            $this->_where .= $conditionExpr['expr'];
            unset($conditionExpr['expr']);
        }
        $arrSetExpr = [];
        foreach ($conditionExpr as $k => $v) {
            $arrSetExpr[] = "$k='$v'";
        }
        $condition = implode(' and ', $arrSetExpr);
        $this->_where = ($condition == '' ? $this->_where : $this->_where . ' and ' . $condition);
        return $this;
    }

    /**
     * @param $conditon
     * @return $this
     */
    public function where($conditon)
    {
        if (is_string($conditon)) {
            $this->_where .= " $conditon";
        } else if (is_array($conditon)) {
            $arrCondition = [];
            if (!is_assoc($conditon)) {
                foreach ($conditon as $v) {
                    $arrCondition[] = $v;
                }
            } else {
                foreach ($conditon as $k => $v) {
                    $arrCondition[] = "$k='$v'";
                }
            }
            if (!empty($arrCondition)) {
                $this->_where .= implode(" and ", $arrCondition);
            }
        }
        return $this;
    }

    /**
     * @param $condition
     * @return $this
     */
    public function  on($condition)
    {
        if (is_string($condition)) {
            $this->_join .= " on $condition ";
        } else if (is_array($condition)) {
            $arrCondition = [];
            foreach ($condition as $k => $v) {
                $arrCondition[] = "$k='$v'";
            }
            $this->_join .= " on " . implode(" and ", $arrCondition);
        }
        return $this;
    }

    /**
     * @param $tablename
     * @param string $join_type
     * @return $this
     */
    public function join($tablename, $join_type = 'join')
    {

        $this->_join .= " $join_type $tablename ";
        return $this;
    }

    /**
     * @param string $arr
     * @return $this
     */
    public function in($arr = "")
    {
        if (is_array($arr)) {
            foreach ($arr as &$field) {
                $field = '\'' . $field . '\'';
            }
            $ins = ' in (' . implode(',', $arr) . ')';
            $this->_where .= "  $ins";
        } else if (is_string($arr)) {
            $this->_where .= " in($arr)";
        }
        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function  and_($field)
    {
        if (is_string($field)) {
            $this->_where .= " and $field";
        } else if (is_array($field)) {
            $arrCondition = [];
            foreach ($field as $k => $v) {
                $arrCondition[] = "$k='$v'";
            }
            if (!empty($arrCondition)) {
                $this->_where .= " and " . implode(" and ", $arrCondition);
            }
        }
        return $this;
    }

    /**
     * @param $condition
     * @return $this
     */
    public function onOr($condition)
    {
        $this->_join .= " or $condition ";
        return $this;
    }

    /**
     * @param array $tables
     * @return $this
     */
    public function update($tables)
    {
        $this->clear();
        $this->_method = SqlBuilder::SQL_UPDATE;
        if (is_array($tables)) {
            $this->_update = implode(",", $tables);
        } else if (is_string($tables)) {
            $this->_update = " $tables ";
        }
        return $this;
    }

    /**
     * @param array $conditionExpr
     * @return $this
     */
    public function  set($conditionExpr = array())
    {
        //拼接expr
        if (is_array($conditionExpr)) {
            if (array_key_exists('expr', $conditionExpr)) {
                $this->_set .= $conditionExpr['expr'];
                unset($conditionExpr['expr']);
            }
            if (is_assoc($conditionExpr)) {
                foreach ($conditionExpr as $k => $v) {
                    $arrSetExpr[] = "$k='$v'";
                }
            } else {
                foreach ($conditionExpr as $v) {
                    $arrSetExpr[] = $v;
                }
            }
//            $arrSetExpr=[];
//            foreach($conditionExpr as $k=>$v){
//                $arrSetExpr[]="$k='$v'";
//            }
            $condition = implode(' , ', $arrSetExpr);
            $this->_set = ($this->_set == '' ? $condition : $this->_set . ' , ' . $condition);
        } else if (is_string($conditionExpr)) {
            $this->_set .= " $conditionExpr ";
        }
        return $this;
    }

    /** get sql
     * @return string
     */
    public function sql()
    {
        $sqlCmd = "";
        switch ($this->_method) {
            case SqlBuilder::SQL_SELECT: {
                $sqlCmd = "select {$this->_select} from {$this->_from}";
                if ($this->_join) {
                    $sqlCmd .= $this->_join;
                }
                if ($this->_where) {
                    $sqlCmd .= ' where ' . $this->_where;
                }
                if ($this->_limit) {
                    $sqlCmd .= $this->_limit;
                }
                if ($this->_group) {
                    $sqlCmd .= $this->_group;
                }
                if ($this->_having) {
                    $sqlCmd .= $this->_having;
                }
                if ($this->_order) {
                    $sqlCmd .= $this->_order;
                }
                break;
            }
            case SqlBuilder::SQL_UPDATE: {
                $sqlCmd = "update {$this->_update } set {$this->_set}";
                if ($this->_where) {
                    $sqlCmd .= ' where ' . $this->_where;
                }
                break;
            }
            case SqlBuilder::SQL_INSERT: {
                $sqlCmd = "insert into  {$this->_insert } VALUES  {$this->_value}";
                break;
            }
        }
        return $sqlCmd;
    }

    /**
     * @param array $values
     * @return $this
     */
    public function values($values = [[]])
    {

        $valueList = [];
        foreach ($values as &$value) {
            foreach ($value as &$v) {
                $v = "'$v'";
            }
            $valueList[] = "(" . implode(",", $value) . ")";
        }
        $this->_value = implode(",", $valueList);
        return $this;
    }

    /**
     * @param array $table
     * @param array $fields
     * @return $this
     */
    public function insertinto($table, $fields)
    {
        $this->clear();
        $this->_method = SqlBuilder::SQL_INSERT;
        if (is_array($table)) {
            $this->_insert = implode(",", $table);
        } else if (is_string($table)) {
            $this->_insert = $table;
        }
        if (is_array($fields)) {
            $insertFields = implode(',', $fields);
            if ($insertFields != "") {
                $this->_insert .= " ($insertFields)  ";
            }
        } else if (is_string($fields)) {
            $this->_insert .= " ($fields)  ";
        }
        return $this;
    }

    /**
     * @param string $expr
     * @return $this
     */
    public function having($expr = "")
    {
        if (is_string($expr)) {
            $this->_having = $expr;
        } elseif (is_array($expr)) {
            $this->_having = " having " . implode(" and ", $expr);
        }
        return $this;
    }

    /**
     * @param $num
     * @param int $offset
     * @return $this
     */
    public function limit($num, $offset = 0)
    {
        if ($offset <= 0)
            $this->_limit = " limit $num";
        else
            $this->_limit = " limit $num,$offset ";
        return $this;
    }

    /**
     * @param string $field
     * @param string $order
     * @return $this
     */
    public function orderby($field = '', $order = 'desc')
    {
        if (is_array($field)) {
            $this->_order = " order by " . implode(',', $field) . " $order";
        } else {
            $this->_order = " order by $field $order";
        }
        return $this;
    }


}

