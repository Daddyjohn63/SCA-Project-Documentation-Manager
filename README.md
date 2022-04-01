Every time somebody gives the Trongate framework a star on Github, middle managers and self appointed PHP aficionados start crying like babies.  Do something amazing today.  Give Trongate a star on GitHub and together we SHALL make PHP great again!  https://github.com/davidjconnelly/trongate-framework# SCA-Project-Documentation-Manager

This is the project documentation project from SCA. It

KEY LEARNING POINTS.  

* FOR ORDERING THE CHAPTERS AND PAGES IN THE VIEW FILE + FOR DRAG AND DROP FUNCTIONALITY.

* How to automatically create priority records in the tables.

* Re-indexing the priority columns in the pages and chapters table so that if a record is deleted, the the priorities keep in incremental order.

* adding function index(){} within a Controller file, will route automatically to url/module-name/

*  function index(){
        $data['view_file'] = 'table_of_contents';
        $this->template('public', $data);
    }

    we then create a view file called 'table_of_contents.php'.

    URL will still be url/module-name/

* One to many relationships (Parent = Chapters, Child = Pages .. the parent is the 'One' and child is the 'many')...

* using query builder to build sql statement so that we populate the data into the page.

