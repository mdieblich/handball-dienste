<?php

class DienstAenderung {
    public int $id;
    public int $dienstID;

    public bool $istNeu = false;
    public bool $entfaellt = false;

    public ?DateTime $anwurfVorher = null;
    public ?string $halleVorher = null;

}