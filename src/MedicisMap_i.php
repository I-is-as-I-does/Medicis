<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis;

interface MedicisMap_i
{
    public function __construct($collectionsDirPath);
    public function getLog();
    public function getMap($mapName);
    public function extractGroupId($collcId);
    public function IdExists($Id, $mapName);
    public function getInfos($Id, $mapName, $infokey = false);
    public function getDistDirStruct();
    public function getDir($dirKey);
    public function getAllIdsOfMap($mapName);
}
