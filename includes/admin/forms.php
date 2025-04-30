<?php

// Check if the WP_List_Table class exists, and include it if not
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

// Define the main class for managing the booking iCal forms
class My_Booking_Ical_Forms extends WP_List_Table {

    private $table_data; // Holds the table data
    private $elements_per_page = 10; // Number of elements per page

    // Define the columns for the table
    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />', // Checkbox column
            'reference' => __('Reference', 'my_booking_ical_form'), // Reference column
            'title' => __('Title', 'my_booking_ical_form'), // Title column
            'ical_booking_url' => "iCal Booking URL", // iCal Booking URL column
            'ical_airbnb_url' => "iCal Airbnb URL", // iCal Airbnb URL column
            'price' =>  __('General price', 'my_booking_ical_form'), // Price column
            'parking_option' =>  __('Parking option', 'my_booking_ical_form'), // Parking option column
            'shortcode' => "Shortcode", // Shortcode column
            'num_requests' => __('Requests', 'my_booking_ical_form'), // Number of requests column
            'pending_count' => __('Pending review', 'my_booking_ical_form') // Pending review count column
        );

        return $columns;
    }

    // Retrieve table data from the database
    private function get_table_data() {
        global $wpdb;

        $table = $wpdb->prefix . 'my_booking_ical_forms';

        return $wpdb->get_results(
            "SELECT * from {$table}",
            ARRAY_A
        );
    }

    // Define the default behavior for each column
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
                return $wpdb->get_var($wpdb->prepare("SELECT count(id) FROM $table_name WHERE form_id = %d", $item['id']));
                break;

            case 'ical_booking_url':
            case 'ical_airbnb_url':
                return $item[$column_name] ? ('<a target="_blank" href="' . $item[$column_name] . '">' . __('View', 'my_booking_ical_form') . '</a>') : '--';
                break;

            case 'pending_count':
                global $wpdb;
                $table_name = $wpdb->prefix . "my_booking_ical_requests";
                return $wpdb->get_var($wpdb->prepare("SELECT count(id) FROM $table_name WHERE `form_id` = %d AND `status` = '%s'", $item['id'], 'pending_review'));
                break;

            case 'title':
            default:
                return $item[$column_name];
        }
    }

    // Define the checkbox column
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="element[]" value="%s" />',
            $item['id']
        );
    }

    // Define sortable columns
    protected function get_sortable_columns() {
        $sortable_columns = array(
            'reference' => array('reference', false),
            'title'  => array('title', false),
            'price' => array('price', false)
        );

        return $sortable_columns;
    }

    // Handle sorting of columns
    function usort_reorder($a, $b) {
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'title'; // Default order by title
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc'; // Default order is ascending
        $result = strcmp($a[$orderby], $b[$orderby]);
        return ($order === 'asc') ? $result : -$result;
    }

    // Define the reference column with action links
    public function column_reference($item) {
        $edit_link = admin_url('admin.php?page=my_booking_ical_forms_edit&id=' .  $item['id']);
        $delete_link = admin_url('admin.php?page=my_booking_ical_forms_delete&id=' . $item['id']); 
        $requests_view_link = admin_url('admin.php?page=my_booking_ical_requests&form_id=' . $item['id']); 
        $output    = '';
 
        $output .= '<strong><a href="' . esc_url($requests_view_link) . '" class="row-title">' . esc_html($item['reference']) . '</a></strong>';
 
        $actions = array(
            'view'   => '<a href="' . esc_url($requests_view_link) . '">' . __('View Requests', 'my_booking_ical_form') . '</a>',
            'edit'   => '<a href="' . esc_url($edit_link) . '">' . __('Edit', 'my_booking_ical_form') . '</a>',
            'delete'   => '<a href="' . esc_url($delete_link) . '" class="link-confirm" data-message="' . __('Are you sure to delete this record?', 'my_booking_ical_form') . '">' . __('Delete', 'my_booking_ical_form') . '</a>',
        );
 
        $row_actions = array();
 
        foreach ($actions as $action => $link) {
            $row_actions[] = '<span class="' . esc_attr($action) . '">' . $link . '</span>';
        }
 
        $output .= '<div class="row-actions">' . implode(' | ', $row_actions) . '</div>';
 
        return $output;
    }

    // Prepare the table items for display
    function prepare_items() {
        $this->table_data = $this->get_table_data();

        $columns = $this->get_columns();
        $hidden = array(); // No hidden columns
        $sortable = $this->get_sortable_columns();
        $primary = 'id';
        $this->_column_headers = array($columns, $hidden, $sortable);

        usort($this->table_data, array(&$this, 'usort_reorder'));

        // Handle pagination
        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);

        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $this->elements_per_page), $this->elements_per_page);

        $this->set_pagination_args(array(
            'total_items' => $total_items, // Total number of items
            'per_page'    => $this->elements_per_page, // Items per page
            'total_pages' => ceil($total_items / $this->elements_per_page) // Total pages
        ));
        
        $this->items = $this->table_data;
    }
}

// Function to display the booking iCal forms table
function my_booking_ical_forms() {
    $table = new My_Booking_Ical_Forms();
    $table->prepare_items();
    
    require(MBIF_DIR . '/views/admin/my_booking_ical_forms.php');
}

// Function to create a new booking iCal form
function my_booking_ical_forms_create() {
    if (isset($_POST['title'])) {
        global $wpdb;

        $reference = $_POST['reference'];
        $title = $_POST['title'];
        $ical_booking_url = $_POST['ical_booking_url'];
        $ical_airbnb_url = $_POST['ical_airbnb_url'];
        $min_days = $_POST['min_days'];
        $price = $_POST['price'];
        $max_capacity = $_POST['max_capacity'];
        $parking_option = $_POST['parking_option'];

        // Insert data into the database
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

        // Redirect to the forms page
        echo '<script>window.location.href = "' . admin_url('admin.php?page=my_booking_ical_forms') . '"</script>';
        exit;
    } else {
        require(MBIF_DIR . '/views/admin/my_booking_ical_forms-create.php');
    }
}

// Function to edit an existing booking iCal form
function my_booking_ical_forms_edit() {
    global $wpdb;
    
    if (isset($_POST['id'])) {
        $reference = $_POST['reference'];
        $title = $_POST['title'];
        $ical_booking_url = $_POST['ical_booking_url'];
        $ical_airbnb_url = $_POST['ical_airbnb_url'];
        $min_days = $_POST['min_days'];
        $price = $_POST['price'];
        $max_capacity = $_POST['max_capacity'];
        $parking_option = $_POST['parking_option'];

        // Update data in the database
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
            array('id' => $_POST['id']),
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
            array('%d')
        );

        // Redirect to the edit page
        echo '<script>window.location.href = "' . admin_url('admin.php?page=my_booking_ical_forms_edit&id=' . $_GET['id']) . '"</script>';
        exit;
    } else {
        $item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "my_booking_ical_forms WHERE id = " . $_GET['id']);
        $table = new My_Booking_Ical_Prices();
        $table->prepare_items(); 
        require(MBIF_DIR . '/views/admin/my_booking_ical_forms-edit.php');
    }
}

// Function to delete a booking iCal form
function my_booking_ical_forms_delete() {
    if (isset($_GET['id'])) {
        global $wpdb;

        // Delete the form and associated requests from the database
        $wpdb->delete(
            $wpdb->prefix . 'my_booking_ical_forms',
            array('id' => $_GET['id'])
        );

        $wpdb->delete(
            $wpdb->prefix . 'my_booking_ical_requests',
            array('form_id' => $_GET['id'])
        );

        // Redirect to the forms page
        echo '<script>window.location.href = "' . admin_url('admin.php?page=my_booking_ical_forms') . '"</script>';
        exit;
    }
}
