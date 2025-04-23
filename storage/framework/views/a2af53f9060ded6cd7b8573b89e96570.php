<nav x-data="{ open: false}" class="fixed top-0 left-0 z-50 w-full inset-x-0 bg-white border-b border-gray-100 h-12">
    <!-- Primary Navigation Menu -->

  <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 mx-auto px-4 sm:px-6 lg:px-8 sm:shadow-lg h-12 " >
        <div class="flex justify-between h-12 w-full" >
            <div class="my-auto flex">
                <!-- L_item -->
                <div class="flex items-center">
                    <!-- Logo -->
                    <div class="shrink-0 flex items-center justify-center h-full">
                        <button class="focus:outline-none"> <!--  @click="toggleSidebar()" > -->
                            <div class="bg-logo1 dark:bg-logo2 h-16 w-16 bg-cover"></div>
                        </button>
                        <h2 class="font-bold text-gray-800 ms-2 dark:text-gray-200"><?php echo e($title); ?></h1>
                    </div>
                    <!-- Navigation Links -->
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <?php
                            $route = 'dashboard';  // 預設路由為 dashboard
                            if (Auth::check()) {
                                $userId = Auth::user()->id;
                                $rolename = Auth::user()->getRoleNames()->first();
                                $arr=['admin'=>'admin', 'arcade-owner'=>'arcades', 'arcade-staff'=>'arcades', 'machine-owner'=>'machine', 'machine-manager'=>'machine', 'member'=>'', 'user'=>''];
                                $route=(empty($rolename)) ? 'dashboard' : $arr[$rolename] .'.dashboard';
                            } else {
                                return redirect()->route('login');
                            }
                        ?>
                    
                        <?php if (isset($component)) { $__componentOriginalc295f12dca9d42f28a259237a5724830 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc295f12dca9d42f28a259237a5724830 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.nav-link','data' => ['href' => ''.e(route($route)).'','active' => request()->routeIs($route)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route($route)).'','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs($route))]); ?>
                            <?php echo e(__('msg.' . $route)); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc295f12dca9d42f28a259237a5724830)): ?>
<?php $attributes = $__attributesOriginalc295f12dca9d42f28a259237a5724830; ?>
<?php unset($__attributesOriginalc295f12dca9d42f28a259237a5724830); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc295f12dca9d42f28a259237a5724830)): ?>
<?php $component = $__componentOriginalc295f12dca9d42f28a259237a5724830; ?>
<?php unset($__componentOriginalc295f12dca9d42f28a259237a5724830); ?>
<?php endif; ?>
                    </div>
                    <?php if(optional(Auth::user()->store)->name): ?> <!-- 店家名稱 -->
                        <span class="ps-5 ms-2 font-thin text-sm dark:text-gray-200"><?php echo e(Auth::user()->store->name); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="my-auto flex"><!-- R_item -->
                <!-- Language Dropdown -->
                <div class="sm:-my-px sm:ms-10 sm:flex space-x-4 h-full ">
                    <?php if (isset($component)) { $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown','data' => ['align' => 'right','width' => '48']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right','width' => '48']); ?>
                        <!-- 觸發器 -->
                         <?php $__env->slot('trigger', null, ['class' => 'flex']); ?> 
                            <button class="font-medium 
                                flex justify-between space-x-2 p-2 
                                text-gray-900 
                                hover:text-gray-700 
                                dark:text-gray-100 
                                dark:hover:text-white 
                                focus:outline-none">
                                <?php if (isset($component)) { $__componentOriginalc295f12dca9d42f28a259237a5724830 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc295f12dca9d42f28a259237a5724830 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.nav-link','data' => ['class' => 'text-sm px-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'text-sm px-3']); ?>
                                    <?php switch(app()->getLocale()):
                                        case ('zh-TW'): ?>
                                            中文
                                            <?php break; ?>
                                        <?php case ('zh-CN'): ?>
                                            简体
                                            <?php break; ?>
                                        <?php case ('en'): ?>
                                            English
                                            <?php break; ?>
                                        <?php default: ?>
                                            Language
                                    <?php endswitch; ?>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc295f12dca9d42f28a259237a5724830)): ?>
<?php $attributes = $__attributesOriginalc295f12dca9d42f28a259237a5724830; ?>
<?php unset($__attributesOriginalc295f12dca9d42f28a259237a5724830); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc295f12dca9d42f28a259237a5724830)): ?>
<?php $component = $__componentOriginalc295f12dca9d42f28a259237a5724830; ?>
<?php unset($__componentOriginalc295f12dca9d42f28a259237a5724830); ?>
<?php endif; ?>
                            </button>
                         <?php $__env->endSlot(); ?>
                        <!-- 內容 -->
                         <?php $__env->slot('content', null, ['class' => '']); ?> 
                            <div class="flex justify-between space-x-2 mx-auto px-2">
                                <a href="/lang/zh-TW" >中文    </a>
                                <a href="/lang/zh-CN" >简体    </a>
                                <a href="/lang/en"    >English</a>
                            </div>
                         <?php $__env->endSlot(); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $attributes = $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $component = $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
                </div>
                
                <!-- user info -->
                <div class="mx-2 flex items-center max-w-full">
                    <button @click="open = ! open" class="items-center justify-center px-2 pt-3 
                        text-gray-300 
                        transition duration-150 ease-in-out">
                        
                        <span class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:text-gray-700 dark:hover:text-300 ">
                            <?php echo e(Auth::user()->name); ?>

                        
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div id="current-time" class="c-list fixed right-0 z-50 flex text-sm text-green-600 dark:text-green-300 justify-end text-right w-[40%] mx-2 p-0 top-0">
        <?php
            date_default_timezone_set('Asia/Taipei');
            echo date('ymd H:i');
        ?>
    </div>

    <!-- Responsive Navigation Menu -->
    <div x-cloak  :class="{'block': open, 'hidden': ! open}" class="absolute z-50
        bg-white dark:bg-gray-800 
        text-gray-700 dark:text-gray-300 
        border-gray-100 dark:border-gray-700
        max-w-48
        rounded-md shadow-lg top-10 right-0">
        <div class="pt-2 pb-3
            bg-white dark:bg-gray-800 
            text-gray-700 dark:text-gray-300 
            space-y-1">
            <?php if(Auth::user()->hasRole('admin')): ?>
            <?php if (isset($component)) { $__componentOriginald69b52d99510f1e7cd3d80070b28ca18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald69b52d99510f1e7cd3d80070b28ca18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.responsive-nav-link','data' => ['href' => ''.e(route('admin.dashboard')).'','active' => request()->routeIs('admin.dashboard')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('responsive-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('admin.dashboard')).'','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('admin.dashboard'))]); ?>
                <?php echo e(__('msg.admin.dashboard')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald69b52d99510f1e7cd3d80070b28ca18)): ?>
<?php $attributes = $__attributesOriginald69b52d99510f1e7cd3d80070b28ca18; ?>
<?php unset($__attributesOriginald69b52d99510f1e7cd3d80070b28ca18); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald69b52d99510f1e7cd3d80070b28ca18)): ?>
<?php $component = $__componentOriginald69b52d99510f1e7cd3d80070b28ca18; ?>
<?php unset($__componentOriginald69b52d99510f1e7cd3d80070b28ca18); ?>
<?php endif; ?>
            <?php else: ?>
            <?php if (isset($component)) { $__componentOriginald69b52d99510f1e7cd3d80070b28ca18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald69b52d99510f1e7cd3d80070b28ca18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.responsive-nav-link','data' => ['href' => ''.e(route('dashboard')).'','active' => request()->routeIs('dashboard')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('responsive-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('dashboard')).'','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('dashboard'))]); ?>
                <?php echo e(__('msg.dashboard')); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald69b52d99510f1e7cd3d80070b28ca18)): ?>
<?php $attributes = $__attributesOriginald69b52d99510f1e7cd3d80070b28ca18; ?>
<?php unset($__attributesOriginald69b52d99510f1e7cd3d80070b28ca18); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald69b52d99510f1e7cd3d80070b28ca18)): ?>
<?php $component = $__componentOriginald69b52d99510f1e7cd3d80070b28ca18; ?>
<?php unset($__componentOriginald69b52d99510f1e7cd3d80070b28ca18); ?>
<?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">
                <?php if(Laravel\Jetstream\Jetstream::managesProfilePhotos()): ?>
                    <div class="shrink-0 me-3">
                        <img class="h-10 w-10 rounded-full object-cover" src="<?php echo e(Auth::user()->profile_photo_url); ?>" alt="<?php echo e(Auth::user()->name); ?>" />
                    </div>
                <?php endif; ?>

                <div>
                    <div class="font-medium 
                    text-base text-gray-700 
                    dark:text-gray-300"><?php echo e(Auth::user()->name); ?></div>
                    <div class="font-thin text-sm text-gray-700 dark:text-gray-300"><?php echo e(Auth::user()->email); ?></div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Account Management -->
                <?php if (isset($component)) { $__componentOriginald69b52d99510f1e7cd3d80070b28ca18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald69b52d99510f1e7cd3d80070b28ca18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.responsive-nav-link','data' => ['href' => ''.e(route('profile')).'','active' => request()->routeIs('profile.show')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('responsive-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('profile')).'','active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request()->routeIs('profile.show'))]); ?>
                   <?php echo e(__('msg.ProfileInfo')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald69b52d99510f1e7cd3d80070b28ca18)): ?>
<?php $attributes = $__attributesOriginald69b52d99510f1e7cd3d80070b28ca18; ?>
<?php unset($__attributesOriginald69b52d99510f1e7cd3d80070b28ca18); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald69b52d99510f1e7cd3d80070b28ca18)): ?>
<?php $component = $__componentOriginald69b52d99510f1e7cd3d80070b28ca18; ?>
<?php unset($__componentOriginald69b52d99510f1e7cd3d80070b28ca18); ?>
<?php endif; ?>

                
                <!-- Authentication -->
                <form method="POST" action="<?php echo e(route('logout')); ?>" x-data>
                    <?php echo csrf_field(); ?>
                    <?php if (isset($component)) { $__componentOriginald69b52d99510f1e7cd3d80070b28ca18 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald69b52d99510f1e7cd3d80070b28ca18 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.responsive-nav-link','data' => ['href' => ''.e(route('logout')).'','@click.prevent' => '$root.submit();']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('responsive-nav-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('logout')).'','@click.prevent' => '$root.submit();']); ?>
                        <?php echo e(__('msg.logout')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald69b52d99510f1e7cd3d80070b28ca18)): ?>
<?php $attributes = $__attributesOriginald69b52d99510f1e7cd3d80070b28ca18; ?>
<?php unset($__attributesOriginald69b52d99510f1e7cd3d80070b28ca18); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald69b52d99510f1e7cd3d80070b28ca18)): ?>
<?php $component = $__componentOriginald69b52d99510f1e7cd3d80070b28ca18; ?>
<?php unset($__componentOriginald69b52d99510f1e7cd3d80070b28ca18); ?>
<?php endif; ?>
                </form>

            </div>
        </div>
    </div>



</nav>
<script>
    // function updateLogo() {
    //     const logo = document.getElementById('logo');
    //     if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    //         logo.src = "<?php echo e(asset('storage/images/iotlink_02.png')); ?>";
    //     } else {
    //         logo.src = "<?php echo e(asset('storage/images/iotlink_01.png')); ?>";
    //     }
    // }

    // 初始載入時更新
    // updateLogo();

    // 監聽 dark 模式變化
    // window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateLogo);
</script>
<?php /**PATH /www/wwwroot/syswaw/resources/views/navigation-menu.blade.php ENDPATH**/ ?>