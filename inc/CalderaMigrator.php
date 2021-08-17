<?php


use FluentForm\App\Modules\Form\Form;
use FluentForm\Framework\Helpers\ArrayHelper;

class CalderaMigrator extends BaseMigrator
{
    public function __construct()
    {
        $this->key = 'caldera';
        $this->title = 'Caldera Forms';
        $this->shortcode = 'caldera_form';
        parent::__construct();
    }

    /**
     * @return bool
     */
    public function exist()
    {
        return defined('CFCORE_VER');
    }

    /**
     * @return array
     */
    public function getForms()
    {
        $forms = [];
        $items = Caldera_Forms_Forms::get_forms();
        foreach ($items as $item) {
            $forms[] = Caldera_Forms_Forms::get_form($item);
        }
        return $forms;
    }

    /**
     * @param $form
     * @return array
     */
    public function getFields($form)
    {
        $fluentFields = [];
        $fields = Caldera_Forms_Forms::get_fields($form);
        $hasStep = false;

        foreach ($fields as $name => $field) {
            if (ArrayHelper::get($field, 'config.type_override')) {
                $field['type'] = $field['config']['type_override'];
            }
            $args = [
                'uniqElKey' => $field['ID'],
                'index' => $field['ID'], // get the order id from order array
                'required' => isset($field['required']) ? true : false,
                'label' => $field['label'],
                'name' => $field['slug'],
                'placeholder' => ArrayHelper::get($field, 'placeholder'),
                'class' => $field['config']['custom_class'],
                'value' => ArrayHelper::get($field, 'config.default'),
                'help_message' => ArrayHelper::get($field, 'caption'),
            ];
            $type = ArrayHelper::get($this->fieldTypes(), $field['type'], '');
            switch ($type) {
                case 'input_text':
                case 'email':
                case 'input_textarea':
                case 'input_url':
                case 'color_picker':
                case 'section_break':
                case 'select':
                case 'input_radio':
                case 'input_checkbox':
                case 'dropdown':
                    $args['options'] = $this->getOptions(ArrayHelper::get($field, 'config.option', []));
                    $args['enable_select_2'] = ArrayHelper::get($field, 'type') == 'filtered_select2' ? 'yes' : 'no';
                    $isBttnType = ArrayHelper::get($field, 'type') == 'toggle_switch' ? true : false;
                    if ($isBttnType) {
                        $args['layout_class'] = 'ff_list_buttons'; //for btn type chkbox
                    }
                    break;
                case 'input_date':
                    $args['format'] = Arrayhelper::get($field, 'config.format');
                    break;
                case 'input_number':
                case 'rangeslider':
                    $args['step'] = $field['config']['step'];
                    $args['min'] = $field['config']['step'];
                    $args['max'] = $field['config']['step'];
                    break;
                case 'input_mask':
                    if ($field['type'] == 'text') {
                        if (!ArrayHelper::isTrue($field, 'config.masked')) {
                            $args['temp_mask'] = '';
                            break; // if masked turned of for txt input then no mask
                        }
                    }
                    $args['temp_mask'] = 'custom';
                    $args['mask'] = str_replace('9', '0',
                        $field['config']['custom']);//replace mask 9 with 0 for numbers
                    break;
                case 'ratings':
                    $number = ArrayHelper::get($field, 'config.number', 5);
                    $args['options'] = array_combine(range(1, $number), range(1, $number));
                    break;
                case 'input_file':
                    $byte = ArrayHelper::get($field, 'config.max_upload', 6000);
                    $kb = ceil($byte / 1000);
                    $args['help_message'] = $field['caption'];
                    $args['allowed_file_types'] = $this->getFileTypes($field);
                    $args['max_size_unit'] = 'KB';
                    $args['max_file_size'] = $kb;
                    $args['max_file_count'] = ArrayHelper::isTrue($field,
                        'config.multi_upload') ? 5 : 1; //limit 5 for unlimited files
                    $args['upload_btn_text'] = ArrayHelper::get($field, 'config.multi_upload_text') ?: 'File Upload';
                    break;
                case 'custom_html':
                    $args['html_codes'] = $field['config']['default'];
                    break;

                case 'gdpr_agreement': // ??
                    $args['tnc_html'] = $field['config']['agreement'];
                    break;
                case 'button':
                    if ($field['config']['type'] == 'next') {
                        $hasStep = true;
                        $type = 'form_step';
                        break; //skipped prev button ,only one is required
                    } elseif ($field['config']['type'] != 'submit') {
                        break;
                    }
                    $this->submitBtn = $this->getSubmitBttn([
                        'uniqElKey' => $field['ID'],
                        'label' => $field['label'],
                        'class' => $field['config']['custom_class'],
                    ]);
                    break;
            }
            $fluentFields[$field['ID']] = $this->getFluentClassicField($type, $args);
        }
        array_filter($fluentFields);
        $returnData = [
            'fields' => $this->getContainer($form, $fluentFields),
            'submitButton' => $this->submitBtn
        ];
        if ($hasStep && defined('FLUENTFORMPRO')) {
            $returnData['stepsWrapper'] = $this->getStepWrapper();
        }
        return $returnData;
    }

    /**
     * @return array
     */
    public function fieldTypes()
    {
        $fieldTypes = [
            'email' => 'email',
            'text' => 'input_mask', //text input has mask option
            'hidden' => 'input_hidden',
            'textarea' => 'input_textarea',
            'paragraph' => 'input_textarea',
            'wysiwyg' => 'input_textarea',
            'url' => 'input_url',
            'color_picker' => 'color_picker',
            'phone_better' => 'phone',
            'phone' => 'input_phone',
            'select' => 'select',
            'dropdown' => 'select',
            'filtered_select2' => 'select',
            'radio' => 'input_radio',
            'checkbox' => 'input_checkbox',
            'toggle_switch' => 'input_checkbox',
            'date_picker' => 'input_date',
            'range' => 'input_number',
            'number' => 'input_number',
            'rangeslider' => 'rangeslider',
            'star_rating' => 'ratings',
            'file' => 'input_file',
            'cf2_file' => 'input_file',
            'advanced_file' => 'input_file',
            'html' => 'custom_html',
            'section_break' => 'section_break',
            'gdpr' => 'gdpr_agreement',
            'button' => 'button',
        ];
        //todo pro fields remove
        return $fieldTypes;
    }

    /**
     * @param $options
     * @return array
     */
    public function getOptions($options)
    {
        $formattedOptions = [];
        foreach ($options as $key => $option) {
            $formattedOptions[] = [
                'label' => ArrayHelper::get($option, 'label', 'Item -' . $key),
                'value' => ArrayHelper::get($option, 'value'),
                'calc_value' => ArrayHelper::get($option, 'calc_value'),
                'id' => $key
            ];
        }
        return $formattedOptions;
    }

    /**
     * @param $field
     * @return array
     */
    private function getFileTypes($field)
    {
        //todo more file types
        $formattedTypes = explode(',', ArrayHelper::get($field, 'config.allowed', ''));
        $fileTypeOptions = [];
        foreach ($formattedTypes as $format) {
            if (!empty($format) && (strpos('jpg|jpeg|gif|png|bmp', $format) != false)) {
                $fileTypeOptions[] = 'jpg|jpeg|gif|png|bmp';
            }
        }
        return array_unique($fileTypeOptions);
    }

    /**
     * @param $form
     * @param $fluentFields
     * @return array
     */
    private function getContainer($form, $fluentFields)
    {
        $containers = [];
        if (empty($form['layout_grid']['fields'])) {
            return $fluentFields;
        }
        //set fields array map for inserting into containers
        foreach ($form['layout_grid']['fields'] as $field_id => $location) {
            if (isset($fluentFields[$field_id])) {
                $location = explode(':', $location);
                $containers[$location[0]][$location[1]]['fields'][] = $fluentFields[$field_id];
            }
        }
        $withContainer = [];
        foreach ($containers as $row => $columns) {

            $colsCount = count($columns);
            $containerConfig = [];
            if ($colsCount != 1) {
                //with container
                $containerConfig[] = [
                    'index' => $row,
                    'element' => 'container',
                    'settings' => [
                        'container_class',
                        'conditional_logics'
                    ],
                    'editor_options' => [
                        'title' => $colsCount . ' Column Container',
                        'icon_class' => $colsCount . 'dashicons dashicons-align-center'
                    ],
                    'columns' => $columns,
                    'uniqElKey' => 'col' . '_' . md5(uniqid(mt_rand(), true))
                ];
            } else {
                //without container
                $containerConfig = $columns[1]['fields'];
            }
            $withContainer[] = $containerConfig;
        }
        array_filter($withContainer);
        return (self::arrayFlat($withContainer));
    }

    /**
     * @param null $array
     * @param int $depth
     * @return array
     */
    public static function arrayFlat($array = null, $depth = 1)
    {
        $result = [];
        if (!is_array($array)) {
            $array = func_get_args();
        }
        foreach ($array as $key => $value) {
            if (is_array($value) && $depth) {
                $result = array_merge($result, self::arrayFlat($value, $depth - 1));
            } else {
                $result = array_merge($result, [$key => $value]);
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    private function getStepWrapper()
    {
        return [
            'stepStart' => [
                'element' => 'step_start',
                'attributes' => [
                    'id' => '',
                    'class' => '',
                ],
                'settings' => [
                    'progress_indicator' => 'progress-bar',
                    'step_titles' => [],
                    'disable_auto_focus' => 'no',
                    'enable_auto_slider' => 'no',
                    'enable_step_data_persistency' => 'no',
                    'enable_step_page_resume' => 'no',
                ],
                'editor_options' => [
                    'title' => 'Start Paging'
                ],
            ],
            'stepEnd' => [
                'element' => 'step_end',
                'attributes' => [
                    'id' => '',
                    'class' => '',
                ],
                'settings' => [
                    'prev_btn' => [
                        'type' => 'default',
                        'text' => 'Previous',
                        'img_url' => ''
                    ]
                ],
                'editor_options' => [
                    'title' => 'End Paging'
                ],
            ]

        ];
    }

    /**
     * @param $form
     * @return array default parsed form metas
     * @throws \Exception
     */
    public function getFormMetas($form)
    {
        $formObject = new Form(wpFluentForm());
        $defaults = $formObject->getFormsDefaultSettings();
        $confirmation = wp_parse_args(
            [
                'messageToShow' => $form['success'],
                'samePageFormBehavior' => isset($form['hide_form']) ? 'hide_form' : 'reset_form',
            ], $defaults['confirmation']
        );
        $advancedValidation = [
            'status' => false,
            'type' => 'all',
            'conditions' => [
                [
                    'field' => '',
                    'operator' => '=',
                    'value' => ''
                ]
            ],
            'error_message' => '',
            'validation_type' => 'fail_on_condition_met'
        ];
        $notifications =
            [
                'sendTo' => [
                    'type' => 'email',
                    'email' => ArrayHelper::get($form, 'mailer.sender_email'),
                    'field' => '',
                    'routing' => [],
                ],
                'enabled' => $form['mailer']['on_insert'] ? true : false,
                'name' => 'Admin Notification',
                'subject' => ArrayHelper::get($form, 'mailer.email_subject', 'Admin Notification'),
                'to' => ArrayHelper::get($form, 'mailer.recipients', '{wp.admin_email}'),
                'replyTo' => '{wp.admin_email}',
                'message' => str_replace('{summary}', '{all_data}', ArrayHelper::get($form, 'mailer.email_message')),
                'fromName' => ArrayHelper::get($form, 'mailer.sender_name'),
                'fromAddress' => ArrayHelper::get($form, 'mailer.sender_email'),
                'bcc' => ArrayHelper::get($form, 'mailer.bcc_to'),
            ];
        return [
            'formSettings' => [
                'confirmation' => $confirmation,
                'restrictions' => $defaults['restrictions'],
                'layout' => $defaults['layout'],
            ],
            'advancedValidationSettings' => $advancedValidation,
            'delete_entry_on_submission' => 'no',
            'notifications' => [$notifications]
        ];
    }

    /**
     * @param $form
     * @return mixed
     */
    protected function getFormId($form)
    {
        return $form['ID'];
    }

    /**
     * @param $form
     * @return mixed
     */
    protected function getFormName($form)
    {
        return $form['name'];
    }
}
