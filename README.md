Report Error CiviCRM Extension
==============================

Sometimes CiviCRM can be real tough to debug. Especially when you are getting
fatal errors, but only by some users, and you can't recreate the problems.
This utility will send you a detailed email when a CiviCRM fatal error occurs.

* when the error was encountered
* which CiviCRM page threw the error 
* which logged-in user encountered the error
* full request parameters ("get")
* optionally the "post" data

The extension can also offer to try to resolve some common errors:

* For contribution pages where the session has expired or the contribution
  page URL is truncated, you can choose to gracefully redirect CiviCRM
  errors to the site CMS default page, a specific contribution page or not
  at all. You also have the option of not getting emails on contribution
  page redirects (since crawlers can make them rather frequent).

* Detect bots and optionally generate a 404 http response instead of 'OK'.

To get the latest version of this module:  
https://github.com/mlutfy/ca.bidon.reporterror

Distributed under the terms of the GNU Affero General public license (AGPL).
See LICENSE.txt for details.

Installation
------------

* Enable this extension in CiviCRM (Administer > System Settings > Manage Extensions)
* A new menu item will be added in Administer > System Settings > Report Errors,
  which you can use to access the extensions settings form.

Requirements
------------

- CiviCRM >= 4.2
- Tested with CiviCRM 4.4 to 4.6.

Contributors
------------

* CiviCRM extension/integration written & maintained by Mathieu Lutfy (Coop SymbioTIC),
  co-authored by Lola S (Freeform), Nicolas Ganivet (CiviDesk) and Young-Jin Kim (Emphanos).
* Based on the civicrm_error Drupal module initially written by Dave Hansen-Lange (dalin):  
  https://drupal.org/project/civicrm_error

Support
-------

Please post bug reports in the issue tracker of this project on github:  
https://github.com/mlutfy/ca.bidon.reporterror/issues

For general support questions, please use the CiviCRM Extensions forum:  
http://forum.civicrm.org/index.php/board,57.0.html

This is a community contributed extension written thanks to the financial
support of organisations using it, as well as the very helpful and collaborative
CiviCRM community.

If you appreciate this module, please consider donating 10$ to the CiviCRM project:  
http://civicrm.org/participate/support-civicrm

While I do my best to provide volunteer support for this extension, please
consider financially contributing to support and further develop this extension.

Commercial support is available through Coop SymbioTIC:  
https://www.symbiotic.coop

Copyright
---------

License: AGPL 3

Copyright (C) 2012-2015 CiviCRM LLC (info@civicrm.org)  
http://www.civicrm.org

Copyright (C) 2012-2015 Mathieu Lutfy (mathieu@symbiotic.coop)  
https://www.symbiotic.coop
