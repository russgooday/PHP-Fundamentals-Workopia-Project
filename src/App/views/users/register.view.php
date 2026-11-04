<!DOCTYPE html>
<html lang='en'>
<?
echo $this->render(
    'partials/head', [ 'title' => $title ?? '', 'scripts' => $scripts ?? []]
);
?>

<body class='bg-gray-100'>
    <?= $this->render('partials/navbar'); ?>

    <!-- Registration Form Box -->
    <div class='flex justify-center items-center mt-20'>
        <div class='bg-white p-8 rounded-lg shadow-md w-full md:w-500 mx-6'>
            <h2 class='text-4xl text-center font-bold mb-4'>Register</h2>
            <?= $this->render('partials/message', ['message' => $session->getMessage('message')]); ?>
            <!-- Display form errors -->
            <?= $this->render('partials/form-errors', ['errors' => $errors ?? []]); ?>
            <!-- Registration Form -->
            <form method='POST' action='/auth/register'>
                <input type='hidden' name='_token' value='<?= e($session->csrfToken()) ?>'>
                <div class='mb-4'>
                    <input
                        type='text'
                        name='name'
                        placeholder='Full Name'
                        class='w-full px-4 py-2 border rounded focus:outline-none'
                        value='<?= e($user->name ?? '') ?>'
                    />
                </div>
                <div class='mb-4'>
                    <input
                        type='email'
                        name='email'
                        placeholder='Email Address'
                        class='w-full px-4 py-2 border rounded focus:outline-none'
                        value='<?= e($user->email ?? '') ?>'
                    />
                </div>
                <div class='mb-4'>
                    <input
                        type='text'
                        name='city'
                        placeholder='City'
                        class='w-full px-4 py-2 border rounded focus:outline-none'
                        value='<?= e($user->city ?? '') ?>'
                    />
                </div>
                <div class='mb-4'>
                    <input
                        list="states"
                        name='state'
                        placeholder='State'
                        class='w-full px-4 py-2 border rounded focus:outline-none'
                        value='<?= e($user->state ?? '') ?>'
                    />
                <?= $this->render('partials/states-list'); ?>
                </div>
                <div class='mb-4'>
                    <input
                        id='password'
                        type='password'
                        name='password'
                        placeholder='Password'
                        class='w-full px-4 py-2 border rounded focus:outline-none'
                    />
                    <!-- Checkbox -->
                    <div class='flex items-center mt-2'>
                        <input
                            type='checkbox'
                            id='toggle-user-password'
                            class='password-toggler cursor-pointer'
                            data-toggle-password='password'
                        />
                        <label
                            for='toggle-user-password'
                            class='ml-2 text-gray-500 select-none cursor-pointer'
                        >Show password</label>
                    </div>
                    <!-- End Checkbox -->
                </div>
                <div class='mb-4'>
                    <input
                        type='password'
                        name='password_confirmation'
                        placeholder='Confirm Password'
                        class='w-full px-4 py-2 border rounded focus:outline-none' />
                </div>
                <button
                    type='submit' class='w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded focus:outline-none'>
                    Register
                </button>

                <p class='mt-4 text-gray-500'>
                    Already have an account?
                    <a class='text-blue-900' href='/auth/login'>Login</a>
                </p>
            </form>
        </div>
    </div>
</body>

</html>