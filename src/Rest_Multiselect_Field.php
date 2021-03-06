<?php

namespace iamntz\Rest_Multiselect;

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Helper\Delimiter;
use Carbon_Fields\Value_Set\Value_Set;
use Carbon_Fields\Field\Predefined_Options_Field;

class Rest_Multiselect_Field extends Predefined_Options_Field
{
    protected $endpoints = [
        'base' => null,
        'search' => null,
        'fetch_by_id' => null,
    ];

    protected $selection_limit = 999;
    protected $value_key = 'id';
    protected $label_key = ['title.rendered'];

    protected $value_delimiter = '|';

    public static function admin_enqueue_scripts()
    {
        $root_uri = Carbon_Fields::directory_to_url(CARBON_REST_MULTISELECT_DIR);
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_script('rest-multiselect', $root_uri . "/assets/build/bundle{$suffix}.js", ['carbon-fields-core'], CARBON_REST_MULTISELECT_VERSION, true);
        wp_enqueue_style('rest-multiselect', $root_uri . '/assets/build/bundle.css', [], CARBON_REST_MULTISELECT_VERSION);
    }

    public function set_endpoint($name, $endpoint)
    {
        $this->endpoints[$name] = $endpoint;

        return $this;
    }

    public function set_value_key($key)
    {
        $this->value_key = $key;

        return $this;
    }

    public function set_label_key($key)
    {
        $this->label_key = $key;

        return $this;
    }

    public function set_selection_limit($selection_limit)
    {
        $this->selection_limit = $selection_limit;

        return $this;
    }

    public function __construct($type, $name, $label)
    {
        $this->set_value_set(new Value_Set(Value_Set::TYPE_MULTIPLE_VALUES));
        parent::__construct($type, $name, $label);
    }

    /**
     * Load the field value from an input array based on its name
     *
     * @param array $input Array of field names and values.
     */
    public function set_value_from_input($input)
    {
        if (!isset($input[$this->get_name()])) {
            $this->set_value(null);
            return $this;
        }

        $value = stripslashes_deep($input[$this->get_name()]);

        $value = array_filter(array_map(function ($val) {
            return Delimiter::unquote($val, $this->value_delimiter);
        }, $value));

        return $this->set_value($value);
    }

    /**
     * Returns an array that holds the field data, suitable for JSON representation.
     *
     * @param bool $load Should the value be loaded from the database or use the value from the current instance.
     *
     * @return array
     */
    public function to_json($load)
    {
        $field_data = parent::to_json($load);

        $field_data['value'] = array_filter($field_data['value']);

        $field_data = array_merge($field_data, [
            'value_delimiter' => $this->value_delimiter,
            'base_endpoint' => $this->endpoints['base'],
            'search_endpoint' => $this->endpoints['search'],
            'fetch_by_id_endpoint' => $this->endpoints['fetch_by_id'],

            'value_key' => $this->value_key,
            'label_key' => $this->label_key,
            'selection_limit' => $this->selection_limit,
        ]);


        return $field_data;
    }
}
