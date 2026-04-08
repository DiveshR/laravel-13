@foreach($users as $user)
    <tr class="border-b">
        <td class="py-2">{{ $user->name }}</td>
        <td class="py-2">{{ $user->email }}</td>
        <td class="py-2">{{ $user->role }}</td>
    </tr>
@endforeach
<tr data-next-page="{{ $users->nextPageUrl() }}" class="hidden"></tr>
