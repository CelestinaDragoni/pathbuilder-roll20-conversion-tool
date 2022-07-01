<?php

namespace PBR20\Services;

class Conversion {

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
     * @return instanceOf PBR20\Services\Conversion
     */
    public static function getInstance()
    {
        static $instance = false;
        return ($instance) ? $instance : $instance = new Conversion();
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
        // This counter ensures that everything is sorted correctly as it goes in. Insane that roll20 uses id as default sort, but here we are.
        static $counter = 0;

        $id = '-'.str_pad($counter, 5, '0', STR_PAD_LEFT).'-'.md5(uniqid('roll20', true));
        $counter+=1;

        return $id;
    }

    /**
     * Convert Player Into Roll20 VTT Struct
     * @param Player $player
     * @return array
     */
    public function convert(\PBR20\Models\Player $player) : Array
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
        $this->_convertFeatsAncestry($player, $data, $data['character']['attribs']);
        $this->_convertFeatsSkill($player, $data, $data['character']['attribs']);
        $this->_convertFeatsGeneral($player, $data, $data['character']['attribs']);
        $this->_convertFeatsClass($player, $data, $data['character']['attribs']);
        $this->_convertFeatsBonus($player, $data, $data['character']['attribs']);
        $this->_convertMagicTraditions($player, $data, $data['character']['attribs']);
        $this->_convertInnateSpells($player, $data, $data['character']['attribs']);
        $this->_convertFocusSpells($player, $data, $data['character']['attribs']);
        $this->_convertCantripSpells($player, $data, $data['character']['attribs']);
        $this->_convertCoreSpells($player, $data, $data['character']['attribs']);

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
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertPlayerHeader(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
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
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertPlayerDetails(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
        {
            $attrs[] = $this->_createStruct('age', $player->age);
            $attrs[] = $this->_createStruct('gender_pronouns', $player->gender);
        }

        /**
         * Convert Character Sheet Language Data
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertPlayerLanguages(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
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
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertAbilities(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
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
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertSkills(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
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
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertSkillsWeapons(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
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
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertSkillsArmor(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
        {
            $attrs[] = $this->_createStruct('armor_class_unarmored_rank', $player->armorSkills['unarmored']);
            $attrs[] = $this->_createStruct('armor_class_light_rank', $player->armorSkills['light']);
            $attrs[] = $this->_createStruct('armor_class_medium_rank', $player->armorSkills['medium']);
            $attrs[] = $this->_createStruct('armor_class_heavy_rank', $player->armorSkills['heavy']);
        }

        /**
         * Convert Character Sheet Skill Saves Data
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertSkillsSaves(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
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
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertSkillsPerception(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
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
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertClassDC(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
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
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertAC(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
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
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertHP(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
        {
            $attrs[] = $this->_createStruct('hit_points', $player->getHP());
            $attrs[] = $this->_createStruct('hit_points_max', $player->getHP());
            $attrs[] = $this->_createStruct('hit_points_class', $player->getClassHP());
            $attrs[] = $this->_createStruct('hit_points_ancestry', $player->ancestryHP);
            $attrs[] = $this->_createStruct('hit_points_notes', $player->toughness ? 'Toughness Figured Into Class HP Attribute' : '');
        }

        /**
         * Convert Character Speed Data
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertSpeed(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
        {
            $attrs[] = $this->_createStruct('speed', $player->speed + $player->speedBonus);
        }

        /**
         * Convert Ancestry Feats
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertFeatsAncestry(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
        {
            if (!empty($player->heritageAncestoryFeats)) {
                foreach ($player->heritageAncestoryFeats as $feat) {
                    $id = $this->_uuid();
                    $attrs[] = $this->_createStruct("repeating_feat-ancestry_{$id}_feat_ancestry", $feat['name']);
                    $attrs[] = $this->_createStruct("repeating_feat-ancestry_{$id}_feat_ancestry_type", '');
                    $attrs[] = $this->_createStruct("repeating_feat-ancestry_{$id}_feat_ancestry_level", $feat['level']);
                    $attrs[] = $this->_createStruct("repeating_feat-ancestry_{$id}_feat_ancestry_traits", implode(', ', $feat['traits']));
                    $attrs[] = $this->_createStruct("repeating_feat-ancestry_{$id}_feat_ancestry_prerequisites", $feat['prereq']);
                    $attrs[] = $this->_createStruct("repeating_feat-ancestry_{$id}_feat_ancestry_action", $feat['economy']);
                    $attrs[] = $this->_createStruct("repeating_feat-ancestry_{$id}_feat_ancestry_trigger", $feat['trigger']);
                    $attrs[] = $this->_createStruct("repeating_feat-ancestry_{$id}_feat_ancestry_requirements", '');
                    $attrs[] = $this->_createStruct("repeating_feat-ancestry_{$id}_feat_ancestry_frequency", '');
                    $attrs[] = $this->_createStruct("repeating_feat-ancestry_{$id}_feat_ancestry_benefits", '');
                    $attrs[] = $this->_createStruct("repeating_feat-ancestry_{$id}_feat_ancestry_notes", $feat['description']."\n\n".$feat['url']);
                    $attrs[] = $this->_createStruct("repeating_feat-ancestry_{$id}_toggles", 'display,');
                }
            }
        }

        /**
         * Convert Skill Feats
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertFeatsSkill(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
        {
            if (!empty($player->skillFeats)) {
                foreach ($player->skillFeats as $feat) {
                    $id = $this->_uuid();
                    $attrs[] = $this->_createStruct("repeating_feat-skill_{$id}_feat_skill", $feat['name']);
                    $attrs[] = $this->_createStruct("repeating_feat-skill_{$id}_feat_skill_level", $feat['level']);
                    $attrs[] = $this->_createStruct("repeating_feat-skill_{$id}_feat_skill_skill", '');
                    $attrs[] = $this->_createStruct("repeating_feat-skill_{$id}_feat_skill_traits", implode(', ', $feat['traits']));
                    $attrs[] = $this->_createStruct("repeating_feat-skill_{$id}_feat_skill_prerequisites", $feat['prereq']);
                    $attrs[] = $this->_createStruct("repeating_feat-skill_{$id}_feat_skill_action", $feat['economy']);
                    $attrs[] = $this->_createStruct("repeating_feat-skill_{$id}_feat_skill_trigger", $feat['trigger']);
                    $attrs[] = $this->_createStruct("repeating_feat-skill_{$id}_feat_skill_requirements", '');
                    $attrs[] = $this->_createStruct("repeating_feat-skill_{$id}_feat_skill_frequency", '');
                    $attrs[] = $this->_createStruct("repeating_feat-skill_{$id}_feat_skill_benefits", '');
                    $attrs[] = $this->_createStruct("repeating_feat-skill_{$id}_feat_skill_notes", $feat['description']."\n\n".$feat['url']);
                    $attrs[] = $this->_createStruct("repeating_feat-skill_{$id}_toggles", 'display,');
                }
            }
        }

        /**
         * Convert General Feats
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertFeatsGeneral(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
        {
            if (!empty($player->generalFeats)) {
                foreach ($player->generalFeats as $feat) {
                    $id = $this->_uuid();
                    $attrs[] = $this->_createStruct("repeating_feat-general_{$id}_feat_general", $feat['name']);
                    $attrs[] = $this->_createStruct("repeating_feat-general_{$id}_feat_general_level", $feat['level']);
                    $attrs[] = $this->_createStruct("repeating_feat-general_{$id}_feat_general_traits", implode(', ', $feat['traits']));
                    $attrs[] = $this->_createStruct("repeating_feat-general_{$id}_feat_general_prerequisites", $feat['prereq']);
                    $attrs[] = $this->_createStruct("repeating_feat-general_{$id}_feat_general_action", $feat['economy']);
                    $attrs[] = $this->_createStruct("repeating_feat-general_{$id}_feat_general_trigger", $feat['trigger']);
                    $attrs[] = $this->_createStruct("repeating_feat-general_{$id}_feat_general_requirements", '');
                    $attrs[] = $this->_createStruct("repeating_feat-general_{$id}_feat_general_frequency", '');
                    $attrs[] = $this->_createStruct("repeating_feat-general_{$id}_feat_general_benefits", '');
                    $attrs[] = $this->_createStruct("repeating_feat-general_{$id}_feat_general_notes", $feat['description']."\n\n".$feat['url']);
                    $attrs[] = $this->_createStruct("repeating_feat-general_{$id}_toggles", 'display,');
                }
            }
        }

        /**
         * Convert Class Feats
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertFeatsClass(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
        {
            if (!empty($player->generalFeats)) {
                foreach ($player->generalFeats as $feat) {
                    $id = $this->_uuid();
                    $attrs[] = $this->_createStruct("repeating_feat-class_{$id}_feat_class", $feat['name']);
                    $attrs[] = $this->_createStruct("repeating_feat-class_{$id}_feat_class_level", $feat['level']);
                    $attrs[] = $this->_createStruct("repeating_feat-class_{$id}_feat_class_traits", implode(', ', $feat['traits']));
                    $attrs[] = $this->_createStruct("repeating_feat-class_{$id}_feat_class_prerequisites", $feat['prereq']);
                    $attrs[] = $this->_createStruct("repeating_feat-class_{$id}_feat_class_action", $feat['economy']);
                    $attrs[] = $this->_createStruct("repeating_feat-class_{$id}_feat_class_trigger", $feat['trigger']);
                    $attrs[] = $this->_createStruct("repeating_feat-class_{$id}_feat_class_requirements", '');
                    $attrs[] = $this->_createStruct("repeating_feat-class_{$id}_feat_class_frequency", '');
                    $attrs[] = $this->_createStruct("repeating_feat-class_{$id}_feat_class_benefits", '');
                    $attrs[] = $this->_createStruct("repeating_feat-class_{$id}_feat_class_notes", $feat['description']."\n\n".$feat['url']);
                    $attrs[] = $this->_createStruct("repeating_feat-class_{$id}_toggles", 'display,');
                }
            }
        }

        /**
         * Convert Bonus Feats
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertFeatsBonus(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
        {
            if (!empty($player->generalFeats)) {
                foreach ($player->generalFeats as $feat) {
                    $id = $this->_uuid();
                    $attrs[] = $this->_createStruct("repeating_feat-bonus_{$id}_feat_bonus", $feat['name']);
                    $attrs[] = $this->_createStruct("repeating_feat-bonus_{$id}_feat_bonus_level", $feat['level']);
                    $attrs[] = $this->_createStruct("repeating_feat-bonus_{$id}_feat_bonus_traits", implode(', ', $feat['traits']));
                    $attrs[] = $this->_createStruct("repeating_feat-bonus_{$id}_feat_bonus_prerequisites", $feat['prereq']);
                    $attrs[] = $this->_createStruct("repeating_feat-bonus_{$id}_feat_bonus_action", $feat['economy']);
                    $attrs[] = $this->_createStruct("repeating_feat-bonus_{$id}_feat_bonus_trigger", $feat['trigger']);
                    $attrs[] = $this->_createStruct("repeating_feat-bonus_{$id}_feat_bonus_requirements", '');
                    $attrs[] = $this->_createStruct("repeating_feat-bonus_{$id}_feat_bonus_frequency", '');
                    $attrs[] = $this->_createStruct("repeating_feat-bonus_{$id}_feat_bonus_benefits", '');
                    $attrs[] = $this->_createStruct("repeating_feat-bonus_{$id}_feat_bonus_notes", $feat['description']."\n\n".$feat['url']);
                    $attrs[] = $this->_createStruct("repeating_feat-bonus_{$id}_toggles", 'display,');
                }
            }
        }


        /**
         * Convert Magic Traditions
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertMagicTraditions(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
        {
            $attrs[] = $this->_createStruct('spellcaster_prepared', $player->prepared ? 'prepared' : '');
            $attrs[] = $this->_createStruct('spellcaster_spontaneous', $player->spontaneous ? 'spontaneous' : '');
            $attrs[] = $this->_createStruct('magic_tradition_arcane_rank', $player->magicSkills['arcane']);
            $attrs[] = $this->_createStruct('magic_tradition_arcane_proficiency', $player->getArcaneProficiency());
            $attrs[] = $this->_createStruct('magic_tradition_primal_rank', $player->magicSkills['primal']);
            $attrs[] = $this->_createStruct('magic_tradition_primal_proficiency', $player->getPrimalProficiency());
            $attrs[] = $this->_createStruct('magic_tradition_occult_rank', $player->magicSkills['occult']);
            $attrs[] = $this->_createStruct('magic_tradition_occult_proficiency', $player->getOccultProficiency());
            $attrs[] = $this->_createStruct('magic_tradition_divine_rank', $player->magicSkills['divine']);
            $attrs[] = $this->_createStruct('magic_tradition_divine_proficiency', $player->getDivineProficiency());
            $attrs[] = $this->_createStruct('focus_points', $player->points, $player->points);
            $attrs[] = $this->_createStruct('cantrips_per_day', $player->slots[0], $player->slots[0]);

            for($i = 1; $i < 11; $i++)
            {
                $attrs[] = $this->_createStruct("level_{$i}_per_day", $player->slots[$i], $player->slots[$i]);
            }
        }

        /**
         * Convert Innate Spells
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertInnateSpells(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
        {
            if (!empty($player->innateSpells)) {
                foreach ($player->innateSpells as $spell) {
                    $id = $this->_uuid();
                    $attrs[] = $this->_createStruct("repeating_spellinnate_{$id}_name", $spell['name']);
                    $attrs[] = $this->_createStruct("repeating_spellinnate_{$id}_spelllevel", $spell['level']);
                    $attrs[] = $this->_createStruct("repeating_spellinnate_{$id}_current_level", $spell['level']);
                    $attrs[] = $this->_createStruct("repeating_spellinnate_{$id}_traits", implode(', ', $spell['traits']));
                    $attrs[] = $this->_createStruct("repeating_spellinnate_{$id}_cast", implode(', ', $spell['components']));
                    $attrs[] = $this->_createStruct("repeating_spellinnate_{$id}_target", $spell['target']);
                    $attrs[] = $this->_createStruct("repeating_spellinnate_{$id}_duration", $spell['duration']);
                    $attrs[] = $this->_createStruct("repeating_spellinnate_{$id}_range", $spell['range']);
                    $attrs[] = $this->_createStruct("repeating_spellinnate_{$id}_school", strtolower($spell['school']));
                    $attrs[] = $this->_createStruct("repeating_spellinnate_{$id}_cast_actions", $spell['economy']);
                    $attrs[] = $this->_createStruct("repeating_spellinnate_{$id}_description", $spell['description']."\n\n".$spell['url']);
                    $attrs[] = $this->_createStruct("repeating_spellinnate_{$id}_uses", 1, 1);
                    $attrs[] = $this->_createStruct("repeating_spellinnate_{$id}_toggles", 'display,');
                }
            }
        }

        /**
         * Convert Focus Spells
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertFocusSpells(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
        {
            if (!empty($player->focusSpells)) {
                foreach ($player->focusSpells as $spell) {
                    $id = $this->_uuid();
                    $attrs[] = $this->_createStruct("repeating_spellfocus_{$id}_name", $spell['name']);
                    $attrs[] = $this->_createStruct("repeating_spellfocus_{$id}_spelllevel", $spell['level']);
                    $attrs[] = $this->_createStruct("repeating_spellfocus_{$id}_current_level", $spell['level']);
                    $attrs[] = $this->_createStruct("repeating_spellfocus_{$id}_traits", implode(', ', $spell['traits']));
                    $attrs[] = $this->_createStruct("repeating_spellfocus_{$id}_cast", implode(', ', $spell['components']));
                    $attrs[] = $this->_createStruct("repeating_spellfocus_{$id}_target", $spell['target']);
                    $attrs[] = $this->_createStruct("repeating_spellfocus_{$id}_duration", $spell['duration']);
                    $attrs[] = $this->_createStruct("repeating_spellfocus_{$id}_range", $spell['range']);
                    $attrs[] = $this->_createStruct("repeating_spellfocus_{$id}_school", strtolower($spell['school']));
                    $attrs[] = $this->_createStruct("repeating_spellfocus_{$id}_cast_actions", $spell['economy']);
                    $attrs[] = $this->_createStruct("repeating_spellfocus_{$id}_description", $spell['description']."\n\n".$spell['url']);
                    $attrs[] = $this->_createStruct("repeating_spellfocus_{$id}_uses", 1, 1);
                    $attrs[] = $this->_createStruct("repeating_spellfocus_{$id}_toggles", 'display,');
                }
            }
        }

        /**
         * Convert Cantrip Spells
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertCantripSpells(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
        {
            if (!empty($player->coreSpells[0])) {
                foreach ($player->coreSpells[0] as $spell) {
                    $id = $this->_uuid();
                    $attrs[] = $this->_createStruct("repeating_cantrip_{$id}_name", $spell['name']);
                    $attrs[] = $this->_createStruct("repeating_cantrip_{$id}_spelllevel", $spell['level']);
                    $attrs[] = $this->_createStruct("repeating_cantrip_{$id}_current_level", $spell['level']);
                    $attrs[] = $this->_createStruct("repeating_cantrip_{$id}_traits", implode(', ', $spell['traits']));
                    $attrs[] = $this->_createStruct("repeating_cantrip_{$id}_cast", implode(', ', $spell['components']));
                    $attrs[] = $this->_createStruct("repeating_cantrip_{$id}_target", $spell['target']);
                    $attrs[] = $this->_createStruct("repeating_cantrip_{$id}_duration", $spell['duration']);
                    $attrs[] = $this->_createStruct("repeating_cantrip_{$id}_range", $spell['range']);
                    $attrs[] = $this->_createStruct("repeating_cantrip_{$id}_school", strtolower($spell['school']));
                    $attrs[] = $this->_createStruct("repeating_cantrip_{$id}_cast_actions", $spell['economy']);
                    $attrs[] = $this->_createStruct("repeating_cantrip_{$id}_description", $spell['description']."\n\n".$spell['url']);
                    $attrs[] = $this->_createStruct("repeating_cantrip_{$id}_uses", 1, 1);
                    $attrs[] = $this->_createStruct("repeating_cantrip_{$id}_toggles", 'display,');
                }
            }
        }

        /**
         * Convert Core Spells
         * @param Player $player
         * @param array $root
         * @param array $attrs
         * @return void
         */
        private function _convertCoreSpells(\PBR20\Models\Player $player, Array &$root, Array &$attrs) : Void
        {
            if (!empty($player->coreSpells)) {
                foreach ($player->coreSpells as $level => $spells) {
                    if ($level == 0) {
                        continue;
                    }

                    if (!empty($spells)) {
                        foreach ($spells as $spell) {
                            $id = $this->_uuid();
                            $attrs[] = $this->_createStruct("repeating_normalspells_{$id}_name", $spell['name']);
                            $attrs[] = $this->_createStruct("repeating_normalspells_{$id}_spelllevel", $spell['level']);
                            $attrs[] = $this->_createStruct("repeating_normalspells_{$id}_current_level", $level);
                            $attrs[] = $this->_createStruct("repeating_normalspells_{$id}_traits", implode(', ', $spell['traits']));
                            $attrs[] = $this->_createStruct("repeating_normalspells_{$id}_cast", implode(', ', $spell['components']));
                            $attrs[] = $this->_createStruct("repeating_normalspells_{$id}_target", $spell['target']);
                            $attrs[] = $this->_createStruct("repeating_normalspells_{$id}_duration", $spell['duration']);
                            $attrs[] = $this->_createStruct("repeating_normalspells_{$id}_range", $spell['range']);
                            $attrs[] = $this->_createStruct("repeating_normalspells_{$id}_school", strtolower($spell['school']));
                            $attrs[] = $this->_createStruct("repeating_normalspells_{$id}_cast_actions", $spell['economy']);
                            $attrs[] = $this->_createStruct("repeating_normalspells_{$id}_description", $spell['description']."\n\n".$spell['url']);
                            $attrs[] = $this->_createStruct("repeating_normalspells_{$id}_uses", 1, 1);
                            $attrs[] = $this->_createStruct("repeating_normalspells_{$id}_toggles", 'display,');
                        }
                    }
                }
            }
        }
}

