<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis;

use SSITU\JackTrades\Jack;

class MedicisMap implements MedicisMap_i
{

    private $collectionDirPath;

    private $bundlesConfigFilename = 'groups-profiles';

    private $dirStructure = ['config', 'data', 'sch', 'transl'];

    private $dirMap = [];
    private $groupMap = [];
    private $proflMap = [];
    private $collcMap = [];

    private $log = [];

    private $init = false;

    public function __construct($collectionDirPath)
    {
        if (!is_dir($collectionDirPath)) {
            mkdir($base, 0777, true);
            $this->log['done'][] = 'created dir. '.basename($collectionDirPath);
        }
        $this->collectionDirPath = trim($collectionDirPath, '/\\') . '/';

        foreach (['buildDirMap', 'loadBundlesMap', 'completeGrpConfig', 'buildCollcMap'] as $initMethod) {
            $do = $this->$initMethod();
            if ($do !== true) {
                $this->log['err'] = $do['err'];
                return $this->log;
            }
        }
    }

    private function buildCollcMap()
    {
        $collcMap = [];
        $files = glob($this->dirMap['src/collc'] . '*/*.json');

        foreach ($files as $file) {
            $Id = basename($file, '.json');
            $groupId = $this->extractGroupId($Id);

            if (!array_key_exists($groupId, $this->groupMap)) {
                return ['err' => 'group "' . $groupId . '" is not listed in "' . $this->bundlesConfigFilename . '" config file'];
            }

            $this->groupMap[$groupId]['collcIds'][] = $Id;
            $collcMap[$Id]['path'] = $file;
            $collcMap[$Id]['groupId'] = $groupId;
            $collcMap[$Id]['groupName'] = $this->groupMap[$groupId]['name'];

        }
        $this->collcMap = $collcMap;
        return true;
    }

    private function buildDirMap()
    {
        $dirs = ['dist', 'src', 'src/collc', 'src/transl', 'src/config', 'dist/profiles', 'dist/partials'];
        foreach ($this->dirStructure as $subDir) {
            $dirs[] = 'dist/partials/' . $subDir;
        }

        foreach ($dirs as $dirk) {
            $dirpath = $this->collectionDirPath . $dirk . '/';
            if (!is_dir($dirpath)) {
                mkdir($dirpath);
                $this->log['done'][] = 'created dir. '.$dirk;
                switch($dirk){
                    case 'src/collc':
                        $err = 'Empty src/collc dir; cannot build anything';
                        break;
                        case 'src/config':
                            $this->log['default-file-write'] = $this->createDlftConfig();
                            break;
                                default:  
                }
            }
            $dirMap[$dirk] = $dirpath;
        }
        if (isset($err)) {
            return ['err' => $err];
        }

        $this->dirMap = $dirMap;
        $this->init = true;
        return true;
    }

    private function completeGrpConfig()
    {
        foreach ($this->groupMap as $groupId => $groupInfos) {
            if (!array_key_exists('name', $groupInfos)) {
                return ['err' => 'group "' . $groupId . '" is missing its name property'];
            }
            if (!array_key_exists('priority', $groupInfos)) {
                $this->groupMap[$groupId]['priority'] = 99;
                $this->log['anomalies'][] = $groupId . ' is missing its priority property';
            }
            $this->groupMap[$groupId]['collcIds'] = [];
        }

        return true;
    }

    private function createDlftConfig(){
        $deflt = [
            'profiles' => [
              'mainPrf' => [
                'name' => 'Main Profile',
                'groups' => [
                  'mainGrp',
                ],
                'priority' => 1,
              ],
            ],
            'groups' => [
              'mainGrp' => [
                'name' => 'Main Group',
                'priority' => 1,
              ],
            ],
        ];
        return Jack::File()->saveJson($deflt, $this->getBundlesConfigPath(), true);
    }

    private function getBundlesConfigPath(){
        return $this->collectionDirPath.'src/config/' . $this->bundlesConfigFilename . '.json';
    }

    private function loadBundlesMap()
    {
        $config = Jack::File()->readJson($this->getBundlesConfigPath());

        if (empty($config) || empty($config['groups']) || empty($config['profiles'])) {
            return ['err' => 'groups and profiles config file either not found or invalid'];
        }

        $this->proflMap = $config['profiles'];
        $this->groupMap = $config['groups'];
        return true;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function getMap($mapName)
    {if ($this->init) {
        if (in_array($mapName, ['group', 'collc', 'profl'])) {
            $propname = $mapName . 'Map';
            return $this->$propname;
        }
    }
        return false;
    }

    public function extractGroupId($collcId)
    {
        $split = explode('-', $collcId);
        array_pop($split);
        return implode('-', $split);
    }

    public function IdExists($Id, $mapName)
    {
        $pool = $this->getMap($mapName);
        if ($pool !== false && array_key_exists($Id, $pool)) {
            return true;
        }
        return false;
    }

    public function getInfos($Id, $mapName, $infokey = false)
    {
        $pool = $this->getMap($mapName);
        if ($pool !== false && array_key_exists($Id, $pool)) {
            if (empty($infokey)) {
                return $pool[$Id];
            }
            if (array_key_exists($infokey, $pool[$Id])) {
                return $pool[$Id][$infokey];
            }
        }
        return false;
    }

    public function getDistDirStruct()
    {
        return $this->dirStructure;
    }

    public function getDir($dirKey)
    {
        if ($this->init && \array_key_exists($dirKey, $this->dirMap)) {
            return $this->dirMap[$dirKey];
        }
        return false;
    }

    public function getAllIdsOfMap($mapName)
    {
        if ($this->init) {
            $pool = $this->getMap($mapName);
            if ($pool !== false) {
                return array_keys($this->$pool);
            }
            return false;
        }
    }

}
