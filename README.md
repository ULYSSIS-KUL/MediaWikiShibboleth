# Prerequisites

Before installing, you need to have SSL and KU Leuven Shibboleth enabled on your domain.
Once you know everything is installed properly, you can proceed to install the extension.

# Installation 

First, download the latest release from https://github.com/ULYSSIS-KUL/MediaWikiShibboleth/releases/latest. Make sure to click the `MediaWikiShibboleth.zip` download button. Then, unzip the zip file in your `<mediawiki installation folder>/extensions/` directory. Finally, add the following lines to your `<mediawiki installation folder>/LocalSettings.php`

```php
wfLoadExtension('MediaWikiShibboleth');
include 'extensions/MediaWikiShibboleth/MediaWikiShibboleth_body.php';

$wgGroupPermissions['*']['edit'] = false;
$wgGroupPermissions['*']['createtalk'] = false;
$wgGroupPermissions['*']['createpage'] = false;
$wgGroupPermissions['*']['writeapi'] = false;
```

If you want to allow anonymous editing, you should *not* add the last 4 lines of the previous paragraph. Though this really defeats the purpose of the extension. 
