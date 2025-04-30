<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class My_Booking_Ical_Requests extends WP_List_Table {

    private $table_data;
    private $elements_per_page = 10;
    private $form;

    public function __construct($form) {
        $this->form = $form;
        parent::__construct(); // Call the parent constructor
    }

    function get_columns() {

        $columns = array(
                //'cb' => '<input type="checkbox" />',
                'reference' => __('Reference', 'my_booking_ical_form'),
                'first_name' => __('First Name', 'my_booking_ical_form'),
                'last_name' => __('Last Name', 'my_booking_ical_form'),
                'entry_date' => __('Entry date', 'my_booking_ical_form'),
                'departure_date' => __('Departure date', 'my_booking_ical_form'),
                'email' => "Email",
                'phone' => __('Phone', 'my_booking_ical_form'),
                'created_at' => __('Request received', 'my_booking_ical_form'),
                'status' => __('Status', 'my_booking_ical_form'),
        );

        return $columns;
    }

    private function get_table_data() {
        global $wpdb;

        $table = $wpdb->prefix . 'my_booking_ical_requests';

        return $wpdb->get_results(
            "SELECT * from {$table} where form_id = {$_GET['form_id']}",
            ARRAY_A
        );
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'reference':
                return createReferenceRequest($this->form->reference, $item['entry_date'], $item['id']);
                break;
            case 'departure_date':
            case 'entry_date':
                return date("d-m-Y", strtotime($item[$column_name]));
                break;
            case 'created_at':
                return date("d-m-Y H:i:s", strtotime($item[$column_name]));
                break;
            case 'status':
                if($item['status'] == 'pending_review'){
                    return '<span style="color:#FFA500">' . __('Pending review', 'my_booking_ical_form') . '</span>';
                }elseif($item['status'] == 'validated'){
                    return __('Validated', 'my_booking_ical_form');
                }else{
                    return __('Denied', 'my_booking_ical_form');
                }
                break;
            case 'id':
            case 'first_name':
            case 'last_name':
            case 'email':
            case 'phone':
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
            'created_at'  => array('created_at', true),
            'first_name'  => array('first_name', false),
            'last_name' => array('last_name', false),
            'email' => array('email', false),
            'entry_date' => array('entry_date', false),
            'departure_date' => array('departure_date', false),
            'status' => array('status', false),
        );

        return $sortable_columns;
    }

    function usort_reorder($a, $b) {
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'created_at';

        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'desc';

        $result = strcmp($a[$orderby], $b[$orderby]);

        return ($order === 'asc') ? $result : -$result;
    }

    // Add action links (column_{namecolumn})

    public function column_reference($item) {
        $requests_show_link = admin_url('admin.php?page=my_booking_ical_requests_show&id=' .  $item['id']);
        $delete_link = admin_url('admin.php?page=my_booking_ical_requests_delete&id=' . $item['id']); 
        $output    = '';
 
        $output .= '<strong><a href="' . esc_url( $requests_show_link ) . '" class="row-title">' . createReferenceRequest($this->form->reference, $item['entry_date'], $item['id']) . '</a></strong>';

        $actions = array(
            'view'   => '<a href="' . esc_url( $requests_show_link ) . '">' . __( 'View', 'my_booking_ical_form' ) . '</a>',
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
        $primary = 'created_at';
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

function my_booking_ical_requests() {

    global $wpdb;

    $item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "my_booking_ical_forms WHERE id = " . $_GET['form_id']);
    $table = new My_Booking_Ical_Requests($item);
    $table->prepare_items();

    require(MBIF_DIR . '/views/admin/my_booking_ical_requests.php');
}

function my_booking_ical_requests_show() {

    global $wpdb;

    if(isset($_POST['status'])){
        $status = $_POST['status'];

        // Update the data in the database
        $wpdb->update(
            $wpdb->prefix . 'my_booking_ical_requests',
            array(
                'status' => $status
            ),
            array( 'id' => $_POST['id'] ),
            array(
                '%s'
            ),
            array( '%d' )
        );

        //echo '<script>window.location.href = "' . admin_url('admin.php?page=my_booking_ical_forms') . '"</script>';
        echo '<script>window.location.href = "' . admin_url('admin.php?page=my_booking_ical_requests_show&id=' . $_POST['id']) . '"</script>';
        exit;

    }else{

        $form = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "my_booking_ical_forms WHERE id = " . $item->form_id);

        $table = new My_Booking_Ical_Requests($form);
        $table->prepare_items();

        $item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "my_booking_ical_requests WHERE id = " . $_GET['id']);

        require(MBIF_DIR . '/views/admin/my_booking_ical_requests_show.php');
    }
}

function my_booking_ical_requests_validate(){
    global $wpdb;
    
    if(isset($_GET['id'])) {

        $item = $wpdb->get_row("SELECT form_id FROM " . $wpdb->prefix . "my_booking_ical_requests WHERE id = " . $_GET['id']);

        $wpdb->update(
            $wpdb->prefix . 'my_booking_ical_requests',
            array(
                'validated_reservation' => $_GET['value']
            ),
            array( 'id' => $_GET['id'] ),
            array(
                '%s'
            ),
            array( '%d' )
        );

        echo '<script>window.location.href = "' . admin_url('admin.php?page=my_booking_ical_requests&form_id=' . $item->form_id) . '"</script>';
        exit;

    }
}

function my_booking_ical_requests_delete() {

    if(isset($_GET['id'])) {

        global $wpdb;

        $item = $wpdb->get_row("SELECT form_id FROM " . $wpdb->prefix . "my_booking_ical_requests WHERE id = " . $_GET['id']);

        $wpdb->delete( 
            $wpdb->prefix . 'my_booking_ical_requests', 
            array( 'id' => $_GET['id'] ) 
        );
        
        // Todo: provisional redirection method using JS. The wp_redirect() function does not work because HTTP headers have already been sent. The way I am doing it is probably not entirely correct; it should be done differently... another day.
        echo '<script>window.location.href = "' . admin_url('admin.php?page=my_booking_ical_requests&form_id=' . $item->form_id) . '"</script>';
        exit;
    }
}