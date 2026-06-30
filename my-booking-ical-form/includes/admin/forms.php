<?php
/**
 * Copyright (c) 2025 Oscar Periche, Metalinked
 * Licensed under GPL v2 or later
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class My_Booking_Ical_Forms extends WP_List_Table {

    private $table_data;
    private $elements_per_page = 10;

    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'reference' => __('Reference', 'my_booking_ical_form'),
            'title' => __('Title', 'my_booking_ical_form'),
            'ical_booking_url' => "iCal Booking URL",
            'ical_airbnb_url' => "iCal Airbnb URL",
            'price' => __('General price', 'my_booking_ical_form'),
            'parking_option' => __('Parking option', 'my_booking_ical_form'),
            'shortcode' => "Shortcode",
            'num_requests' => __('Requests', 'my_booking_ical_form'),
            'pending_count' => __('Pending review', 'my_booking_ical_form')
        );

        return $columns;
    }

    private function get_table_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'my_booking_ical_forms';
        return $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'shortcode':
                return '[booking_ical_form form_id="' . intval($item['id']) . '"]';

            case 'price':
                return esc_html($item[$column_name]) . ' ' . esc_html(get_option('currency'));

            case 'parking_option':
                return $item[$column_name] ? __('Yes', 'my_booking_ical_form') : __('No', 'my_booking_ical_form');

            case 'num_requests':
                global $wpdb;
                $table_name = $wpdb->prefix . "my_booking_ical_requests";
                return intval($wpdb->get_var($wpdb->prepare("SELECT count(id) FROM $table_name WHERE form_id = %d", intval($item['id']))));

            case 'ical_booking_url':
            case 'ical_airbnb_url':
                return $item[$column_name] ? ('<a target="_blank" href="' . esc_url($item[$column_name]) . '">' . __('View', 'my_booking_ical_form') . '</a>') : '--';

            case 'pending_count':
                global $wpdb;
                $table_name = $wpdb->prefix . "my_booking_ical_requests";
                return intval($wpdb->get_var($wpdb->prepare("SELECT count(id) FROM $table_name WHERE form_id = %d AND status = %s", intval($item['id']), 'pending_review')));

            case 'title':
            default:
                return esc_html($item[$column_name]);
        }
    }

    function column_cb($item) {
        return sprintf('<input type="checkbox" name="element[]" value="%s" />', intval($item['id']));
    }

    protected function get_sortable_columns() {
        return array(
            'reference' => array('reference', false),
            'title' => array('title', false),
            'price' => array('price', false)
        );
    }

    function usort_reorder($a, $b) {
        $orderby = (!empty($_GET['orderby'])) ? sanitize_key($_GET['orderby']) : 'title';
        $order = (!empty($_GET['order'])) ? sanitize_key($_GET['order']) : 'asc';
        $result = strcmp($a[$orderby], $b[$orderby]);
        return ($order === 'asc') ? $result : -$result;
    }

    public function column_reference($item) {
        $edit_link = admin_url('admin.php?page=my_booking_ical_forms_edit&id=' . intval($item['id']));
        $delete_link = wp_nonce_url(
            admin_url('admin.php?page=my_booking_ical_forms_delete&id=' . intval($item['id'])),
            'mbif_delete_form_' . intval($item['id'])
        );
        $requests_view_link = admin_url('admin.php?page=my_booking_ical_requests&form_id=' . intval($item['id']));

        $output = '<strong><a href="' . esc_url($requests_view_link) . '" class="row-title">' . esc_html($item['reference']) . '</a></strong>';

        $actions = array(
            'view'   => '<a href="' . esc_url($requests_view_link) . '">' . __('View Requests', 'my_booking_ical_form') . '</a>',
            'edit'   => '<a href="' . esc_url($edit_link) . '">' . __('Edit', 'my_booking_ical_form') . '</a>',
            'delete' => '<a href="' . esc_url($delete_link) . '" class="link-confirm" data-message="' . esc_attr(__('Are you sure to delete this record?', 'my_booking_ical_form')) . '">' . __('Delete', 'my_booking_ical_form') . '</a>',
        );

        $row_actions = array();
        foreach ($actions as $action => $link) {
            $row_actions[] = '<span class="' . esc_attr($action) . '">' . $link . '</span>';
        }

        $output .= '<div class="row-actions">' . implode(' | ', $row_actions) . '</div>';
        return $output;
    }

    function prepare_items() {
        $this->table_data = $this->get_table_data();

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        usort($this->table_data, array(&$this, 'usort_reorder'));

        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);

        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $this->elements_per_page), $this->elements_per_page);

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $this->elements_per_page,
            'total_pages' => ceil($total_items / $this->elements_per_page)
        ));

        $this->items = $this->table_data;
    }
}

function my_booking_ical_forms() {
    $table = new My_Booking_Ical_Forms();
    $table->prepare_items();
    require(MBIF_DIR . '/views/admin/my_booking_ical_forms.php');
}

function my_booking_ical_forms_create() {
    if (isset($_POST['title'])) {
        check_admin_referer('mbif_create_form', 'mbif_nonce');

        global $wpdb;

        $reference = sanitize_text_field($_POST['reference']);
        $title = sanitize_text_field($_POST['title']);
        $ical_booking_url = esc_url_raw($_POST['ical_booking_url']);
        $ical_airbnb_url = esc_url_raw($_POST['ical_airbnb_url']);
        $min_days = intval($_POST['min_days']);
        $price = floatval($_POST['price']);
        $max_capacity = intval($_POST['max_capacity']);
        $parking_option = intval($_POST['parking_option']);

        $wpdb->insert(
            $wpdb->prefix . 'my_booking_ical_forms',
            array(
                'reference' => $reference,
                'title' => $title,
                'ical_booking_url' => $ical_booking_url,
                'ical_airbnb_url' => $ical_airbnb_url,
                'min_days' => $min_days,
                'price' => $price,
                'max_capacity' => $max_capacity,
                'parking_option' => $parking_option
            ),
            array('%s', '%s', '%s', '%s', '%d', '%f', '%d', '%d')
        );

        echo '<script>window.location.href = "' . esc_js(admin_url('admin.php?page=my_booking_ical_forms')) . '"</script>';
        exit;
    } else {
        require(MBIF_DIR . '/views/admin/my_booking_ical_forms-create.php');
    }
}

function my_booking_ical_forms_edit() {
    global $wpdb;

    if (isset($_POST['id'])) {
        check_admin_referer('mbif_edit_form', 'mbif_nonce');

        $reference = sanitize_text_field($_POST['reference']);
        $title = sanitize_text_field($_POST['title']);
        $ical_booking_url = esc_url_raw($_POST['ical_booking_url']);
        $ical_airbnb_url = esc_url_raw($_POST['ical_airbnb_url']);
        $min_days = intval($_POST['min_days']);
        $price = floatval($_POST['price']);
        $max_capacity = intval($_POST['max_capacity']);
        $parking_option = intval($_POST['parking_option']);

        $wpdb->update(
            $wpdb->prefix . 'my_booking_ical_forms',
            array(
                'reference' => $reference,
                'title' => $title,
                'ical_booking_url' => $ical_booking_url,
                'ical_airbnb_url' => $ical_airbnb_url,
                'min_days' => $min_days,
                'price' => $price,
                'max_capacity' => $max_capacity,
                'parking_option' => $parking_option
            ),
            array('id' => intval($_POST['id'])),
            array('%s', '%s', '%s', '%s', '%d', '%f', '%d', '%d'),
            array('%d')
        );

        echo '<script>window.location.href = "' . esc_js(admin_url('admin.php?page=my_booking_ical_forms_edit&id=' . intval($_POST['id']))) . '"</script>';
        exit;
    } else {
        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . $wpdb->prefix . "my_booking_ical_forms WHERE id = %d",
            intval($_GET['id'])
        ));
        $table = new My_Booking_Ical_Prices();
        $table->prepare_items();
        require(MBIF_DIR . '/views/admin/my_booking_ical_forms-edit.php');
    }
}

function my_booking_ical_forms_delete() {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        check_admin_referer('mbif_delete_form_' . $id);

        global $wpdb;

        $wpdb->delete($wpdb->prefix . 'my_booking_ical_forms', array('id' => $id), array('%d'));
        $wpdb->delete($wpdb->prefix . 'my_booking_ical_requests', array('form_id' => $id), array('%d'));
        $wpdb->delete($wpdb->prefix . 'my_booking_ical_prices', array('form_id' => $id), array('%d'));

        echo '<script>window.location.href = "' . esc_js(admin_url('admin.php?page=my_booking_ical_forms')) . '"</script>';
        exit;
    }
}
