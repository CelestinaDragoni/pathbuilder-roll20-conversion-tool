<?php

namespace PBR20\Factory;

/**
 * Pathbuilder Player Factory
 * Takes a pathbuilder struct and puts it into our normalized player model.
 *
 * Developer Note:
 * This class is very unslightly, I'm unhappy with the cleanliness of this. Refactor?
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

    public static function create(String $pathbuilderData) : \PBR20\Models\Player
    {
        $data = json_decode($pathbuilderData, true);
        if (empty($data)) {
            throw New Exception('Invalid Pathbuilder Data');
        }

        // Create Player
        $player = new \PBR20\Models\Player();

        /**
         * We are validating and importing the pathbuilder model here.
         * Yes I know this is lengthy and very wordy, however I don't trust the data.
         */

        // General Profile
        $player->name           = isset($data['build']['name'])         ? $data['build']['name'] : '';
        $player->class          = isset($data['build']['class'])        ? $data['build']['class'] : '';
        $player->level          = isset($data['build']['level'])        ? abs(intval($data['build']['level'])) : 1;
        $player->ancestry       = isset($data['build']['ancestry'])     ? $data['build']['ancestry'] : '';
        $player->heritage       = isset($data['build']['heritage'])     ? $data['build']['heritage'] : '';
        $player->background     = isset($data['build']['background'])   ? $data['build']['background'] : '';
        $player->alignment      = isset($data['build']['alignment'])    ? $data['build']['alignment'] : '';
        $player->gender         = isset($data['build']['gender'])       ? $data['build']['gender'] : '';
        $player->age            = isset($data['build']['age'])          ? $data['build']['age'] : '';
        $player->deity          = isset($data['build']['deity'])        ? $data['build']['deity'] : '';
        $player->languages      = isset($data['build']['languages'])    ? $data['build']['languages'] : '';

        // HP and Speed Stats
        $player->ancestryHP     = isset($data['build']['attributes']['ancestryhp'])     ? abs(intval($data['build']['attributes']['ancestryhp'])) : 0;
        $player->classHP        = isset($data['build']['attributes']['classhp'])        ? abs(intval($data['build']['attributes']['classhp'])) : 0;
        $player->bonusHP        = isset($data['build']['attributes']['bonushp'])        ? abs(intval($data['build']['attributes']['bonushp'])) : 0;
        $player->speed          = isset($data['build']['attributes']['speed'])          ? abs(intval($data['build']['attributes']['speed'])) : 25;
        $player->speedBonus     = isset($data['build']['attributes']['speedBonus'])     ? abs(intval($data['build']['attributes']['speedBonus'])) : 0;
        $player->stat           = isset(Self::$_abilityMap[$data['build']['keyability']]) ? Self::$_abilityMap[$data['build']['keyability']] : 'strength';

        // Ability Scores
        foreach (Self::$_abilityMap as $short => $long) {
            $player->abilities[$long] = isset($data['build']['abilities'][$short]) ? abs(intval($data['build']['abilities'][$short])) : 10;
        }

        // Skills
        foreach (Self::$_skillsMap as $key) {
            $player->skills[$key] = isset($data['build']['proficiencies'][$key]) && in_array($data['build']['proficiencies'][$key], Self::$_skillsValueMap) ? abs(intval($data['build']['proficiencies'][$key])) : 0;
        }

        foreach (Self::$_armorSkillsMap as $key) {
            $player->armorSkills[$key] = isset($data['build']['proficiencies'][$key]) && in_array($data['build']['proficiencies'][$key], Self::$_skillsValueMap) ? abs(intval($data['build']['proficiencies'][$key])) : 0;
        }

        foreach (Self::$_weaponSkillsMap as $key) {
            $player->weaponSkills[$key] = isset($data['build']['proficiencies'][$key]) && in_array($data['build']['proficiencies'][$key], Self::$_skillsValueMap) ? abs(intval($data['build']['proficiencies'][$key])) : 0;
        }

        foreach (Self::$_magicSkillsMap as $key) {
            $pmk = 'casting'.ucfirst($key);
            $player->magicSkills[$key] = isset($data['build']['proficiencies'][$pmk]) && in_array($data['build']['proficiencies'][$pmk], Self::$_skillsValueMap) ? abs(intval($data['build']['proficiencies'][$pmk])) : 0;
        }

        foreach (Self::$_saveSkillsMap as $key) {
            $player->saveSkills[$key] = isset($data['build']['proficiencies'][$key]) && in_array($data['build']['proficiencies'][$key], Self::$_skillsValueMap) ? abs(intval($data['build']['proficiencies'][$key])) : 0;
        }

        $player->dc         = isset($data['build']['proficiencies']['classDC']) && in_array($data['build']['proficiencies']['classDC'], Self::$_skillsValueMap) ? abs(intval($data['build']['proficiencies']['classDC'])) : 0;
        $player->perception = isset($data['build']['proficiencies']['perception']) && in_array($data['build']['proficiencies']['perception'], Self::$_skillsValueMap) ? abs(intval($data['build']['proficiencies']['perception'])) : 0;

        // Sort Out Feats
        if (!empty($data['build']['feats']) && is_array($data['build']['feats'])) {
            foreach ($data['build']['feats'] as $feat) {

                // Type Validate Struct
                if (empty($feat[0])) {
                    $feat[0] = "Unknown Feat";
                }
                if (empty($feat[2])) {
                    $feat[2] = "Bonus";
                }
                if (empty($feat[3])) {
                    $feat[3] = 1;
                }

                // Check Feat For Special Flags
                if ($feat[0] == 'Untrained Improvisation') {
                    $player->improvisation = true;
                } else if ($feat[0] == 'Incredible Initiative') {
                    $player->initiative = true;
                } else if ($feat[0] == 'Toughness') {
                    $player->toughness = true;
                }

                // Beef Up Feat
                $struct = \PBR20\Services\Document::getInstance()->getFeatByName($feat[0]);

                // Insert Feat Into Correct Struct
                if ($feat[2] == "Heritage" || $feat[2] == "Ancestry Feat") {
                    $player->heritageAncestoryFeats[] = $struct;
                } else if ($feat[2] == "Skill Feat") {
                    $player->skillFeats[] = $struct;
                } else if ($feat[2] == "Class Feat") {
                    $player->classFeats[] = $struct;
                } else if ($feat[2] == "General Feat") {
                    $player->generalFeats[] = $struct;
                } else {
                    $player->bonusFeats[] = $struct;
                }
            }
        }

        // Sort Out Magic
        $coreSpells = [];
        $focusSpells = [];
        $innateSpells = [];

        // Sort Out (This is not keyed unfortunately)
        if (!empty($data['build']['spellCasters']) && is_array($data['build']['spellCasters'])) {
            foreach ($data['build']['spellCasters'] as $group) {
                if (empty($coreSpells) && in_array($group['name'], Self::$_spellClasses)) {
                    $coreSpells = $group;
                } else if (empty($focusSpells) && $group['name'] == "Focus Spells") {
                    $focusSpells = $group;
                } else {
                    $innateSpells[] = $group;
                }
            }
        }

        // Convert Core Spells
        if (!empty($coreSpells)) {
            foreach($coreSpells['perDay'] as $i => $amount) {
                $player->slots[$i] = abs(intval($amount));
            }

            if ($coreSpells['spellcastingType'] == 'spontaneous') {
                $player->spontaneous = true;
            } else if  ($coreSpells['spellcastingType'] == 'prepared') {
                $player->prepared = true;
            }

            if (!empty($coreSpells['spells']) && is_array($coreSpells['spells'])) {
                foreach ($coreSpells['spells'] as $rank) {
                    $level = abs(intval($rank['spellLevel']));
                    if (!empty($rank['list']) && is_array($rank['list'])) {
                        foreach ($rank['list'] as $spell) {
                            $player->coreSpells[$level][] = \PBR20\Services\Document::getInstance()->getSpellByName($spell);
                        }
                    }
                }
            }
        }

        // Convert Focus Spells
        if (!empty($focusSpells)) {
            $player->points = abs(intval($focusSpells['focusPoints']));
            if (!empty($focusSpells['spells']) && is_array($focusSpells['spells'])) {
                foreach ($focusSpells['spells'] as $rank) {
                    if (!empty($rank['list']) && is_array($rank['list'])) {
                         foreach ($rank['list'] as $spell) {
                            $player->focusSpells[] = \PBR20\Services\Document::getInstance()->getSpellByName($spell);
                         }
                    }
                }
            }
        }

        // Convert Innate Spells
        if (!empty($innateSpells)) {
            foreach ($innateSpells as $innateSpell) {
                if (!empty($innateSpell['name'])) {
                    $player->innateSpells[] = \PBR20\Services\Document::getInstance()->getSpellByName($innateSpell['name']);
                }
            }
        }

        return $player;
    }
}
