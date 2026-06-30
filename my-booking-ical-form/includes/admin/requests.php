<?php
/**
 * Copyright (c) 2025 Oscar Periche, Metalinked
 * Licensed under GPL v2 or later
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class My_Booking_Ical_Requests extends WP_List_Table {

    private $table_data;
    private $elements_per_page = 10;
    private $form;

    public function __construct($form) {
        $this->form = $form;
        parent::__construct();
    }

    function get_columns() {
        return array(
            'reference'      => __('Reference', 'my_booking_ical_form'),
            'first_name'     => __('First Name', 'my_booking_ical_form'),
            'last_name'      => __('Last Name', 'my_booking_ical_form'),
            'entry_date'     => __('Entry date', 'my_booking_ical_form'),
            'departure_date' => __('Departure date', 'my_booking_ical_form'),
            'email'          => "Email",
            'phone'          => __('Phone', 'my_booking_ical_form'),
            'created_at'     => __('Request received', 'my_booking_ical_form'),
            'status'         => __('Status', 'my_booking_ical_form'),
        );
    }

    private function get_table_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'my_booking_ical_requests';
        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table} WHERE form_id = %d", intval($_GET['form_id'])),
            ARRAY_A
        );
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'reference':
                return createReferenceRequest($this->form->reference, $item['entry_date'], $item['id']);

            case 'departure_date':
            case 'entry_date':
                return date("d-m-Y", strtotime($item[$column_name]));

            case 'created_at':
                return date("d-m-Y H:i:s", strtotime($item[$column_name]));

            case 'status':
                if ($item['status'] == 'pending_review') {
                    return '<span style="color:#FFA500">' . __('Pending review', 'my_booking_ical_form') . '</span>';
                } elseif ($item['status'] == 'validated') {
                    return '<span style="color:#28a745">' . __('Validated', 'my_booking_ical_form') . '</span>';
                } else {
                    return '<span style="color:#dc3545">' . __('Denied', 'my_booking_ical_form') . '</span>';
                }

            case 'first_name':
            case 'last_name':
            case 'email':
            case 'phone':
            default:
                return esc_html($item[$column_name]);
        }
    }

    function column_cb($item) {
        return sprintf('<input type="checkbox" name="element[]" value="%s" />', intval($item['id']));
    }

    protected function get_sortable_columns() {
        return array(
            'created_at'     => array('created_at', true),
            'first_name'     => array('first_name', false),
            'last_name'      => array('last_name', false),
            'email'          => array('email', false),
            'entry_date'     => array('entry_date', false),
            'departure_date' => array('departure_date', false),
            'status'         => array('status', false),
        );
    }

    function usort_reorder($a, $b) {
        $orderby = (!empty($_GET['orderby'])) ? sanitize_key($_GET['orderby']) : 'created_at';
        $order = (!empty($_GET['order'])) ? sanitize_key($_GET['order']) : 'desc';
        $result = strcmp($a[$orderby], $b[$orderby]);
        return ($order === 'asc') ? $result : -$result;
    }

    public function column_reference($item) {
        $requests_show_link = admin_url('admin.php?page=my_booking_ical_requests_show&id=' . intval($item['id']));
        $delete_link = wp_nonce_url(
            admin_url('admin.php?page=my_booking_ical_requests_delete&id=' . intval($item['id'])),
            'mbif_delete_request_' . intval($item['id'])
        );

        $output = '<strong><a href="' . esc_url($requests_show_link) . '" class="row-title">' . esc_html(createReferenceRequest($this->form->reference, $item['entry_date'], $item['id'])) . '</a></strong>';

        $actions = array(
            'view'   => '<a href="' . esc_url($requests_show_link) . '">' . __('View', 'my_booking_ical_form') . '</a>',
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

function my_booking_ical_requests() {
    global $wpdb;

    $item = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM " . $wpdb->prefix . "my_booking_ical_forms WHERE id = %d",
        intval($_GET['form_id'])
    ));
    $table = new My_Booking_Ical_Requests($item);
    $table->prepare_items();

    require(MBIF_DIR . '/views/admin/my_booking_ical_requests.php');
}

function my_booking_ical_requests_show() {
    global $wpdb;

    if (isset($_POST['status'])) {
        check_admin_referer('mbif_update_status', 'mbif_nonce');

        $allowed_statuses = ['pending_review', 'validated', 'denied'];
        $status = in_array($_POST['status'], $allowed_statuses) ? $_POST['status'] : 'pending_review';

        $wpdb->update(
            $wpdb->prefix . 'my_booking_ical_requests',
            array('status' => $status),
            array('id' => intval($_POST['id'])),
            array('%s'),
            array('%d')
        );

        echo '<script>window.location.href = "' . esc_js(admin_url('admin.php?page=my_booking_ical_requests_show&id=' . intval($_POST['id']))) . '"</script>';
        exit;

    } else {
        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . $wpdb->prefix . "my_booking_ical_requests WHERE id = %d",
            intval($_GET['id'])
        ));
        $form = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . $wpdb->prefix . "my_booking_ical_forms WHERE id = %d",
            intval($item->form_id)
        ));

        $table = new My_Booking_Ical_Requests($form);
        $table->prepare_items();

        require(MBIF_DIR . '/views/admin/my_booking_ical_requests_show.php');
    }
}

function my_booking_ical_requests_validate() {
    global $wpdb;

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        check_admin_referer('mbif_validate_request_' . $id);

        $allowed_statuses = ['pending_review', 'validated', 'denied'];
        $status = in_array($_GET['value'], $allowed_statuses) ? $_GET['value'] : 'pending_review';

        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT form_id FROM " . $wpdb->prefix . "my_booking_ical_requests WHERE id = %d",
            $id
        ));

        $wpdb->update(
            $wpdb->prefix . 'my_booking_ical_requests',
            array('status' => $status),
            array('id' => $id),
            array('%s'),
            array('%d')
        );

        echo '<script>window.location.href = "' . esc_js(admin_url('admin.php?page=my_booking_ical_requests&form_id=' . intval($item->form_id))) . '"</script>';
        exit;
    }
}

function my_booking_ical_requests_delete() {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        check_admin_referer('mbif_delete_request_' . $id);

        global $wpdb;

        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT form_id FROM " . $wpdb->prefix . "my_booking_ical_requests WHERE id = %d",
            $id
        ));

        $wpdb->delete($wpdb->prefix . 'my_booking_ical_requests', array('id' => $id), array('%d'));

        echo '<script>window.location.href = "' . esc_js(admin_url('admin.php?page=my_booking_ical_requests&form_id=' . intval($item->form_id))) . '"</script>';
        exit;
    }
}
