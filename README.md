Alpha-base
==========

Alpha is a web template / boilerplate for smaller websites and web applications using PHP.

Built by Lenny Erlesand, based on work by Mikael Roos.

Installation and Set up
--------------------------------
Before you are able to use Alpha you need to set up your configuration. 
* Open up `webroot/config.php`. 
* Edit the array `$alpha['database']` and fill in your credentials. 
* You can also edit the items on the navigation bar through `$alpha['menu']`
* If you want to append some text in the title bar, use `$alpha['title_append']`
* Finally, edit the footer, `$alpha['footer']` 

Classes
------------------
All of the classes of Alpha are found in the folder `/src/`, the most prominent classes are:
* __CDatabase__: Handles a lot of the database interactions. 
* __CForm2__: Helper to generate forms.
* __CHTMLTable__: Class to create HTML tables. 
* __CUser__: Has methods for user authentication. 

There are a lot of other classes, and they will be documented in the next version of Alpha. 

License 
------------------

This software is free software and carries a MIT license.



Use of external libraries
-----------------------------------

The following external modules are included and subject to its own license.


### jQuery
* Website: http://www.jquery.com/
* Version: 1.11.2
* License: MIT license
* Path: included in `webroot/js/jquery.js`

### Modernizr
* Website: http://modernizr.com/
* Version: 2.8.3
* License: MIT license 
* Path: included in `webroot/js/modernizr.js`

### Normalize.css
* Website: http://www.git.io/normalize/
* Version: 3.0.3
* License: MIT license
* Path: included in `webroot/css/normalize.css`



History
-----------------------------------
v0.2.0 (2015-06-01)
v0.1.0 (2015-04-04)

* First release of Alpha.



------------------
 .  
..:

Copyright (c) 2015 Lenny Erlesand