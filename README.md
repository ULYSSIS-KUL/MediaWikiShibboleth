# Installation
Add to your `LocalSettings.php`:

```php
wfLoadExtension('MediaWikiShibboleth');
include 'extensions/MediaWikiShibboleth/MediaWikiShibboleth_body.php';
```

To disable anonymous editing, add:

```php
$wgGroupPermissions['*']['edit'] = false;
$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['createtalk'] = false;
$wgGroupPermissions['*']['createpage'] = false;
$wgGroupPermissions['*']['writeapi'] = false;
```
