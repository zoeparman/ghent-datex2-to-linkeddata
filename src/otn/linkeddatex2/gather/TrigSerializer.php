<?php
/**
 * Created by PhpStorm.
 * User: faplord
 * Date: 27/03/17
 * Time: 16:48
 */

namespace otn\linkeddatex2\gather;

// TODO this is a very primitive way of working and has many bugs,
// TODO but it works for us

class TrigSerializer
{
    public function serialize($graphs) {
        $output = "";
        $turtle_serializer = new \EasyRdf_Serialiser_Turtle();
        foreach($graphs as $graph) {
            $turtle = $turtle_serializer->serialise($graph, "turtle");
            $output .= "<" . $graph->getUri() . ">{";
            $output .= "\n";
            $output .= $turtle;
            $output .= "}\n\n";
        }
        $output = $this->getPrefixes($output);
        return $output;
    }

    private function getPrefixes($output) {
        $lines = explode("\n", $output);
        $amount = count($lines);
        $prefixes = array();
        for ($i = 0; $i < $amount; $i++) {
            $line = $lines[$i];
            if (substr($line, 0, 7) === "@prefix") {
                array_splice($lines, $i, 1);
                $i--; $amount--;
                if (!in_array($line, $prefixes)) {
                     array_push($prefixes, $line);
                }
            }
            if ($line === "") {
                array_splice($lines, $i, 1);
                $i--; $amount--;
            }
        }
        return implode("\n", $prefixes) . "\n\n" . implode("\n", $lines);
    }
}