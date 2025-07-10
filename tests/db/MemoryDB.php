<?php
require_once __DIR__."/../../src/log/Log.php";

require_once __DIR__."/ColumnNameClause.php";
require_once __DIR__."/WhereClause.php";
require_once __DIR__."/OrderByClause.php";

define('ARRAY_A', 'ARRAY_A');

class MemoryDB {
    private Log $logfile;
    public $prefix = 'wp_';
    private $tables = [];
    public int $insert_id;

    public function __construct(Log $logfile = null) {
        $this->logfile = $logfile ?? new NoLog();
    }

    public function insert($table, $data) {
        if (!isset($this->tables[$table])) {
            $this->tables[$table] = [];
        }
        $data['id'] = rand();
        $this->tables[$table][] = $data;
        $this->insert_id = $data['id'];
        return true;
    }

    public function update($table, $data, $where) {
        if (!isset($this->tables[$table])) return false;
        foreach ($this->tables[$table] as &$row) {
            $match = true;
            foreach ($where as $k => $v) {
                if (!isset($row[$k]) || $row[$k] != $v) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                foreach ($data as $k => $v) {
                    $row[$k] = $v;
                }
                return true;
            }
        }
        return false;
    }

    public function delete($table, $where) {
        if (!isset($this->tables[$table])) return false;
        foreach ($this->tables[$table] as $i => $row) {
            $match = true;
            foreach ($where as $k => $v) {
                if (!isset($row[$k]) || $row[$k] != $v) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                unset($this->tables[$table][$i]);
                return true;
            }
        }
        return false;
    }

    public function get_var($query) {
        $results = $this->get_results($query, ARRAY_A);
        if ($results && count($results) > 0) {
            return reset($results[0]);
        }
        return null;
    }
    
    public function get_results($query, $output = OBJECT): array {
        if (!preg_match('/SELECT\s+((?:(?! FROM ).)+) FROM (\w+) WHERE (.+?)(?: ORDER BY (.*))?$/i', $query, $matches)) {
            $this->logfile->log("FEHLER: Query passt nicht zu Format: $query");
            return [];
        }

        $columnNameClause = new ColumnNameClause($matches[1]);
        $tableName = $matches[2];
        $whereClause = new WhereClause(
            $matches[3],
            Closure::fromCallable([$this, 'get_results'])
        );
        $orderByClause = new OrderByClause($matches[4]);

        $table = $this->tables[$tableName];
        if(!isset($table)){
            $this->logfile->log("WARNUNG: Tabelle $tableName existiert nicht.");
            return [];
        }        

        $fullRows = $whereClause->filterRows($table);
        $orderByClause->sort($fullRows);
        $reducedRows = $columnNameClause->filterColumns($fullRows);
        return $reducedRows;
    }
    
    public function get_row($query, $output = OBJECT) {
        $results = $this->get_results($query, $output);
        if (!$results || count($results) === 0) {
            return null;
        }
        return $results[0];
    }

    public function query($query) {
        // Only supports DELETE FROM $table
        if (preg_match('/DELETE FROM (\w+)/i', $query, $matches)) {
            $table = $matches[1];
            $this->tables[$table] = [];
            return true;
        }
        return false;
    }
}