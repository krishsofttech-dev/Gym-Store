@extends('admin.layouts.admin')
@section('title', 'Edit Category')
@section('content')
<div class="max-w-lg">
    <a href="{{ route('admin.categories.index') }}" class="text-stone-500 hover:text-white text-sm mb-6 inline-block">← Back</a>
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="bg-stone-900 border border-stone-800 rounded-2xl p-6 space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Name *</label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" class="input-dark" required>
        </div>
        <div>
            <label class="block text-xs text-stone-500 uppercase tracking-widest mb-1.5">Description</label>
            <textarea name="description" rows="3" class="input-dark resize-none">{{ old('description', $category->description) }}</textarea>
        </div>
        <div>
            <label class="block text-xs text-stone-500 uppercase tracking-widests mb-1.5">Parent Category</label>
            <select name="parent_id" class="input-dark">
                <option value="">None (top-level)</option>
                @foreach($parents as $p)
                    <option value="{{ $p->id }}" {{ $category->parent_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-stone-500 uppercase tracking-widests mb-1.5">Sort Order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0" class="input-dark">
        </div>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" class="accent-yellow-400" {{ $category->is_active ? 'checked' : '' }}>
            <span class="text-sm text-stone-400">Active</span>
        </label>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary">Update Category</button>
            <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection