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
**Read/write permissions are necessary in the folders `public/parking/out` and `resources`.**
**Note: this will keep gathering data, whether the development server is running or not.**

## Files

### .env
Basic configuration file.
`BASE_URL`: the base url for the website, used in construction of the graphs (https://linked.open.gent/parking/).

## Classes that can be used

A graph is always represented as a nested PHP array, compatible with [hardf](https://github.com/pietercolpaert/hardf):
```
[
  ["graph" => graph,
   "subject" => subject,
   "predicate" => predicate,
   "object" => object],
   ...
]
```

### otn\linkeddatex2\GhentToRDF

Static function `getPrefixes()` returns all relevant prefixes in a PHP array (`prefix => url`). Constants `STATIC` and `DYNAMIC` are defined
for use with `get()`: if `STATIC` is passed as an argument, the "static" data will be fetched (description, number of places, etc.) from
http://opendataportaalmobiliteitsbedrijf.stad.gent/datex2/v2/parkings/. If `DYNAMIC` is passed, "dynamic" data will be fetched (number of available
spaces, opening status, etc.). Static data is not expected to change over the course of months or years. The data is returned as a `hardf` graph.

### otn\linkeddatex2\Metadata

Static function `get()` returns all metadata triples in a `hardf` graph (graph is `#Metadata`).

### otn\linkeddatex2\View

Given an accept header, a `hardf` graph, and a url (the url of the current graph)
the `view` method will stream a string to HTTP output. Will take care of HTTP response and cache headers as well.

### otn\linkeddatex2\gather\GraphProcessor

`construct_graph()` constructs a `hardf` graph containing only the number of available spaces for saving to disk.

### otn\linkeddated2\gather\ParkingHistoryFilesystem

Represents a filesystem to keep the history of the gathered data. The constructor takes a directory name where the TriG files will be,
and a directory name for resources. Provides the following interface:
* `has_file`: Takes a filename and returns true if it exists.
* `get_graphs_from_file_with_links`: Takes a filename and returns a fully dressed `hardf` graph with all relevant data.
* `get_closest_page_for_timestamp`: Takes a UNIX timestamp and returns the filename of the page with the recordings that are closest to that timestamp.
* `get_last_page`: Gets the filename of the most recent page.
* `write_measurement`: Takes a UNIX timestamp and a PHP array with prefixes and a `hardf` graph, and writes it to disk to the relevant file. The relevant file is found using the UNIX timestamp, the PHP array is of the following form:
```
[
  "triples" => hardf_graph
  "prefixes" => [prefix => iri, ...]
]
```
