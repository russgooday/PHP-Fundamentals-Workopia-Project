<!DOCTYPE html>
<html lang="en">
<?
$error_code = $error_code ?? '';
loadPartial('head', ['page' => ['title' => htmlspecialchars($error_code)]]);
?>

<body class="bg-gray-100">
    <? loadPartials(['navbar', 'top-banner']); ?>

    <!-- Error Message -->
    <section>
        <div class="container mx-auto p-4 mt-4">
            <div class="text-center text-3xl mb-4 font-bold border border-gray-300 p-3">
                Error <?= htmlspecialchars($error_code) ?>
            </div>
            <? if ($error_code == 404): ?>
            <p class="text-center text-2xl mb-4">This page does not exist</p>
            <? else: ?>
            <p class="text-center text-2xl mb-4">An unexpected error occurred</p>
            <? endif; ?>
        </div>
    </section>

</body>

</html>