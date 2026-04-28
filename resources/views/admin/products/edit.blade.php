@extends('admin.layouts.admin')
@section('title', 'Edit: ' . Str::limit($product->name, 30))

@section('content')

<div class="max-w-3xl">
    <a href="{{ route('admin.products.index') }}" class="text-stone-500 hover:text-white text-sm transition-colors mb-6 inline-block">← Back to products</a>

    {{-- IMPORTANT: method="POST" + @method('PATCH') — NOT PUT.
         PHP can only read file uploads from POST requests.
         PATCH is spoofed via hidden field so Laravel routes to update(). --}}
    <form method="POST"
          action="{{ route('admin.products.update', $product) }}"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf
        @method('PATCH')

        {{-- Basic info --}}
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-6">
            <h2 class="font-display text-lg tracking-wide mb-5 text-stone-400">BASIC INFO</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}"
                           class="input-dark @error('name') border-red-500 @enderror" required>
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Category *</label>
                        <select name="category_id" class="input-dark" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Brand</label>
                        <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" class="input-dark">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Short Description</label>
                    <input type="text" name="short_description" value="{{ old('short_description', $product->short_description) }}" class="input-dark">
                </div>
                <div>
                    <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Full Description *</label>
                    <textarea name="description" rows="5" class="input-dark resize-none" required>{{ old('description', $product->description) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-6">
            <h2 class="font-display text-lg tracking-wide mb-5 text-stone-400">PRICING</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Price (Rs.) *</label>
                    <input type="number" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" class="input-dark" required>
                </div>
                <div>
                    <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Compare Price</label>
                    <input type="number" name="compare_price" value="{{ old('compare_price', $product->compare_price) }}" step="0.01" min="0" class="input-dark">
                </div>
                <div>
                    <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Cost Price</label>
                    <input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" min="0" class="input-dark">
                </div>
            </div>
        </div>

        {{-- Inventory --}}
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-6">
            <h2 class="font-display text-lg tracking-wide mb-5 text-stone-400">INVENTORY</h2>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="input-dark font-mono">
                </div>
                <div>
                    <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" class="input-dark" required>
                </div>
            </div>
            <div class="flex gap-6 flex-wrap">
                @foreach([
                    ['track_quantity', 'Track Quantity',   $product->track_quantity],
                    ['is_active',      'Active (visible)', $product->is_active],
                    ['is_featured',    'Featured',         $product->is_featured],
                    ['is_new',         'New Arrival',      $product->is_new],
                ] as [$field, $label, $checked])
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="{{ $field }}" value="1" class="accent-yellow-400" {{ $checked ? 'checked' : '' }}>
                    <span class="text-sm text-stone-400">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Shipping --}}
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-6">
            <h2 class="font-display text-lg tracking-wide mb-5 text-stone-400">SHIPPING</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Weight (kg)</label>
                    <input type="number" name="weight" value="{{ old('weight', $product->weight) }}" step="0.01" min="0" class="input-dark">
                </div>
                <div>
                    <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Dimensions</label>
                    <input type="text" name="dimensions" value="{{ old('dimensions', $product->dimensions) }}" class="input-dark" placeholder="30x20x15 cm">
                </div>
            </div>
        </div>

        {{-- Images --}}
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-6">
            <h2 class="font-display text-lg tracking-wide mb-5 text-stone-400">IMAGES</h2>

            @if($product->thumbnail)
            <div class="mb-4">
                <p class="text-xs text-stone-500 mb-2">Current Thumbnail</p>
                <img src="{{ $product->thumbnail_url }}" class="w-20 h-20 object-cover rounded-xl bg-stone-800">
            </div>
            @endif

            @if($product->images && count($product->images))
            <div class="mb-4">
                <p class="text-xs text-stone-500 mb-2">Current Gallery</p>
                <div class="flex gap-2 flex-wrap">
                    @foreach($product->image_urls as $url)
                    <img src="{{ $url }}" class="w-16 h-16 object-cover rounded-lg bg-stone-800">
                    @endforeach
                </div>
            </div>
            @endif

            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Replace Thumbnail</label>
                    <input type="file" name="thumbnail" accept="image/*"
                           class="block w-full text-sm text-stone-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-stone-800 file:text-stone-300 hover:file:bg-stone-700 file:cursor-pointer">
                </div>
                <div>
                    <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Replace Gallery Images</label>
                    <input type="file" name="images[]" accept="image/*" multiple
                           class="block w-full text-sm text-stone-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-stone-800 file:text-stone-300 hover:file:bg-stone-700 file:cursor-pointer">
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Update Product</button>
            <a href="{{ route('admin.products.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection