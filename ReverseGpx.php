<?php

namespace ReverseGpx;

use SimpleXMLElement;

class ReverseGpx
{

    private $file;
    private $path;
    public function __construct($path)
    {
        $this->file = simplexml_load_file($path);
        $this->path = $path;
    }

    public function reverse()
    {

        $trackSegment = $this->file->trk->trkseg;
        $arr = [];
        $key = 0;
        foreach ($trackSegment->trkpt as $seg) {
            $arr[$key]['lat'] = $seg['lat'];
            $arr[$key]['lon'] = $seg['lon'];
            $key++;
        }

        $reversed = array_reverse($arr);
        $this->createReversedXML($reversed, $this->file);
        return ['reversed' => $reversed];
    }

    public function createReversedXML($reversed, $original)
    {
        $out = <<<XML
            <gpx xmlns="http://www.topografix.com/GPX/1/1" xmlns:gpsies="https://www.gpsies.com/GPX/1/0" creator="GPSies https://www.gpsies.com - Pienza - San Quirico d&amp;apos;Orcia" version="1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd https://www.gpsies.com/GPX/1/0 https://www.gpsies.com/gpsies.xsd">
            </gpx>
        XML;
        $newXML = new SimpleXMLElement($out);
        if (isset($original->metadata)) {
            $this->addMetadata($newXML, $original);
        }
        if (isset($original->wpt)) {
            $this->addWPT($newXML, $original);
        }
        $newXML->addChild('trk');
        foreach ($original->trk->trkseg as $seg) {

            $this->addTrackSegment($newXML, $original, $reversed);
        }

        $filename = explode('.', $this->path);
        $newXML->asXML($filename[0] . '_reversed.' . $filename[1]);
    }

    private function addMetadata(&$newXML, $original)
    {
        $newXML->addChild('metadata');
        isset($original->metadata->name) ? $newXML->metadata->addChild('name', $original->metadata->name) : '';
        isset($original->metadata->author) ? $newXML->metadata->addChild('author', $original->metadata->author) : '';
        isset($original->metadata->author->name) ? $newXML->metadata->author->addChild('name', $original->metadata->author->name) : '';
        isset($original->metadata->time) ? $newXML->metadata->addChild('time', $original->metadata->time) : '';
        if(isset($original->metadata->extensions)){
            $this->addExtensions($newXML,$original);
        }
    }


    private function addExtensions(&$newXML,$original){
        $newXML->metadata->addChild('extensions');
        var_dump($original->metadata->extensions);
    //   <gpsies:property>one-way trip</gpsies:property>
    //   <gpsies:trackLengthMeter>9347.554717852248</gpsies:trackLengthMeter>
    //   <gpsies:totalAscentMeter>271.0</gpsies:totalAscentMeter>
    //   <gpsies:totalDescentMeter>330.0</gpsies:totalDescentMeter>
    //   <gpsies:minHeightMeter>288.0</gpsies:minHeightMeter>
    //   <gpsies:maxHeightMeter>474.0</gpsies:maxHeightMeter>
    // </extensions>
    }

    private function addWPT(&$newXML, $original)
    {
        $newXML->addChild('wpt');
        isset($original->wpt['lat']) ? $newXML->wpt->addAttribute('lat', $original->wpt['lat']) : '';
        isset($original->wpt['lon']) ? $newXML->wpt->addAttribute('lon', $original->wpt['lon']) : '';
        isset($original->wpt->ele) ? $newXML->wpt->addChild('ele', $original->wpt->ele) : '';
        isset($original->wpt->name) ? $newXML->wpt->addChild('name', $original->wpt->name) : '';
        isset($original->wpt->desc) ? $newXML->wpt->addChild('desc', $original->wpt->desc) : '';
        isset($original->wpt->sym) ? $newXML->wpt->addChild('sym', $original->wpt->sym) : '';
        isset($original->wpt->type) ? $newXML->wpt->addChild('type', $original->wpt->type) : '';
    }

    private function addTrackSegment(&$newXML, $original, $reversed)
    {
        $newXML->trk->addChild('trkseg');
        $counter = 0;
        foreach ($original->trk->trkseg->trkpt as $orig) {
            $seg = $newXML->trk->trkseg->addChild('trkpt');;
            $seg->addAttribute('lat', $reversed[$counter]['lat']);
            $seg->addAttribute('lon', $reversed[$counter]['lon']);
            $seg->addChild('ele', $orig->ele);
            $seg->addChild('time', $orig->time);
            $counter++;
        }
    }
}
