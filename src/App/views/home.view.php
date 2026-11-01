<!DOCTYPE html>
<html lang='en'>
<?= $this->render('partials/head' , compact('title')); ?>

<body class='bg-gray-100'>
    <?= $this->render('partials/navbar') ?>
    <?= $this->render('partials/showcase-search') ?>
    <?= $this->render('partials/top-banner') ?>

    <!-- Job Listings -->
    <section>
        <div class='container mx-auto p-4 mt-4'>
            <div class='text-center text-3xl mb-4 font-bold border border-gray-300 p-3'>Recent Jobs</div>
            <div class='grid grid-cols-1 md:grid-cols-3 gap-4 mb-6'>
                <?=
                    (!empty($listings))
                        ? $this->renderForEach('partials/job-listing', $listings, 'job')
                        : '<p>Sorry, no listings to show you.</p>';
                ?>
            </div>
            <a href='/listings' class='block text-xl text-center'>
                <i class='fa fa-arrow-alt-circle-right'></i>
                Show All Jobs
            </a>
    </section>

    <?= $this->render('partials/bottom-banner') ?>

</body>

</html>