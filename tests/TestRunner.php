<?php
class TestRun{
    public string $name;
    public bool $success;
    public function __construct(string $name, bool $success){
        $this->name = $name;
        $this->success = $success;
    }
}

$testrun1 = new TestRun("Arschlecken", true);
$testrun2 = new TestRun("Arschkriechen", false);

$testRuns = array($testrun1, $testrun2);
?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <title>Hello, world!</title>
  </head>
  <body>
    <h1>Testergebnis</h1>

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

    <div class="accordion accordion-flush" id="accordionTestResults">    
        <?php foreach($testRuns as $i=>$testrun){ ?>
        <div class="accordion-item">
            <h2 class="accordion-header" id="testclass-<?php echo $i;?>">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#testclass-content-<?php echo $i;?>" aria-expanded="false" aria-controls="flush-collapseOne">
                <?php echo $testrun->name; ?> &nbsp;
                <?php if($testrun->success){?>
                        <span class="badge bg-success">Erfolgreich</span>
                    <?php } else { ?>
                        <span class="badge bg-danger">Fehlschlag</span>
                    <?php } ?>
                </button>
            </h2>
            <div id="testclass-content-<?php echo $i;?>" class="accordion-collapse collapse" aria-labelledby="testclass-<?php echo $i;?>" data-bs-parent="#accordionTestResults">
                <div class="accordion-body">
                        Hier die Methoden der Klasse
                </div>
            </div>
        </div>
        <?php } ?>
        </div>
    </div>
  </body>
</html>
<?php
?>