<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis;

interface MetaMedicis_i
{
    public function __construct($MedicisMap);
    public function getMedicisMap();
    public function getMedicisMember($member);
    public function getBundleInfos($GroupOrProflId, $mapName);
    public function getSchema($SchPathOrId);
    public function getPartialsDistPath($Id, $suffx);
    public function saveDistFile($content, $Id, $suffx, $proflFilename = false);
    public function getProfileDistPath($Id, $suffx, $proflFilename);
}
