<?php
class Chapters extends Trongate {

    private $default_limit = 20;
    private $per_page_options = array(10, 20, 50, 100); 
    
    function index(){
        //fetch contents
        $data['toc_rows'] = $this->_fetch_toc_rows();
        $data['view_file'] = 'table_of_contents';
        $this->template('public', $data);
    }

    function _fetch_toc_rows(){
        //will get data from chapters and pages tables.
        $sql = '
        SELECT
            chapters.url_string as chapter_url_str,
            chapters.chapter_title,
            pages.id as page_id,
            pages.url_string as page_url_str,
            pages.page_headline,
            pages.chapters_id as chapter_id
        FROM
            chapters
        LEFT OUTER JOIN
            pages
        ON
            chapters.id = pages.chapters_id
        ORDER BY
            chapters.priority, pages.priority
        ';
        $rows = $this->model->query($sql, 'object');
        return $rows;
    }

    //reinc priorities when a chapter is deleted.
    function _reinc_priorities(){
        $chapters = $this->model->get('id', 'chapters');
        $count = 0;
        foreach($chapters as $chapter) {
            $count++;
            $update_id = $chapter->id;
            $data['priority'] = $count;
            $this->model->update($update_id, $data, 'chapters');
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

        if (is_numeric($update_id)) {
            $data['headline'] = 'Update Chapter Record';
            $data['cancel_url'] = BASE_URL.'chapters/show/'.$update_id;
        } else {
            $data['headline'] = 'Create New Chapter Record';
            $data['cancel_url'] = BASE_URL.'chapters/manage';
        }

        $data['form_location'] = BASE_URL.'chapters/submit/'.$update_id;
        $data['view_file'] = 'create';
        $this->template('admin', $data);
    }

    function manage() {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        if (segment(4) !== '') {
            $data['headline'] = 'Search Results';
            $searchphrase = trim($_GET['searchphrase']);
            $params['chapter_title'] = '%'.$searchphrase.'%';
            $sql = 'select * from chapters
            WHERE chapter_title LIKE :chapter_title
            ORDER BY id';
            $all_rows = $this->model->query_bind($sql, $params, 'object');
        } else {
            $data['headline'] = 'Manage Chapters';
            $all_rows = $this->model->get('id');
        }

        $pagination_data['total_rows'] = count($all_rows);
        $pagination_data['page_num_segment'] = 3;
        $pagination_data['limit'] = $this->_get_limit();
        $pagination_data['pagination_root'] = 'chapters/manage';
        $pagination_data['record_name_plural'] = 'chapters';
        $pagination_data['include_showing_statement'] = true;
        $data['pagination_data'] = $pagination_data;

        $data['rows'] = $this->_reduce_rows($all_rows);
        $data['selected_per_page'] = $this->_get_selected_per_page();
        $data['per_page_options'] = $this->per_page_options;
        $data['view_module'] = 'chapters';
        $data['view_file'] = 'manage';
        $this->template('admin', $data);
    }

    function show() {
        $this->module('trongate_security');
        $token = $this->trongate_security->_make_sure_allowed();
        $update_id = segment(3);

        if ((!is_numeric($update_id)) && ($update_id != '')) {
            redirect('chapters/manage');
        }

        $data = $this->_get_data_from_db($update_id);
        $data['token'] = $token;

        if ($data == false) {
            redirect('chapters/manage');
        } else {
            $data['update_id'] = $update_id;
            $data['headline'] = 'Chapter Information';
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

            $this->validation_helper->set_rules('chapter_title', 'Chapter Title', 'required|min_length[2]|max_length[255]');

            $result = $this->validation_helper->run();

            if ($result == true) {

                $update_id = segment(3);
                $data = $this->_get_data_from_post();
                $data['url_string'] = strtolower(url_title($data['chapter_title']));

                if (is_numeric($update_id)) {
                    //update an existing record
                    $this->model->update($update_id, $data, 'chapters');
                    $flash_msg = 'The record was successfully updated';
                } else {
                    //insert the new record
                    $data['priority'] = $this->_calc_next_priority();
                    $update_id = $this->model->insert($data, 'chapters');
                    $flash_msg = 'The record was successfully created';
                }

                set_flashdata($flash_msg);
                redirect('chapters/show/'.$update_id);

            } else {
                //form submission error
                $this->create();
            }

        }          

    }

    function _calc_next_priority(){
        $number_rows = $this->model->count();
        $next_priority = $number_rows + 1;
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
            $params['module'] = 'chapters';
            $this->model->query_bind($sql, $params);

            //delete the record
            $this->model->delete($params['update_id'], 'chapters');

            $this->_reinc_priorities();

            //set the flashdata
            $flash_msg = 'The record was successfully deleted';
            set_flashdata($flash_msg);

            //redirect to the manage page
            redirect('chapters/manage');
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
        redirect('chapters/manage');
    }

    function _get_data_from_db($update_id) {
        $record_obj = $this->model->get_where($update_id, 'chapters');

        if ($record_obj == false) {
            $this->template('error_404');
            die();
        } else {
            $data = (array) $record_obj;
            return $data;        
        }
    }

    function _get_data_from_post() {
        $data['chapter_title'] = post('chapter_title', true);        
        return $data;
    }

}