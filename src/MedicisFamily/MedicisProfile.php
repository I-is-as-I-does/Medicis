<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

use SSITU\JackTrades\Jack;

class MedicisProfile implements MedicisProfile_i
{
    private $MetaMedicis;
    private $MedicisMap;
    private $MedicisGroup;

    public function __construct($MetaMedicis)
    {
        $this->MetaMedicis = $MetaMedicis;
        $this->MedicisGroup = $MetaMedicis->getMedicisMember('Group');
        $this->MedicisMap = $MetaMedicis->getMedicisMap();

    }
    public function profileBuild($profileId, $translToo = true)
    {

        $proflInfos = $this->MetaMedicis->getBundleInfos($profileId, 'profl');
        if (array_key_exists('err', $proflInfos)) {
            return $proflInfos;
        }
        $rslt = [];
        foreach ($proflInfos['groups'] as $groupId) {
            $rslt['groups'][$groupId] = $this->MedicisGroup->groupBuild($groupId, $translToo);
        }
        $rslt['files'] = $this->buildProfileFiles($profileId, $proflInfos);

        if ($translToo === true) {
            $rslt['transl'][$profileId . '.transl'] = $this->MetaMedicis->getMedicisMember('Transl')->bundleTranslCheck($profileId);
        }
        return $rslt;
    }

    private function buildProfileFiles($profileId, $proflInfos = false)
    {
        if (empty($proflInfos)) {
            $proflInfos = $this->MetaMedicis->getBundleInfos($profileId, 'profl');
            if (array_key_exists('err', $proflInfos)) {
                return $proflInfos;
            }
        }
        $groups = $proflInfos["groups"];
        $pages = [];
        $sections = [];
        $paths = ['data' => [], 'sch' => [], 'transl' => []];
        $fails = [];
        foreach ($groups as $grk => $groupId) {
            $groupData = $this->getGroupData($groupId);
            if (!array_key_exists('err', $groupData)) {
                $pages = array_merge($pages, $groupData["pages"]);
                $sections = array_merge($sections, $groupData["section"]);
                foreach ($paths as $subdir => $subpaths) {
                    $paths[$subdir] = array_merge($subpaths, $groupData["paths"][$subdir]);
                }
            } else {
                $fails[] = 'Fail to get group data for "' . $groupId . '"; log: ' . $groupData['err'];
            }
        }
        if (!empty($fails)) {
            return ['err' => implode(PHP_EOL, $fails)];
        }
        $rslt = [];
        foreach (['sections' => $sections, 'pages' => $pages] as $jobK => $saveData) {
            $filename = $profileId . '-' . $jobK . '-config';
            $rslt[$jobK] = $this->MetaMedicis->saveDistFile($saveData, $profileId, 'config', $filename);
        }
        foreach ($paths as $dirK => $subpaths) {
            foreach ($subpaths as $subpath) {
                $filename = basename($subpath, '.json');
                $dest = $this->MetaMedicis->getProfileDistPath($profileId, $dirK, $filename);
                $rslt[$filename] = Jack::File()->copySrcToDest($subpath, $dest, true);
            }
        }
        return $rslt;
    }

    private function getGroupData($groupId, $tryLog = false)
    {
        $rslt = [];
        $path = $this->MedicisMap->getDir('dist/partials/config') . $groupId . '-config.json';
        $content = Jack::File()->readJson($path);
        if (empty($content) || empty($content["pages"]) || empty($content["section"]) || empty($content["paths"])) {

            if ($tryLog === false) {
                $tryLog = $this->MedicisGroup->groupBuild($groupId);
                return $this->getGroupData($groupId, $tryLog);
            }
            return ['err' => json_encode($tryLog, JSON_PRETTY_PRINT)];
        }
        return $content;
    }

    private function getBasePath($profileId)
    {
        $base = $this->profileDir . $profileId . '/';
        if (!is_dir($base)) {
            mkdir($base);
        }
        return $base;
    }

}
