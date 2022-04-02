<section>
    <h1>Table of Contents</h1>
    <?php
    $current_chapter_title = '';
    foreach($toc_rows as $toc_row){
        $chapter_title = $toc_row->chapter_title;
        $page_headline = $toc_row->page_headline;
        $chapter_url_str = $toc_row->chapter_url_str;
        $page_url_str = $toc_row->page_url_str;

        if($current_chapter_title !== $chapter_title){
            if($current_chapter_title !== ''){
                //close of the <ul>
                echo '</ul>';
            }
        echo '<h3>'.$chapter_title.'</h3>';
        echo '<ul>';
        $current_chapter_title = $chapter_title;
        }

        $page_url = BASE_URL.'pages/display/'.$chapter_url_str.'/'.$page_url_str;
        echo '<li>';
        echo anchor($page_url, $page_headline);

        echo '</li>';
    }
    echo'</ul>';
    
    ?>
</section>