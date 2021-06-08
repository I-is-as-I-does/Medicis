<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

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
        $errlog = [];

        $bundle = [];
        foreach ($groupCollcs as $collcId => $collcPaths) {

            $collcBuild = $this->MetaMedicis->getMedicisMember('Collc')->collcBuild($collcId, $translToo);
            if (array_key_exists('err', $collcBuild)) {
                $errlog[$collcId]['err'] = $collcBuild['err'];
                $bundle = [];
                break;
            }
            foreach ($collcBuild as $subDir => $buildRslt) {
                if (array_key_exists($subDir . '-bundle', $errlog)) {
                    continue;
                }
                if (!array_key_exists('err', $buildRslt) && !array_key_exists('skipped', $buildRslt) && !array_key_exists('todo', $buildRslt)) {
                    $prc = $this->bundleContent($collcId, $subDir, $collcPaths['collcDistPaths']);
                    if (!array_key_exists('err', $prc)) {
                        $bundle[$subDir] = $prc;
                        continue;
                    } else {
                        $errlog[$subDir . '-bundle']['err'] = $prc['err'];
                    }
                } else {
                    foreach (['err', 'todo', 'skipped'] as $badK) {
                        if (!empty($buildRslt[$badK])) {
                            $errlog[$subDir . '-bundle'][$badK] = $buildRslt[$badK];
                        }
                    }
                }
                $bundle[$subDir] = [];
            }

        }

        if (!empty($bundle)) {
            $jobs = ['config' => $groupInfos['groupSrcConfig'],
                'transl' => $groupId];
            foreach ($jobs as $jobK => $scdargm) {
                if (!empty($bundle[$jobK])) {
                    $method = 'prcBundle' . ucfirst($jobK);
                    $bundle[$jobK] = $this->$method($bundle[$jobK], $scdargm);
                    if (array_key_exists('err', $bundle[$jobK])) {
                        $errlog[$jobK . '-bundle']['err'] = $bundle[$jobK]['err'];
                        unset($bundle[$jobK]);
                    }
                }
            }
            $bundleRslt = $this->createBundleFiles($groupId, $bundle);
        }

        return $this->mergeRslt($bundleRslt, $errlog);
    }

    private function mergeRslt($bundleRslt, $errlog)
    {
        if (!empty($errlog)) {
            foreach ($errlog as $bundleK => $errdata) {
                $errk = array_key_first($errdata);
                $errc = $errdata[$errk];
                if (is_array($errc)) {
                    $errc = implode(PHP_EOL, $errc);
                }
                $bundleRslt[$bundleK][$errk] = $errc;
            }
        }
        return $bundleRslt;
    }
    private function prcBundleTransl($bundleTransl, $groupId)
    {
        $groupNameTransl = $this->MetaMedicis->getMedicisMember('Transl')->groupTranslBuild($groupId);
        if (array_key_exists('err', $groupNameTransl)) {
            return ['err' => implode(PHP_EOL, $groupNameTransl['err'])];
        }
        if (array_key_exists('todo', $groupNameTransl)) {
            return ['err' => implode(PHP_EOL, $groupNameTransl['todo'])];
        }

        foreach ($groupNameTransl['success'] as $lang => $data) {

            $bundleTransl[$lang][$groupId] = $data['name'][$groupId];
        }

        return $bundleTransl;
    }

    private function prcBundleConfig($bundleConfig, $groupSrcConfig)
    {
        if (file_exists($groupSrcConfig)) {
            return $this->groupConfig($bundleConfig, $groupSrcConfig);
        }
        return $bundleConfig;
    }

    private function bundleContent($collcId, $subDir, $collcDistPaths)
    {
        if ($subDir !== 'transl') {
            $content = $this->MetaMedicis->getCollcFile($collcDistPaths[$subDir]);
            if (array_key_exists('err', $content)) {
                return $content;
            }
            return [$collcId => $content];
        }
        $stock = [];
        foreach ($collcDistPaths[$subDir] as $lang => $translPath) {
            $content = $this->MetaMedicis->getCollcFile($translPath);
            if (array_key_exists('err', $content)) {
                return $content;
            }
            $stock[$lang][$collcId] = $content;
        }
        return $stock;
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

    private function createBundleFiles($groupId, $bundle)
    {
        $rslt = [];
        foreach ($bundle as $subDir => $collcData) {
            if (!empty($collcData)) {
                if ($subDir == 'transl') {
                    foreach ($collcData as $lang => $data) {
                        $rslt['transl-bundle-' . $lang] = $this->MetaMedicis->saveDistFile($data, $groupId, $subDir, true, $lang);
                    }
                } else {
                    $rslt[$subDir . '-bundle'] = $this->MetaMedicis->saveDistFile($collcData, $groupId, $subDir, true);
                }
            }
        }
        return $rslt;
    }

}
