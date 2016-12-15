<?php

namespace Solspace\Addons\Fbc\Model;

use EllisLab\ExpressionEngine\Model\Member\MemberGroup;
use EllisLab\ExpressionEngine\Service\Model\Model;
use EllisLab\ExpressionEngine\Service\Validation\Result;

class Preference extends Model
{
    protected static $_primary_key = 'fbc_preference_id';
    protected static $_table_name  = 'fbc_preferences';

    protected $fbc_preference_id;
    protected $fbc_preference_name;
    protected $fbc_preference_value;
    protected $site_id;

    /**
     * List of all default preferences and their validation rules
     *
     * @var array
     */
    private $defaultPreferences = array(
        'app_id'                          => array(
            'type'     => 'text',
            'default'  => '',
            'validate' => 'required',
        ),
        'app_secret'                      => array(
            'type'     => 'text',
            'default'  => '',
            'validate' => 'required',
        ),
        'eligible_member_groups'          => array(
            'type'     => 'checkbox',
            'default'  => '',
            'validate' => 'required',
        ),
        'enable_passive_registration'     => array(
            'type'     => 'yes_no',
            'default'  => 'n',
            'validate' => 'required|enum[y,n]',
        ),
        'member_group'                    => array(
            'type'     => 'select',
            'choices'  => array(),
            'default'  => '',
            'validate' => 'required',
        ),
        'confirm_before_syncing_accounts' => array(
            'type'     => 'yes_no',
            'default'  => 'n',
            'validate' => 'required|enum[y,n]',
        ),
    );

    /**
     * Populate ee()->config with FBC preferences
     */
    public static function updateConfigWithData()
    {
        $existingAppId = ee()->config->item('fbc_app_id');
        $existingAppSecret = ee()->config->item('fbc_app_secret');
        if (!$existingAppId || !$existingAppSecret) {
            $existingPreferences = ee('Model')
                ->get('fbc:Preference')
                ->filter('site_id', ee()->config->item('site_id'))
                ->all()
                ->getDictionary(
                    'fbc_preference_name',
                    'fbc_preference_value'
                );

            foreach ($existingPreferences as $name => $value) {
                ee()->config->set_item("fbc_$name", $value);
            }
        }
    }

    /**
     * @param array $inputs incoming inputs to validate
     *
     * @return Result instance of validator result
     */
    public function validateDefaultPreferences($inputs = array())
    {
        $preferenceData = $this->getDefaultPreferences();

        $rules = array();

        foreach ($preferenceData as $name => $data) {
            if (isset($data['validate'])) {
                $rules[$name] = $data['validate'];
            }
        }

        return ee('Validation')->make($rules)->validate($inputs);
    }

    /**
     * loads items with lang lines and choices before sending off.
     * (Requires ee('Model')->make() to access.)
     *
     * @return array key->value array of pref names and defaults
     */
    public function getDefaultPreferences()
    {
        //just in case this gets removed in the future.
        if (isset(ee()->lang) && method_exists(ee()->lang, 'loadfile')) {
            ee()->lang->loadfile('fbc');
        }

        $preferences = $this->defaultPreferences;

	    $preferences['eligible_member_groups']['choices'] = $this->getMemberGroupChoices();
	    $preferences['eligible_member_groups']['default'] = '5';
	    $preferences['member_group']['choices']           = $this->getMemberGroupChoices();
	    $preferences['member_group']['default']           = 5;

        return $preferences;
    }

    /**
     * Prepares an array with member group ID => Title values for select and checkbox fields
     *
     * @return array
     */
    private function getMemberGroupChoices()
    {
        $choices = array();

        $memberGroupModel = ee('Model')
            ->get('MemberGroup')
            ->filter('group_id', '!=', 1)
            ->all();

        /** @var MemberGroup $model */
        foreach ($memberGroupModel as $model) {
            $choices[$model->getId()] = $model->group_title;
        }

        return $choices;
    }
}
