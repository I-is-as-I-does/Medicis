<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis;

class MetaMedicis implements MetaMedicis_i
{
    private $MedicisMap;

    private $MedicisSchema;
    private $MedicisGroup;
    private $MedicisTransl;
    private $MedicisCollc;
    private $MedicisModels;

    public function __construct($MedicisMap)
    {
        $this->MedicisMap = $MedicisMap;

    }

    public function getMedicisMap()
    {
        return $this->MedicisMap;
    }

    public function getMedicisMember($member)
    {
        if ($member === 'Map') { //@doc in case of mistake
            return $this->MedicisMap;
        }
        $classProp = 'Medicis' . $member;
        if (\property_exists($this, $classProp)) {
            if (empty($this->$classProp)) {
                $className = 'SSITU\Medicis\MedicisFamily\\' . $classProp;
                $this->$classProp = new $className($this);
            }
            return $this->$classProp;
        }
        return false;
    }

    public function quickCheckSchema($collcId, $sch = [])
    {
        if (empty($sch)) {
            $sch = $this->getCollcFile($collcId, 'sch');
        }
        if (!array_key_exists('properties', $sch)) {
            $sch = ['err' => 'Unvalid schema; collc Id: "' . $collcId . '"'];
        }
        return $sch;
    }

    public function quickCheckSrc($collcId, $src = [])
    {
        if (empty($src)) {
            $src = $this->getCollcFile($collcId, 'src');
        }
        if (!array_key_exists('props', $src)) {
            $sch = ['err' => 'Unvalid source; collc Id: "' . $collcId . '"'];
        }
        return $src;
    }

    public function getCollcFile($PathOrId, $dirKey = '', $lang = false)
    {
        if (substr($PathOrId, -5) == '.json') {
            $path = $PathOrId;
        } elseif ($dirKey == 'src') {
            $path = $this->MedicisMap->getCollcSrcPath($PathOrId);
        } elseif (!empty($dirKey)) {
            $path = $this->MedicisMap->getDistPath($PathOrId, $dirKey, false, $lang);
        }
        if (!empty($path) && is_file($path)) {
            $content = Jack\File::readJson($path);
            if (!empty($content)) {
                return $content;
            }
        }
        return ['err' => 'Unvalid content, path or id: ' . $PathOrId];
    }

    public function saveDistFile($content, $Id, $subDir, $group = false, $lang = false)
    {
        $path = $this->MedicisMap->getDistPath($Id, $subDir, $group, $lang);
        if ($path === false) {
            return ['err' => 'Unvalid Id "' . $Id . '" or sub directory "' . $subDir . '"'];
        }
        return Jack\File::saveJson($content, $path, true);
    }

}
