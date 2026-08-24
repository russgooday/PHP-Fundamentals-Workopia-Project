<!DOCTYPE html>
<html lang="en">
<?= loadPartial('head', ['title' => "{$status_code} {$title}"]); ?>

<body class="bg-gray-100">
    <?= loadPartials(['navbar', 'top-banner']); ?>

    <section>
        <div class="container mx-auto p-4 mt-4">
            <div class="text-center text-3xl mb-4 font-bold border border-gray-300 p-3">
                Error <?= e("{$status_code} : {$title}"); ?>
            </div>
            <p class="text-center text-2xl mb-4"><?= e($message) ?></p>
        </div>
    </section>
</body>

</html>
