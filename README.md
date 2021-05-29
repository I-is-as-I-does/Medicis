
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
   
### Collection

The whole point of Medicis is to write Json schema (and make use of them), easy and quick.
To create a collection:
- in `src/collc` folder: create a sub folder, named after your group of collections, and add that group in `groups-profiles.json` config file;
 *example*:   
 `src/collc/pplces/`
 and in `groups-profiles.json`:
   ```json
   "groups": {
      "c-pplcs": { "name": "People and Places", "priority": 1 }
  }
  ```

- then inside that folder, create collection json file(s); file name(s) must be 'group name' dash 'collection name';
 *example:*
 `c-pplcs-directory.json` and `c-pplcs-activities.json`


Each collection file must specify 4 main properties: 
  - `name` | *string* (default collection name in absence of translation), 
  - `priority` | *int* (aka weight in potential UI menu; relative to group), 
  - `required`  | *array* (required properties; will be used for schema validation),  
  - and `props`  | *array* (schema properties).

`props` items are objects, each containing 2 properties:
-  `method`  | *string*
- and `param`  | *array*

The shortcut is within `method` and `param`: they refer to methods in *MedicisModels* that will do the heavy work.

*Example:*
`c-pplcs-activities.json`:
```json
{
  "name": "Activities",
  "priority": 2,
  "required": ["activity"],
  "props": [
    {
      "method": "String",
      "param": ["activity", "Art historian"]
    }
  ]
}
```
Related ethod in *MedicisModel*:
```php
$MedicisModels->String($id, $example, $title = false, $minLen = false, $maxLen = false, $pattern = false);
```
*MedicisModel* interface file will serve as a **cheat sheet** for available methods:
`Medicis/src/MedicisFamily/MedicisModels_i.php`

To generate from there a real schema + dummy data + page config (for UI) + transl, in CLI: pick `'Collections'` then `'c-pplcs-activities -> all'`.
You'll find generated files in `dist/` folder.


### Translation

For each language your are planning translations, create two files in `src/transl` folder:
- `collections-names-{language}.json` for collections, groups and profiles names;
- `collections-props-{language}.json` for schema properties titles.
In CLI, run `'transl'` commands for either collections, groups or profiles: source translation files will automatically be populated with keys that requires translation (and you'll get a log).

Note that `'all'` commands include translation.  

### MetaMedicis

*MedicisCli* is fine and dandy for quick, common use operations, but please note that its `'all'` commands are cascading.
A profile command will run action for **all its groups**, and each group will run action for **all its collections**.

For more targeted operations:
-  use *Euclid* main tool (in which case, please refer to *Euclid* [doc](https://github.com/I-is-as-I-does/Euclid)), 
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