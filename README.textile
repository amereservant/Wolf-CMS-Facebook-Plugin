h1. Wolf CMS Facebook Plugin

This is a *"Wolf CMS":wolf_url Plugin* that is based off of the "Facebook PHP SDK":Facebook_PHP_SDK
project hosted at GitHub.
It integrates the *Facebook API* into the Wolf CMS system and currently provides
_login-via-Facebook_ and Wolf user account integration so users can use their Facebook
accounts to access the Wolf Administrator section (if given permission).

It is still being developed and new features will continue to be added.

This repository contains the open source PHP SDK and WolfCMS plugin files that allows 
you to utilize the above on your Wolf CMS website. 
Except as otherwise noted, the *Facebook PHP SDK* _(facebook.php)_
is licensed under the "Apache License, Version 2.0":http://www.apache.org/licenses/LICENSE-2.0.html
and the rest of the project is free-for-all, such as the *MIT* License.

h2. Usage

Documentation and usage instructions/examples can be found at the project's 
"Wiki Page":Wiki_Page .

h2. Feedback

*Report An Issue/Bug*
Please report issues on the project's "GitHub Issues":issues page.

*Comments/Suggestions*
If you'd like to leave some general feedback, please visit the "Wolf CMS - Facebook Plugin":feedback_link
and leave a comment on that topic.  Hopefully a better commenting system can be
implimented at a later date.

[issues]http://github.com/amereservant/Wolf-CMS-Facebook-Plugin/issues
[feedback_link]http://www.wolfcms.org/forum/post4269.html#p4269
[Wiki_Page]http://wiki.github.com/amereservant/Wolf-CMS-Facebook-Plugin/
[Facebook_PHP_SDK]http://github.com/facebook/php-sdk
[wolf_url]http://www.wolfcms.org

h3. 1.0.1 Changes

* Changes to *facebook.php* were removed and written to the *FacebookConnect* class instead for better compatibility in future revisions to the "Facebook SDK":Facebook_PHP_SDK project.
* The *Facebook* class was updated to version 2.0.5, which is currently the latest version.
* Made minor changes to the _*facebook-login*_ snippet, which is due to the added _Observer_ in the index.php file.  This allowed the _*fb_login()*_ function to be called before anything else so the user's information can be used anywhere in the page itself.
* Improvements and modifications made to *facebookconnect.php*.
* The *enable.php* file was changed so that the _Facebook APP ID_ and _Facebook Secret_ values  weren't replaced every time the plugin was enabled/disabled.


