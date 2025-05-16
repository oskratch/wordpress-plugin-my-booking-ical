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

        $columns = array(
                'cb' => '<input type="checkbox" />',
                'from_date' => __('From', 'my_booking_ical_form'),
                'to_date' => __('To', 'my_booking_ical_form'),
                'price' => __('Price', 'my_booking_ical_form'),
        );

        return $columns;
    }

    private function get_table_data() {
        global $wpdb;

        $table = $wpdb->prefix . 'my_booking_ical_prices';
        $form_id = $_GET['id'];

        return $wpdb->get_results(
            "SELECT * from {$table} WHERE form_id = {$form_id}",
            ARRAY_A
        );
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'from_date':
            case 'to_date':
                return date("d-m-Y", strtotime($item[$column_name]));
                break;
            case 'price':
                return $item[$column_name] . ' ' . get_option('currency');
                break;
            default:
                return $item[$column_name];
        }
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="element[]" value="%s" />',
            $item['id']
        );
    }

    protected function get_sortable_columns() {
        $sortable_columns = array(
            'from_date'  => array('from_date', false),
            'to_date' => array('to_date', false),
            'price' => array('price', false)
        );

        return $sortable_columns;
    }

    function usort_reorder($a, $b) {
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'from_date';
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
        $result = strcmp($a[$orderby], $b[$orderby]);
        return ($order === 'asc') ? $result : -$result;
    }

    // Add action links (column_{namecolumn})

    public function column_from_date($item) {
        $edit_link = admin_url('admin.php?page=my_booking_ical_prices_edit&id=' .  $item['id']);
        $delete_link = admin_url('admin.php?page=my_booking_ical_prices_delete&id=' . $item['id']);
        $output    = '';
 
        $output .= '<strong><a href="' . esc_url( $requests_view_link ) . '" class="row-title">' . date("d-m-Y", strtotime(esc_html(  $item['from_date']))   ) . '</a></strong>';
 
        $actions = array(
            'edit'   => '<a href="' . esc_url( $edit_link ) . '">' . __( 'Edit', 'my_booking_ical_form' ) . '</a>',
            'delete'   => '<a href="' . esc_url( $delete_link ) . '" class="link-confirm" data-message="' . __('Are you sure to delete this record?', 'my_booking_ical_form') . '">' . __( 'Delete', 'my_booking_ical_form' ) . '</a>',
        );
 
        $row_actions = array();
 
        foreach ($actions as $action => $link) {
            $row_actions[] = '<span class="' . esc_attr( $action ) . '">' . $link . '</span>';
        }
 
        $output .= '<div class="row-actions">' . implode( ' | ', $row_actions ) . '</div>';
 
        return $output;
    }

    function prepare_items() {

        // Data

        $this->table_data = $this->get_table_data();

        $columns = $this->get_columns();
        $hidden = array();
        //$sortable = array();
        $sortable = $this->get_sortable_columns();
        $primary = 'id';
        $this->_column_headers = array($columns, $hidden, $sortable);

        usort($this->table_data, array(&$this, 'usort_reorder'));

        // Pagination

        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);

        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $this->elements_per_page), $this->elements_per_page);

        $this->set_pagination_args(array(
                'total_items' => $total_items, // total number of items
                'per_page'    => $this->elements_per_page, // items to show on a page
                'total_pages' => ceil( $total_items / $this->elements_per_page ) // use ceil to round up
        ));
        
        $this->items = $this->table_data;
    }
}

function my_booking_ical_prices_create() {
    
    if(isset($_POST['from_date'])){
        
        global $wpdb;

        $from_date = $_POST['from_date'];
        $to_date = $_POST['to_date'];
        $price = $_POST['price'];

        // Save the data to the database
        $wpdb->insert(
            $wpdb->prefix . 'my_booking_ical_prices',
            array(
                'form_id' => $_POST['form_id'],
                'from_date' => $from_date,
                'to_date' => $to_date,
                'price' => $price
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        echo '<script>window.location.href = "' . admin_url('admin.php?page=my_booking_ical_forms_edit&id=' . $_POST['form_id']) . '"</script>';
        exit;

    }else{
        
        require(MBIF_DIR . '/views/admin/my_booking_ical_prices-create.php');
    }
}

function my_booking_ical_prices_edit() {
        
    global $wpdb;
    
    if(isset($_POST['id'])){

        $from_date = $_POST['from_date'];
        $to_date = $_POST['to_date'];
        $price = $_POST['price'];
        
        $item = $wpdb->get_row("SELECT form_id FROM " . $wpdb->prefix . "my_booking_ical_prices WHERE id = " . $_POST['id']);

        // Update the data in the database
        $wpdb->update(
            $wpdb->prefix . 'my_booking_ical_prices',
            array(
                'from_date' => $from_date,
                'to_date' => $to_date,
                'price' => $price
            ),
            array( 'id' => $_POST['id'] ),
            array(
                '%s',
                '%s',
                '%s',
                '%s'
            ),
            array( '%d' )
        );

        echo '<script>window.location.href = "' . admin_url('admin.php?page=my_booking_ical_forms_edit&id=' . $item->form_id) . '"</script>';
        exit;

    }else{
        $item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "my_booking_ical_prices WHERE id = " . $_GET['id']);
        require(MBIF_DIR . '/views/admin/my_booking_ical_prices-edit.php');
    }
}

function my_booking_ical_prices_delete() {

    if(isset($_GET['id'])) {

        global $wpdb;

        $item = $wpdb->get_row("SELECT form_id FROM " . $wpdb->prefix . "my_booking_ical_prices WHERE id = " . $_GET['id']);

        $wpdb->delete( 
            $wpdb->prefix . 'my_booking_ical_prices', 
            array( 'id' => $_GET['id'] ) 
        );
        
        echo '<script>window.location.href = "' . admin_url('admin.php?page=my_booking_ical_forms_edit&id=' . $item->form_id) . '"</script>';
        exit;
    }
}