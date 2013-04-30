Report Error CiviCRM Extension
==============================

This extension is CMS independent and when a CiviCRM error is
encountered sends an email to a designated person, which could be an administrator, implementer, or support person. The outgoing email contains the following items related to the error encountered:

* when the error was encountered
* which CiviCRM page threw the error 
* which logged in user encountered the error

For contribution pages where the session has expired or the contribution
page URL is truncated, you can choose to gracefully redirect CiviCRM
errors to the site CMS default page, a specific contribution page or not
at all. You also have the option of not getting emails on contribution
page redirects.
