<?php

define('ARRAY_A', 'ARRAY_A');

class MemoryDB {
    public $prefix = 'wp_';
    private $tables = [];

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
    
    public function get_results($query, $output = OBJECT) {
        if (!preg_match('/SELECT \* FROM (\w+)( WHERE (.+?))?( ORDER BY (.+))?$/i', $query, $matches)) {
            return [];
        }
        $table = $matches[1];
        $orderBy = isset($matches[5]) ? trim($matches[5]) : null;

        [$where, $nullChecks] = $this->parseWhere(isset($matches[3]) ? $matches[3] : null);

        if (!isset($this->tables[$table])) {
            echo "WARNING: Table '$table' does not exist in MemoryDB.\n";
            return [];
        }

        $results = [];
        foreach ($this->tables[$table] as $row) {
            if ($this->rowMatches($row, $where, $nullChecks)) {
                $results[] = ($output === ARRAY_A) ? $row : (object)$row;
            }
        }

        if ($orderBy && count($results) > 1) {
            $this->sortResults($results, $orderBy, $output);
        }

        return $results;
    }

    private function parseWhere($whereString) {
        $where = [];
        $nullChecks = [];
        if ($whereString) {
            $conds = explode('AND', $whereString);
            foreach ($conds as $cond) {
                $cond = trim($cond);
                if (preg_match('/(\w+)\s+is\s+(not\s+)?null/i', $cond, $cm)) {
                    $nullChecks[] = [
                        'col' => $cm[1],
                        'not' => isset($cm[2]) && trim(strtolower($cm[2])) === 'not'
                    ];
                    continue;
                }
                if (preg_match('/(\w+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|(\S+))/', $cond, $cm)) {
                    $value = isset($cm[2]) && $cm[2] !== '' ? $cm[2]
                        : (isset($cm[3]) && $cm[3] !== '' ? $cm[3] : $cm[4]);
                    $where[$cm[1]] = $value;
                }
            }
        }
        return [$where, $nullChecks];
    }

    private function rowMatches($row, $where, $nullChecks) {
        foreach ($where as $k => $v) {
            if (!isset($row[$k]) || $row[$k] != $v) {
                return false;
            }
        }
        foreach ($nullChecks as $check) {
            $col = $check['col'];
            $isNot = $check['not'];
            $isNull = !isset($row[$col]) || $row[$col] === null;
            if ($isNot && $isNull) {
                return false;
            }
            if (!$isNot && !$isNull) {
                return false;
            }
        }
        return true;
    }

    private function sortResults(&$results, $orderBy, $output) {
        $parts = preg_split('/\s*,\s*/', $orderBy);
        usort($results, function($a, $b) use ($parts, $output) {
            foreach ($parts as $part) {
                if (preg_match('/(\w+)(\s+DESC)?/i', $part, $pm)) {
                    $col = $pm[1];
                    $desc = isset($pm[2]) && stripos($pm[2], 'DESC') !== false;
                    $va = is_array($a) ? ($a[$col] ?? null) : ($a->$col ?? null);
                    $vb = is_array($b) ? ($b[$col] ?? null) : ($b->$col ?? null);
                    if ($va == $vb) continue;
                    if ($va < $vb) return $desc ? 1 : -1;
                    if ($va > $vb) return $desc ? -1 : 1;
                }
            }
            return 0;
        });
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