<?php if ($message):
    $message['bg-color'] = $message['type'] === 'success' ? 'bg-green-100' : 'bg-red-100';
?>
<div class="message <?= e($message['bg-color']) ?> p-3 my-3">
    <?= e($message['message']) ?>
</div>
<?php endif; ?>