<?php

define('ARRAY_A', 'ARRAY_A');

class MemoryDB {
    public $prefix = 'wp_';
    private $tables = [];

    public function insert($table, $data) {
        if (!isset($this->tables[$table])) {
            $this->tables[$table] = [];
        }
        $data['id'] = count($this->tables[$table]) + 1;
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

    public function get_results($query, $output = OBJECT) {
        // Only supports: SELECT * FROM $table WHERE ... AND ...
        if (!preg_match('/SELECT \* FROM (\w+)( WHERE (.+))?/i', $query, $matches)) {
            return [];
        }
        $table = $matches[1];
        $where = [];
        $nullChecks = [];
        if (isset($matches[3])) {
            $conds = explode('AND', $matches[3]);
            foreach ($conds as $cond) {
                $cond = trim($cond);
                // IS NULL / IS NOT NULL
                if (preg_match('/(\w+)\s+is\s+(not\s+)?null/i', $cond, $cm)) {
                    $nullChecks[] = [
                        'col' => $cm[1],
                        'not' => isset($cm[2]) && trim(strtolower($cm[2])) === 'not'
                    ];
                    continue;
                }
                // = Vergleich
                if (preg_match('/(\w+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|(\S+))/', $cond, $cm)) {
                    $value = isset($cm[2]) && $cm[2] !== '' ? $cm[2]
                        : (isset($cm[3]) && $cm[3] !== '' ? $cm[3] : $cm[4]);
                    $where[$cm[1]] = $value;
                }
            }
        }
        $results = [];
        if (!isset($this->tables[$table])) {
            echo "WARNING: Table '$table' does not exist in MemoryDB.\n";
            return [];
        }
        foreach ($this->tables[$table] as $row) {
            $match = true;
            foreach ($where as $k => $v) {
                if (!isset($row[$k]) || $row[$k] != $v) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                // Prüfe IS NULL / IS NOT NULL Bedingungen
                foreach ($nullChecks as $check) {
                    $col = $check['col'];
                    $isNot = $check['not'];
                    $isNull = !isset($row[$col]) || $row[$col] === null;
                    if ($isNot && $isNull) {
                        $match = false;
                        break;
                    }
                    if (!$isNot && !$isNull) {
                        $match = false;
                        break;
                    }
                }
            }
            if ($match) {
                $results[] = ($output === ARRAY_A) ? $row : (object)$row;
            }
        }
        return $results;
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