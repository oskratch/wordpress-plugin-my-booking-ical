<?php

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
                'price' =>  __('General price', 'my_booking_ical_form'),
                'parking_option' =>  __('Parking option', 'my_booking_ical_form'),
                'shortcode' => "Shortcode",
                'num_requests' => __('Requests', 'my_booking_ical_form'),
                'pending_count' => __('Pending review', 'my_booking_ical_form')
        );

        return $columns;
    }

    private function get_table_data() {
        global $wpdb;

        $table = $wpdb->prefix . 'my_booking_ical_forms';

        return $wpdb->get_results(
            "SELECT * from {$table}",
            ARRAY_A
        );
    }

    function column_default($item, $column_name) {
        switch ($column_name) {

            case 'shortcode':
                return '[booking_ical_form form_id="' . $item['id'] . '"]';
                break;

            case 'price':
                return $item[$column_name] . ' ' . get_option('currency');
                break;

            case 'parking_option':
                return $item[$column_name] ? __('Yes', 'my_booking_ical_form') : __('No', 'my_booking_ical_form');
                break;

            case 'num_requests':
                global $wpdb;
                $table_name = $wpdb->prefix . "my_booking_ical_requests";
                return $wpdb->get_var( $wpdb->prepare("SELECT count(id) FROM $table_name WHERE form_id = %d", $item['id']) );
                break;

            case 'ical_booking_url':
            case 'ical_airbnb_url':
                return $item[$column_name] ? ('<a target="_blank" href="' . $item[$column_name] . '">' . __('View', 'my_booking_ical_form') . '</a>') : '--';
                break;

            case 'pending_count':
                global $wpdb;
                $table_name = $wpdb->prefix . "my_booking_ical_requests";
                return $wpdb->get_var( $wpdb->prepare("SELECT count(id) FROM $table_name WHERE `form_id` = %d AND `status` = '%s'", $item['id'], 'pending_review') );
                break;

            case 'title':
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
            'reference' => array('reference', false),
            'title'  => array('title', false),
            'price' => array('price', false)
        );

        return $sortable_columns;
    }

    function usort_reorder($a, $b) {
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'title';
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
        $result = strcmp($a[$orderby], $b[$orderby]);
        return ($order === 'asc') ? $result : -$result;
    }

    // Add action links (column_{namecolumn})

    public function column_reference($item) {
        $edit_link = admin_url('admin.php?page=my_booking_ical_forms_edit&id=' .  $item['id']);
        $delete_link = admin_url('admin.php?page=my_booking_ical_forms_delete&id=' . $item['id']); 
        $requests_view_link = admin_url('admin.php?page=my_booking_ical_requests&form_id=' . $item['id']); 
        $output    = '';
 
        $output .= '<strong><a href="' . esc_url( $requests_view_link ) . '" class="row-title">' . esc_html(  $item['reference']   ) . '</a></strong>';
 
        $actions = array(
            'view'   => '<a href="' . esc_url( $requests_view_link ) . '">' . __( 'View Requests', 'my_booking_ical_form' ) . '</a>',
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

function my_booking_ical_forms() {

    $table = new My_Booking_Ical_Forms();
    $table->prepare_items();
    
    require(MBIF_DIR . '/views/admin/my_booking_ical_forms.php');
}

function my_booking_ical_forms_create() {
    
    if(isset($_POST['title'])){
        
        global $wpdb;

        $reference = $_POST['reference'];
        $title = $_POST['title'];
        $ical_booking_url = $_POST['ical_booking_url'];
        $ical_airbnb_url = $_POST['ical_airbnb_url'];
        $min_days = $_POST['min_days'];
        $price = $_POST['price'];
        $max_capacity = $_POST['max_capacity'];
        $parking_option = $_POST['parking_option'];

        // Guarda les dades a la base de dades
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
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        echo '<script>window.location.href = "' . admin_url('admin.php?page=my_booking_ical_forms') . '"</script>';
        exit;

    }else{
        
        require(MBIF_DIR . '/views/admin/my_booking_ical_forms-create.php');
    }
}

function my_booking_ical_forms_edit() {
        
    global $wpdb;
    
    if(isset($_POST['id'])){

        $reference = $_POST['reference'];
        $title = $_POST['title'];
        $ical_booking_url = $_POST['ical_booking_url'];
        $ical_airbnb_url = $_POST['ical_airbnb_url'];
        $min_days = $_POST['min_days'];
        $price = $_POST['price'];
        $max_capacity = $_POST['max_capacity'];
        $parking_option = $_POST['parking_option'];

        // Actualitza les dades a la base de dades
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
            array( 'id' => $_POST['id'] ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            ),
            array( '%d' )
        );

        //echo '<script>window.location.href = "' . admin_url('admin.php?page=my_booking_ical_forms') . '"</script>';
        echo '<script>window.location.href = "' . admin_url('admin.php?page=my_booking_ical_forms_edit&id=' . $_GET['id']) . '"</script>';
        exit;

    }else{
        $item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "my_booking_ical_forms WHERE id = " . $_GET['id']);
        $table = new My_Booking_Ical_Prices();
        $table->prepare_items(); 
        require(MBIF_DIR . '/views/admin/my_booking_ical_forms-edit.php');
    }
}

function my_booking_ical_forms_delete() {

    if(isset($_GET['id'])) {

        global $wpdb;

        $wpdb->delete( 
            $wpdb->prefix . 'my_booking_ical_forms', 
            array( 'id' => $_GET['id'] ) 
        );

        $wpdb->delete( 
            $wpdb->prefix . 'my_booking_ical_requests', 
            array( 'form_id' => $_GET['id'] ) 
        );
        
        // Todo: manera provisional de redirecció mitjançant JS. La funció wp_redirect() no funciona perquè ja s'han enviat les capçaleres http. Tal com ho estic fent, 
        // no deu ser la manera correcta del tot, cal fer-ho d'una altra manera... un altre dia
        echo '<script>window.location.href = "' . admin_url('admin.php?page=my_booking_ical_forms') . '"</script>';
        exit;
    }
}