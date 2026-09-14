<!DOCTYPE html>
<html lang="en">
<?= $this->render('partials/head', ['page' => ['title' => 'Post a job']]); ?>
<!-- ? inspect(get_defined_vars()); ? -->
<body class="bg-gray-100">
    <?= $this->render('partials/navbar'); ?>

    <!-- Post a Job Form Box -->
    <section class="flex justify-center items-center mt-20">
        <div class="bg-white p-8 rounded-lg shadow-md w-full md:w-600 mx-6">
            <h2 class="text-4xl text-center font-bold mb-4">Create Job Listing</h2>
            <!--
            <div class="message bg-red-100 p-3 my-3">This is an error message.</div>
            <div class="message bg-green-100 p-3 my-3">This is a success message.</div>
            -->
            <form method="POST" action="/listings/store">
                <h2 class="text-2xl font-bold mb-6 text-center text-gray-500">
                    Job Info
                </h2>
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
                        value="<?= e($listings['title'] ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <textarea
                        name="description"
                        placeholder="Job Description"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                    ><?= e($listings['description'] ?? '') ?></textarea>
                </div>
                <div class="mb-4">
                    <input
                        type="text"
                        name="salary"
                        placeholder="Annual Salary"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($listings['salary'] ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <input
                        type="text"
                        name="requirements"
                        placeholder="Requirements"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($listings['requirements'] ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <input
                        type="text"
                        name="benefits"
                        placeholder="Benefits"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($listings['benefits'] ?? '') ?>"
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
                        value="<?= e($listings['company'] ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <input
                        type="text"
                        name="address"
                        placeholder="Address"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($listings['address'] ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <input
                        type="text"
                        name="city"
                        placeholder="City"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($listings['city'] ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <input
                        type="text"
                        name="state"
                        placeholder="State"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($listings['state'] ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <input
                        type="text"
                        name="phone"
                        placeholder="Phone"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($listings['phone'] ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <input
                        type="email"
                        name="email"
                        placeholder="Email Address For Applications"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($listings['email'] ?? '') ?>"
                    />
                </div>
                <div class="mb-4">
                    <input
                        type="text"
                        name="tags"
                        placeholder="Tags (comma separated)"
                        class="w-full px-4 py-2 border rounded focus:outline-none"
                        value="<?= e($listings['tags'] ?? '') ?>"
                    />
                </div>
                <button
                    class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 my-3 rounded focus:outline-none">
                    Save
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