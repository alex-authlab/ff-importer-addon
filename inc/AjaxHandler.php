<?php

namespace FF_Importer;

class AjaxHandler
{
    protected $importer;

    public function init()
    {
        add_action('wp_ajax_ff-import-other-forms', function () {
            if (!wp_verify_nonce($_REQUEST['nonce'], 'ff_migrator_admin_nonce')) {
                die();
            }
            $route = sanitize_text_field($_REQUEST['route']);

            switch ($_REQUEST['form_type']){
                case 'caldera':
                    $this->importer = new CalderaMigrator();
                    break;
                case 'ninja_forms':
                    $this->importer = new NinjaFormsMigrator();
                    break;
                case 'gravityform':
                    $this->importer = new GravityFormsMigrator();
                    break;
            }

            $this->handleEndpoint($route);


        });

    }

    private function handleEndpoint($route)
    {
        $validRoutes = [
            'import_all_forms' => 'importAll',
            'import_single_form' => 'importSingleForm',
            'import_entries' => 'importEntries',

        ];

        if (isset($validRoutes[$route])) {
            $this->{$validRoutes[$route]}();
        }
        die();
    }

    public function importAll()
    {
        $this->importer->import_forms();

    }
    public function importSingleForm()
    {
        $formId = sanitize_text_field($_REQUEST['form_id']);
        $this->importer->import_forms($formId);

    }
    public function importEntries()
    {
        $fluentFormId = $_REQUEST['imported_fluent_form_id'];
        $importFormId = $_REQUEST['form_id'];
        $this->importer->insertEntries($fluentFormId, $importFormId);
    }
}