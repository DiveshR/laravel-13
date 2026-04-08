<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Users</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <input id="searchInput" type="text" value="{{ $query }}" placeholder="Search users by name..."
                       class="w-full border-gray-300 rounded-md mb-4">
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
