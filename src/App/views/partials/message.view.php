<?php if ($flash):
    $flash['bg-color'] = $flash['type'] === 'success' ? 'bg-green-100' : 'bg-red-100';
?>
<div class="message <?= e($flash['bg-color']) ?> p-3 my-3">
    <?= e($flash['message']) ?>
</div>
<?php endif; ?>