<?php

use Solspace\Addons\Fbc\Library\AddonBuilder;
use Solspace\Addons\Fbc\Library\Fbc_api;
use Solspace\Addons\Fbc\Model\Preference;

class Fbc_mcp extends AddonBuilder
{
    /** @var Fbc_api */
    public $api;

    /**
     * Constructor
     *
     * @param bool $switch Enable calling of methods based on URI string
     *
     * @access public
     */
    public function __construct($switch = true)
    {
        parent::__construct('module');

        // Install or Uninstall Request
        if ((bool)$switch === false) {
            return;
        }

        // --------------------------------------------
        //  Module Menu Items
        // --------------------------------------------
        $this->set_nav(
            array(
                'preferences'    => array(
                    'name'  => 'preferences',
                    'link'  => $this->mcp_link('preferences'),
                    'title' => lang('preferences'),
                ),
                'diagnostics'    => array(
                    'name'  => 'diagnostics',
                    'link'  => $this->mcp_link('diagnostics'),
                    'title' => lang('diagnostics'),
                ),
                'demo_templates' => array(
                    'link'  => $this->mcp_link('code_pack'),
                    'title' => lang('demo_templates'),
                ),
                'resources'      => array(
                    'title'    => lang('fbc_resources'),
                    'sub_list' => array(
                        'product_info'  => array(
                            'link'     => 'https://solspace.com/expressionengine/facebook-connect',
                            'title'    => lang('fbc_product_info'),
                            'external' => true,
                        ),
                        'documentation' => array(
                            'link'     => $this->docs_url,
                            'title'    => lang('fbc_documentation'),
                            'external' => true,
                        ),
                        'support'       => array(
                            'link'     => 'https://solspace.com/expressionengine/support',
                            'title'    => lang('fbc_official_support'),
                            'external' => true,
                        ),
                    ),
                ),
            )
        );

        // --------------------------------------------
        //  Sites
        // --------------------------------------------
        $this->cached_vars['sites'] = array();
        foreach ($this->model('Data')->get_sites() as $site_id => $site_label) {
            $this->cached_vars['sites'][$site_id] = $site_label;
        }

        ee()->cp->add_to_head('<link rel="stylesheet" type="text/css" href="' . URL_THIRD_THEMES . 'fbc/css/solspace-fa.css">');
    }

    /**
     * Api - Initialize the API object if it hasn't been initialized already
     */
    public function api()
    {
        if (isset($this->api->cached) === true) {
            return;
        }

        $this->api = new Fbc_api();
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public function index($message = '')
    {
        return $this->preferences($message);
    }

    /**
     * Code Pack installer page
     *
     * @param string $message
     *
     * @return string
     */
    public function code_pack($message = '')
    {
        $this->prep_message($message, true, true);

        // --------------------------------------------
        //	Load vars from code pack lib
        // --------------------------------------------
        $codePack         = $this->lib('CodePack');
        $cpl              =& $codePack;
        $cpl->autoSetLang = true;

        $cpt = $cpl->getTemplateDirectoryArray($this->addon_path . 'code_pack/');

        // --------------------------------------------
        //  Start sections
        // --------------------------------------------
        $main_section = array();

        // --------------------------------------------
        //  Prefix
        // --------------------------------------------
        $main_section['template_group_prefix'] = array(
            'title'  => lang('template_group_prefix'),
            'desc'   => lang('template_group_prefix_desc'),
            'fields' => array(
                'prefix' => array(
                    'type'  => 'text',
                    'value' => $this->lower_name . '_',
                ),
            ),
        );

        // --------------------------------------------
        //  Templates
        // --------------------------------------------
        $main_section['templates'] = array(
            'title'  => lang('groups_and_templates'),
            'desc'   => lang('groups_and_templates_desc'),
            'fields' => array(
                'templates' => array(
                    'type'    => 'html',
                    'content' => $this->view('code_pack_list', compact('cpt')),
                ),
            ),
        );

        // --------------------------------------------
        //  Compile
        // --------------------------------------------
        $this->cached_vars['sections'][] = $main_section;
        $this->cached_vars['form_url']   = $this->mcp_link(
            array(
                'method' => 'code_pack_install',
            )
        );
        $this->cached_vars['box_class']  = 'code_pack_box';

        // --------------------------------------------
        //  Load Page and set view vars
        // --------------------------------------------
        //  Final view variables we need to render the form
        $this->cached_vars += array(
            'base_url'              => $this->mcp_link(
                array(
                    'method' => 'code_pack_install',
                )
            ),
            'cp_page_title'         => lang('demo_templates') .
                '<br /><i>' . lang('demo_description') . '</i>',
            'save_btn_text'         => 'install_demo_templates',
            'save_btn_text_working' => 'btn_saving',
        );

        ee('CP/Alert')->makeInline('shared-form')
                      ->asIssue()
                      ->addToBody(lang('prefix_error'))
                      ->cannotClose()
                      ->now();

        return $this->mcp_view(
            array(
                'file'      => 'code_pack_form',
                'highlight' => 'demo_templates',
                'pkg_css'   => array('mcp_defaults'),
                'pkg_js'    => array('code_pack'),
                'crumbs'    => array(
                    array(lang('demo_templates')),
                ),
            )
        );
    }

    /**
     * @return string
     */
    public function code_pack_install()
    {
        $prefix = trim((string)ee()->input->get_post('prefix'));

        if ($prefix === '') {
            return ee()->functions->redirect(
                $this->mcp_link(
                    array(
                        'method' => 'code_pack',
                    )
                )
            );
        }

        // -------------------------------------
        //	load lib
        // -------------------------------------
        $codePack         = $this->lib('CodePack');
        $cpl              =& $codePack;
        $cpl->autoSetLang = true;

        // -------------------------------------
        //	¡Las Variables en vivo! ¡Que divertido!
        // -------------------------------------

        $variables = array();

        $variables['code_pack_name'] = $this->lower_name . '_code_pack';
        $variables['code_pack_path'] = $this->addon_path . 'code_pack/';
        $variables['prefix']         = $prefix;

        // -------------------------------------
        //	install
        // -------------------------------------

        $return = $cpl->installCodePack($variables);

        //--------------------------------------------
        //	Table
        //--------------------------------------------

        $table = ee(
            'CP/Table',
            array(
                'sortable' => false,
                'search'   => false,
            )
        );

        $tableData = array();

        //--------------------------------------------
        //	Errors or regular
        //--------------------------------------------

        if (!empty($return['errors'])) {
            foreach ($return['errors'] as $error) {
                $item = array();

                //	Error
                $item[] = lang('error');

                //	Label
                $item[] = $error['label'];

                //	Field type
                $item[] = str_replace(
                    array(
                        '%conflicting_groups%',
                        '%conflicting_data%',
                        '%conflicting_global_vars%',
                    ),
                    array(
                        implode(", ", $return['conflicting_groups']),
                        implode("<br />", $return['conflicting_global_vars']),
                    ),
                    $error['description']
                );

                $tableData[] = $item;
            }
        } else {
            foreach ($return['success'] as $success) {
                $item = array();

                //	Error
                $item[] = lang('success');

                //	Label
                $item[] = $success['label'];

                //	Field type
                if (isset($success['link'])) {
                    $item[] = array(
                        'content' => $success['description'],
                        'href'    => $success['link'],
                    );
                } else {
                    $item[] = str_replace(
                        array(
                            '%template_count%',
                            '%global_vars%',
                            '%success_link%',
                        ),
                        array(
                            $return['template_count'],
                            implode("<br />", $return['global_vars']),
                            '',
                        ),
                        $success['description']
                    );
                }

                $tableData[] = $item;
            }
        }

        $table->setColumns(
            array(
                'status',
                'description',
                'details',
            )
        );

        $table->setData($tableData);

        $table->setNoResultsText('no_results');

        $this->cached_vars['table'] = $table->viewData();

        $this->cached_vars['form_url'] = '';

        //---------------------------------------------
        //  Load Page and set view vars
        //---------------------------------------------

        return $this->mcp_view(
            array(
                'file'      => 'code_pack_install',
                'highlight' => 'demo_templates',
                'pkg_css'   => array('mcp_defaults'),
                'crumbs'    => array(
                    array(lang('demo_templates')),
                ),
            )
        );
    }

    /**
     * @param string $message
     *
     * @return string
     */
    public function preferences($message = '')
    {
        $this->prep_message($message, true, true);

        /** @var Preference $preferenceModel */
        $preferenceModel    = $this->make('Preference');
        $defaultPreferences = $preferenceModel->getDefaultPreferences();

        $existingPreferences = $this
            ->fetch('Preference')
            ->filter('site_id', ee()->config->item('site_id'))
            ->all()
            ->getDictionary(
                'fbc_preference_name',
                'fbc_preference_value'
            );

        $sections     = array();
        $main_section = array();
        foreach ($defaultPreferences as $key => $data) {
            $descriptionKey = 'fbc_' . $key . '_exp';
            $description    = lang($descriptionKey);

            //if we don't have a description don't set it
            $description = ($description !== $descriptionKey) ? $description : '';

            $value = isset($existingPreferences[$key]) ? $existingPreferences[$key] : $data['default'];

            // Eligible member groups is an imploded array with "|" as the glue
            if ($key == 'eligible_member_groups') {
                $value = explode('|', $value);
            }

            $main_section[$key] = array(
                'title'  => lang('fbc_' . $key),
                'desc'   => $description,
                'fields' => array(
                    $key => array_merge(
                        $data,
                        array(
                            'value'    => $value,
                            'required' => in_array($key, array('app_id', 'app_secret')),
                        )
                    ),
                ),
            );

            if (in_array($key, array('member_group', 'eligible_member_groups'))) {
                $main_section[$key]['caution'] = true;
            }
        }

        $sections[] = $main_section;

        $this->cached_vars['sections'] = $sections;
        $this->cached_vars['form_url'] = $this->mcp_link(
            array(
                'method' => 'update_preferences',
            )
        );

        ee()->cp->add_to_foot('<script>var emptyMemberGroupLabel = ' . json_encode(lang('fbc_empty_member_group')) . ';</script>');

        //---------------------------------------------
        //  Load Page and set view vars
        //---------------------------------------------

        // Final view variables we need to render the form
        $this->cached_vars += array(
            'base_url'              => $this->mcp_link(
                array(
                    'method' => 'update_preferences',
                )
            ),
            'cp_page_title'         => lang('preferences'),
            'save_btn_text'         => 'btn_save_settings',
            'save_btn_text_working' => 'btn_saving',
        );

        return $this->mcp_view(
            array(
                'file'      => 'preferences',
                'highlight' => 'preferences',
                'pkg_js'    => array('preferences'),
                'pkg_css'   => array('mcp_defaults'),
                'crumbs'    => array(
                    array(lang('preferences')),
                ),
            )
        );
    }

    /**
     * Updates existing preferences or inserts new ones
     *
     * Either goes back to the preference page, or shows an error
     */
    public function update_preferences()
    {
        /** @var Preference $preferences */
        $preferences           = $this->make('Preference');
        $defaultPreferences    = $preferences->getDefaultPreferences();
        $defaultPreferenceKeys = array_keys($defaultPreferences);

        $postData = array();
        foreach ($defaultPreferenceKeys as $key) {
            if (isset($_POST[$key])) {
                $postData[$key] = ee()->input->post($key);
            }
        }

        $result = $preferences->validateDefaultPreferences($postData);

        // If posted data is - INVALID
        // ===========================
        if ($result->isNotValid()) {
            $allErrors = $result->getAllErrors();
            $errors    = array();

            foreach ($allErrors as $fieldName => $errorList) {
                foreach ($errorList as $name => $message) {
                    $errors[] = lang('fbc_' . $fieldName) . ': ' . $message;
                }
            }

            return $this->show_error($errors);
        }


        // If posted data is - VALID
        // ===========================

        $siteId = ee()->config->item('site_id');

        $existingPreferences = $this
            ->fetch('Preference')
            ->filter('site_id', $siteId)
            ->all()
            ->indexBy('fbc_preference_name');

        if (!isset($postData['eligible_member_groups'])) {
            $postData['eligible_member_groups'] = '';
        }

        foreach ($postData as $name => $value) {
            $preferenceExists = isset($existingPreferences[$name]);

            if (is_array($value)) {
                $value = implode('|', $value);
            }

            if ($preferenceExists) {
                $existingPreferences[$name]->fbc_preference_value = $value;
                $existingPreferences[$name]->save();
            } else {
                $newPreferenceModel = $this->make('Preference');

                $newPreferenceModel->fbc_preference_value = $value;
                $newPreferenceModel->fbc_preference_name  = $name;
                $newPreferenceModel->site_id              = $siteId;
                $newPreferenceModel->save();
            }
        }


        return ee()->functions->redirect(
            $this->mcp_link(
                array(
                    'method' => 'preferences',
                    'msg'    => 'preferences_updated',
                )
            )
        );
    }


    /**
     * Prepare message
     *
     * @param string $message
     *
     * @return bool
     */
    public function _prep_message($message = '')
    {
        if ($message == '' AND isset($_GET['msg'])) {
            $message = lang($_GET['msg']);
        }

        $this->cached_vars['message'] = $message;

        return true;
    }

    /**
     * Diagnostics page
     *
     * @param string $message
     *
     * @return mixed
     */
    public function diagnostics($message = '')
    {
        $this->api();
        $appId = $this->api->getAppId();
        $appSecret = $this->api->getAppSecret();

        // --------------------------------------------
        //	API Credentials present
        // --------------------------------------------

        $this->cached_vars['api_credentials_present'] = lang('api_credentials_are_present');
        $this->cached_vars['credentials_present'] = true;
        if (empty($appId) || empty($appSecret)) {
            $this->cached_vars['api_credentials_present'] = lang('api_credentials_are_not_present');
            $this->cached_vars['credentials_present'] = false;
        }

        // --------------------------------------------
        //	API successful connect
        // --------------------------------------------
        $this->api->connect_to_api();

        $this->cached_vars['api_not_connected'] = lang('api_connect_was_not_successful');
        $this->cached_vars['successful_connect'] = false;
        $this->cached_vars['api_successful_connect'] = lang('api_connect_was_successful');

        // --------------------------------------------
        //	API login button
        // --------------------------------------------

        $this->cached_vars['facebook_loader_js']   = $this->model('Data')->get_facebook_loader_js();
        $this->cached_vars['api_successful_login'] = lang('api_login_was_successful');
        $this->cached_vars['fbc_app_id']           = $appId;

        // --------------------------------------------
        //	Try login
        // --------------------------------------------

        try {
            $appobj = $this->api->FB->api($appId);

            $app = array();

            if (is_object($appobj) === true) {
                $app['connect_url'] = $appobj->connect_url;
                $app['app_id']      = $appobj->app_id;
            } elseif (is_array($appobj) === true) {
                $app = $appobj;
            }

            if (empty($app['connect_url'])) {
                $this->cached_vars['api_connect_url_test'] = lang('api_connect_url_is_empty');
            } elseif ($app['connect_url'] != $this->cached_vars['api_connect_url']) {
                $this->cached_vars['api_connect_url_test'] = str_replace(
                    array('%incorrect_connect_url%', '%correct_connect_url%'),
                    array($app['connect_url'], $this->cached_vars['api_connect_url']),
                    lang('api_connect_url_incorrect')
                );
            } else {
                $this->cached_vars['api_connect_url_test'] = '';
            }

            if (!empty($app['app_id'])) {
                $this->cached_vars['api_connect_url_facebook'] = str_replace(
                    '%fbc_url%',
                    'http://www.facebook.com/developers/editapp.php?app_id=' . $app['app_id'],
                    lang('api_connect_url_facebook')
                );
            }
        } catch (Exception $e) {
        }

        // --------------------------------------------
        //	Prep message
        // --------------------------------------------

        $this->_prep_message($message);

        // --------------------------------------------
        //  Title and Crumbs
        // --------------------------------------------

        $this->add_crumb(lang('diagnostics'));

        // --------------------------------------------
        //  Load Homepage
        // --------------------------------------------

        $this->cached_vars['module_menu_highlight'] = 'module_diagnostics';

        return $this->mcp_view(
            array(
                'file'      => 'diagnostics',
                'highlight' => 'diagnostics',
                'pkg_js'    => array('diagnostics'),
                'pkg_css'   => array('mcp_defaults'),
                'crumbs'    => array(
                    array(lang('diagnostics')),
                ),
            )
        );
    }

    /**
     * Module Upgrading
     *
     * This function is not required by the 1.x branch of ExpressionEngine by default.  However,
     * as the install and deinstall ones are, we are just going to keep the habit and include it
     * anyhow.
     *        - Originally, the $current variable was going to be passed via parameter, but as there might
     *          be a further use for such a variable throughout the module at a later date we made it
     *          a class variable.
     *
     * @return    bool
     */
    public function fbc_module_update()
    {
        if (!isset($_POST['run_update']) OR $_POST['run_update'] != 'y') {
            $this->add_crumb(lang('update_fbc_module'));
            $this->cached_vars['form_url'] = $this->cached_vars['base_uri'] . '&method=fbc_module_update';

            return $this->ee_cp_view('update_module.html');
        }

        require_once $this->addon_path . 'upd.fbc.php';

        $U = new Fbc_upd();

        if ($U->update() !== true) {
            return ee()->functions->redirect($this->base . AMP . 'msg=update_failure');
        } else {
            return ee()->functions->redirect($this->base . AMP . 'msg=update_successful');
        }
    }
}
