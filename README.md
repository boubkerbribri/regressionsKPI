# Regressions KPI

##How to use

Put your token in a file named `token.txt` at the root of the project.

##Mandatory environment variables

| Parameter   | Description      |
|-------------|----------------- |
| VERSION     | Version of PrestaShop to use (patch version or .x version) |
| FREEZE_DATE | Arbitrary freeze date (`YYYY-MM-DD`) |
| RELEASE_DATE | Arbitrary release date (`YYYY-MM-DD`) |

##Examples of use

To get all the information about a patch release:
```shell script
VERSION=1.7.6.2 php getVersionData.php
```
