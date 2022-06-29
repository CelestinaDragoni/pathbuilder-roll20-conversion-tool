<?php

class PathbuilderPlayer {

    /** General Profile Crap **/
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
    public $stat        = 'str';
    public $dc          = 0;

    /** Feats **/
    public $heritageAncestoryFeats  = [];
    public $classFeats              = [];
    public $skillFeats              = [];
    public $generalFeats            = [];
    public $bonusFeats              = [];

    /**
     * Constructor
     * @return void
     */
    public function __construct(String $pathbuilderData)
    {
        $data = json_decode($pathbuilderData, true);
        if (empty($data)) {
            throw New Exception('Invalid Pathbuilder Data');
        }

        // General Profile Crap
        $this->name         = $data['build']['name'];
        $this->class        = $data['build']['class'];
        $this->level        = $data['build']['level'];
        $this->ancestry     = $data['build']['ancestry'];
        $this->heritage     = $data['build']['heritage'];
        $this->background   = $data['build']['background'];
        $this->alignment    = $data['build']['alignment'];
        $this->gender       = $data['build']['gender'];
        $this->age          = $data['build']['age'];
        $this->deity        = $data['build']['deity'];
        $this->languages    = $data['build']['languages'];

        // HP and Speed Stats
        $this->ancestryHP = $data['build']['attributes']['ancestryhp'];
        $this->classHP    = $data['build']['attributes']['classhp'];
        $this->bonusHP    = $data['build']['attributes']['bonushp'];
        $this->speed      = $data['build']['attributes']['speed'];
        $this->speedBonus = $data['build']['attributes']['speedBonus'];

        // Attribute Map
        $map = [
            'str' => 'strength',
            'dex' => 'dexterity',
            'con' => 'constitution',
            'int' => 'intelligence',
            'wis' => 'wisdom',
            'cha' => 'charisma',
        ];

        // Base Stats
        foreach ($map as $short => $long) {
            $this->abilities[$long] = $data['build']['abilities'][$short];
        }

        // Class Stat
        $this->stat = $map[$data['build']['keyability']];
        $this->dc   = $data['build']['proficiencies']['classDC'];

        // Skills
        $this->skills = [
            'acrobatics'      => $data['build']['proficiencies']['acrobatics'],
            'arcana'          => $data['build']['proficiencies']['arcana'],
            'athletics'       => $data['build']['proficiencies']['athletics'],
            'crafting'        => $data['build']['proficiencies']['crafting'],
            'deception'       => $data['build']['proficiencies']['deception'],
            'diplomacy'       => $data['build']['proficiencies']['diplomacy'],
            'intimidation'    => $data['build']['proficiencies']['intimidation'],
            'medicine'        => $data['build']['proficiencies']['medicine'],
            'nature'          => $data['build']['proficiencies']['nature'],
            'occultism'       => $data['build']['proficiencies']['occultism'],
            'performance'     => $data['build']['proficiencies']['performance'],
            'religion'        => $data['build']['proficiencies']['religion'],
            'society'         => $data['build']['proficiencies']['society'],
            'stealth'         => $data['build']['proficiencies']['stealth'],
            'survival'        => $data['build']['proficiencies']['survival'],
            'thievery'        => $data['build']['proficiencies']['thievery'],
        ];

        $this->armorSkills = [
            'unarmored'     => $data['build']['proficiencies']['unarmored'],
            'light'         => $data['build']['proficiencies']['light'],
            'medium'        => $data['build']['proficiencies']['medium'],
            'heavy'         => $data['build']['proficiencies']['heavy'],
        ];

        $this->weaponSkills = [
            'unarmed'       => $data['build']['proficiencies']['unarmed'],
            'simple'        => $data['build']['proficiencies']['simple'],
            'martial'       => $data['build']['proficiencies']['martial'],
        ];

        $this->magicSkills = [
            'arcane'        => $data['build']['proficiencies']['castingArcane'],
            'divine'        => $data['build']['proficiencies']['castingDivine'],
            'occult'        => $data['build']['proficiencies']['castingOccult'],
            'primal'        => $data['build']['proficiencies']['castingPrimal'],
        ];

        $this->saveSkills = [
            'fortitude'     => $data['build']['proficiencies']['fortitude'],
            'reflex'        => $data['build']['proficiencies']['reflex'],
            'will'          => $data['build']['proficiencies']['will'],
        ];

        $this->perception = $data['build']['proficiencies']['perception'];

        // Sort Out Feats
        if (!empty($data['build']['feats']) && is_array($data['build']['feats'])) {
            foreach ($data['build']['feats'] as $feat) {

                // Check Feat For Special Flags
                if ($feat[0] == 'Untrained Improvisation') {
                    $this->improvisation = true;
                } else if ($feat[0] == 'Incredible Initiative') {
                    $this->initiative = true;
                } else if ($feat[0] == 'Toughness') {
                    $this->toughness = true;
                }

                // Type Validate Struct
                if (empty($feat[2])) {
                    $feat[2] = "Bonus";
                }
                if (empty($feat[3])) {
                    $feat[3] = 1;
                }

                // Feat Struct
                $struct = [
                    'title' => $feat[0],
                    'level' => $feat[3],
                ];

                // Insert Feat Into Correct Struct
                if ($feat[2] == "Heritage" || $feat[2] == "Ancestry Feat") {
                    $this->heritageAncestoryFeats[] = $struct;
                } else if ($feat[2] == "Skill Feat") {
                    $this->skillFeats[] = $struct;
                } else if ($feat[2] == "Class Feat") {
                    $this->classFeats[] = $struct;
                } else if ($feat[2] == "General Feat") {
                    $this->generalFeats[] = $struct;
                } else {
                    $this->bonusFeats[] = $struct;
                }
            }
        }
    }

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
};
