<?php
class Pages extends Trongate {

    private $default_limit = 20;
    private $per_page_options = array(10, 20, 50, 100); 

    function display(){
        //pass in the url segments as parameters, so we can get all info on that page.
        $params['chapter_url_str'] = segment(3);
        $params['page_url_str'] = segment(4);
        //json($params);
        $sql = 'SELECT
                    pages.* 
                FROM
                    chapters
                INNER JOIN
                    pages
                ON
                    chapters.id = pages.chapters_id
                WHERE
                    chapters.url_string=:chapter_url_str
                AND
                    pages.url_string=:page_url_str
        ';
        //$rows is all the info we need on this page.
        $rows = $this->model->query_bind($sql,$params,'object');
       // json($rows);
       if(!isset($rows[0])){
           //not found therefore redirect back to the table of contents
           redirect('chapters');
       }
       //turn the first row into an array.
       $data = (array)$rows[0];
       //var_dump($data);
       $next_prev = $this->_fetch_next_prev($data['id']); //pass in page id.
       $data['prev_url'] = $next_prev['prev_url'];
       $data['next_url'] = $next_prev['next_url'];
       $data['view_file'] = 'display';
       $this->template('public', $data);
    }

    function _fetch_next_prev($page_id){
        $this->module('chapters');
        //get info an all pages from the DB.
        $toc_rows = $this->chapters->_fetch_toc_rows();
       // json($toc_rows);
       foreach($toc_rows as $key => $toc_row){
          //json($key);
           $row_page_id = $toc_row->page_id;
           if($row_page_id == $page_id){
               $current_page_key = $key; //prev url would therefore be $key-1 and next url would be $key+1.
           }
       }
      if(isset($toc_rows[$current_page_key-1])){
          $record_obj = $toc_rows[$current_page_key-1];
          $chapter_url_str = $record_obj->chapter_url_str;
          $page_url_str = $record_obj->page_url_str;
          $prev_url = BASE_URL.'pages/display/'.$chapter_url_str.'/'.$page_url_str;
         // echo $prev_url; die();
      } else {
          $prev_url = 'chapters';
      }

      if(isset($toc_rows[$current_page_key+1])){
        $record_obj = $toc_rows[$current_page_key+1];
        $chapter_url_str = $record_obj->chapter_url_str;
        $page_url_str = $record_obj->page_url_str;
        $next_url = BASE_URL.'pages/display/'.$chapter_url_str.'/'.$page_url_str;
       // echo $prev_url; die();
    } else {
        $next_url = 'chapters';
    }

    $next_prev['prev_url'] = $prev_url;
    $next_prev['next_url'] = $next_url;
    return $next_prev;

    }
    
    function _reinc_priorities($chapter_id){
        //fetch all the pages that belong to this chapter id.
        $params['chapter_id'] = $chapter_id;
        $sql = 'SELECT id FROM pages WHERE chapters_id = :chapter_id';
        //will return some rows of data that have the chapter id
        $pages = $this->model->query_bind($sql, $params, 'object');
        //set count to zero
        $count = 0;
        foreach($pages as $page) {
            //count increments by one
            $count++;
            //grab the records id so we can target it.
            $update_id = $page->id;
            //the data element with a key of 'priority' (which is the column title in the table). Set it to equal the current count.
            $data['priority'] = $count;
            //pass in the data and update the DB.
            $this->model->update($update_id, $data, 'pages');
        }
    }

    function create() {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $update_id = segment(3);
        $submit = post('submit');

        if (($submit == '') && (is_numeric($update_id))) {
            $data = $this->_get_data_from_db($update_id);
        } else {
            $data = $this->_get_data_from_post();
        }

        $data['chapters_options'] = $this->_get_chapters_options($data['chapters_id']);

        if (is_numeric($update_id)) {
            $data['headline'] = 'Update Page Record';
            $data['cancel_url'] = BASE_URL.'pages/show/'.$update_id;
        } else {
            $data['headline'] = 'Create New Page Record';
            $data['cancel_url'] = BASE_URL.'pages/manage';
        }

        $data['form_location'] = BASE_URL.'pages/submit/'.$update_id;
        $data['view_file'] = 'create';
        $this->template('admin', $data);
    }

    function manage() {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        if (segment(4) !== '') {
            $data['headline'] = 'Search Results';
            $searchphrase = trim($_GET['searchphrase']);
            $params['page_headline'] = '%'.$searchphrase.'%';
            $sql = 'select * from pages
            WHERE page_headline LIKE :page_headline
            ORDER BY id';
            $all_rows = $this->model->query_bind($sql, $params, 'object');
        } else {
            $data['headline'] = 'Manage Pages';
            $all_rows = $this->model->get('id');
        }

        $pagination_data['total_rows'] = count($all_rows);
        $pagination_data['page_num_segment'] = 3;
        $pagination_data['limit'] = $this->_get_limit();
        $pagination_data['pagination_root'] = 'pages/manage';
        $pagination_data['record_name_plural'] = 'pages';
        $pagination_data['include_showing_statement'] = true;
        $data['pagination_data'] = $pagination_data;

        $data['rows'] = $this->_reduce_rows($all_rows);
        $data['selected_per_page'] = $this->_get_selected_per_page();
        $data['per_page_options'] = $this->per_page_options;
        $data['view_module'] = 'pages';
        $data['view_file'] = 'manage';
        $this->template('admin', $data);
    }

    function show() {
        $this->module('trongate_security');
        $token = $this->trongate_security->_make_sure_allowed();
        $update_id = segment(3);

        if ((!is_numeric($update_id)) && ($update_id != '')) {
            redirect('pages/manage');
        }

        $data = $this->_get_data_from_db($update_id);
        $data['token'] = $token;

        if ($data == false) {
            redirect('pages/manage');
        } else {
            $data['update_id'] = $update_id;
            $data['headline'] = 'Page Information';
            $data['view_file'] = 'show';
            $this->template('admin', $data);
        }
    }
    
    function _reduce_rows($all_rows) {
        $rows = [];
        $start_index = $this->_get_offset();
        $limit = $this->_get_limit();
        $end_index = $start_index + $limit;

        $count = -1;
        foreach ($all_rows as $row) {
            $count++;
            if (($count>=$start_index) && ($count<$end_index)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    function submit() {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $submit = post('submit', true);

        if ($submit == 'Submit') {

            $this->validation_helper->set_rules('page_headline', 'Page Headline', 'required|min_length[2]|max_length[255]');
            $this->validation_helper->set_rules('page_body', 'Page Body', 'required|min_length[2]');

            $result = $this->validation_helper->run();

            if ($result == true) {

                $update_id = segment(3);
                $data = $this->_get_data_from_post();
                $data['chapters_id'] = (is_numeric($data['chapters_id']) ? $data['chapters_id'] : 0);
                $data['url_string'] = strtolower(url_title($data['page_headline']));

                if (is_numeric($update_id)) {
                    //update an existing record
                    $this->model->update($update_id, $data, 'pages');
                    $flash_msg = 'The record was successfully updated';
                } else {
                    //insert the new record
                    $data['priority'] = $this->_calc_next_priority($data['chapters_id']);
                    $update_id = $this->model->insert($data, 'pages');
                    $flash_msg = 'The record was successfully created';
                }

                set_flashdata($flash_msg);
                redirect('pages/show/'.$update_id);

            } else {
                //form submission error
                $this->create();
            }

        }

    }

    function _calc_next_priority($chapter_id){
        //count pages for this chapter and then add one
        $params['chapter_id'] = $chapter_id;
        $sql = 'SELECT id FROM pages WHERE chapters_id = :chapter_id';
        //will return some rows of data.
        $rows = $this->model->query_bind($sql, $params, 'object');
        $num_rows=count($rows);
        $next_priority = $num_rows + 1;
        return $next_priority;
    }

    function submit_delete() {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $submit = post('submit');
        $params['update_id'] = segment(3);

        if (($submit == 'Yes - Delete Now') && (is_numeric($params['update_id']))) {
            //delete all of the comments associated with this record
            $sql = 'delete from trongate_comments where target_table = :module and update_id = :update_id';
            $params['module'] = 'pages';
            $this->model->query_bind($sql, $params);
            //get the chapter id from the page that is being deleted. we can the pass this below into _reinc_priorities
            $record_obj = $this->model->get_where($params['update_id']);
            //get the chapters_id from the object we just created.
            $chapter_id = $record_obj->chapters_id;

            //delete the record
            $this->model->delete($params['update_id'], 'pages');

            //pass in chapter id from above.
            $this->_reinc_priorities($chapter_id);

            //set the flashdata
            $flash_msg = 'The record was successfully deleted';
            set_flashdata($flash_msg);

            //redirect to the manage page
            redirect('pages/manage');
        }
    }

    function _get_limit() {
        if (isset($_SESSION['selected_per_page'])) {
            $limit = $this->per_page_options[$_SESSION['selected_per_page']];
        } else {
            $limit = $this->default_limit;
        }

        return $limit;
    }

    function _get_offset() {
        $page_num = segment(3);

        if (!is_numeric($page_num)) {
            $page_num = 0;
        }

        if ($page_num>1) {
            $offset = ($page_num-1)*$this->_get_limit();
        } else {
            $offset = 0;
        }

        return $offset;
    }

    function _get_selected_per_page() {
        if (!isset($_SESSION['selected_per_page'])) {
            $selected_per_page = $this->per_page_options[1];
        } else {
            $selected_per_page = $_SESSION['selected_per_page'];
        }

        return $selected_per_page;
    }

    function set_per_page($selected_index) {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        if (!is_numeric($selected_index)) {
            $selected_index = $this->per_page_options[1];
        }

        $_SESSION['selected_per_page'] = $selected_index;
        redirect('pages/manage');
    }

    function _get_data_from_db($update_id) {
        $record_obj = $this->model->get_where($update_id, 'pages');

        if ($record_obj == false) {
            $this->template('error_404');
            die();
        } else {
            $data = (array) $record_obj;
            return $data;        
        }
    }

    function _get_data_from_post() {
        $data['page_headline'] = post('page_headline', true);
        $data['page_body'] = post('page_body', true);        
        $data['chapters_id'] = post('chapters_id');
        return $data;
    }

    function _get_chapters_options($selected_key) {
        $this->module('module_relations');
        $options = $this->module_relations->_fetch_options($selected_key, 'pages', 'chapters');
        return $options;
    }
}