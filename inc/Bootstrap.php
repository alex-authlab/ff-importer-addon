<?php

namespace  FF_Importer;
Class Bootstrap
{
    public $migratorLinks = [];

    public function init()
    {
        $this->includeFiles();
        $this->ajaxHandler();
        add_action('fluentform_after_before_export_import_container', [$this, 'render'], 99);
    }

    public function ajaxHandler()
    {
        (new AjaxHandler())->init();
    }

    public function includeFiles()
    {
        require_once FF_MIG_DIR_PATH . 'inc/BaseMigrator.php';
        require_once FF_MIG_DIR_PATH . 'inc/CalderaMigrator.php';
        require_once FF_MIG_DIR_PATH . 'inc/NinjaFormsMigrator.php';
        require_once FF_MIG_DIR_PATH . 'inc/GravityFormsMigrator.php';
        require_once FF_MIG_DIR_PATH . 'inc/AjaxHandler.php';
    }

    public function render()
    {
        $migratorLinks = [];

        if ((new CalderaMigrator())->exist()) {
            $migratorLinks[] = [
                'name' => 'Caldera Forms',
                'key' => 'caldera',
                'forms' => (new CalderaMigrator())->getFormsFormatted()

            ];
        }
        if ((new NinjaFormsMigrator())->exist()) {
            $migratorLinks[] = [
                'name' => 'Ninja Forms',
                'key' => 'ninja_forms',
                'forms' => (new NinjaFormsMigrator())->getFormsFormatted()
            ];
        }
        if ((new GravityFormsMigrator())->exist()) {
            $migratorLinks[] = [
                'name' => 'Gravity Forms',
                'key' => 'gravityform',
                'forms' => (new GravityFormsMigrator())->getFormsFormatted()

            ];
        }
        $this->migratorLinks = $migratorLinks;
        $this->loadFiles();

        ob_start();
        ?>
        <style>

            .ff-mig-addon-tabs-nav ul {
                margin: 0;
                padding: 0;
            }

            .ff-mig-addon-tabs-nav li {
                display: inline-block;
                background: #545c64;
                color: #fefefe;
                border: 1px solid #c1c1c1;
                margin-right: 5px;
            }

            .ff-mig-addon-tabs-nav a {
                display: block;
                padding: 10px 15px;
                font-weight: bold;
                color: #fff;
                text-decoration: none;
            }
            .ff-mig-addon-tabs-nav li.active {
                background: #FFF;
                color: #000;
            }

            .ff-mig-addon-tabs-nav li.active a {
                color: inherit;
            }

            .ff-mig-addon-tabs-content {
                border: 1px solid #ebeef5;
                padding: 10px;
                background: #FFF;
                margin-top: -1px;
                overflow: hidden;
            }

            .ff-mig-addon-tabs-content IMG {
                margin-right: 10px;
            }
            /* Hide all but first content div */

            .ff-mig-addon-tabs-content:not(:first-child) {
                display: none;
            }
            .ff-mig-addon-tabs-content ul{
                display:flex;
                list-style: none;
                flex-direction:column;
                padding-left: 0;
                margin: 0;
                text-align:center;
            }

            .ff-mig-addon-tabs-content ul li{
                width:100%;
                padding: 10px 5px;
                box-sizing: border-box;
                justify-content: space-between;flex-direction: row;
                border-bottom: 1px solid #ebeef5;
                display: flex;
            }
            .ff-mig-addon-tabs-content ul li a {
                text-decoration: none;
            }

        </style>
        <div class="ff-migrator-addon">
            <h2> Import Other Forms</h2>
            <div class="ff-mig-addon-tabs-nav">
                <ul>
                    <?php foreach ($migratorLinks as $link): ?>
                        <li class="ff-mig-addon-tabs-nav-a "><a href="#<?php echo $link['key'] ?>"><?php echo $link['name'] ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <section>
                <?php foreach ($migratorLinks as $link): ?>
                    <div  class="ff-mig-addon-tabs-content" id="<?php echo $link['key'] ?>">
                        <ul>
                            <?php if(is_array($link['forms']) && count($link['forms']) > 0){

                                foreach ($link['forms'] as  $form){
                            ?>
                                    <li>
                                        <div><?php echo $form['name']?> </div>



                                        <div>
                                            <?php if ($form['imported_ff_id']){
                                                $skipEntryImport = ['gravityform','ninja_forms'];
                                                if(!in_array($link['key'],$skipEntryImport)){
                                                ?>
                                                <button data-imported_ff_id="<?php echo $form['imported_ff_id'] ?>" data-form_id="<?php echo $form['id']?>" data-form_type="<?php echo $link['key']?>" type="button" class="el-button el-button--primary el-button--mini import-entry">
                                                    <?php _e(' Import Entries','') ?>
                                                </button>
                                            <?php }
                                            }?>

                                            <button data-form_id="<?php echo $form['id']?>" data-form_type="<?php echo $link['key']?>" type="button" class="el-button el-button--success el-button--mini import-single-form">
                                                <?php if ($form['imported_ff_id']){ echo ' <i class="el-icon-check"></i>'; }  ?>
                                                <?php _e(' Import Form','') ?>
                                            </button>
                                        </div>
                                    </li>
                            <?php
                                }
                            }?>
                            <li>
                                <div></div>
                                <div>
                                    <button type="button" class="ff-migrator-link-all el-button  el-button--secondary el-button--mini" data-key="<?php echo $link['key']?>">
                                        <?php _e('Import All','') ?>
                                    </button>
                                </div>
                            </li>
                        </ul>


                    </div>

                <?php endforeach; ?>


            </section>

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
