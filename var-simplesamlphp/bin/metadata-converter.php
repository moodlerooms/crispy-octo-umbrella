<?php
$baseDir = dirname(dirname(__FILE__));

require_once $baseDir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . '_autoload.php';
require_once SimpleSAML\Utils\Config::getConfigDir(). DIRECTORY_SEPARATOR . 'config.php';

$config = SimpleSAML_Configuration::getInstance();

if (count($argv) !== 2) {
    echo "Wrong number of parameters. Run:   " . $argv[0] . " filename\n"; exit;
}

$file = $argv[1];
if (!file_exists($file)) {
   echo 'File does not exist.'; exit;
}

$xmldata = file_get_contents($file);

if (!empty($xmldata)) {
    \SimpleSAML\Utils\XML::checkSAMLMessage($xmldata, 'saml-meta');
    $entities = SimpleSAML_Metadata_SAMLParser::parseDescriptorsString($xmldata);

    // get all metadata for the entities
    foreach ($entities as &$entity) {
        $entity = array(
            'shib13-sp-remote'  => $entity->getMetadata1xSP(),
            'shib13-idp-remote' => $entity->getMetadata1xIdP(),
            'saml20-sp-remote'  => $entity->getMetadata20SP(),
            'saml20-idp-remote' => $entity->getMetadata20IdP(),
        );
    }

    // transpose from $entities[entityid][type] to $output[type][entityid]
    $output = SimpleSAML\Utils\Arrays::transpose($entities);

    // merge all metadata of each type to a single string which should be added to the corresponding file
    foreach ($output as $type => &$entities) {
        $text = '';
        foreach ($entities as $entityId => $entityMetadata) {

            if ($entityMetadata === null) {
                continue;
            }

            // remove the entityDescriptor element because it is unused, and only makes the output harder to read
            unset($entityMetadata['entityDescriptor']);

            $text .= '$metadata['.var_export($entityId, true).'] = '.
                var_export($entityMetadata, true).";\n";
        }
        $entities = $text;
    }
} else {
    $xmldata = '';
    $output = array();
}

foreach ($output as $out) {
   echo $out . "\n";
}
