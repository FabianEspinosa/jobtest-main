<?php

namespace App\Model;

use App\System\Engine as Engine;

class Model extends Engine
{
    protected $primary      = 'id';
    protected $table;
    private $start;
    private $limit;
    private $page;
    private $first = false;
    protected $where;
    protected $orWhere;
    protected $orWhereGroup = "@DEFAULT";

    const SORT_ASC   = 'ASC';
    const SORT_DESC  = 'DESC';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $attributes = [];

    public static    $snakeAttributes = true;
    protected static    $withCache = [];
    protected static $mutatorCache    = [];
    protected static $autoCache       = [];

    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function __isset($key)
    {
        return !is_null($this->getAttribute($key));
    }

    public function __unset($key)
    {
        unset($this->attributes[$key], $this->relations[$key]);
    }

    public function start($start)
    {
        $this->start = $start;

        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    public function paginate($page = null)
    {
        if ($page === null) {
            if (isset($_GET['page'])) {
                $page = $_GET['page'];
            } else {
                $page = 1;
            }
        }

        $this->limit = $this->config->data('PAGE_LIMIT', 20);
        $this->start = ($page - 1) * $this->limit;

        return $this;
    }

    public function first($first = true)
    {
        $this->first = $first;

        return $this;
    }

    public function makeQuery($action = 'find')
    {
        $this->prepareFind();       

        $query = "SELECT ";
        if ($action == 'count') {
            $query .= "COUNT(id) as total ";
        } else {
            $query .= "* ";
        }

        $query .= " FROM $this->table";        

        if ($this->where) {
            $query .= " WHERE " . implode(' AND ', $this->where);
        }
        
        if ($action == 'find' && !is_null($this->start) && !is_null($this->limit)) {
            $query .= " LIMIT $this->start, $this->limit";
        }
        
        $query = $this->db->query($query);
        
        if ($action == 'count') {
            $items = $query->row['total'];
        } else {
            if (!$this->first) {
                $items = $this->parseResults($query);
            } else {
                $items = $query->row;
            }
        }      

        $this->start = null;
        $this->limit = null;
        $this->first = false;        

        return $items;
    }

    public function parseResults($results)
    {
        $class = get_called_class();
        $class = new $class();
        $total = $class->total();

        return [
            'rows' => $results->rows,
            'pagination' => [
                'start' => $this->start,
                'limit' => $this->limit,
                'total' => $total,
                'pages' => ceil($total / $this->limit)
            ]
        ];
    }


    public function get()
    {
        $items = $this->makeQuery(); 
        return $items;
    }


    public function total()
    {
        $items = $this->makeQuery('count');
        return $items;
    }

    public function delete($id)
    {
        $this->db->query("DELETE FROM $this->table WHERE id = $id LIMIT 1");
    }

    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function getAttribute($key)
    {
        if (!$key) {
            return;
        }

        if (array_key_exists($key, $this->attributes) || $key === $this->primary) {
            return $this->getAttributeValue($key);
        }

        if (method_exists(self::class, $key)) {
            return;
        }

        return null;
    }

    public function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        return $value;
    }

    protected function getAttributeFromArray($key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }
    }
    /**
     * @return bool|Model
     * @throws Exception
     */
    public function save()
    {
        if (!$this->table) {
            trigger_error('No table defined');
        }        

        $isNew = !!empty($this->attributes[$this->primary]);

        $sql = !$isNew ? "UPDATE " : "INSERT INTO ";

        $sql .= " `" . $this->table . "` SET ";

        $values = [];

        foreach ($this->attributes as $field => $value) {
            if ($field == $this->primary) {
                continue;
            }

            if (in_array($field, ['created_at', 'updated_at']) && $value === true) {
                $values[] = "`$field` = NOW()";
            } else {
                $values[] = "`$field` = '" . $this->db->escape($value) . "'";
            }
        }

        if (!$values) {
            trigger_error('No values found');
        }

        $sql .= implode(", ", $values);

        if (!$isNew) {
            $sql .= " WHERE `" . $this->primary . "` = '" . $this->db->escape($this->attributes[$this->primary]) . "'";
        }

        try {
            if ($this->db->query($sql)) {
                $id = !$isNew ? $this->attributes[$this->primary] : $this->db->getLastId();
                return $this->where($id)->get();
            }
        } catch (\Exception $ex) {
            throw new $ex;
        }

        return false;
    }

    public function where()
    {
        $num_args = func_num_args();       
        $args = func_get_args();

        if ($num_args === 1) {
            $this->setWhere($this->primary, $this->getFilterColumn($this->primary, $args[0]));
            $this->first = true;
        } elseif ($num_args === 2) {               
            $this->setWhere($args[0], $this->getFilterColumn($args[0], $args[1]));
        } elseif ($num_args === 3) {
            if ($args[1] !== 'between') {
                $this->setWhere($args[0], [$args[1] => $this->getFilterColumn($args[0], $args[2], ['operator' => $args[1]])]);
            } else {
                $values = [];
                foreach ($args[2] as $k => $arg) {
                    $values[($k > 0 ? '$lte' : '$gte')] = $this->getFilterColumn($args[0], $arg);
                }
                $this->setWhere($args[0], $values);
            }
        }

        return $this;
    }

    public function orWhere()
    {
        $num_args = func_num_args();
        $args = func_get_args();

        $or = [];
        if (is_array($args[0])) {
            foreach ($args as $arg) {
                if (is_array($arg)) {
                    $arg_count = count($arg);
                    if ($arg_count === 2) {
                        $or[$arg[0]] = $this->getFilterColumn($arg[0], $arg[1]);
                    } elseif ($arg_count === 3) {
                        $operator = self::getOperator($arg[1]);
                        $or[][$arg[0]] = [$operator => $this->getFilterColumn($arg[0], $arg[2])];
                    }
                }
            }
        } else {
            if ($num_args === 1) {
                $or[][$this->primary] = $this->getFilterColumn($this->primary, $args[0]);
            } elseif ($num_args === 2) {
                $or[][$args[0]] = $this->getFilterColumn($args[0], $args[1]);
            } elseif ($num_args === 3) {
                if ($args[1] !== 'between') {
                    $operator = self::getOperator($args[1]);
                    $or[][$args[0]] = [$operator => $this->getFilterColumn($args[0], $args[2])];
                } else {
                    $values = [];
                    foreach ($args[2] as $k => $arg) {
                        $values[($k > 0 ? '>=' : '<=')] = $this->getFilterColumn($args[0], $arg);
                    }
                    $or[][$args[0]] = $values;
                }
            }
        }

        if (!isset($this->orWhere[$this->orWhereGroup])) {
            $this->orWhere[$this->orWhereGroup] = [];
        }
        $this->orWhere[$this->orWhereGroup][] = ['$and' => $or];

        return $this;
    }

    public static function getOperator($operator)
    {
        $find = [
            'eq',
            'gte',
            'lte',
            'ne',
            'gt',
            'lt'
        ];
        $replace = [
            '=',
            '>=',
            '<=',
            '<>',
            '>',
            '<'
        ];

        $operator = str_replace($find, $replace, $operator);
        return $operator;
    }

    private function setField($field)
    {
        if (strpos($field, '.') === false) {
            $field = $this->table . '.`' . $field . '`';
        } else {
            $field_array = explode('.', $field);
            $field = $field_array[0] . '.`' . implode('.', array_slice($field_array, 1)) . '`';
        }

        return $field;
    }

    public function setWhere($key, $value)
    {
        if (is_array($value)) {
            $value_str = '';
            foreach ($value as $k => $v) {
                if (is_array($v) && in_array(strtolower($k), ['in', 'not in'])) {
                    $value_str = ' ' . strtoupper($k) . " ('" . implode("', '", $v) . "')";
                } else {
                    $value_str = $k . " '" . $v . "'";
                }
            }

            $value = $value_str;
        }

        $this->where[] = $this->setField($key) . $value;            

        return $this;
    }

    protected function getFilterColumn($column, $value, $options = [])
    {
        if (starts_with($column, $this->subtable . '.')) {          
            $column = substr($column, strlen($this->subtable . '.'));
        }

        if (in_array($column, ['created_at', 'updated_at'])) {       
            $this->columns[$column] = 'datetime';
        }

        if ($column == $this->primary) {       
            return "= '" . (int)$value . "'";
        }

        if ($value === 'null') {           
            return null;
        }

        if (isset($this->columns[$column]) && !is_array($this->columns[$column])) {   
            if (!is_null($value)) {                
                switch ($this->columns[$column]) {
                    case 'string':
                        if (is_array($value)) {
                            $val = [];
                            foreach ($value as $v) {
                                if (!is_array($v) || !is_object($v)) {
                                    if ($v == null)
                                        $val[] = $v;
                                    else $val[] = trim((string)$v);
                                }
                            }
                            $value = $val;
                        } else if (!is_bool($value)) {
                            if (!empty($options['operator'])) {
                                $value = $this->db->escape(trim((string)$value));
                            } else {
                                $value = "= '" . $this->db->escape(trim((string)$value)) . "'";
                            }
                        }                     
                        break;
                    case 'float':
                        $value = (float)$value;
                        break;
                    case 'date':
                    case 'datetime':
                        if (!is_array($value) && ($value == 'yesterday' || $value == 'today' || $value == 'tomorrow')) {
                            try {
                                $from = new \DateTime($value);
                                $to = new \DateTime($value);

                                $to = $to->modify('+1 day');
                                $from = $from->setTime(0, 0, 0);

                                $this->valueAs($from . ' 00:00:00');
                            } catch (\Exception $ex) {
                            }
                        } else {
                            if ($value !== false && !is_null($value)) {
                                if ($value !== '') {
                                    if ($value == 'yesterday' || $value == 'today' || $value == 'tomorrow') {
                                        $from = new \DateTime($value);
                                        $from = $from->getTimestamp();
                                    } elseif ($value == 'null') {
                                        $from = 'null';
                                    } else {
                                        $from = $value;
                                    }

                                    if ($from != 'null') {
                                        $value = $from;
                                    } else {
                                        $value = null;
                                    }
                                }
                            }
                        }
                        break;
                    case 'integer':
                        if (is_array($value)) {
                            $vals = $value;
                            $value = [];
                            foreach ($vals as $val) {
                                $value[] = (int)$val;
                            }
                        } else {
                            $value = "= '" . (int)$value . "'";
                        }
                        break;
                    case 'decimal':
                        if (is_array($value)) {
                            $vals = $value;
                            $value = [];
                            foreach ($vals as $val) {
                                $value[] = (float)$val;
                            }
                        } else {
                            $value = "= '" . (float)$value . "'";
                        }
                        break;
                    case 'boolean':
                        $value = $value ? 1 : 0;
                        break;
                    default:
                        $value = $value;
                        break;
                }
            }

            return $value;            
        }

        return null;
    }


    protected function valueAs($value)
    {
        $fn = ['NOW()'];
        if (in_array($value, $fn)) {
            return $value;
        } else {
            return "'" .  $this->db->escape($value) . "'";
        }
    }


    public function prepareFind()
    {
        $class = get_called_class();

        foreach (get_class_methods($class) as $method) {
            if (preg_match('/^get(.+)Attribute$/', $method, $matches)) {
                if (static::$snakeAttributes) {
                    $matches[1] = $this->inflector->underscore($matches[1]);
                }

                if (!isset(static::$mutatorCache[$class]) || !in_array($matches[1], static::$mutatorCache[$class])) {
                    static::$mutatorCache[$class][] = $matches[1];
                }
            }
        }
    }
}
