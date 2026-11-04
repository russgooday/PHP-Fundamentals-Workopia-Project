

                <!-- Job Listing <?= e($job->id) . ': ' . e($job->title) ?> -->
                <div class='rounded-lg shadow-md bg-white'>
                    <div class='p-4'>
                        <h2 class='text-xl font-semibold'><?= e($job->title) ?></h2>
                        <p class='text-gray-700 text-lg mt-2'><?= e($job->description) ?></p>
                        <ul class='my-4 bg-gray-100 p-4 rounded'>
                            <li class='mb-2'>
                                <strong>Salary: </strong><?= toDollars((float) $job->salary, 0) ?>
                            </li>
                            <li class='mb-2'>
                                <strong>Location: </strong><?= e($job->city) ?>
                                <!--span class='text-xs bg-blue-500 text-white rounded-full px-2 py-1 ml-2'>Local</span-->
                            </li>
                            <? if (!empty($job->tags)): ?>
                            <li class='mb-2'>
                                <strong>Tags: </strong><span><?= e($job->tags) ?></span>
                            </li>
                            <? endif; ?>
                        </ul>
                        <a href='/listings/<?= e($job->id) ?>'
                            class='block w-full text-center px-5 py-2.5 shadow-sm rounded border text-base font-medium text-indigo-700 bg-indigo-100 hover:bg-indigo-200'>
                            Details
                        </a>
                    </div>
                </div>
