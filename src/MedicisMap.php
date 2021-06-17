<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis;

use SSITU\Jack\Jack;


class MedicisMap implements MedicisMap_i
{

    private $abslURIBase;
    private $collectionDirPath;

    private $dirStructure = ['exmpl', 'sch', 'transl'];

    private $dirMap = [];
    private $collcIndex = [];
    private $collcMap = [];
    private $translMap = [];
    private $langs = [];

    private $log = [];

    private $init = false;

    public function __construct($collectionDirPath,$abslURIBase = null)
    {
        if(empty($abslURIBase)){
            $abslURIBase = Jack::Web()->getProtocol().'://'.$_SERVER['HTTP_HOST'].'/sch';
        }
        $this->abslURIBase = Jack::File()->reqTrailingSlash($abslURIBase);
        if (!is_dir($collectionDirPath)) {
            mkdir($base, 0777, true);
            $this->log['done'][] = 'created dir. ' . basename($collectionDirPath);
        }
        $this->collectionDirPath = trim($collectionDirPath, '/\\') . '/';

        foreach (['buildDirMap', 'buildTranslMap', 'buildCollcMap'] as $initMethod) {
            $do = $this->$initMethod();

            if ($do !== true) {
                $this->log['err'] = $do['err'];
                return $this->log;
            }
        }
    }

    public function getSchAbslId($collcId)
    {
       return $this->abslURIBase.$collcId.'.json';
    }

    private function buildCollcMap()
    {
        $this->collcMap = [];
        $this->collcIndex = [];
        $groupPaths = glob($this->dirMap['src/collc'] . '*/', GLOB_ONLYDIR);
        foreach ($groupPaths as $groupPath) {
            $groupId = basename($groupPath);
            $srcPaths = glob($groupPath . '*.json');
            $distDirPaths = $this->groupDistPaths($groupId);
            $this->collcMap[$groupId]['distDirPaths'] = $distDirPaths;
            $this->collcMap[$groupId]['groupDistPaths'] = $this->bundleDistPaths($groupId);

            foreach ($srcPaths as $srcPath) {
                $collcId = basename($srcPath, '.json');
                $this->collcIndex[$collcId] = $groupId;

                $this->collcMap[$groupId]['collcs'][$collcId]['srcPath'] = $srcPath;
                $this->collcMap[$groupId]['collcs'][$collcId]['collcDistPaths'] = $this->collcDistPaths($distDirPaths, $collcId);
            }
        }
        return true;
    }

    private function buildTranslMap()
    {
        $files = glob($this->dirMap['src/transl'] . 'collections-*.json');
        if (empty($files)) {
            $this->log["anomaly"][] = "No translation files found";
            return true;
        }
        foreach ($files as $path) {
            $lang = $this->extractLang($path);
            $this->translMap[$lang] = $path;
        }
        $this->langs = array_keys($this->translMap);
        return true;
    }

    private function extractLang($path)
    {
        $parts = explode('-', basename($path, '.json'));
        return array_pop($parts);
    }

    private function collcDistPaths($distDirPaths, $collcId)
    {
        $collcDistPaths = [];
        foreach ($distDirPaths as $subDir => $distPath) {
            $basePath = $distPath . $collcId . '-' . $subDir;
            $collcDistPaths[$subDir] = $this->buildDistPath($basePath, $subDir)[$subDir];
        }
        return $collcDistPaths;
    }

    private function bundleDistPaths($groupId)
    {
        $bundlePaths = [];
        $bundleDirPath = $this->dirMap['dist'] . $groupId . '/bundle';
        if (!is_dir($bundleDirPath)) {
            mkdir($bundleDirPath, 0777, true);
            $this->log['done'][] = 'created dir. dist/' . $groupId . '/bundle';
        }
        foreach ($this->dirStructure as $subDir) {
            $basePath = $bundleDirPath . '/' . $groupId . '-' . $subDir;
            $bundlePaths[$subDir] = $this->buildDistPath($basePath, $subDir)[$subDir];
        }
        return $bundlePaths;
    }

    private function buildDistPath($basePath, $subDir)
    {
        $stock = [];
        if ($subDir == 'transl') {
            foreach ($this->langs as $lang) {
                $stock[$subDir][$lang] = $basePath . '-' . $lang . '.json';
            }
        } else {
            $stock[$subDir] = $basePath . '.json';
        }
        return $stock;
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

    public function getTranslMap()
    {
        if ($this->init) {
            return $this->translMap;
        }
        return false;
    }

    public function getAvailableLangs()
    {
        if ($this->init) {
            return $this->langs;
        }
        return false;
    }

    public function getCollcIndex()
    {
        if ($this->init) {
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

    public function getDistPath($Id, $subDir, $group = false, $lang = false)
    {
        $hook = 'collc';
        if ($group === true) {
            $hook = 'group';
        }
        $method = 'get' . ucfirst($hook) . 'Infos';
        $infosK = $hook . 'DistPaths';
        $infos = $this->$method($Id);
        if ($infos !== false && in_array($subDir, $this->dirStructure)) {
            if ($subDir !== 'transl') {
                return $infos[$infosK][$subDir];
            }
            if (!empty($lang) && in_array($lang, $this->langs)) {
                return $infos[$infosK][$subDir][$lang];
            }
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
