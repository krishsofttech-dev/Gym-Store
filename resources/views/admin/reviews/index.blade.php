@extends('admin.layouts.admin')
@section('title', 'Reviews')
@section('content')

<form method="GET" class="flex gap-3 mb-6">
    <select name="status" onchange="this.form.submit()" class="input-dark text-sm py-2 w-44">
        <option value="">All Reviews</option>
        <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
    </select>
    @if(request('status'))
        <a href="{{ route('admin.reviews.index') }}" class="btn-secondary text-sm py-2 px-4">Clear</a>
    @endif
</form>

<div class="space-y-4">
    @forelse($reviews as $review)
    <div class="bg-stone-900 border border-stone-800 rounded-2xl p-5
                {{ $review->status === 'pending' ? 'border-amber-500/30' : '' }}">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-3">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-white font-medium text-sm">{{ $review->user->name }}</span>
                    @if($review->verified_purchase)
                        <span class="text-xs bg-emerald-500/15 text-emerald-400 px-2 py-0.5 rounded-full">Verified</span>
                    @endif
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ match($review->status) {'pending'=>'bg-amber-500/15 text-amber-400','approved'=>'bg-emerald-500/15 text-emerald-400',default=>'bg-stone-700 text-stone-500'} }}">
                        {{ ucfirst($review->status) }}
                    </span>
                </div>
                <p class="text-stone-500 text-xs mt-0.5">
                    On <a href="{{ route('products.show', $review->product) }}" target="_blank"
                          class="text-stone-400 hover:text-white transition-colors">{{ $review->product->name }}</a>
                    · {{ $review->created_at->diffForHumans() }}
                </p>
            </div>
            <div class="flex text-accent text-sm flex-shrink-0">
                @for($i = 1; $i <= 5; $i++){{ $i <= $review->rating ? '★' : '☆' }}@endfor
            </div>
        </div>

        @if($review->title)
            <p class="text-white text-sm font-medium mb-1">{{ $review->title }}</p>
        @endif
        @if($review->body)
            <p class="text-stone-400 text-sm leading-relaxed mb-4">{{ $review->body }}</p>
        @endif

        {{-- Actions --}}
        <div class="flex items-center gap-2 flex-wrap">
            @if($review->status !== 'approved')
            <form method="POST" action="{{ route('admin.reviews.approve', $review) }}">
                @csrf @method('PATCH')
                <button type="submit" class="text-xs bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20 px-3 py-1.5 rounded-lg transition-colors">
                    Approve
                </button>
            </form>
            @endif
            @if($review->status !== 'rejected')
            <form method="POST" action="{{ route('admin.reviews.reject', $review) }}">
                @csrf @method('PATCH')
                <button type="submit" class="text-xs bg-stone-800 border border-stone-700 text-stone-400 hover:text-white px-3 py-1.5 rounded-lg transition-colors">
                    Reject
                </button>
            </form>
            @endif
            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}"
                  onsubmit="return confirm('Delete this review?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-stone-600 hover:text-red-400 px-3 py-1.5 rounded-lg hover:bg-stone-800 transition-colors">
                    Delete
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="text-center py-20 text-stone-600">No reviews found.</div>
    @endforelse
</div>

@if($reviews->hasPages())
<div class="mt-6">{{ $reviews->withQueryString()->links('pagination::tailwind') }}</div>
@endif
@endsection