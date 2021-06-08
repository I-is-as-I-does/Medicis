# Medicis

A PHP tool dedicated to fastforward JSON collection creation.

- Handles schema, sample data, translations
- Can generate additional custom config files
- Bundles collections in groups
- Run in CLI

## Setup

### Install

```bash
$ composer require ssitu/medicis
```

`ssitu/jacktrades` will be installed too. It's a very small library of utils.

### Ready for CLI

To uses Medicis in CLI, install `ssitu/euclid` too. It should be suggested by Composer; otherwise:

```bash
$ composer require ssitu/euclid
```

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

Directories will be mkdir'd.

### TLDR

Take a look at files in `samples/collections`;  
And to dip toes in CLI:

```bash
$ php samples/bin/medicis
```

## Collections

The whole point of Medicis is to write Json schema (and make use of them), easy and quick.

Collections will be arranged in _groups_.  
Groups are bundles of related collections.  
Of course, a group can also host a single collection.

### Prep

To set up a collection:

- in `src/collc` : create a sub folder, named after your group of collections.
  _example_:  
  `src/collc/people/`

- then inside that folder, create collection json file(s).
  File(s) name(s) must be 'group name' dash 'collection name';
  _example:_
  `people-contacts.json` and `people-activities.json`

Each collection file must specify 2 main properties:

- `required` | _array_ (required properties; will be used for schema validation),
- and `props` | _array_ (schema properties).

`props` items are objects, each containing 2 properties:

- `method` | _string_
- and `argm` | _array_

The shortcut is within `method` and `argm`: they refer to methods in _MedicisModels_ that will do the heavy work.

_Example:_
`people-activities.json`:

```json
{
  "required": ["activity"],
  "props": [
    {
      "method": "String",
      "argm": ["activity", "Art historian"]
    }
  ]
}
```

Related method in _MedicisModel_:

```php
$MedicisModels->String( $id,
                        $example,
                        $minLen = false,
                        $maxLen = false,
                        $pattern = false);
```

_MedicisModel_ interface file will serve as a **cheat sheet** for available methods:
`Medicis/src/MedicisFamily/MedicisModels_i.php`

### Config

You can add a config property to each collection; what it may contain is entirely up to you.
If collections are going to be integrated in some UI, it could be things like auth level, template used, position in menu, etc.

```JSON
"config":
  {
    "priority":2,
    "status": "required",
    "auth": 1,
    "template": "collections"
}
```

Content will be detached from the src file, into its own file in `dist/`. As for the other collection files, a bundle is generated when running `'Groups'` commands in CLI.

You can also create a similar file for group-wide config, in `src/collc/your-group/`, naming it after your group: `your-group.json`. The group bundle file will then include the group config, and wrap collections config in an 'items' property.

### Translation

For each language your are planning translations, create a file in `src/transl` folder, named as follow: `collections-{language}.json`

In CLI, run `'transl'` commands for either collections, or groups: source translation files will automatically be populated with keys that requires translation (and you'll get a log).

Note that `'all'` commands include translation.

Entries are separated between:

- `'name'` for collections and groups names,
- and `'prop'` for properties titles.

_Example_
`collections-en.json`

```JSON
{
  "name":{
    "people": "People",
    "people-activities": "People | Activities",
    "people-contacts": "People | Contact"
    },
  "prop":{
    "name": "Name",
    "addresses": "Addresses",
    "emails": "Emails",
    "websites": "Websites",
    "activity": "Activity"
    }
}
```

### Distill

To generate from there a real schema, dummy data, translation arrays, and optionally some config, in CLI:

- pick `'Collc'`
- then `'people-activities -> all'`

You'll get a log of what worked, failed, have been skipped, etc, and you'll find generated files in `dist/people/` folders:  
`sch`, `exmpl`, `config` and `transl`.

### Merge

In CLI, a `'Group'` command will:

- run action for **all group collections** at once,
- plus generate a bundled file for each category. Destination folder: `dist/your-group/bundle/`.

## MetaMedicis

_MedicisCli_ is fine and dandy for quick, common use operations, but for more targeted operations:

- use _Euclid_ main tool (in which case, please refer to _Euclid_ [doc](https://github.com/I-is-as-I-does/Euclid)),
- or operate from the _MetaMedicis_ class:

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
$MedicisTransl->collcTranslBuild($collcId);

$MedicisSchema = $MetaMedicis->getMedicisMember('Schema');
$MedicisSchema->schBuild($collcId);

$MedicisCollc = $MetaMedicis->getMedicisMember('Collc');
$MedicisCollc->collcBuild($collcId, $translToo = true);
$MedicisCollc->collcConfigBuild($collcId);
$MedicisCollc->dummyDataBuild($collcId);

$MedicisGroup = $MetaMedicis->getMedicisMember('Group');
$MedicisGroup->groupBuild($groupId, $translToo = true);
```

## Contributing

Sure! You can take a loot at [CONTRIBUTING](CONTRIBUTING.md).

## License

This project is under the MIT License; cf. [LICENSE](LICENSE) for details.
