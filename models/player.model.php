<?php

namespace PBR20\Models;

/**
 * General Pathfinder Player Model Struct
 */
class Player {

    /** General Profile **/
    public $name        = '';
    public $class       = '';
    public $level       = 1;
    public $ancestry    = '';
    public $heritage    = '';
    public $background  = '';
    public $alignment   = '';
    public $gender      = '';
    public $age         = '';
    public $deity       = '';
    public $languages   = [];

    /** Base Stats **/
    public $abilities = [
        'strength'      => 0,
        'dexterity'     => 0,
        'constitution'  => 0,
        'intelligence'  => 0,
        'wisdom'        => 0,
        'charisma'      => 0,
    ];

    /** HP Stats **/
    public $ancestryHP = 0;
    public $classHP    = 0;
    public $bonusHP    = 0;
    public $toughness  = false;

    /** Speed Stats **/
    public $speed      = 0;
    public $speedBonus = 0;

    /** Skills Training **/
    public $skills = [
        'acrobatics'      => 0,
        'arcana'          => 0,
        'athletics'       => 0,
        'crafting'        => 0,
        'deception'       => 0,
        'diplomacy'       => 0,
        'intimidation'    => 0,
        'medicine'        => 0,
        'nature'          => 0,
        'occultism'       => 0,
        'performance'     => 0,
        'religion'        => 0,
        'society'         => 0,
        'stealth'         => 0,
        'survival'        => 0,
        'thievery'        => 0,
    ];

    /** Ability Modifier For Each Skill **/
    public $skillsMod = [
        'acrobatics'      => 'dexterity',
        'arcana'          => 'intelligence',
        'athletics'       => 'strength',
        'crafting'        => 'intelligence',
        'deception'       => 'charisma',
        'diplomacy'       => 'charisma',
        'intimidation'    => 'charisma',
        'medicine'        => 'wisdom',
        'nature'          => 'wisdom',
        'occultism'       => 'intelligence',
        'performance'     => 'charisma',
        'religion'        => 'wisdom',
        'society'         => 'intelligence',
        'stealth'         => 'dexterity',
        'survival'        => 'wisdom',
        'thievery'        => 'dexterity',
    ];
    public $improvisation = false; // Untrained Improvisation Feat

    /** Preception / Initiative **/
    public $perception  = 0;
    public $initiative = false; // Incredible Initiative Feat

    /** Armor Skills Training **/
    public $armorSkills = [
        'unarmored'     => 0,
        'light'         => 0,
        'medium'        => 0,
        'heavy'         => 0,
    ];

    /** Weapon Skills Training **/
    public $weaponSkills = [
        'unarmed'       => 0,
        'simple'        => 0,
        'martial'       => 0,
    ];

    /** Magic Skills Training **/
    public $magicSkills = [
        'arcane'        => 0,
        'divine'        => 0,
        'occult'        => 0,
        'primal'        => 0,
    ];

    /** Saves Skills Training **/
    public $saveSkills = [
        'fortitude'     => 0,
        'reflex'        => 0,
        'will'          => 0,
    ];

    /** DC **/
    public $stat        = 'strength';
    public $dc          = 0;

    /** Feats **/
    public $heritageAncestoryFeats  = [];
    public $classFeats              = [];
    public $skillFeats              = [];
    public $generalFeats            = [];
    public $bonusFeats              = [];

    /** Spells **/
    public $prepared = false;
    public $spontaneous = false;
    public $slots = [
        0, // Cantrips
        0, // Level 1
        0, // Level 2
        0, // Level 3
        0, // Level 4
        0, // Level 5
        0, // Level 6
        0, // Level 7
        0, // Level 8
        0, // Level 9
        0, // Level 10
    ];
    public $points = 0;
    public $coreSpells = [
        [], // Cantrips
        [], // Level 1
        [], // Level 2
        [], // Level 3
        [], // Level 4
        [], // Level 5
        [], // Level 6
        [], // Level 7
        [], // Level 8
        [], // Level 9
        [], // Level 10
    ];
    public $focusSpells = [];
    public $innateSpells = [];

    /**
     * Get Ability Modifier Value
     * @param string $ability - Ability Key
     * @return int
     */
    public function getAbilityMod(String $ability) : Int
    {
        if (!isset($this->abilities[$ability])) {
            return 0;
        }

        return floor(($this->abilities[$ability]-10)/2);
    }

    /**
     * Get Ability Modifier Half Value
     * @param string $ability - Ability Key
     * @return int
     */
    public function getAbilityModHalf(String $ability) : Int
    {
        if (!isset($this->abilities[$ability])) {
            return 0;
        }

        return floor($this->getAbilityMod($ability)/2);
    }

    /**
     * Get Skill Proficency Value
     * @param string $skill - Skill Key
     * @return int
     */
    public function getSkillProficiency(String $skill) : Int
    {
        if (!isset($this->skills[$skill]) || !isset($this->skillsMod[$skill])) {
            return 0;
        }

        if ($this->skills[$skill] > 0) {
            return $this->level + $this->skills[$skill];
        } else {
            return 0;
        }
    }

    /**
     * Get Skill Total Value
     * @param string $skill - Skill Key
     * @return int
     */
    public function getSkillTotal(String $skill) : Int
    {
        if (!isset($this->skills[$skill]) || !isset($this->skillsMod[$skill])) {
            return 0;
        }

        $improv = $this->getSkillImprov($skill);

        if ($this->skills[$skill] > 0) {
            return $this->level + $this->skills[$skill] + $this->getSkillMod($skill);
        } else {
            return $improv + $this->getSkillMod($skill);
        }
    }

    /**
     * Get Skill Modifier Value
     * @param string $skill - Skill Key
     * @return int
     */
    public function getSkillMod(String $skill) : Int
    {
        if (!isset($this->skills[$skill]) || !isset($this->skillsMod[$skill])) {
            return 0;
        }

        return $this->getAbilityMod($this->skillsMod[$skill]);
    }

    /**
     * Get Skill Improv Value (If The Player Has This Feat)
     * @param string $skill - Skill Key
     * @return int
     */
    public function getSkillImprov(String $skill) : Int
    {
        if (!isset($this->skills[$skill]) || !isset($this->skillsMod[$skill])) {
            return 0;
        }

        if ($this->improvisation && $this->skills[$skill] <= 0) {
            if ($this->level >= 7) {
                return $this->level;
            } else {
                return floor($this->level/2);
            }
        }

        return 0;
    }

    /**
     * Get Perception Total
     * @return int
     */
    public function getPerceptionTotal() : Int
    {
        return $this->getPreceptionProficiency() + $this->getAbilityMod('wisdom') + $this->getPerceptionIncredibleInitiative();
    }

    /**
     * Get Perception Proficiency Total
     * @return int
     */
    public function getPreceptionProficiency() : Int
    {
        return $this->level + $this->perception;
    }

    /**
     * Get Incredible Initiative Value
     * @return int
     */
    public function getPerceptionIncredibleInitiative() : Int
    {
        return ($this->initiative) ? 2 : 0;
    }

    /**
     * Get Fortitude Save Value
     * @return int
     */
    public function getSavingFortitude() : Int
    {
        return $this->getSavingFortitudeProficiency() + $this->getAbilityMod('constitution');
    }

    /**
     * Get Fortitude Save Proficiency
     * @return int
     */
    public function getSavingFortitudeProficiency() : Int
    {
        if ($this->saveSkills['fortitude'] > 0) {
            return $this->level + $this->saveSkills['fortitude'];
        }

        return 0;
    }

    /**
     * Get Reflex Save Value
     * @return int
     */
    public function getSavingReflex() : Int
    {
        return $this->getSavingFortitudeProficiency() + $this->getAbilityMod('dexterity');
    }

    /**
     * Get Reflex Save Proficiency
     * @return int
     */
    public function getSavingReflexProficiency() : Int
    {
        if ($this->saveSkills['reflex'] > 0) {
            return $this->level + $this->saveSkills['reflex'];
        }

        return 0;
    }

    /**
     * Get Will Save Value
     * @return int
     */
    public function getSavingWill() : Int
    {
        return $this->getSavingFortitudeProficiency() + $this->getAbilityMod('will');
    }

    /**
     * Get Will Save Proficiency
     * @return int
     */
    public function getSavingWillProficiency() : Int
    {
        if ($this->saveSkills['will'] > 0) {
            return $this->level + $this->saveSkills['will'];
        }

        return 0;
    }

    /**
     * Get Class DC
     * @return int
     */
    public function getClassDC() : Int
    {
        return 10 + $this->getClassDCProficiency() + $this->getAbilityMod($this->stat);
    }

    /**
     * Get Class DC Proficiency
     * @return int
     */
    public function getClassDCProficiency() : Int
    {
        if ($this->dc > 0) {
            return $this->level + $this->dc;
        }

        return 0;
    }

    /**
     * Get Class AC
     * @return int
     */
    public function getUnarmoredAC() : Int
    {
        return 10 + $this->getUnarmoredACProficiency() + $this->getAbilityMod('dexterity');
    }

    /**
     * Get Class AC Proficiency
     * @return int
     */
    public function getUnarmoredACProficiency() : Int
    {
        if ($this->armorSkills['unarmored'] > 0) {
            return $this->level + $this->armorSkills['unarmored'];
        }

        return 0;
    }

    /**
     * Get Current HP At Level
     * @return int
     */
    public function getHP() : Int
    {
        return $this->ancestryHP + (($this->getAbilityMod('constitution') + $this->getClassHP()) * $this->level);
    }

    /**
     * Get Class HP Base
     * @return int
     */
    public function getClassHP() : Int
    {
        return ($this->toughness) ? $this->classHP + 1 : $this->classHP;
    }

    /**
     * Get Arcane Spell Proficiency
     * @return int
     */
    public function getArcaneProficiency() : Int
    {
        if ($this->magicSkills['arcane'] > 0) {
            return $this->level + $this->magicSkills['arcane'];
        }

        return 0;
    }

    /**
     * Get Divine Spell Proficiency
     * @return int
     */
    public function getDivineProficiency() : Int
    {
        if ($this->magicSkills['divine'] > 0) {
            return $this->level + $this->magicSkills['divine'];
        }

        return 0;
    }

    /**
     * Get Occult Spell Proficiency
     * @return int
     */
    public function getOccultProficiency() : Int
    {
        if ($this->magicSkills['occult'] > 0) {
            return $this->level + $this->magicSkills['occult'];
        }

        return 0;
    }

    /**
     * Get Primal Spell Proficiency
     * @return int
     */
    public function getPrimalProficiency() : Int
    {
        if ($this->magicSkills['primal'] > 0) {
            return $this->level + $this->magicSkills['primal'];
        }

        return 0;
    }
};
