<button <?php echo e($attributes->merge(['type' => 'submit', 'class' => '
text-gray-200 
hover:text-gray-300  dark:hover:text-gray-200
active:text-gray-600 dark:active:text-gray-100 
bg-blue-600             
hover:bg-blue-500   dark:hover:bg-blue-500 
active:bg-gray-400  dark:active:bg-gray-700 
inline-flex items-center  
border border-transparent 
rounded-md 
font-semibold text-xs
uppercase tracking-widest 
focus:outline-none 
focus:ring-2 focus:ring-indigo-500 
focus:ring-offset-2 transition ease-in-out duration-150 
px-4 py-2'])); ?>>
        <?php echo e($slot); ?>

    </button>
<?php /**PATH /www/wwwroot/syswaw/resources/views/components/button.blade.php ENDPATH**/ ?>