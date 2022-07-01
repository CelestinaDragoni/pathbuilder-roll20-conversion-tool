<?php

namespace PBR20\Services;

/**
 * Pathfinder 2e SRD Document Lookup Service
 * Give it a name, gives you details. Uses a flat filesystem lookup and a hashtable. Can't get faster than that.
 */
class Document
{
    /** Hastable Index of Feats **/
    private $_featIndex = [];

    /** Hastable Index of Spells **/
    private $_spellIndex = [];

    /**
     * Singleton Constructor
     * @return instanceOf PBR20\Services\Document
     */
    public static function getInstance()
    {
        static $instance = false;
        return ($instance) ? $instance : $instance = new Document();
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        // Load Hashtable Indexes
        $this->_featIndex   = json_decode(file_get_contents(getcwd().'/database/feats-index.json'), true);
        $this->_spellIndex  = json_decode(file_get_contents(getcwd().'/database/spells-index.json'), true);
    }

    /**
     * Get Feat Details By Name, On Failure Returns Standard Struct
     * @param string $name
     * @return array
     */
    public function getFeatByName(String $name) : Array
    {
        $lookup = trim(strtolower($name));
        $key = !empty($this->_featIndex[$lookup]) ? $this->_featIndex[$lookup] : sha1($name);

        $filename = sprintf('%s/database/feats/%s.json', getcwd(), $key);
        if (file_exists($filename)) {
            $data = json_decode(file_get_contents($filename), true);
            if (!empty($data)) {
                return $data;
            }
        }

        return [
            'url'       => '',
            'name'      => $name,
            'source'        => '',
            'rarity'        => '',
            'traits'        => [],
            'level'         => 1,
            'prereq'        => '',
            'summary'       => '',
            'description'   => '',
            'economy'       => 'none',
            'trigger'       => '',
        ];
    }

    /**
     * Get Spell Details By Name, On Failure Returns Standard Struct
     * @param string $name
     * @return array
     */
    public function getSpellByName(String $name) : Array
    {
        $lookup = trim(strtolower($name));
        $key = !empty($this->_spellIndex[$lookup]) ? $this->_spellIndex[$lookup] : sha1($name);

        $filename = sprintf('%s/database/spells/%s.json', getcwd(), $key);
        if (file_exists($filename)) {
            $data = json_decode(file_get_contents($filename), true);
            if (!empty($data)) {
                return $data;
            }
        }

        return [
            'url'           => '',
            'name'          => $name,
            'source'        => '',
            'traditions'    => [],
            'rarity'        => '',
            'traits'        => [],
            'cantrip'       => false,
            'focus'         => false,
            'level'         => 1,
            'summary'       => '',
            'heightenable'  => '',
            'description'   => '',
            'economy'       => 'none',
            'components'    => [],
            'trigger'       => '',
            'range'         => '',
            'target'        => '',
            'duration'      => '',
            'saving'        => '',
            'school'        => '',
        ];
    }
}
