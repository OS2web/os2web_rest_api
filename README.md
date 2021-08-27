# OS2Web REST API module

## Module purpose

The module purpose is to provide URL's for fetching data via REST API.

## How does it work

After enabling module there is available URL's that could be used as REST API.

Most of them are coming from [RESTful Web Services module](https://www.drupal.org/docs/8/core/modules/rest/overview) 
that is included in Drupal core.

Module provides preconfigured REST sources and Views for fetching lists nodes
and terms in JSON-format
* /node/[node id]?_format=json - particular node
* /rest/os2web/list/node/[taxonomy term id] - list of nodes filtered by 
[taxonomy term id]
* /rest/os2web/list/term - list of all taxonomy terms
* /rest/os2web/list/term/[vocabulary machine name] - list of taxonomy terms by vocabulary machine name

For advanced filtering you can use multiple arguments. For example:
```
/rest/os2web/list/node/1+2+3
```
for `AND` condition
```
/rest/os2web/list/node/1,2,3
```
for `OR` condition

**NOTE**: It's not possible to use `AND` condition on filtering taxonomy terms.

## Install
Module is available to download via composer.
```
composer require os2web/os2web_rest_api
drush en os2web_rest_api
```

## Update
Updating process for OS2Web REST API module is similar to usual composer package.
Use Composer's built-in command for listing packages that have updates available:

```
composer outdated os2web/os2web_rest_api
```

## Contribution

Project is opened for new features and os course bugfixes.
If you have any suggestion or you found a bug in project, you are very welcome
to create an issue in github repository issue tracker.
For issue description there is expected that you will provide clear and
sufficient information about your feature request or bug report.

### Code review policy
See [OS2Web code review policy](https://github.com/OS2Web/docs#code-review)

### Git name convention
See [OS2Web git name convention](https://github.com/OS2Web/docs#git-guideline)
