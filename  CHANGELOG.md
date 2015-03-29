## Changelog

All notable changes to `cosenary/instagram` will be documented in this file.

> Version 3.0 is in development and includes support for real-time subscriptions.

**Instagram 2.2 - 04/10/2014**

- `feature` Added "Enforce signed header"
- `feature` Implemented PSR4 autoloading.
- `update` Increased timeout from 5 to 20 seconds
- `update` Class name, package renamed

**Instagram 2.1 - 30/01/2014**

- `update` added min and max_timestamp to `searchMedia()`
- `update` public authentication for `getUserMedia()` method
- `fix` support for inconsistent pagination return type (*relationship endpoint*)

**Instagram 2.0 - 24/12/2013**

- `release` version 2.0

**Instagram 2.0 beta - 20/11/2013**

- `feature` Added *Locations* endpoint
- `update` Updated example project to display Instagram videos

**Instagram 2.0 alpha 4 - 01/11/2013**

- `feature` Comment endpoint implemented
- `feature` New example with a fancy GUI
- `update` Improved documentation

**Instagram 2.0 alpha 3 - 04/09/2013**

- `merge` Merged master branch updates
	- `update` Updated documentation
	- `bug` / `change` cURL CURLOPT_SSL_VERIFYPEER disabled (fixes #6, #7, #8, #16)
	- `feature` Added cURL error message
	- `feature` Added `limit` to `getTagMedia()` method

**Instagram 2.0 alpha 2 - 14/06/2013**

- `feature` Improved Pagination functionality
- `change` Added `distance` parameter to `searchMedia()` method (thanks @jonathanwkelly)

**Instagram 2.0 alpha 1 - 28/05/2012**

- `feature` Added Pagination method
- `feature` Added User Relationship endpoints
- `feature` Added scope parameter table for the `getLoginUrl()` method

**Instagram 1.5 - 31/01/2012**

- `release` Second master version
- `feature` Added Tag endpoints
- `change` Edited the "Get started" example
- `change` Now you can pass the `getOAuthToken()` object directly into `setAccessToken()`

**Instagram 1.0 - 20/11/2011**

- `release` First public release
- `feature` Added sample App with documented code
- `update` New detailed documentation

**Instagram 0.8 - 16/11/2011**

- `release` First inital released version
- `feature` Initialize the class with a config array or string (see example)

**Instagram 0.5 - 12/11/2011**

- `release` Beta version
- `update` Small documentation