<?php

class ACFQuickEdit {
    
    public $post_type;
    public $columns = [];
    public $quickedit = [];
    public $bulkedit = [];
    
    public function __construct($post_type, $fields) {
        $this->post_type = $post_type;
        
        /* Column Hooks */
        add_filter("manage_{$post_type}_posts_columns",             [$this, 'posts_columns']);
        add_action("manage_{$post_type}_posts_custom_column",       [$this, 'manage_columns'], 99, 2);
        
        /* Quick/Bulk Edit Hooks */
        add_action("quick_edit_custom_box",                         [$this, 'quickedit_box'], 99, 2);
        add_action("bulk_edit_custom_box",                          [$this, 'bulkedit_box'], 99, 2);
        add_action("save_post",                                     [$this, 'save_post']);
        
        /* Enqueue */
        add_action("admin_enqueue_scripts",                         [$this, 'admin_enqueue']);
        
        /* Setup */
        if (is_array($fields)) {
            foreach ($fields as $key => $field) {
                if ((is_array($field) && @$field['quickedit'] === true) || $field === true) {
                    $this->quickedit[] = $key;
                }
                
                if ((is_array($field) && @$field['bulkedit'] === true) || $field === true) {
                    $this->bulkedit[] = $key;
                }
                
                $this->columns[$key] = is_string(@$field['column']) ? @$field['column'] : $key;
            }
        }
        
    }
    
    public function admin_enqueue() {
        wp_enqueue_script('acf-quickedit', get_template_directory_uri() . '/inc/quickedit/acf-quickedit.js', ['jquery']);
        wp_localize_script('acf-quickedit', 'acf_qe_data', $this->get_data());
        
		wp_enqueue_script('acf-input');
		wp_enqueue_style('acf-input');
    }
    
    public function save_post($post_id){
        $post_type = get_post_type($post_id);
        if ($post_type == $this->post_type && isset($_REQUEST['acf'])){
            foreach ($_REQUEST['acf'] as $field => $value) {
                update_field($field, $value, $post_id);
            }
        }
    }
    
    public function get_data() {
        global $wp_query;
        $post_ids = wp_list_pluck($wp_query->posts, 'ID');
        $fields = array_unique(array_merge($this->quickedit, $this->bulkedit));
        $data = [];
        
        foreach ($post_ids as $pid) {
            $data[$pid] = [];
            
            foreach ($fields as $field) {
                $data[$pid][$field] = get_field($field, $pid);
            }
        }
        
        return $data;
    }
    
    public function quickedit_box($column_name, $post_type) {
        if (in_array($column_name, $this->quickedit)) {
            $this->render_field($column_name);
        }
    }
    
    public function bulkedit_box($column_name, $post_type) {
        if (in_array($column_name, $this->bulkedit)) {
            $this->render_field($column_name);
        }
    }
    
    public function render_field($column_name) {
        $field = acf_get_field($column_name);
        echo "<div class=\"acf-inline-edit-field\" data-name=\"$column_name\"><div class=\"inline-edit-col\">";
        echo "<div class=\"wp-clearfix\"><label class=\"inline-edit-acf alignleft\"><span class=\"title\" style=\"width: auto\">{$field['label']}</span></label></div>";
        acf_render_field($field);
        echo "</div></div>";
    }
    
    
    /* Columns */
    
    public function manage_columns($field_key, $post_id) {
        if (array_key_exists($field_key, $this->columns)) {
            $field = get_field_object($field_key, $post_id);
            $value = @$field['value'];
            
            if ($value === null) {
                echo '-';
            } else {
                if (is_string(@$field['choices'][$value])) {
                    echo $field['choices'][$value];
                } else {
                    echo $value;
                }
            }
        }
    }
    
    public function posts_columns($cols) {
        $begin = array_slice($cols, 0, 2, true);
        $end = array_slice($cols, 2, null, true);
        return array_merge($begin, array_merge($this->columns, $end));
    }
    
}


// Add quickedit fields
$product_quickedit = new ACFQuickEdit('product', [
    'product_type' => [ // Product Type
        'quickedit' => true,
        'bulkedit'  => true,
        'column'    => 'Type'
    ], 
    'product_usage' => [ // Product Usage
        'quickedit' => true,
        'bulkedit'  => true,
        'column'    => 'Usage'
    ]
]);