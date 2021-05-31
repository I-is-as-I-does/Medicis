<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis;

interface MetaMedicis_i
{
    public function __construct($MedicisMap);
    public function getMedicisMap();
    public function getMedicisMember($member);
    public function quickCheckSchema($collcId, $sch = array());
    public function quickCheckSrc($collcId, $src = array());
    public function getCollcFile($PathOrId, $dirKey = '');
    public function saveDistFile($content, $collcId, $subDir);
}
