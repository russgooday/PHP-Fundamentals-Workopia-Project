<!DOCTYPE html>
<html lang="en">
<?
    $title = isset($job->id) ? 'Update a job listing' : 'Create a job listing';
    echo $this->render('partials/head', compact('title'));
?>
<body class="bg-gray-100">
    <?= $this->render('partials/navbar'); ?>

    <!-- Post a Job Form Box -->
    <section class="flex justify-center items-center mt-20">
        <div class="bg-white p-8 rounded-lg shadow-md w-full md:w-600 mx-6">
            <h2 class="text-4xl text-center font-bold mb-4"><?= e($title) ?></h2>

            <form method="POST" action="/listings/<?= isset($job->id) ? $job->id : 'store' ?>">

                <?php if (isset($job->id)): ?>
                <input type='hidden' name='_method' value='PUT' />
                <?php endif; ?>

                <h2 class="text-2xl font-bold mb-6 text-center text-gray-500">Job Info</h2>
                <?php if (isset($errors) && !empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <div class="message bg-red-100 p-3 my-3"><?= $error ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="mb-4">
                    <input
                        type="text"
                        name="title"
                        placeholder="Job Title"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($job->title ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <textarea
                        name="description"
                        placeholder="Job Description"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                    ><?= e($job->description ?? '') ?></textarea>
                </div>
                <div class="mb-4">
                    <input
                        type="text"
                        name="salary"
                        placeholder="Annual Salary"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($job->salary ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <input
                        type="text"
                        name="requirements"
                        placeholder="Requirements"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($job->requirements ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <input
                        type="text"
                        name="benefits"
                        placeholder="Benefits"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($job->benefits ?? '') ?>"
                    />
                </div>
                <h2 class="text-2xl font-bold mb-6 text-center text-gray-500">
                    Company Info & Location
                </h2>
                <div class="mb-4">
                    <input
                        type="text"
                        name="company"
                        placeholder="Company Name"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($job->company ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <input
                        type="text"
                        name="address"
                        placeholder="Address"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($job->address ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <input
                        type="text"
                        name="city"
                        placeholder="City"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($job->city ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <input
                        type="text"
                        name="state"
                        placeholder="State"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($job->state ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <input
                        type="text"
                        name="phone"
                        placeholder="Phone"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($job->phone ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <input
                        type="email"
                        name="email"
                        placeholder="Email Address For Applications"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($job->email ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <input
                        type="text"
                        name="tags"
                        placeholder="Tags (comma separated)"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($job->tags ?? '') ?>"
                    />
                </div>
                <button
                    class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 my-3 rounded focus:outline-none"
                >Save
                </button>
                <a
                    href="/"
                    class="block text-center w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded focus:outline-none">
                    Cancel
                </a>
            </form>
        </div>
    </section>

    <?= $this->render('partials/bottom-banner'); ?>

</body>

</html>