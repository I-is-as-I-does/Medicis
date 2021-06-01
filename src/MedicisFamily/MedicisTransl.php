<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

use SSITU\Jack\Jack;

class MedicisTransl implements MedicisTransl_i
{

    private $MetaMedicis;
    private $mainTranslPath;
    private $propsKey = 'prop';
    private $namesKey = 'name';

    public function __construct($MetaMedicis)
    {
        $this->MetaMedicis = $MetaMedicis;
        $this->mainTranslPath = $MetaMedicis->getMedicisMap()->getDir('src/transl');
    }

    public function collcTranslBuild($collcId, $sch = [])
    {
        $sch = $this->MetaMedicis->quickCheckSchema($collcId, $sch = []);
        if (array_key_exists('err', $sch)) {
            return $sch;
        }
        $propIds = array_keys($sch['properties']);
        $rslt = [];
        $rslt['properties'] = $this->prcDoneAndTodo($propIds, $this->propsKey, $collcId);
        $rslt['name'] = $this->prcDoneAndTodo([$collcId], $this->namesKey, $collcId);
        return $rslt;
    }

    public function groupTranslCheck($GroupId)
    {
        return $this->prcDoneAndTodo([$GroupId], $this->namesKey);
    }

    private function prcDoneAndTodo($itemIds, $trslKey, $saveId = false)
    {

        $files = glob($this->mainTranslPath . 'collections-*.json');
        if (empty($files)) {
            return ["anomaly" => "No translation files found"];
        }
        $rslt = [];
        foreach ($files as $path) {
            $content = Jack::File()->readJson($path);
            $lang = $this->extractLang($path);
            $rslt[$lang] = [];
            $saveStock = [];
            $saveFile = false;
            if(!array_key_exists($trslKey,$content)){
                $content[$trslKey] = [];
                $saveFile = true;
            }
            foreach ($itemIds as $itemId) {
                if (empty($content[$trslKey]) || empty($content[$trslKey][$itemId])) {
                    $rslt[$lang]['todo'][] = $itemId; 
                    if (!array_key_exists($itemId, $content[$trslKey])) {
                        $content[$trslKey][$itemId] = '';
                        $saveFile = true;
                    }
                } elseif ($saveId !== false) {
                    $saveStock[$trslKey][$itemId] = $content[$trslKey][$itemId];
                }
            }
            if (!empty($rslt[$lang]['todo'])) {
                $rslt[$lang]['todo'] = implode("; ", $rslt[$lang]['todo']);
                if ($saveFile) {
                    ksort($content);
                    $rslt[$lang]['file-update'] = Jack::File()->saveJson($content, $path, true);
                }
            } else {
                $rslt[$lang]['success'] = "all done";
                if ($saveId !== false) {
                    ksort($saveStock);
                    $rslt[$lang]['save-transl'] = $this->MetaMedicis->saveDistFile($saveStock, $saveId, 'transl');
                }
            }
        }
        return $rslt;
    }

    private function extractLang($path)
    {
        $parts = explode('-', basename($path, '.json'));
        return array_pop($parts);
    }

}
