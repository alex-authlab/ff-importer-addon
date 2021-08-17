<?php

Class Bootstrap
{

    public $migratorLinks = [];

    public function init()
    {
        $this->ajaxHandler();
        $this->includeFiles();
        add_action('fluentform_after_before_export_import_container', [$this, 'render'], 99);

    }

    public function ajaxHandler()
    {
        add_action('wp_ajax_ff-import-other-forms', function () {
            if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'ff_migrator_admin_nonce' ) ) {
                die();
            }
            BaseMigrator::init();
        });
    }

    public function includeFiles()
    {
        require_once FF_MIG_DIR_PATH . 'inc/BaseMigrator.php';
        require_once FF_MIG_DIR_PATH . 'inc/CalderaMigrator.php';
        require_once FF_MIG_DIR_PATH . 'inc/NinjaFormsMigrator.php';
        require_once FF_MIG_DIR_PATH . 'inc/GravityFormsMigrator.php';
    }

    public function render()
    {
        $migratorLinks = [];

        if ((new CalderaMigrator())->exist()) {
            $migratorLinks[] = [
                'name' => 'Import Caldera Forms',
                'key' => 'caldera'
            ];
        }
        if ((new NinjaFormsMigrator())->exist()) {
            $migratorLinks[] = [
                'name' => 'Import Ninja Forms',
                'key' => 'ninja_forms'
            ];
        }
        if ((new GravityFormsMigrator())->exist()) {
            $migratorLinks[] = [
                'name' => 'Import Gravity Forms',
                'key' => 'gravityform'
            ];
        }
        $this->migratorLinks = $migratorLinks;
        $this->loadFiles();

        ob_start();
        ?>
        <style>

        </style>
        <div class="ff-migrator-addon">
            <h2> Import Other Forms</h2>
            <?php foreach ($migratorLinks as $link): ?>

                <button type="button" class="ff-migrator-link-call el-button  el-button--secondary el-button--medium"
                        data-key="<?php echo $link['key'] ?> " class="el-button el-button--info el-button--small">
                    <?php echo $link['name']; ?>
                </button>

            <?php endforeach; ?>
            </button>
        </div>
        <div class="ff-m-response">
        </div>
        <?php
        $html = ob_get_clean();
        echo $html;
    }

    public function loadFiles()
    {
        if (isset($_GET['page']) & $_GET['page'] == 'fluent_forms_transfer') {
            wp_enqueue_script(
                'ff_migrator_admin',
                FF_MIG_DIR_URL . 'assets/ff-migrator.js',
                array('jquery'),
                FF_MIG_VER,
                true
            );
            wp_localize_script('ff_migrator_admin', 'ff_migrator_admin_vars', [
                'links' => $this->migratorLinks,
                'action' => 'ff-import-other-forms',
                'ff_migrator_admin_nonce' => wp_create_nonce('ff_migrator_admin_nonce'),
                'ajaxurl' => admin_url('admin-ajax.php')
            ]);
        }

    }

}
