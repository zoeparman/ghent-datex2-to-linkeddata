<?php

namespace otn\linkeddatex2\gather;

// TODO this is a very primitive way of working and has many bugs,
// TODO but it works for us
// TODO github issue

class TrigParser
{


    public function parse($contents) {
        $result = array();
        $lines = explode("\n", $contents);
        $i = 0;
        $parser = new \EasyRdf_Parser_Turtle();
        $prefixes = array();
        while ($i < count($lines)) {
            $line = $lines[$i];
            if (substr($line, 0, 7) === "@prefix") {
                array_push($prefixes, $lines[$i]);
            }
            if (substr($line, 0, 1) === "<") {
                $uri = substr($line, 1, strlen($line)-3);
                $graph = new \EasyRdf_Graph($uri);
                $i++; $line = $lines[$i]; $start = $i;
                while ($line !== "}") {
                    $i++; $line = $lines[$i];
                }
                $turtle_arr = array_slice($lines, $start, $i-$start);
                $parser->parse($graph, implode("\n", $prefixes) . implode("\n", $turtle_arr), "turtle", $uri);
                array_push($result, $graph);
            }
            $i++;
        }
        return $result;
    }
}