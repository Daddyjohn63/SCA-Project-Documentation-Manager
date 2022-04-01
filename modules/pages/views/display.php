
<!-- single.php for displaying each page content -->
<section>
    <div class="page-nav">
        <div><?php
        $link_attr['class'] = 'button';
        echo anchor('chapters', '<i class=\'fa fa-home\'></i>', $link_attr);
        echo anchor($prev_url, '<i class=\'fa fa-arrow-left\'></i>', $link_attr );
        ?></div>
        <div>
        <?=anchor($next_url, '<i class=\'fa fa-arrow-right\'></i>', $link_attr ) ?>
        </div>
    </div>

<h1><?=$page_headline?></h1>
<p><?=$page_body?></p>
</section>

<style>

    h1{
        margin-top: 30px;
        font-size: 40px;
    }
    .page-nav {
        display:flex;
        align-items:center;
        justify-content:space-between;
    }
</style>