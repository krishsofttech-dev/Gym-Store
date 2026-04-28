@extends('admin.layouts.admin')
@section('title', 'Categories')
@section('content')

<div class="flex items-center justify-between mb-6">
    <p class="text-stone-500 text-sm">{{ $categories->count() }} categories</p>
    <a href="{{ route('admin.categories.create') }}" class="btn-primary flex items-center gap-2 text-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Category
    </a>
</div>

<div class="bg-stone-900 border border-stone-800 rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-stone-800 text-xs uppercase tracking-widest text-stone-500">
                <th class="text-left px-5 py-3">Name</th>
                <th class="text-left px-5 py-3 hidden sm:table-cell">Slug</th>
                <th class="text-left px-5 py-3">Products</th>
                <th class="text-left px-5 py-3">Status</th>
                <th class="text-right px-5 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-800/50">
            @forelse($categories as $cat)
            <tr class="hover:bg-stone-800/30 transition-colors">
                <td class="px-5 py-3">
                    <p class="text-white font-medium">{{ $cat->name }}</p>
                    @if($cat->parent_id)
                        <p class="text-stone-600 text-xs">↳ subcategory</p>
                    @endif
                </td>
                <td class="px-5 py-3 text-stone-500 font-mono text-xs hidden sm:table-cell">{{ $cat->slug }}</td>
                <td class="px-5 py-3 text-stone-300">{{ $cat->products_count }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $cat->is_active ? 'bg-emerald-500/15 text-emerald-400' : 'bg-stone-700 text-stone-500' }}">
                        {{ $cat->is_active ? 'Active' : 'Hidden' }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.categories.edit', $cat) }}" class="text-xs text-stone-400 hover:text-white px-2 py-1 rounded hover:bg-stone-800 transition-colors">Edit</a>
                        @if($cat->products_count === 0)
                        <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" onsubmit="return confirm('Delete {{ addslashes($cat->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-stone-600 hover:text-red-400 px-2 py-1 rounded hover:bg-stone-800 transition-colors">Delete</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-12 text-center text-stone-600">No categories yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection