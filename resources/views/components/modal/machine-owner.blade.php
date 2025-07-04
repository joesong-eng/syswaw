<!-- 新增機主模態框 -->
<div id="addOwnerModal" class="flex hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
    <div class="relative w-full max-w-md bg-white rounded-lg shadow-lg">
        <!-- 模態框標題 -->
        <div class="flex justify-between items-center px-4 py-3 border-b">
            <h3 class="text-lg font-medium text-gray-900">新增機主</h3>
            <button class="text-gray-500 hover:text-gray-800"
                onclick="toggleModal('addOwnerModal')">
                X
            </button>
        </div>
        <!-- 模態框內容 -->
        <form action="{{ route('stores.addMachineOwner') }}" method="POST" class="p-6">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">名稱</label>
                <input type="text" name="name" id="name" value="mo-{{ time() }}"
                    class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">電子郵件</label>
                <input type="email" name="email" id="email" value="{{ time() }}@tg25.win"
                    class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">密碼</label>
                <input type="text" name="password" id="password" value="12345678"
                    class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    required>
            </div>
            <input type="text" name="parent_id" value="{{ Auth::user()->id }}"> <!-- hidden class="hidden"> -->
            <input type="text" name="created_by" value="{{ Auth::user()->id }}"> <!-- hidden class="hidden"> -->
            <!-- 按鈕 -->
            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    新增
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }
</script>
