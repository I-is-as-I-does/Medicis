<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

use SSITU\JackTrades\Jack;

class MedicisGroup implements MedicisGroup_i
{
    private $MetaMedicis;
    private $MedicisMap;
    private $dirStruc;
    public function __construct($MetaMedicis)
    {
        $this->MetaMedicis = $MetaMedicis;
        $this->MedicisMap = $MetaMedicis->getMedicisMap();
        $this->dirStruc = $this->MedicisMap->getDistDirStruct();
    }

    public function groupBuild($groupId, $translToo = true)
    {
        $groupInfos = $this->MedicisMap->getGroupInfos($groupId);
        if (array_key_exists('err', $groupInfos)) {
            return $groupInfos;
        }
        $groupCollcs = $groupInfos['collcs'];
        $rslt = [];
        $bundle = [];
        foreach ($groupCollcs as $collcId => $collcPaths) {
            $collcBuild = $this->MetaMedicis->getMedicisMember('Collc')->collcBuild($collcId, $translToo);
            $rslt['collcs-build'][$collcId] = $collcBuild;
            if (!array_key_exists('err', $collcBuild)) {
                foreach ($this->dirStruc as $subDir) {
                    $rslt[$subDir . '-bundle'] = [];
                    if (array_key_exists('success', $collcBuild[$subDir]) && ($translToo || $subDir !== 'transl')) {
                        $content = $this->MetaMedicis->getCollcFile($collcPaths['distPaths'][$subDir]);
                        if (!array_key_exists('err', $content)) {
                            $bundle[$subDir][$collcId] = $content;
                        } else {
                            $rslt[$subDir . '-bundle']['err'][$collcId] = $content;
                        }
                    }
                }
            }
        }
        if (!empty($bundle)) {
            foreach ($bundle as $subDir => $collcData) {
                if (!array_key_exists('err', $rslt[$subDir . '-bundle'])) {
                    $bundlepath = $groupInfos['distDirPaths'][$subDir] . $groupId . '.json';
                    $rslt[$subDir . '-bundle'] = Jack::File()->saveJson($collcData, $bundlepath, true);
                }
            }
        }

        if ($translToo === true) {
            $rslt['transl'] = $this->MetaMedicis->getMedicisMember('Transl')->groupTranslCheck($groupId);
        }
        return $rslt;
    }

}
