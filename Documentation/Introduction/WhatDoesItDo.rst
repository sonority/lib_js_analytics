What does it do?
^^^^^^^^^^^^^^^^

This extension simple integrates google's analytics.js into the HEAD of your website.
The most important parameters are configurable in constants editor and can be extended by typoscript.
The dynamically generated script-tag gets inserted by the PageRenderer (Hook).

If you ever wondered why google's pagespeed-analysis grumbled about the uncached analytics.js (which interestingly is provided by google itself and you cannot influence the caching!), than you maybe thought about saving the javascript-file locally.
In this case the file needs to be updated manually, that's why this extensions provides a scheduler task to manage that for you.

You can also run the updater after installation in the extensions-manager to manually update the script or if you don't want to run the scheduler periodically.

Per default (configurable), the script will be included only if there is no active backend-user-session, and analytics will not count your activities while you are editing the website's content.
