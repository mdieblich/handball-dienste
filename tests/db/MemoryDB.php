<?php
// MemoryDB.php
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
        if (isset($matches[3])) {
            $conds = explode('AND', $matches[3]);
            foreach ($conds as $cond) {
                if (preg_match('/(\w+)\s*=\s*("?)(.*?)\2/', trim($cond), $cm)) {
                    $where[$cm[1]] = $cm[3];
                }
            }
        }
        $results = [];
        if (!isset($this->tables[$table])) return [];
        foreach ($this->tables[$table] as $row) {
            $match = true;
            foreach ($where as $k => $v) {
                if (!isset($row[$k]) || $row[$k] != $v) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $results[] = ($output === ARRAY_A) ? $row : (object)$row;
            }
        }
        return $results;
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