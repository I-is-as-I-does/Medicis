<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

use SSITU\JackTrades\Jack;

class MedicisTransl implements MedicisTransl_i
{

    private $MetaMedicis;
    private $mainTranslPath;
    private $trslprfx = 'collc.'; //@doc: prefix of keys in transl files, to avoid conflicts when possibly merging different transl sources (for offline caching for ex)
    private $propsKey = 'prop';
    private $namesKey = 'name';

    private $logRslt = [];

    public function __construct($MetaMedicis)
    {
        $this->MetaMedicis = $MetaMedicis;
        $this->mainTranslPath = $MetaMedicis->getMedicisMap()->getDir('src/transl');
    }

    public function collcTranslBuild($SchPathOrId)
    {
        $sch = $this->MetaMedicis->getSchema($SchPathOrId);
        if (array_key_exists('err', $sch)) {
            return $sch;
        }
        $collcId = substr($sch['$id'], 0, -5);
        $propIds = array_keys($sch['properties']);
        $rslt = [];
        $rslt['properties'] = $this->prcDoneAndTodo($propIds, $this->propsKey, $collcId);
        $rslt['name'] = $this->prcDoneAndTodo([$collcId], $this->namesKey, $collcId);
        return $rslt;
    }

    public function bundleTranslCheck($GroupOrProfileId)
    {
        return $this->prcDoneAndTodo([$GroupOrProfileId], $this->namesKey);
    }

    private function fillDoneAndTodo($content, $trkey, $path)
    {
        $rslt = [];
        if (empty($content) || empty($content[$trkey])) {
            $rslt['rslt'] = '[todo]'; //@doc: [] to avoid case of 'todo' translation
            if (!array_key_exists($trkey, $content)) {
                $content[$trkey] = '';
                $rslt['file-update'] = Jack::File()->saveJson($content, $path, true);
            }
        } else {
            $rslt['rslt'] = $content[$trkey];
        }
        return $rslt;
    }

    private function prcDoneAndTodo($itemIds, $fileNameKey, $saveId = false)
    {
       
        $files = glob($this->mainTranslPath . 'collections-' . $fileNameKey . 's-*.json');
        if(empty($files)){
            return ["anomaly"=> "No translation files found"];
        }
        $rslt = [];
        foreach ($files as $path) {
            $content = Jack::File()->readJson($path);
            $lang = $this->extractLang($path);
            $rslt[$lang] = [];
            $saveStock = [];
            foreach ($itemIds as $itemId) {
                $trkey = $this->trslprfx . $fileNameKey . '.' . $itemId;
                $subPrc = $this->fillDoneAndTodo($content, $trkey, $path);
                if ($subPrc['rslt'] === '[todo]') {
                    $rslt[$lang]['todo'][] = $itemId;
                    if (!empty($subPrc['file-update'])) {
                        $rslt[$lang]['file-update'] = $subPrc['file-update'];
                    }
                } elseif ($saveId !== false) {
                    $saveStock[$trkey] = $subPrc['rslt'];
                }
            }
            if (!empty($rslt[$lang]['todo'])) {
                $rslt[$lang]['todo'] = implode("; ", $rslt[$lang]['todo']);
            } else {
                $rslt[$lang]['success'] = "all done";
                if ($saveId !== false) {
                    $rslt[$lang]['save-transl'] = $this->MetaMedicis->saveDistFile($saveStock, $saveId, 'transl');
                }
            }
        }
        return $rslt;
    }

    private function extractLang($file)
    {
        $parts = explode('-', trim($file, '.json'));
        return array_pop($parts);
    }

}
