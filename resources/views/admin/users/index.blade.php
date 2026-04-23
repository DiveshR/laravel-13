<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Users</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 flex items-center gap-3">
                    <svg class="h-5 w-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-green-800 text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4 gap-4">
                    <input id="searchInput" type="text" value="{{ $query }}" placeholder="Search users by name..."
                           class="flex-1 border-gray-300 rounded-md">
                    <form action="{{ route('admin.users.export') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export CSV
                        </button>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-gray-600 border-b">
                                <th class="py-2">Name</th>
                                <th class="py-2">Email</th>
                                <th class="py-2">Role</th>
                            </tr>
                        </thead>
                        <tbody id="tableRows" data-next-page="{{ $users->nextPageUrl() }}">
                            @include('admin.users.partials.rows', ['users' => $users])
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const input = document.getElementById('searchInput');
            const rows = document.getElementById('tableRows');
            let debounceTimer;
            let loading = false;

            const fetchRows = async (url, replace = false) => {
                if (!url || loading) return;
                loading = true;
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const html = await response.text();
                if (replace) rows.innerHTML = html; else rows.insertAdjacentHTML('beforeend', html);
                rows.dataset.nextPage = rows.querySelector('tr[data-next-page]')?.dataset.nextPage ?? '';
                loading = false;
            };

            input.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    const url = `{{ route('admin.users.index') }}?q=${encodeURIComponent(input.value)}`;
                    fetchRows(url, true);
                }, 350);
            });

            window.addEventListener('scroll', () => {
                if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
                    fetchRows(rows.dataset.nextPage);
                }
            });
        })();
    </script>
</x-app-layout>
