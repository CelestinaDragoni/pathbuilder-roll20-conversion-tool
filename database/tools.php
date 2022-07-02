<?php

$schools = [
    'Abjuration',
    'Conjuration',
    'Divination',
    'Enchantment',
    'Evocation',
    'Illusion',
    'Necromancy',
    'Transmutation',
];

$saving = [
    '/^Reflex/' => 'Reflex',
    '/^Will/' => 'Will',
    '/^Fortitude/' => 'Fortitude',
];

 foreach(glob("./spells/*.json") as $filename) {
    $document = json_decode(file_get_contents($filename), true);

    $school = '';
    foreach($document['traits'] as $trait) {
        if (in_array($trait, $schools)) {
            $school = $trait;
        }
    }

    $document['school'] = $school;

    if (!empty($document['saving'])) {
        foreach ($saving as $regex=>$value) {
            if (preg_match($regex, $document['saving'])){
                $document['saving'] = $value;
                break;
            }
        }
    }

    file_put_contents($filename, json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

foreach(glob("./feats/*.json") as $filename) {
    $document = json_decode(file_get_contents($filename), true);
    if ($document['prereq'] == '&Mdash;') {
        $document['prereq'] = '';
    }
    file_put_contents($filename, json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}
