<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Combined Search</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form method="GET" action="{{ route('admin.search.index') }}" class="mb-4">
                    <input type="text" name="q" value="{{ $query }}" placeholder="Search users and products..."
                           class="w-full border-gray-300 rounded-md">
                </form>
                <table class="w-full text-left">
                    <thead>
                    <tr class="text-gray-600 border-b">
                        <th class="py-2">Name</th>
                        <th class="py-2">Product</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($results as $row)
                        <tr class="border-b">
                            <td class="py-2">{{ $row['user_name'] }}</td>
                            <td class="py-2">{{ $row['product_name'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="py-4 text-gray-500">No results yet. Try searching above.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
