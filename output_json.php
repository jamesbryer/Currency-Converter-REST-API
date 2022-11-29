<?php

header('Content-Type: application/json');

function json_prepare_xml($domNode)
{
    foreach ($domNode->childNodes as $node) {
        if ($node->hasChildNodes()) {
            json_prepare_xml($node);
        } else {
            if ($domNode->hasAttributes() && strlen($domNode->nodeValue)) {
                $domNode->setAttribute("nodeValue", $node->textContent);
                $node->nodeValue = "";
            }
        }
    }
}
$xmlfile = "response.xml";
$dom = new DOMDocument();
$dom->loadXML(file_get_contents($xmlfile));
json_prepare_xml($dom);
$sxml = simplexml_load_string($dom->saveXML());
$json = json_encode($sxml);
print_r($json);