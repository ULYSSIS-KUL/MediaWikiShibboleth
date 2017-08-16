# Prerequisites

Before installing, you need to have SSL and KU Leuven Shibboleth enabled on your domain.
For instructions on how to get SSL: https://docs.ulyssis.org/Getting_SSL
Requesting Shibboleth: https://docs.ulyssis.org/Shibboleth
Once you know everything is installed properly, you can proceed to install the extension.

# Installation 

First unzip the zip file in your <mediawiki root>/extensions/ directory.
Then, add the following lines to your `<mediawiki root>/LocalSettings.php`:

```php
wfLoadExtension('MediaWikiShibboleth');
include 'extensions/MediaWikiShibboleth/MediaWikiShibboleth_body.php';

$wgGroupPermissions['*']['edit'] = false;
$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['createtalk'] = false;
$wgGroupPermissions['*']['createpage'] = false;
$wgGroupPermissions['*']['writeapi'] = false;
```

If you want to allow anonymous page editing and creation (you probably don't want this), do NOT add the last 5 lines of the previous paragraph. 
