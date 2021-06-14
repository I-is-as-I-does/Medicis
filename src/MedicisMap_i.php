<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis;

interface MedicisMap_i
{
    public function __construct($collectionDirPath,$abslURIBase = null);
    public function getSchAbslId($collcId);
    public function getLog();
    public function getCollcMap();
    public function getTranslMap();
    public function getAvailableLangs();
    public function getCollcIndex();
    public function getDirMap();
    public function getDir($dirKey);
    public function collcExists($collcId);
    public function groupExists($groupId);
    public function getGroupInfos($groupId);
    public function getGroupId($collcId);
    public function getCollcInfos($collcId);
    public function getCollcSrcPath($collcId);
    public function getDistPath($Id, $subDir, $group = false, $lang = false);
    public function getDistDirStruct();
    public function getAllGroupIds();
    public function getAllCollcIds();
}
