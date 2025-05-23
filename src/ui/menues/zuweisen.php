<?php
require_once __DIR__."/../../handball/Dienst.php";
require_once __DIR__."/../../handball/SpieleListe.php";
require_once __DIR__."/../../db/dao/MannschaftDAO.php";
require_once __DIR__."/../../db/dao/DienstDAO.php";

require_once __DIR__."/../../db/service/SpielService.php";
require_once __DIR__."/../../ui/components/SpielZeile.php";

$hook_zuweisen;
function addDiensteZuweisenKonfiguration(){
    global $hook_zuweisen;
    $hook_zuweisen = add_submenu_page( 'dienste', 'Dienste zuweisen', 'Dienste zuweisen', 'administrator', 'dienste-zuweisen', 'displayDiensteZuweisen');
    
    add_action( 'admin_enqueue_scripts', 'enqueue_dienste_js' );
}
add_action( 'wp_ajax_dienst_zuweisen', 'dienst_zuweisen' );
add_action( 'wp_ajax_dienst_entfernen', 'dienst_entfernen' );

function enqueue_dienste_js($hook){
    global $hook_zuweisen;
    if ( $hook !== $hook_zuweisen ) {
        return;
    }
    wp_enqueue_script(
        'dienste-script',
        plugins_url( '/zuweisen.js', __FILE__ ),
        array( 'jquery' ),
        '1.0.0'
    );
}

function dienst_zuweisen(){
    $dienstDAO = new DienstDAO();
    $dienstDAO->assign( $_POST['dienst'], $_POST['mannschaft']);
    http_response_code(200);
    wp_die();
}
function dienst_entfernen(){
    $dienstDAO = new DienstDAO();
    $dienstDAO->unassign( $_POST['dienst']);
    http_response_code(200);
    wp_die();
}

function displayDiensteZuweisen(){
    $mannschaftDAO = new MannschaftDAO();
    $spielService = new SpielService();

    $mannschaftsListe = $mannschaftDAO->loadMannschaften();
    $from = new DateTime('yesterday');
    if(isset($_GET['seit'])){
        $enteredDate = DateTime::createFromFormat('d.m.Y', $_GET['seit']);
        if($enteredDate){
            $from = $enteredDate;
        }
    }
    $fromFormatted = $from->format('Y-m-d');
    $spieleListe = $spielService->loadSpieleMitDiensten(" anwurf > '$fromFormatted'");
 ?>
<script>
mannschaften = [<?= implode(",",$mannschaftsListe->getIDs()); ?>];

// URL anpassen
const url = new URL(window.location.href);
url.searchParams.set('seit', '<?=$from->format('d.m.Y')?>');
window.history.replaceState({}, '', url);
</script>
<div class="wrap">
    <div style="float:right; width: 200px; background-color:#ddd; padding: 5px">
    Filter
    <?php foreach ($mannschaftsListe->mannschaften as $mannschaft) { ?>
        <br>
        <input type="checkbox" name="filter-<?= $mannschaft->id;?>" checked onchange="mannschaftDarstellen(<?= $mannschaft->id;?>, this.checked)" id="filter-<?= $mannschaft->id;?>">
        <label for="filter-<?= $mannschaft->id;?>"><?= $mannschaft->getKurzname(); ?></label>
    <?php } ?>
    </div>
    <h1>Dienste zuweisen</h1>
    Die Eingaben der Checkboxen werden direkt gespeichert. Es werden Dienste seit <?php echo $from->format('d.m.Y'); ?> angezeigt (<a href="<?php global $wp;
echo add_query_arg( 'seit', "01.01.2000" );?>">alles</a> anzeigen) <br>
    <small>* stellt Sekretär bei Heimspielen.</small>
    <table cellpadding="3" cellspacing="3" id="tabelle-dienste-zuweisen">
    <tr style="background-color:#ddd; position: sticky; top: 32px">
        <th>Spiel-Nr.</th>
        <th>Datum</th>
        <th>Halle</th>
        <th>Heim</th>
        <th>Auswärts</th>
        <?php
foreach($mannschaftsListe->mannschaften as $mannschaft){
    $anzahlDienste = $spieleListe->zaehleDienste($mannschaft);
    echo "<td style=\"text-align:center\" mannschaft=\"".$mannschaft->id."\">".$mannschaft->getKurzname();
    echo "<div style=\"font-size: 0.6em\">"; 
        echo "&sum; ";
        echo "<span title=\"Summe\" mannschaft=\"".$mannschaft->id."\" counter=\"Summe\">0</span>/";
        echo "<span title=\"gewichtete Summe\nBerücksichtigt unterschiedlichen Aufwand pro Dienst\nAufbau:50%\nZ/S Heim: 100%\nCatering:180%\nZ/N Auswärts:180%\nAbbau:100%\" mannschaft=\"".$mannschaft->id."\" counter=\"gewichtete Summe\">0</span>";
        echo "<br>";
        echo "<span title=\"Auf- und Abbau\" mannschaft=\"".$mannschaft->id."\" counter=\"Auf- und Abbau\">0</span>/";
        echo "<span title=\"Zeitnehmer & Sekretär (HEIM)\" mannschaft=\"".$mannschaft->id."\" counter=\"Heim\">0</span>/";
        echo "<span title=\"Catering\" mannschaft=\"".$mannschaft->id."\" counter=\"Catering\">0</span>/";
        echo "<span title=\"Sekretär (AUSWÄRTS)\" mannschaft=\"".$mannschaft->id."\" counter=\"Auswaerts\">0</span>";
    echo "</div>"; 
    echo "</td>";
}
?>
    </tr>
<?php
$vorherigesSpiel = null;
$zeilenFarbePrimaer = true;
foreach($spieleListe->spiele as $spiel){

    if(!$spiel->isAmGleichenTag($vorherigesSpiel)){
        $zeilenFarbePrimaer = !$zeilenFarbePrimaer;
    }
    $backgroundColor = $zeilenFarbePrimaer?"#ddd":"#eee";
    $nahgelegeneSpieleProMannschaft = $spieleListe->findNahgelegeneSpiele($spiel);
    
    $spielZeile = new SpielZeile($spiel, $backgroundColor, $nahgelegeneSpieleProMannschaft, $mannschaftsListe);
    echo $spielZeile->toHTML();
   
    $vorherigesSpiel = $spiel;
}
?>
    </table>
    <small>* stellt Sekretär bei Heimspielen</small>
</div>
 <?php
}
?>