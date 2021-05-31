<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

use SSITU\JackTrades\Jack;

class MedicisGroup implements MedicisGroup_i
{
    private $MetaMedicis;
    private $MedicisMap;

    public function __construct($MetaMedicis)
    {
        $this->MetaMedicis = $MetaMedicis;
        $this->MedicisMap = $MetaMedicis->getMedicisMap();
    }

    public function groupBuild($groupId, $translToo = true)
    {
        $groupInfos = $this->MedicisMap->getGroupInfos($groupId);
        if (array_key_exists('err', $groupInfos)) {
            return $groupInfos;
        }
        $groupCollcs = $groupInfos['collcs'];
        $rslt = [];
        $configBundle = [];
        foreach ($groupCollcs as $collcId => $collcPaths) {
            $collcBuild = $this->MetaMedicis->getMedicisMember('Collc')->collcBuild($collcId, $translToo);
            $rslt['collcs-build'][$collcId] = $collcBuild;
            if (!array_key_exists('err', $collcBuild) && array_key_exists('success', $collcBuild['config'])) {
                $configBundle[$collcId] = $this->MetaMedicis->getCollcFile($collcPaths['distPaths']['config']);
            }
        }
        if (!empty($configBundle)) {
            $bundlepath = $groupInfos['distDirPaths']['config'] . $groupId . '.json';
            $rslt['config-bundle'] = Jack::File()->saveJson($configBundle, $bundlepath, true);
        } else {
            $rslt['config-bundle'] = 'skipped: no config file found';
        }

        if ($translToo === true) {
            $rslt['transl'] = $this->MetaMedicis->getMedicisMember('Transl')->groupTranslCheck($groupId);
        }
        return $rslt;
    }

}
