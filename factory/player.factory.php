<?php

namespace PBR20\Factory;

/**
 * Pathbuilder Player Factory
 * Takes a pathbuilder struct and puts it into our normalized player model.
 */
class Player
{
    /** Shorthand (Pathbuilder) To Longhand Stat Name **/
    static private $_abilityMap = [
        'str' => 'strength',
        'dex' => 'dexterity',
        'con' => 'constitution',
        'int' => 'intelligence',
        'wis' => 'wisdom',
        'cha' => 'charisma',
    ];

    /** Acceptable Values for Skills **/
    static private $_skillsValueMap = [
        0, // Untrained
        2, // Trained
        4, // Expert
        6, // Master
        8, // Legendary
    ];

    /** Skills Map **/
    static private $_skillsMap = [
        'acrobatics',
        'arcana',
        'athletics',
        'crafting',
        'deception',
        'diplomacy',
        'intimidation',
        'medicine',
        'nature',
        'occultism',
        'performance',
        'religion',
        'society',
        'stealth',
        'survival',
        'thievery',
    ];

    /** Armor Skills **/
    static private $_armorSkillsMap = [
        'unarmored',
        'light',
        'medium',
        'heavy',
    ];

    /** Weapon Skills **/
    static private $_weaponSkillsMap = [
        'unarmed',
        'simple',
        'martial',
    ];

    /** Magic Skills **/
    static private $_magicSkillsMap = [
        'arcane',
        'divine',
        'occult',
        'primal',
    ];

    /** Saving Throw Skills **/
    static private $_saveSkillsMap = [
        'fortitude',
        'reflex',
        'will',
    ];

    static private $_spellClasses = [
        'Bard',
        'Champion',
        'Cleric',
        'Druid',
        'Magus',
        'Monk',
        'Oracle',
        'Sorcerer',
        'Summoner',
        'Witch',
        'Wizard',
    ];

    /**
     * Validates String
     * @param mixed $var
     * @param string $default
     * @return string
     */
    private static function _validateString(&$var, $default = '') : String
    {
        if (!empty($var) && is_string($var)) {
            return strip_tags(trim($var));
        }

        return $default;
    }

    /**
     * Validates Integer
     * @param mixed $var
     * @param int $default
     * @return int
     */
    private static function _validateInt(&$var, $default = 0) : Int
    {
        if (!empty($var) && (is_string($var) || is_int($var))) {
            return abs(intval($var));
        }

        return $default;
    }

    /**
     * Validates Skill
     * @param mixed $var
     * @param int $default
     * @return int
     */
    private static function _validateSkill(&$var, $default = 0) : Int
    {
        if (!empty($var) && (is_string($var) || is_int($var))) {
            $skill = abs(intval($var));
            if (in_array($skill, Self::$_skillsValueMap)) {
                return $skill;
            }
        }

        return $default;
    }

    /**
     * Validates Array String
     * @param mixed $var
     * @param array $default
     * @return array
     */
    private static function _validateArrayString(&$var, $default = []) : Array
    {
        if (!empty($var) && is_array($var)) {
            $data = [];
            foreach ($var as $v) {
                $data[] = Self::_validateString($v);
            }
            return $data;
        }

        return $default;
    }

    /**
     * Creates Pathfinder Character
     * @param string $pathbuilderData (JSON Structure)
     * @return \PBR20\Models\Player
     */
    public static function create(String $pathbuilderData) : \PBR20\Models\Player
    {
        // Decode Data
        $data = json_decode($pathbuilderData, true);
        if (empty($data)) {
            throw New \Exception('Invalid Pathbuilder Data');
        }

        // Create Player
        $player = new \PBR20\Models\Player();

        // General Profile
        $player->name           = Self::_validateString($data['build']['name']);
        $player->class          = Self::_validateString($data['build']['class']);
        $player->level          = Self::_validateInt($data['build']['level'], 1);
        $player->ancestry       = Self::_validateString($data['build']['ancestry']);
        $player->heritage       = Self::_validateString($data['build']['heritage']);
        $player->background     = Self::_validateString($data['build']['background']);
        $player->alignment      = Self::_validateString($data['build']['alignment']);
        $player->gender         = Self::_validateString($data['build']['gender']);
        $player->age            = Self::_validateString($data['build']['age']);
        $player->deity          = Self::_validateString($data['build']['deity']);
        $player->languages      = Self::_validateArrayString($data['build']['languages']);

        // HP and Speed Stats
        $player->ancestryHP     = Self::_validateInt($data['build']['attributes']['ancestryhp']);
        $player->classHP        = Self::_validateInt($data['build']['attributes']['classhp']);
        $player->bonusHP        = Self::_validateInt($data['build']['attributes']['bonushp']);
        $player->speed          = Self::_validateInt($data['build']['attributes']['speed']);
        $player->speedBonus     = Self::_validateInt($data['build']['attributes']['speedBonus']);
        $player->stat           = isset(Self::$_abilityMap[$data['build']['keyability']]) ? Self::$_abilityMap[$data['build']['keyability']] : 'strength';

        // Ability Scores
        foreach (Self::$_abilityMap as $short => $long) {
            $player->abilities[$long] = Self::_validateInt($data['build']['abilities'][$short], 10);
        }

        // Standard Skills
        foreach (Self::$_skillsMap as $key) {
            $player->skills[$key] = Self::_validateSkill($data['build']['proficiencies'][$key]);
        }

        // Armor Skills
        foreach (Self::$_armorSkillsMap as $key) {
            $player->armorSkills[$key] = Self::_validateSkill($data['build']['proficiencies'][$key]);
        }

        // Weapon Skills
        foreach (Self::$_weaponSkillsMap as $key) {
            $player->weaponSkills[$key] = Self::_validateSkill($data['build']['proficiencies'][$key]);
        }

        // Magic Skills
        foreach (Self::$_magicSkillsMap as $key) {
            $pmk = 'casting'.ucfirst($key);
            $player->magicSkills[$key] = Self::_validateSkill($data['build']['proficiencies'][$pmk]);
        }

        // Saving Throw Skills
        foreach (Self::$_saveSkillsMap as $key) {
            $player->saveSkills[$key] = Self::_validateSkill($data['build']['proficiencies'][$key]);
        }

        // DC and Perception
        $player->dc = Self::_validateSkill($data['build']['proficiencies']['classDC']);
        $player->perception = Self::_validateSkill($data['build']['proficiencies']['perception']);

        // Feats
        if (!empty($data['build']['feats']) && is_array($data['build']['feats'])) {
            foreach ($data['build']['feats'] as $feat) {
                // Validate Struct
                $featTitle = Self::_validateString($feat[0], 'Unknown Feat');
                $featSection = Self::_validateString($feat[2], 'Bonus');

                // Check Feat For Special Flags
                if ($featTitle == 'Untrained Improvisation') {
                    $player->improvisation = true;
                } else if ($featTitle == 'Incredible Initiative') {
                    $player->initiative = true;
                } else if ($featTitle == 'Toughness') {
                    $player->toughness = true;
                }

                // Get SRD Document
                $featSRD = \PBR20\Services\Document::getInstance()->getFeatByName($featTitle);

                // Sort
                if ($featSection == 'Heritage' || $featSection == 'Ancestry Feat') {
                    $player->heritageAncestoryFeats[] = $featSRD;
                } else if ($featSection == 'Skill Feat') {
                    $player->skillFeats[] = $featSRD;
                } else if ($featSection == 'Class Feat') {
                    $player->classFeats[] = $featSRD;
                } else if ($featSection == 'General Feat') {
                    $player->generalFeats[] = $featSRD;
                } else {
                    $player->bonusFeats[] = $featSRD;
                }
            }
        }

        // Pre-Sort Magic
        // Developer Note:
        // For some reason the spell section does not use a strict structure like the rest of the schema.
        // I don't understand the reason behind this since there is innate, focus, core magic.
        $coreSpells     = [];
        $focusSpells    = [];
        $innateSpells   = [];

        if (!empty($data['build']['spellCasters']) && is_array($data['build']['spellCasters'])) {
            foreach ($data['build']['spellCasters'] as $group) {
                $groupName = Self::_validateString($group['name'], 'innate');

                if (empty($coreSpells) && in_array($groupName, Self::$_spellClasses)) {
                    $coreSpells = $group;
                } else if (empty($focusSpells) && $groupName == "Focus Spells") {
                    $focusSpells = $group;
                } else {
                    $innateSpells[] = $group;
                }
            }
        }

        // Convert Core Spells
        if (!empty($coreSpells)) {
            // Slots Per Day
            if (isset($coreSpells['perDay']) && is_array($coreSpells['perDay'])) {
                foreach ($coreSpells['perDay'] as $i => $amount) {
                    $player->slots[$i] = Self::_validateInt($amount);
                }
            }

            // Determine Spellcasting Type
            $coreSpells['spellcastingType'] = Self::_validateString($coreSpells['spellcastingType']);
            if ($coreSpells['spellcastingType'] == 'spontaneous') {
                $player->spontaneous = true;
            } else if  ($coreSpells['spellcastingType'] == 'prepared') {
                $player->prepared = true;
            }

            // Populate Spellbook
            if (!empty($coreSpells['spells']) && is_array($coreSpells['spells'])) {
                foreach ($coreSpells['spells'] as $rank) {
                    $level = Self::_validateInt($rank['spellLevel']);
                    if (!empty($rank['list']) && is_array($rank['list'])) {
                        foreach ($rank['list'] as $spell) {
                            $spellName = Self::_validateString($spell, 'Unknown Spell');
                            $player->coreSpells[$level][] = \PBR20\Services\Document::getInstance()->getSpellByName($spellName);
                        }
                    }
                }
            }
        }

        // Convert Focus Spells
        if (!empty($focusSpells)) {
            $player->points = Self::_validateInt($focusSpells['focusPoints']);
            if (!empty($focusSpells['spells']) && is_array($focusSpells['spells'])) {
                foreach ($focusSpells['spells'] as $rank) {
                    if (!empty($rank['list']) && is_array($rank['list'])) {
                         foreach ($rank['list'] as $spell) {
                            $spellName = Self::_validateString($spell, 'Unknown Spell');
                            $player->focusSpells[] = \PBR20\Services\Document::getInstance()->getSpellByName($spellName);
                         }
                    }
                }
            }
        }

        // Convert Innate Spells
        if (!empty($innateSpells)) {
            foreach ($innateSpells as $innateSpell) {
                $spellName = Self::_validateString($innateSpell['name'], 'Unknown Spell');
                $player->innateSpells[] = \PBR20\Services\Document::getInstance()->getSpellByName($spellName);
            }
        }

        return $player;
    }
}
