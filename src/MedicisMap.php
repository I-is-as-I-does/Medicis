<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis;

class MedicisMap implements MedicisMap_i
{

    private $collectionDirPath;

    private $dirStructure = ['config', 'data', 'sch', 'transl'];

    private $dirMap = [];
    private $collcIndex = [];
    private $collcMap = [];

    private $log = [];

    private $init = false;

    public function __construct($collectionDirPath)
    {
        if (!is_dir($collectionDirPath)) {
            mkdir($base, 0777, true);
            $this->log['done'][] = 'created dir. ' . basename($collectionDirPath);
        }
        $this->collectionDirPath = trim($collectionDirPath, '/\\') . '/';

        foreach (['buildDirMap', 'buildCollcMap'] as $initMethod) {
            $do = $this->$initMethod();

            if ($do !== true) {
                $this->log['err'] = $do['err'];
                return $this->log;
            }
        }
    }

    private function buildCollcMap()
    {
        $this->collcMap = [];
        $this->collcIndex = [];
        $groupPaths = glob($this->dirMap['src/collc'] . '*/', GLOB_ONLYDIR);
        foreach ($groupPaths as $groupPath) {
            $groupId = basename($groupPath);
            $srcPaths = glob($groupPath . $groupId.'-*.json');
            $distDirPaths = $this->groupDistPaths($groupId);
            $this->collcMap[$groupId]['groupSrcConfig'] = $groupPath.$groupId.'.json';
            $this->collcMap[$groupId]['distDirPaths'] = $distDirPaths;

            foreach ($srcPaths as $srcPath) {
                $collcId = basename($srcPath, '.json');
                $this->collcIndex[$collcId] = $groupId;

                $this->collcMap[$groupId]['collcs'][$collcId]['srcPath'] = $srcPath;
                $this->collcMap[$groupId]['collcs'][$collcId]['distPaths'] = $this->collcDistPaths($distDirPaths, $collcId);
            }
        }
        return true;
    }

    private function collcDistPaths($distDirPaths, $collcId)
    {
        $collcDistPaths = [];
        foreach ($distDirPaths as $subDir => $distPath) {
            $collcDistPaths[$subDir] = $distPath . $collcId . '-' . $subDir . '.json';
        }
        return $collcDistPaths;
    }

    private function groupDistPaths($groupId)
    {
        $distDirPaths = [];
        $baseDistPath = $this->dirMap['dist'] . $groupId . '/';
        foreach ($this->dirStructure as $subDir) {
            $subDirPath = $baseDistPath . $subDir . '/';
            $distDirPaths[$subDir] = $subDirPath;
            if (!is_dir($subDirPath)) {
                mkdir($subDirPath, 0777, true);
                $this->log['done'][] = 'created dir. dist/' . $groupId . '/' . $subDir;
            }
        }
        return $distDirPaths;
    }

    private function buildDirMap()
    {
        $dirs = ['dist', 'src', 'src/collc', 'src/transl'];
        foreach ($dirs as $dirk) {
            $dirpath = $this->collectionDirPath . $dirk . '/';
            if (!is_dir($dirpath)) {
                mkdir($dirpath);
                $this->log['done'][] = 'created dir. ' . $dirk;
                if ($dirk == 'src/collc') {
                    $err = 'Empty src/collc dir; cannot build anything';
                }
            }
            $this->dirMap[$dirk] = $dirpath;
        }
        if (isset($err)) {
            return ['err' => $err];
        }
        $this->init = true;
        return true;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function getCollcMap()
    {if ($this->init) {
        return $this->collcMap;
    }
        return false;
    }

    public function getCollcIndex()
    {if ($this->init) {
        return $this->collcIndex;
    }
        return false;
    }

    public function getDirMap()
    {
        if ($this->init) {
            return $this->dirMap;
        }
        return false;
    }

    public function getDir($dirKey)
    {
        if ($this->init && \array_key_exists($dirKey, $this->dirMap)) {
            return $this->dirMap[$dirKey];
        }
        return false;
    }

    public function collcExists($collcId)
    {
        if ($this->init && \array_key_exists($collcId, $this->collcIndex)) {
            return true;
        }
        return false;
    }

    public function groupExists($groupId)
    {
        if ($this->init && \array_key_exists($groupId, $this->collcMap)) {
            return true;
        }
        return false;
    }

    public function getGroupInfos($groupId)
    {
        if ($this->groupExists($groupId)) {
            return $this->collcMap[$groupId];
        }
        return false;
    }

    public function getGroupId($collcId)
    {
        if ($this->collcExists($collcId)) {
            return $this->collcIndex[$collcId];
        }
        return false;
    }

    public function getCollcInfos($collcId)
    {
        $groupId = $this->getGroupId($collcId);
        if ($groupId !== false) {
            return $this->collcMap[$groupId]['collcs'][$collcId];
        }
        return false;
    }

    public function getCollcSrcPath($collcId)
    {
        $infos = $this->getCollcInfos($collcId);
        if ($infos !== false) {
            return $infos['srcPath'];
        }
        return false;
    }

    public function getCollcDistPath($collcId, $subDir)
    {
        $infos = $this->getCollcInfos($collcId);
        if ($infos !== false && in_array($subDir, $this->dirStructure)) {
            return $infos['distPaths'][$subDir];
        }
        return false;
    }

    public function getDistDirStruct()
    {
        return $this->dirStructure;
    }

    public function getAllGroupIds()
    {
        if ($this->init) {
            return array_keys($this->collcMap);
        }
        return false;
    }

    public function getAllCollcIds()
    {
        if ($this->init) {
            return array_keys($this->collcIndex);
        }
        return false;
    }

}
