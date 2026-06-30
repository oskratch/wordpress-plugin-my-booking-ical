<?php
/**
 * Copyright (c) 2025 Oscar Periche, Metalinked
 * Licensed under GPL v2 or later
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

class My_Booking_Ical_Prices extends WP_List_Table {

    private $table_data;
    private $elements_per_page = 50;

    function get_columns() {
        return array(
            'cb'        => '<input type="checkbox" />',
            'from_date' => __('From', 'my_booking_ical_form'),
            'to_date'   => __('To', 'my_booking_ical_form'),
            'price'     => __('Price', 'my_booking_ical_form'),
        );
    }

    private function get_table_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'my_booking_ical_prices';
        $form_id = intval($_GET['id']);
        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table} WHERE form_id = %d", $form_id),
            ARRAY_A
        );
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'from_date':
            case 'to_date':
                return date("d-m-Y", strtotime($item[$column_name]));

            case 'price':
                return esc_html($item[$column_name]) . ' ' . esc_html(get_option('currency'));

            default:
                return esc_html($item[$column_name]);
        }
    }

    function column_cb($item) {
        return sprintf('<input type="checkbox" name="element[]" value="%s" />', intval($item['id']));
    }

    protected function get_sortable_columns() {
        return array(
            'from_date' => array('from_date', false),
            'to_date'   => array('to_date', false),
            'price'     => array('price', false)
        );
    }

    function usort_reorder($a, $b) {
        $orderby = (!empty($_GET['orderby'])) ? sanitize_key($_GET['orderby']) : 'from_date';
        $order = (!empty($_GET['order'])) ? sanitize_key($_GET['order']) : 'asc';
        $result = strcmp($a[$orderby], $b[$orderby]);
        return ($order === 'asc') ? $result : -$result;
    }

    public function column_from_date($item) {
        $edit_link = admin_url('admin.php?page=my_booking_ical_prices_edit&id=' . intval($item['id']));
        $delete_link = wp_nonce_url(
            admin_url('admin.php?page=my_booking_ical_prices_delete&id=' . intval($item['id'])),
            'mbif_delete_price_' . intval($item['id'])
        );

        $output = '<strong>' . date("d-m-Y", strtotime($item['from_date'])) . '</strong>';

        $actions = array(
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

function my_booking_ical_prices_create() {
    if (isset($_POST['from_date'])) {
        check_admin_referer('mbif_create_price', 'mbif_nonce');

        global $wpdb;

        $from_date = sanitize_text_field($_POST['from_date']);
        $to_date = sanitize_text_field($_POST['to_date']);
        $price = floatval($_POST['price']);

        $wpdb->insert(
            $wpdb->prefix . 'my_booking_ical_prices',
            array(
                'form_id'   => intval($_POST['form_id']),
                'from_date' => $from_date,
                'to_date'   => $to_date,
                'price'     => $price
            ),
            array('%d', '%s', '%s', '%f')
        );

        echo '<script>window.location.href = "' . esc_js(admin_url('admin.php?page=my_booking_ical_forms_edit&id=' . intval($_POST['form_id']))) . '"</script>';
        exit;

    } else {
        require(MBIF_DIR . '/views/admin/my_booking_ical_prices-create.php');
    }
}

function my_booking_ical_prices_edit() {
    global $wpdb;

    if (isset($_POST['id'])) {
        check_admin_referer('mbif_edit_price', 'mbif_nonce');

        $from_date = sanitize_text_field($_POST['from_date']);
        $to_date = sanitize_text_field($_POST['to_date']);
        $price = floatval($_POST['price']);

        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT form_id FROM " . $wpdb->prefix . "my_booking_ical_prices WHERE id = %d",
            intval($_POST['id'])
        ));

        $wpdb->update(
            $wpdb->prefix . 'my_booking_ical_prices',
            array('from_date' => $from_date, 'to_date' => $to_date, 'price' => $price),
            array('id' => intval($_POST['id'])),
            array('%s', '%s', '%f'),
            array('%d')
        );

        echo '<script>window.location.href = "' . esc_js(admin_url('admin.php?page=my_booking_ical_forms_edit&id=' . intval($item->form_id))) . '"</script>';
        exit;

    } else {
        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . $wpdb->prefix . "my_booking_ical_prices WHERE id = %d",
            intval($_GET['id'])
        ));
        require(MBIF_DIR . '/views/admin/my_booking_ical_prices-edit.php');
    }
}

function my_booking_ical_prices_delete() {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        check_admin_referer('mbif_delete_price_' . $id);

        global $wpdb;

        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT form_id FROM " . $wpdb->prefix . "my_booking_ical_prices WHERE id = %d",
            $id
        ));

        $wpdb->delete($wpdb->prefix . 'my_booking_ical_prices', array('id' => $id), array('%d'));

        echo '<script>window.location.href = "' . esc_js(admin_url('admin.php?page=my_booking_ical_forms_edit&id=' . intval($item->form_id))) . '"</script>';
        exit;
    }
}
