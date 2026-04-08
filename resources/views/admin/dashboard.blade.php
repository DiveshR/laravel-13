<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin Dashboard
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid gap-6 md:grid-cols-2">
            <div class="bg-white p-6 rounded-lg shadow">
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="text-3xl font-bold mt-2">{{ number_format($userCount) }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <p class="text-sm text-gray-500">Total Products</p>
                <p class="text-3xl font-bold mt-2">{{ number_format($productCount) }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
