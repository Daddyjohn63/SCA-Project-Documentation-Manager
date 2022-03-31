<h1><?= $headline ?></h1>
<?= validation_errors() ?>
<div class="card">
    <div class="card-heading">
        Page Details
    </div>
    <div class="card-body">
        <?php
        echo form_open($form_location);
        echo form_label('Page Headline');
        echo form_input('page_headline', $page_headline, array("placeholder" => "Enter Page Headline"));
        echo form_label('Page Body');
        echo form_textarea('page_body', $page_body, array("placeholder" => "Enter Page Body"));
        echo form_submit('submit', 'Submit');
        echo anchor($cancel_url, 'Cancel', array('class' => 'button alt'));
        echo form_close();
        ?>
    </div>
</div>