<h3><?php echo $title ?></h3>
<?php if ($acheteurs->count() > 0): ?>
            <ul>
    <?php foreach ($acheteurs as $cvi): ?>
                <?php include_partial('headerAcheteursItem', array('name' => $list_acheteurs[$cvi]['nom'])) ?>
    <?php endforeach; ?>
            </ul>
<?php endif; ?>