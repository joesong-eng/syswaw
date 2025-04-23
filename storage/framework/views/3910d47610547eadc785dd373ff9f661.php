<div x-cloak x-show="createRoleModal" class="fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative w-full h-full flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-gray-800 w-full max-w-md rounded-lg shadow-lg" @click.away="createRoleModal = false">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Create New Role</h2>
            </div>
            <div class="p-6">
                <form action="<?php echo e(route('roles.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo e(__('msg.name')); ?></label>
                        <input type="text" id="name" name="name" required class="mt-1 block w-full px-4 py-2 bg-gray-50 border border-gray-300 dark:bg-gray-700 dark:border-gray-600 rounded-md focus:outline-none focus:ring focus:ring-blue-300 dark:focus:ring-blue-500 transition">
                    </div>
                    <div class="mb-4">
                        <label for="level" class="block text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo e(__('msg.level')); ?></label>
                        <input type="text" id="level" name="level" required class="mt-1 block w-full px-4 py-2 bg-gray-50 border border-gray-300 dark:bg-gray-700 dark:border-gray-600 rounded-md focus:outline-none focus:ring focus:ring-blue-300 dark:focus:ring-blue-500 transition">
                    </div>
                    <div class="mb-4">
                        
                        <input type="hidden" id="slug" name="slug" value="web" required class="mt-1 block w-full px-4 py-2 bg-gray-50 border border-gray-300 dark:bg-gray-700 dark:border-gray-600 rounded-md focus:outline-none focus:ring focus:ring-blue-300 dark:focus:ring-blue-500 transition">
                    </div>
                    <div class="flex items-center justify-end space-x-3 mt-6">
                        <button type="button" @click="createRoleModal = false" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-700 transition">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /www/wwwroot/syswaw/resources/views/components/modal/role_create-modal.blade.php ENDPATH**/ ?>