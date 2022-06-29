<?php

class Roll20ConversionService {

    /** Roll20 Rank 'Display' Character **/
    private $_rank = [
        0 => 'U',
        2 => 'T',
        4 => 'E',
        6 => 'M',
        8 => 'L',
    ];

    /** Default Roll20 VTT Import/Export Schema **/
    private $_schema = [
      'schema_version'    => 3,
      'type'              => 'character',
      'character' => [
          'name'              => '',
          'bio'               => '',
          'avatar'            => '',
          'gmnotes'           => '',
          'defaulttoken'      => '',
          'tags'              => '[]',
          'controlledby'      => '',
          'inplayerjournals'  => '',
          'attribs'           => [
              [
                  'name'    => 'version_character',
                  'current' => 4.5,
                  'max'     => '',
                  'id'      => '',
              ],
          ],
          'abilities' => [],
      ],
    ];

    /**
     * Singleton Constructor
     * @return instanceOf Roll20ConversionService
     */
    public static function getInstance()
    {
        static $instance = false;
        return ($instance) ? $instance : $instance = new Roll20ConversionService();
    }

    /**
     * Generate a Unique ID
     * Developer Note:
     *      Roll20 Uses Some Weird UUID System. Replication of its jank isn't required.
     *      Also this isn't cryptography, this is good enough. Don't @ me on md5 being used here.
     * @return string
     */
    private function _uuid() : String
    {
        return md5(uniqid('roll20', true));
    }

    /**
     * Convert PathbuilderPlayer Into Roll20 VTT Struct
     * @param PathbuilderPlayer $player
     * @return array
     */
    public function convert(PathbuilderPlayer $player) : Array
    {
        // Clone Our Schema
        $data = $this->_schema;

        // Basic Bitch Stuff
        $data['character']['name'] = $player->name;
        $data['character']['attribs'][0]['id'] = $this->_uuid();

        // Bring On The Pain
        $this->_convertPlayerHeader($player, $data, $data['character']['attribs']);
        $this->_convertPlayerDetails($player, $data, $data['character']['attribs']);
        $this->_convertPlayerLanguages($player, $data, $data['character']['attribs']);
        $this->_convertAbilities($player, $data, $data['character']['attribs']);
        $this->_convertSkills($player, $data, $data['character']['attribs']);
        $this->_convertSkillsWeapons($player, $data, $data['character']['attribs']);
        $this->_convertSkillsArmor($player, $data, $data['character']['attribs']);
        $this->_convertSkillsSaves($player, $data, $data['character']['attribs']);
        $this->_convertSkillsPerception($player, $data, $data['character']['attribs']);
        $this->_convertClassDC($player, $data, $data['character']['attribs']);
        $this->_convertAC($player, $data, $data['character']['attribs']);
        $this->_convertHP($player, $data, $data['character']['attribs']);
        $this->_convertSpeed($player, $data, $data['character']['attribs']);

        return $data;
    }

        /**
         * Creates basic struct used for attribs
         * @param string $name
         * @param mixed $current
         * @param mixed max
         * @param string $id
         * @return array
         */
        private function _createStruct($name, $current, $max = '', $id = '') : Array
        {
            if (empty($id)) {
                $id = $this->_uuid();
            }

            return [
                'name'      => $name,
                'current'   => $current,
                'max'       => $max,
                'id'        => $id,
            ];
        }

        /**
         * Convert Character Sheet Header Data
         * @param PathbuilderPlayer $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertPlayerHeader(PathbuilderPlayer $player, Array &$root, Array &$attrs) : Void
        {
            $attrs[] = $this->_createStruct('name', $player->name);
            $attrs[] = $this->_createStruct('ancestry_heritage', sprintf('%s / %s', $player->ancestry, $player->heritage));
            $attrs[] = $this->_createStruct('deity', $player->deity);
            $attrs[] = $this->_createStruct('class', $player->class);
            $attrs[] = $this->_createStruct('background', $player->background);
            $attrs[] = $this->_createStruct('alignment', $player->alignment);
            $attrs[] = $this->_createStruct('level', $player->level);
        }

        /**
         * Convert Character Sheet Details Data
         * @param PathbuilderPlayer $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertPlayerDetails(PathbuilderPlayer $player, Array &$root, Array &$attrs) : Void
        {
            $attrs[] = $this->_createStruct('age', $player->age);
            $attrs[] = $this->_createStruct('gender_pronouns', $player->gender);
        }

        /**
         * Convert Character Sheet Language Data
         * @param PathbuilderPlayer $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertPlayerLanguages(PathbuilderPlayer $player, Array &$root, Array &$attrs) : Void
        {
            foreach ($player->languages as $lang) {
                $id = $this->_uuid();

                $attrs[] = $this->_createStruct(
                    sprintf('repeating_languages_%s_language', $id),
                    $lang,
                );

                $attrs[] = $this->_createStruct(
                    sprintf('repeating_languages_%s_toggles', $id),
                    'display,',
                );
            }
        }

        /**
         * Convert Character Sheet Abilities Data
         * @param PathbuilderPlayer $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertAbilities(PathbuilderPlayer $player, Array &$root, Array &$attrs) : Void
        {
            foreach ($player->abilities as $ability => $value) {
                $attrs[] = $this->_createStruct($ability, $value);

                $attrs[] = $this->_createStruct(
                    sprintf('%s_score', $ability),
                    (String)$value,
                );

                $attrs[] = $this->_createStruct(
                    sprintf('%s_modifier', $ability),
                    $player->getAbilityMod($ability),
                );

                $attrs[] = $this->_createStruct(
                    sprintf('%s_modifier_half', $ability),
                    $player->getAbilityModHalf($ability),
                );
            }
        }

        /**
         * Convert Character Sheet Skills Data
         * @param PathbuilderPlayer $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertSkills(PathbuilderPlayer $player, Array &$root, Array &$attrs) : Void
        {
            foreach ($player->skills as $skill => $rank) {
                $attrs[] = $this->_createStruct(
                    $skill,
                    $player->getSkillTotal($skill),
                );

                $attrs[] = $this->_createStruct(
                    sprintf('%s_proficiency_display', $skill),
                    $this->_rank[$rank],
                );

                $attrs[] = $this->_createStruct(
                    sprintf('%s_proficiency', $skill),
                    $player->getSkillProficiency($skill),
                );

                $attrs[] = $this->_createStruct(
                    sprintf('%s_item', $skill),
                    $player->getSkillImprov($skill),
                );

                $attrs[] = $this->_createStruct(
                    sprintf('%s_temporary', $skill),
                    '0',
                );

                $attrs[] = $this->_createStruct(
                    sprintf('%s_armor', $skill),
                    '0',
                );

                $attrs[] = $this->_createStruct(
                    sprintf('%s_rank', $skill),
                    $rank,
                );

                $attrs[] = $this->_createStruct(
                    sprintf('%s_ability', $skill),
                    $player->getSkillMod($skill),
                );

                $attrs[] = $this->_createStruct(
                    sprintf('%s_ability_select', $skill),
                    sprintf('@{%s_modifier}', $player->skillsMod[$skill]),
                );
            }
        }

        /**
         * Convert Character Skill Weapons Data
         * @param PathbuilderPlayer $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertSkillsWeapons(PathbuilderPlayer $player, Array &$root, Array &$attrs) : Void
        {
            $attrs[] = $this->_createStruct('weapon_proficiencies_simple_rank', $player->weaponSkills['simple']);
            $attrs[] = $this->_createStruct('weapon_proficiencies_martial_rank', $player->weaponSkills['martial']);

            if ($player->weaponSkills['unarmed'] > 0)
            {
                $id = $this->_uuid();

                $attrs[] = $this->_createStruct(
                    sprintf('repeating_weapon-proficiencies_%s_weapon_proficiencies_other', $id),
                    'Unarmed',
                );

                $attrs[] = $this->_createStruct(
                    sprintf('repeating_weapon-proficiencies_%s_weapon_proficiencies_other_rank', $id),
                    (String)$player->weaponSkills['unarmed'],
                );
            }
        }

        /**
         * Convert Character Skill Armor Data
         * @param PathbuilderPlayer $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertSkillsArmor(PathbuilderPlayer $player, Array &$root, Array &$attrs) : Void
        {
            $attrs[] = $this->_createStruct('armor_class_unarmored_rank', $player->armorSkills['unarmored']);
            $attrs[] = $this->_createStruct('armor_class_light_rank', $player->armorSkills['light']);
            $attrs[] = $this->_createStruct('armor_class_medium_rank', $player->armorSkills['medium']);
            $attrs[] = $this->_createStruct('armor_class_heavy_rank', $player->armorSkills['heavy']);
        }

        /**
         * Convert Character Sheet Skill Saves Data
         * @param PathbuilderPlayer $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertSkillsSaves(PathbuilderPlayer $player, Array &$root, Array &$attrs) : Void
        {
            $attrs[] = $this->_createStruct('saving_throws_fortitude', $player->getSavingFortitude());
            $attrs[] = $this->_createStruct('saving_throws_fortitude_proficiency', $player->getSavingFortitudeProficiency());
            $attrs[] = $this->_createStruct('saving_throws_fortitude_proficiency_display', $this->_rank[$player->saveSkills['fortitude']]);
            $attrs[] = $this->_createStruct('saving_throws_fortitude_rank', $player->saveSkills['fortitude']);
            $attrs[] = $this->_createStruct('saving_throws_fortitude_ability_select', '@{constitution_modifier}');

            $attrs[] = $this->_createStruct('saving_throws_reflex', $player->getSavingReflex());
            $attrs[] = $this->_createStruct('saving_throws_reflex_proficiency', $player->getSavingReflexProficiency());
            $attrs[] = $this->_createStruct('saving_throws_reflex_proficiency_display', $this->_rank[$player->saveSkills['reflex']]);
            $attrs[] = $this->_createStruct('saving_throws_reflex_rank', $player->saveSkills['reflex']);
            $attrs[] = $this->_createStruct('saving_throws_reflex_ability_select', '@{dexterity_modifier}');

            $attrs[] = $this->_createStruct('saving_throws_will', $player->getSavingWill());
            $attrs[] = $this->_createStruct('saving_throws_will_proficiency', $player->getSavingWillProficiency());
            $attrs[] = $this->_createStruct('saving_throws_will_proficiency_display', $this->_rank[$player->saveSkills['will']]);
            $attrs[] = $this->_createStruct('saving_throws_will_rank', $player->saveSkills['will']);
            $attrs[] = $this->_createStruct('saving_throws_will_ability_select', '@{wisdom_modifier}');
        }

        /**
         * Convert Character Sheet Preception Data
         * @param PathbuilderPlayer $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertSkillsPerception(PathbuilderPlayer $player, Array &$root, Array &$attrs) : Void
        {
            $attrs[] = $this->_createStruct('perception', $player->getPerceptionTotal());
            $attrs[] = $this->_createStruct('perception_proficiency_display', $this->_rank[$player->perception]);
            $attrs[] = $this->_createStruct('perception_proficiency', $player->getPreceptionProficiency());
            $attrs[] = $this->_createStruct('perception_item', $player->getPerceptionIncredibleInitiative());
            $attrs[] = $this->_createStruct('perception_temporary','0');
            $attrs[] = $this->_createStruct('perception_rank', $player->perception);
        }

       /**
         * Convert Character Sheet Class DC Data
         * @param PathbuilderPlayer $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertClassDC(PathbuilderPlayer $player, Array &$root, Array &$attrs) : Void
        {
            $attrs[] = $this->_createStruct('class_dc', $player->getClassDC());
            $attrs[] = $this->_createStruct('class_dc_rank', $player->dc);
            $attrs[] = $this->_createStruct('class_dc_proficiency_display', $this->_rank[$player->dc]);
            $attrs[] = $this->_createStruct('class_dc_proficiency', $player->getClassDCProficiency());
            $attrs[] = $this->_createStruct('class_dc_key_ability', $player->getAbilityMod($player->stat));
            $attrs[] = $this->_createStruct(
                'class_dc_key_ability_select',
                sprintf('@{%s_modifier}', $player->stat)
            );
        }

        /**
         * Convert Character Sheet AC Data
         * @param PathbuilderPlayer $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertAC(PathbuilderPlayer $player, Array &$root, Array &$attrs) : Void
        {
            $attrs[] = $this->_createStruct('ac', $player->getUnarmoredAC());
            $attrs[] = $this->_createStruct('armor_class', $player->getUnarmoredAC());
            $attrs[] = $this->_createStruct('armor_class_ability', $player->getAbilityMod('dexterity'));
            $attrs[] = $this->_createStruct('armor_class_rank', $player->armorSkills['unarmored']);
            $attrs[] = $this->_createStruct('armor_class_proficiency', $player->getUnarmoredACProficiency());
            $attrs[] = $this->_createStruct('armor_class_proficiency_display', $this->_rank[$player->armorSkills['unarmored']]);
            $attrs[] = $this->_createStruct('armor_class_ability_select', '@{dexterity_modifier}');
        }

        /**
         * Convert Character Sheet HP Data
         * @param PathbuilderPlayer $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertHP(PathbuilderPlayer $player, Array &$root, Array &$attrs) : Void
        {
            $attrs[] = $this->_createStruct('hit_points', $player->getHP());
            $attrs[] = $this->_createStruct('hit_points_max', $player->getHP());
            $attrs[] = $this->_createStruct('hit_points_class', $player->getClassHP());
            $attrs[] = $this->_createStruct('hit_points_ancestry', $player->ancestryHP);
            $attrs[] = $this->_createStruct('hit_points_notes', $player->toughness ? 'Toughness Figured Into Class HP Attribute' : '');
        }

        /**
         * Convert Character Speed Data
         * @param PathbuilderPlayer $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertSpeed(PathbuilderPlayer $player, Array &$root, Array &$attrs) : Void
        {
            $attrs[] = $this->_createStruct('speed', $player->speed + $player->speedBonus);
        }
}
