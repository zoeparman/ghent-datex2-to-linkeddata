# Linked Datex 2 from the City of Ghent to queryable Linked Data documents

This is a PHP library to convert the Datex2 Open Data feeds from the city of Ghent to Linked Data. The data can be queried using the Linked Data Fragments client.

Link: http://linked.open.gent/parking

## Install

Be sure to have composer installed on your system: http://getcomposer.org/

After cloning this repo, perform:
```
composer install
```

If you have a webserver, such as apache, direct your webserver to have the `public/` directory as the web root.

For development purposes, you can test your code with `php -S localhost:1234 -t public/` and your site will be available at http://localhost:1234/

To gather and deploy data periodically (in turtle files in /public/parking/out), add the following line to your crontab:

```
* * * * * /bin/php [REPO]/cron.php 1>> /dev/null 2>&1
```

Replace /bin/php with your PHP interpreter binary if you have it located somewhere else, and replace [REPO]
with the absolute path to the repository on your system.
Read/write permissions are necessary in the folder `public/parking/out`.
Note: this will keep gathering data, whether the development server is running or not.

## Classes that can be used

### otn\linkeddatex2\GhentToRDF

(old) Constructor takes a URL to a Datex2 file and optionally an `EasyRDF_Graph`. The data is added to the `$graph` element.

TODO

### otn\linkeddatex2\Metadata

(old) Given an `EasyRDF_Graph` object, generates the metadata and adds it to the graph

TODO

### otn\linkeddatex2\View

(old) Given an accept header, a metadata EasyRDF object and a data EasyRDF object, will stream a string to HTTP output. Will take care of HTTP response and cache headers as well.

TODO

### otn\linkeddatex2\gather\GraphProcessor

TODO

### otn\linkeddated2\gather\ParkingHistoryFilesystem

TODO
