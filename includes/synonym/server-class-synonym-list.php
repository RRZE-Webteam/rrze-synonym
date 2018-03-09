<?php

namespace RRZE\Synonym\Server;

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-screen.php' );
    require_once( ABSPATH . 'wp-admin/includes/screen.php' );
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    require_once( ABSPATH . 'wp-admin/includes/template.php' );
}

if(is_admin()) {
    new Synonym_list_main();
}

ob_start();

class Synonym_list_main {
    
    public function __construct() {
        add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
        add_action( 'admin_menu', array($this, 'rrze_synonym_client_list_table_page' ), 11);
    }
    
    public static function set_screen( $status, $option, $value ) {
        return $value;
    }
    
    public function screen_option() {

        $option = 'per_page';
        $args   = [
                'label'   => 'Synonyme',
                'default' => 20,
                'option'  => 'files_per_page'
        ];

        add_screen_option( $option, $args );
        
    }
   
    public function rrze_synonym_client_list_table_page() {
        $hook = add_submenu_page( 
           'edit.php?post_type=synonym', __( 'Show Server Synonyms', 'rrze-synonym-server' ), __( 'Show Server Synonyms', 'rrze-synonym-server' ), 'manage_options', 'rrze_synonym_client_options', array(&$this, 'rrze_synonym_client_list_table_view')
        );
        
        add_action( "load-$hook", [ $this, 'screen_option' ] );
        
    } 
  
    public function rrze_synonym_client_list_table_view() {
        $remote_synonyme = new RRZE_Synonym(); ?>
        <form method="post">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
        <?php $remote_synonyme->prepare_items();
        ?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <h2><?php _e( 'Synonym overview', 'rrze-synonym-server' ) ?></h2>
                <?php $remote_synonyme->display(); ?>
            </div>
        </form>
        <?php
    }
}

class RRZE_Synonym extends \WP_List_Table {
    
    public function get_bulk_actions() {
        $actions = array(
            'update'        => __( 'Update list', 'rrze-synonym-server' ),
        );
        return $actions;
    }
    
    public function process_bulk_action() {
        
        if ('update' === $this->current_action()) {
            $syn = WplisttableHelper::getSynonymsForWPListTable();
            update_option('serversynonyms', $syn);
            $html = '<div id="message" class="updated notice is-dismissible">
			<p>' . __( 'List updated!', 'rrze-synonym-server' ) .'</p>
		</div>';
            echo $html;
        }
    }
    
    function extra_tablenav( $which ) {
        $search = @$_POST['s'] ? esc_attr($_POST['s']) : "";
        if ( $which == "top" ) : ?>
        <form method="post">
            <div class="actions">
                    <p class="search-box">
                            <label for="post-search-input" class="screen-reader-text">Search Pages:</label>
                            <input type="search" value="<?php echo $search; ?>" name="s" id="post-search-input">
                            <input type="submit" value="<?php _e( 'Search', 'rrze-synonym-server' ); ?>" class="button" id="search-submit" name="">
                    </p>
            </div>
        </form>
        <?php endif;
	}
    
    function prepare_items() {
        $columns = $this->get_columns();
        $this->_column_headers = $this->get_column_info();
        $this->process_bulk_action();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $data = get_option('serversynonyms');
        if(!$data) $data = WplisttableHelper::getSynonymsForWPListTable();
        if(@$_POST['s']) {
        $s = @$_POST['s'];
        $filterBy = $s;
        $data = array_filter($data, function ($var) use ($filterBy) {
            return ($var['domain']  == $filterBy ||
                    $var['title']   == $filterBy ||
                    $var['slug']    == $filterBy ||
                    $var['synonym'] == $filterBy ||
                    $var['id'] == $filterBy);
            });
        }
        if($data) usort( $data, array( &$this, 'usort_reorder' ) );
        $this->_column_headers = array($columns, $hidden, $sortable);
        $perPage     = $this->get_items_per_page( 'files_per_page', 10 );
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
        if($data) {
            $items = array_slice($data,(($currentPage-1)*$perPage),$perPage);
        } else {
            $items = '';
        }
        $this->items = $items;
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'domain'    => array('domain', false),
            'id'        => array('id',false),
            'slug'      => array('slug',false),
            'title'     => array('title',false),
            'synonym'   => array('synonym',false)
        );
        return $sortable_columns;
    }
    
    function get_columns(){
        $columns = array(
          'domain'  => 'Domain',
          'id'      => 'ID',
          'title'   => __( 'Title', 'rrze-synonym-server' ),
          'slug'    => 'Slug',
          'synonym' => 'Synonym'
        );
        return $columns;
    }
    
    function column_default( $item, $column_name ) {
        switch( $column_name ) { 
            case 'domain':
            case 'id':
            case 'title':
            case 'slug':
            case 'synonym':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        }
    }
    
    function usort_reorder( $a, $b ) {
        $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'id';
        $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
        $result = strcmp( $a[$orderby], $b[$orderby] );
        return ( $order === 'asc' ) ? $result : -$result;
    }
    
    function no_items() {
        _e( 'No data found!', 'rrze-synonym-server' );
        delete_option('serversynonyms');
    }
    
}
?>
