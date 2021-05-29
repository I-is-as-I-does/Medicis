
# Medicis

A PHP tool dedicated to fastforward JSON collection creation. 
- Handles schema, sample data, translations
- Generates pages and menu config for UI integratio 
- Bundles collections in groups and profiles
- Run in CLI

## Setup

### Install

```bash
$ composer require ssitu/medicis
```
`ssitu/jacktrades` will be installed too. It's a very small library of utils.

### Ready for CLI

To uses Medicis in CLI, install `ssitu/euclid` too.
Check out `MedicisCli.php` in `src/`, and `bin/medicis` in `samples/`.
```bash
$ php bin/medicis
```
### Quick Start

Take a look at files in `samples/`, and test bin commands here.


## Getting Started

### Init

```php
use SSITU\Medicis\MedicisCli;
# OR:
use SSITU\Medicis\MedicisMap;

#If not already summoned:
require_once  'path_to_composer/autoload.php';

# Pick a path to host your collections files:
$pathToCollc = 'path_to_collections_dir/';

# Then:
$MedicisCli = new MedicisCli($pathToCollc, true);
# OR:
$MedicisMap = new MedicisMap($pathToCollc);
var_dump($MedicisMap->getLog());
```
Directories and a default 'groups and profiles' config file will be created.

### Config

Said 'groups and profiles' config file is located here: 
`path_to_collections_dir/src/config/groups-profiles.json`

At least one group and one profile must be set.
Default:
```JSON
{
  "profiles": {
    "mainPrf": {
      "name": "Main Profile",
      "groups": ["mainGrp"],
      "priority": 1
    }
  },
  "groups": {
    "mainGrp": {
      "name": "Main Group",
      "priority": 1
    }
  }
}
```
- Profiles contain groups
- Groups are bundles of related collections
- Priority is the rank in a navigation menu, for potential use is some UI. 
   It is not required.
   
### Schema



### Translation

For each language your are planning translations, create two files in `src/transl` folder:
- `collections-names-{language}.json` for collections, groups and profiles names;
- `collections-props-{language}.json` for schema properties titles.
In CLI, run `transl` commands for either collections, groups or profiles: source translation files will automatically be populated with keys that requires translation (and you'll get a log).

Note that `all` commands include translation.  

### MetaMedicis

*MedicisCli* is fine and dandy for quick, common use operations, but please note that its `all` commands are cascading.
A profile command will run action for **all its groups**, and each group will run action for **all its collections**.

For more targeted operations:
-  use Euclid main tool (in which case, please refer to Euclid doc), 
- or operate from the *MetaMedicis* class:
```php
$MedicisMap = new MedicisMap($collectionDirPath);
$log = $MedicisMap->getLog();
if (!empty($log['err'])) {
 var_dump($log);
 exit;
}
$MetaMedicis = new MetaMedicis($MedicisMap);
```
A non-exhaustive list of possibilities (cf. interfaces for more):
```php
$MedicisTransl = $MetaMedicis->getMedicisMember('Transl');
$MedicisTransl->collcTranslBuild($SchPathOrId)
$MedicisTransl->bundleTranslCheck($GroupOrProfileId);

$MedicisSchema = $MetaMedicis->getMedicisMember('Schema');
$MedicisSchema->schBuild($collcId);

$MedicisCollc = $MetaMedicis->getMedicisMember('Collc');
$MedicisCollc->collcBuild($collcId, $translToo = true);
$MedicisCollc->pageConfigBuild($collcId, $groupId, $priority);
$MedicisCollc->dummyDataBuild($SchPathOrId);

$MedicisGroup = $MetaMedicis->getMedicisMember('Group');
$MedicisGroup->groupBuild($groupId, $translToo = true);
$MedicisGroup->buildGroupConfig($groupId);

$MedicisProfile = $MetaMedicis->getMedicisMember('Profile');
$MedicisProfile->profileBuild($profileId, $translToo = true);
```

## Contributing

Sure! You can take a loot at [CONTRIBUTING](CONTRIBUTING.md).

## License

This project is under the MIT License; cf. [LICENSE](LICENSE) for details.