<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis;

use SSITU\Euclid\EuclidCompanion;

class MedicisCli
{
    private $Companion;
    private $MedicisMap;
    private $MetaMedicis;

    private $currentMenu;

    private $callableMap;
    private $backToMain = [',' => 'Main Menu' . PHP_EOL];
    private $mapNames = [1 => 'collc', 2 => 'group', 3 => 'profl'];
    private $mainMenu = [1 => 'Collections', 2 => "Groups", 3 => "Profiles"];
    private $proflOpts = ["all", 'transl'];
    private $groupOpts = ["all", 'transl'];
    private $collcOpts = ['all', 'transl'];
    private $divider = ' -> ';

    public function __construct($collectionDirPath, $runBuild = false)
    {
        $this->Companion = EuclidCompanion::inst();
        $this->MedicisMap = new MedicisMap($collectionDirPath);
        $log = $this->MedicisMap->getLog();
        if (!empty($log['err'])) {
            $this->Companion::output($log);
            exit;
        }
        if (!empty($log['anomalies'])) {
            $anomlMsg = 'MedicisMap recorded some non-critic anomalies: ' . PHP_EOL . implode(PHP_EOL, $this->MedicisMap['anomalies']);
            $this->Companion::msg($anomlMsg, 'yellow');
        }

        $this->MetaMedicis = new MetaMedicis($this->MedicisMap);
        $this->callableMap = $this->mainMenu;

        if ($runBuild) {
            return $this->run();
        }
    }

    private function setCallableMap($idK)
    {
        $mapName = $this->mapNames[$idK];
        $optprop = $mapName . 'Opts';
        $opts = $this->$optprop;
        $callableMap = $this->backToMain;
        $items = $this->MedicisMap->getMap($mapName);
        $sk = 0;
        foreach ($items as $Id => $Infos) {
            $sk++;
            foreach ($opts as $k => $opt) {
                $callableMap[$sk . ($k + 1)] = $Id . $this->divider . $opt;
            }
        }
        $this->callableMap = $callableMap;
        $this->currentMenu = $idK;

    }

    public function run()
    {
        $this->Companion->set_callableMap($this->callableMap);
        $this->Companion::echoDfltNav();
        $requestk = $this->Companion->printCallableAndListen();

        $this->handleCmd($requestk);
    }

    protected function handleCmd($requestk)
    {
        if (strlen('' . $requestk . '') == 1) {
            if (array_key_exists($requestk, $this->mapNames)) {
                $this->setCallableMap($requestk);
            } else {
                $this->callableMap = $this->mainMenu;
            }
            return $this->run();
        }
        $request = $this->callableMap[$requestk];
        $split = explode($this->divider, $request);
        $Id = $split[0];
        $opt = $split[1];
        if ($opt === 'transl') {
            if ($this->currentMenu === 1) {
                $build = $this->MetaMedicis->getMedicisMember('Transl')->collcTranslBuild($Id);
            } else {
                $build = $this->MetaMedicis->getMedicisMember('Transl')->bundleTranslCheck($Id);
            }
        } else {
            switch ($this->currentMenu) {
                case '1':
                    $build = $this->MetaMedicis->getMedicisMember('Collc')->collcBuild($Id, true);
                    break;
                case '2':
                    $build = $this->MetaMedicis->getMedicisMember('Group')->groupBuild($Id, true);
                    break;
                case '3':
                    $build = $this->MetaMedicis->getMedicisMember('Profile')->profileBuild($Id, true);
                    break;
            }
        }
        $requestk = $this->Companion->printRslt($build);
        $this->handleCmd($requestk);
    }
}
