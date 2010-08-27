eZUrlRegex Datatype Extension
Version 1.x by eZ Systems AS / André R.
---------------------------------------
This datatype extends the normal ezurl datatype by adding support
for defining regex rules the url needs to match. Primary use case is
to match certain urls to specific sites. For instance video sites
like youtube and dailymotion. It will also store the index of the
regex that matches (see ezurlregex.ini).


Installation
------------

1.) Upload the ezurlregex folder to the extensions folder in your
eZ Publish installation.

2.) Activate the extension from the 'Extensions' portion of the
'Setup' tab in the eZ publish admin interface.
And update the autoload array by clicking "Regenerate autoload arrays for extensions"

3.) Now you can add the ezurlregex datatype like any other datatype when editing classes.

