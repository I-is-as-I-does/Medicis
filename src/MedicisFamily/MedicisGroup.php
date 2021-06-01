<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

use SSITU\Jack\Jack;

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
        $transl = [];
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
                            $rslt[$subDir . '-bundle']['err'][$collcId] = $content['err'];
                        }
                    }
                }
            }
        }
        if (!empty($bundle)) {
            if (!empty($bundle['config']) && file_exists($groupInfos['groupSrcConfig'])) {
                $wrapConfig = $this->groupConfig($bundle['config'], $groupInfos['groupSrcConfig']);
                if (array_key_exists('err', $wrapConfig)) {
                    $rslt['config-bundle']['err']['group-config'] = $wrapConfig['err'];
                } else {
                    $bundle['config'] = $wrapConfig;
                }
            }
            $rslt = $this->createBundleFiles($groupId, $bundle, $groupInfos['bundlePaths'], $rslt);
        }

        if ($translToo === true) {
            $rslt['transl'] = $this->MetaMedicis->getMedicisMember('Transl')->groupTranslCheck($groupId);
        }
        return $rslt;
    }

    private function groupConfig($bundleConfig, $groupConfigPath)
    {

        $groupConfig = $this->MetaMedicis->getCollcFile($groupConfigPath);
        if (!array_key_exists('err', $groupConfig)) {
            $groupConfig['config']["items"] = $bundleConfig;
            return $groupConfig['config'];
        }

    return $groupConfig;
    }

    private function createBundleFiles($groupId, $bundle, $bundlePaths, $rslt)
    {
        foreach ($bundle as $subDir => $collcData) {
            if (!array_key_exists('err', $rslt[$subDir . '-bundle'])) {
                $rslt[$subDir . '-bundle'] = Jack::File()->saveJson($collcData, $bundlePaths[$subDir], true);
            }
        }
        return $rslt;
    }

}
