<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis;

use SSITU\JackTrades\Jack;

class MetaMedicis implements MetaMedicis_i
{
    private $MedicisMap;

    private $MedicisSchema;
    private $MedicisGroup;
    private $MedicisTransl;
    private $MedicisCollc;
    private $MedicisProfile;
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

    public function getBundleInfos($GroupOrProflId, $mapName)
    {
        $infos = $this->MedicisMap->getInfos($GroupOrProflId, $mapName);
        if ($infos === false || empty($infos['name'])) {
            return ['err' => 'unvalid ' . $mapName . ' Id: ' . $GroupOrProflId];
        }
        return $infos;
    }

    public function getSchema($SchPathOrId)
    {
        if (is_array($SchPathOrId) && array_key_exists('$id', $SchPathOrId)) {
            return $SchPathOrId;
        }
        if (is_string($SchPathOrId)) {
            $path = $SchPathOrId;
            if (!is_file($SchPathOrId)) {
                $path = $this->distDirs['sch'] . $SchPathOrId . '.json';
            }
            $sch = Jack::File()->readJson($path);
        }

        if (empty($sch)) {
            return ['err' => 'Unvalid schema or path: ' . var_export($SchPathOrId, true)];
        }
        return $sch;
    }

    public function getPartialsDistPath($Id, $suffx)
    {
        return $this->MedicisMap->getDir('dist/partials/' . $suffx) . $Id . '-' . $suffx . '.json';
    }

    public function saveDistFile($content, $Id, $suffx, $proflFilename = false)
    {
        if ($proflFilename === false) {
            $path = $this->getPartialsDistPath($Id, $suffx);
        } else {
            $path = $this->getProfileDistPath($Id, $suffx, $proflFilename);
        }
        return Jack::File()->saveJson($content, $path, true);
    }

    public function getProfileDistPath($Id, $suffx, $proflFilename)
    {
        $base = $this->MedicisMap->getDir('dist/profiles') . $Id . '/' . $suffx . '/';
        if (!is_dir($base)) {
            mkdir($base, 0777, true);
        }
        return $base . $proflFilename . '.json';
    }

}
