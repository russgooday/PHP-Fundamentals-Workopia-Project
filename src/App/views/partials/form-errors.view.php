<?php
if (!empty($errors)):
    foreach ($errors as $error):
?>
    <div class='message bg-red-100 p-3 my-3'><?= e($error) ?></div>
<?php
    endforeach;
endif;
?>
