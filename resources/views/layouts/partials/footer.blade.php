<footer class="bg-stone-950 border-t border-stone-800 mt-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
            <div class="md:col-span-2">
                <span class="font-display text-3xl tracking-widest">GYM<span class="text-accent">STORE</span></span>
                <p class="mt-4 text-stone-500 text-sm leading-relaxed max-w-xs">
                    Premium gym accessories and equipment. Built for athletes who don't compromise.
                </p>
            </div>
            <div>
                <h4 class="text-xs font-semibold tracking-widest uppercase text-stone-400 mb-4">Shop</h4>
                <ul class="space-y-2 text-sm text-stone-500">
                    <li><a href="{{ route('products.index') }}" class="hover:text-white transition-colors">All Products</a></li>
                    <li><a href="{{ route('products.index') }}?sort=popular" class="hover:text-white transition-colors">Best Sellers</a></li>
                    <li><a href="{{ route('products.index') }}?is_new=1" class="hover:text-white transition-colors">New Arrivals</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xs font-semibold tracking-widest uppercase text-stone-400 mb-4">Account</h4>
                <ul class="space-y-2 text-sm text-stone-500">
                    @auth
                        <li><a href="{{ route('orders.index') }}" class="hover:text-white transition-colors">My Orders</a></li>
                        <li><a href="{{ route('profile.show') }}" class="hover:text-white transition-colors">Profile</a></li>
                        <li><a href="{{ route('wishlist.index') }}" class="hover:text-white transition-colors">Wishlist</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Login</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Register</a></li>
                    @endauth
                </ul>
            </div>
        </div>
        <div class="border-t border-stone-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-stone-600 text-xs">© {{ date('Y') }} GymStore. All rights reserved.</p>
            <p class="text-stone-700 text-xs">Built with Laravel + Three.js</p>
        </div>
    </div>
</footer>