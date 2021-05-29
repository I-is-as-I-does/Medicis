<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

use SSITU\JackTrades\Jack;

class MedicisGroup implements MedicisGroup_i
{
    private $MetaMedicis;
    private $MedicisMap;
    private $distDirStruct;

    public function __construct($MetaMedicis)
    {
        $this->MetaMedicis = $MetaMedicis;
        $this->MedicisMap = $MetaMedicis->getMedicisMap();
        $this->distDirStruct = $this->MedicisMap->getDistDirStruct();
    }

    public function groupBuild($groupId, $translToo = true)
    {

        $groupInfos = $this->MetaMedicis->getBundleInfos($groupId, 'group');
        if (array_key_exists('err', $groupInfos)) {
            return $groupInfos;
        }
        $rslt = [];
        foreach ($groupInfos['collcIds'] as $collcId) {
            $rslt['collcs'][$collcId] = $this->MetaMedicis->getMedicisMember('Collc')->collcBuild($collcId, $translToo);
        }
        $rslt['config'] = $this->buildGroupConfig($groupId, $groupInfos);
        if ($translToo === true) {
            $rslt['transl'] = $this->MetaMedicis->getMedicisMember('Transl')->bundleTranslCheck($groupId);
        }
        return $rslt;
    }

    public function buildGroupConfig($groupId, $groupInfos = false)
    {if (empty($groupInfos)) {
        $groupInfos = $this->MetaMedicis->getBundleInfos($groupId, 'group');
        if (array_key_exists('err', $groupInfos)) {
            return $groupInfos;
        }
    }
        $rslt = [];
        $config = [];
        $paths = $this->getRelCollcsFilePaths($groupId);
        $configPaths = $paths['config'];
        unset($paths['config']);
        $config['paths'] = $paths;
        $jobs = ['pages' => [$configPaths, $groupInfos['collcIds']], 'section' => [$groupId, $groupInfos['name'], $groupInfos['priority']]];
        foreach ($jobs as $jobk => $jobParams) {
            $method = 'get' . ucfirst($jobk) . 'Config';
            $do = $this->$method(...$jobParams);
            if (!empty($do['log'])) {
                $rslt[$jobk . '-log'] = $do['log'];
            }
            $config[$jobk] = $do['config'];
        }
        $rslt['save'] = $this->MetaMedicis->saveDistFile($config, $groupId, 'config');
        return $rslt;
    }

    private function getRelCollcsFilePaths($groupId)
    {
        $paths = [];
        foreach ($this->distDirStruct as $dirK) {
            $paths[$dirK] = glob($this->MedicisMap->getDir('dist/partials/' . $dirK) . $groupId . '-*-*.json');
        }
        return $paths;
    }

    private function getPagesConfig($configPaths, $collcIds)
    {

        $log = [];
        $pages = [];
        foreach ($configPaths as $file) {
            $content = Jack::File()->readJson($file);
            if (empty($content)) {
                $log['err'][] = 'unvalid json or path: ' . $file;
            } else {
                $collcId = basename($file, '-config.json');
                $searchkey = array_search($collcId, $collcIds);
                if ($searchkey === false) {
                    $log['err'][] = $collcId . ' is not in map';
                } else {
                    unset($collcIds[$searchkey]);
                    $pages = array_merge($pages, $content);
                }
            }
        }
        if (!empty($collcIds)) {
            $log['todo'] = implode('; ', $collcIds);
        }
        return ['log' => $log, 'config' => $pages];

    }

    private function getSectionConfig($groupId, $groupName, $groupPriority)
    {
        $section = [];
        $section[$groupId]['header'] = $groupName;
        $section[$groupId]['bloc'] = 'studio';
        $section[$groupId]['priority'] = $groupPriority;
        return ['config' => $section];
    }

}
