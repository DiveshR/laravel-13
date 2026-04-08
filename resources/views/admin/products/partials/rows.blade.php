@foreach($products as $product)
    <tr class="border-b">
        <td class="py-2">{{ $product->name }}</td>
        <td class="py-2">{{ \Illuminate\Support\Str::limit($product->description, 80) }}</td>
        <td class="py-2">{{ $product->user?->name }}</td>
    </tr>
@endforeach
<tr data-next-page="{{ $products->nextPageUrl() }}" class="hidden"></tr>
