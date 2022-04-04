<section>
    <h1>Table of Contents</h1>

    <div class="contents">
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
                echo '</ul></div>';
            }

        echo '<div class="chapter" draggable="true">';
        echo '<h3>'.$chapter_title.'</h3>';
        echo '<ul>';
        $current_chapter_title = $chapter_title;
        }

        $page_url = BASE_URL.'pages/display/'.$chapter_url_str.'/'.$page_url_str;
        echo '<li>';
        echo anchor($page_url, $page_headline);

        echo '</li>';
    }
    echo'</ul></div>';
    
    ?></div>
</section>

<style>

.contents {
    max-width: 450px;
}
.chapter{
    border:3px silver dashed;
    border-radius:6px;
    padding:12px;
}

</style>

<script>
// define dropzone.
var dropzone = document.getElementsByTagName('body')[0];
//clarify what the chapters are.
var chapters = document.getElementsByClassName('chapter');
//clarify the conainer the chapters sit in.
var chaptersContainer = document.getElementsByClassName('contents')[0];
var selectedNode;
var selectedNodePos = 0;

for (var i = 0; i < chapters.length; i++) {
    chapters[i].addEventListener('dragstart', (ev) => {
        //console.log('drag started');
        selectedNode = ev.target;
    });
}

dropzone.addEventListener('dragover', (ev) => {
    ev.preventDefault();
    console.log('something is dragging over me');
});

dropzone.addEventListener('drop', (ev) => {
    ev.preventDefault();
    console.log('dropped');
    dropChapter(ev.clientY);
});

//we need to know the position of the selected node (chapter) in relation to it's siblings (the other chapters).
function estSelectedNodePos(yPos){
    var siblings = chapters;
    var foundNodeAbove = false;
    for (var i = 0; i < siblings.length; i++) {
        //get the position (obj) of each of the siblings.
        var elPos = siblings[i].getBoundingClientRect();
        //get the top (y position) of the siblings
        var elTop = elPos.top;
        
        //get the bottom (y position) of the siblings
        var elBottom = elPos.bottom;
        //calculate the centreY position of the siblings.
        var elCenterY = elTop + ((elBottom - elTop)/2);

        console.log(elTop);
        console.log(elCenterY);
        console.log(elBottom);
        console.log('*********');
        
        if(elCenterY < yPos){
            //this sibling element MUST BE ABOVE the mouse pointer.
            selectedNodePos = i+1;
            foundNodeAbove = true;
        }
    }

    if (foundNodeAbove == false){
        selectedNodePos = 0;
    }
}

function dropChapter(yPos) {
    //establish the position of the selectedNode (chapter).
    estSelectedNodePos(yPos);
    console.log(selectedNodePos);
    //add the selectedNode back onto our list of chapters.

}

</script>