<?php

require_once __DIR__."\..\dao\DAO.php";

class Firma{
    public string $name;
}

class Person {
    public int $id;
    public string $name;
    public int $alter;
    public bool $angestellt = false;
    public bool $lebendig = true;
    public DateTime $geburtstag;
    public Firma $unternehmen;
}

class FirmaDAO extends DAO{}
class PersonDAO extends DAO{}

class PseudoHandle{
    public string $prefix = "wp_";
    public function get_charset_collate(){return "BEISPIEL_CHARSET";}
}

$myHandle = new PseudoHandle();

?>
<pre>
<?= FirmaDAO::tableCreation($myHandle); ?>


<?= PersonDAO::tableCreation($myHandle); ?>
</pre>