<a href="{{ route($route) }}" class="flex items-center px-2 
          sm:rounded-lg sm:shadow 
          w-full sm:w-auto whitespace-nowrap sm:whitespace-normal 
          hover:border-b-4 sm:hover:border-b-0 sm:hover:border-r-4
          hover:border-indigo-400
          hover:bg-indigo-100 dark:hover:bg-indigo-700
          active:bg-indigo-100 dark:active:bg-indigo-700
          sm:my-1 
    @if(request()->routeIs($route)) 
        bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white
    @else
        bg-gray-100 dark:bg-gray-900 
        {{-- text-gray-900 dark:text-gray-100 --}}
    @endif">
    <x-dynamic-component :component="'icon.'.$icon" 
        class="h-6 w-6 @if(request()->routeIs($route)) text-gray-700 dark:text-gray-200 @else text-gray-500 dark:text-gray-100 @endif" 
    />
    <div class="max-w-48 overflow-hidden pl-3 sm:py-1 ">
        <p class="text-base font-medium @if(request()->routeIs($route)) dark:text-white @else text-gray-900 dark:text-gray-100 @endif">
            {{ $title }}
        </p>
        <small class="hidden sm:block mt-1 text-xs @if(request()->routeIs($route)) dark:text-white @else text-gray-500 dark:text-gray-400 @endif">
            {{ $description }}
        </small>
    </div>
</a>

