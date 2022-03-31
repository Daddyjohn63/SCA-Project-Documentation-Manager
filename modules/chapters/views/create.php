<h1><?= $headline ?></h1>
<?= validation_errors() ?>
<div class="card">
    <div class="card-heading">
        Chapter Details
    </div>
    <div class="card-body">
        <?php
        echo form_open($form_location);
        echo form_label('Chapter Title');
        echo form_input('chapter_title', $chapter_title, array("placeholder" => "Enter Chapter Title", "autocomplete"=>"off"));
        echo form_submit('submit', 'Submit');
        echo anchor($cancel_url, 'Cancel', array('class' => 'button alt'));
        echo form_close();
        ?>
    </div>
</div>