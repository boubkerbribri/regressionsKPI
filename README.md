# Regressions KPI

## How to use

Put your token in a file named `token.txt` at the root of the project.

## Mandatory environment variables

| Parameter   | Description      |
|-------------|----------------- |
| VERSION     | Version of PrestaShop to use (patch version or .0 version) |
| FREEZE_DATE | Arbitrary freeze date (`YYYY-MM-DD`) |
| RELEASE_DATE | Arbitrary release date (`YYYY-MM-DD`) |

## Examples of use

To get all the information about a patch release:
```shell script
VERSION=1.7.6.2 php getPatchVersionData.php
```

to get all the information about a minor release:
```shell script
VERSION=1.7.6.0 FREEZE_DATE=2019-04-08 RELEASE_DATE=2019-06-01 php getMinorVersionData.php
```
Make sure to use a version number ending in `0` (`1.7.6.0`, `1.7.7.0`, and so on).
