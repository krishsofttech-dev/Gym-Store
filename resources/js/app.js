import Alpine from 'alpinejs'
import './three/product-tilt'

window.Alpine = Alpine

/**
 * LESSON: Alpine.data() registers a reusable component/store.
 * We register 'gymStore' which is used on <body x-data="gymStore()">.
 * Every property and method here is accessible from ANY element
 * inside the body via Alpine's reactivity.
 */
Alpine.data('gymStore', () => ({
    // ---- State ----
    loading:        true,
    cartCount:      0,
    searchOpen:     false,
    mobileMenuOpen: false,

    // ---- Init ----
    initStore() {
        // Hide loading screen after a short delay
        setTimeout(() => { this.loading = false }, 800)

        // Set initial cart count from server-rendered data
        // LESSON: We embed the count in a <meta> tag to avoid
        // a separate AJAX call just to get the count on page load.
        const countEl = document.getElementById('cart-count-data')
        if (countEl) this.cartCount = parseInt(countEl.dataset.count || 0)

        // Listen for cart updates from AJAX calls
        window.addEventListener('cart-updated', (e) => {
            this.cartCount = e.detail.count
        })
    },
}))

Alpine.start()

/**
 * LESSON: These are plain JS functions (not Alpine), called from
 * onclick attributes in Blade templates.
 *
 * They use the Fetch API to call Laravel routes and return JSON.
 * The controller checks $request->wantsJson() and returns
 * the right response automatically.
 *
 * CSRF: The meta tag <meta name="csrf-token"> in app.blade.php
 * provides the token. We read it here for every POST request.
 */

// ============================================================
// GET CSRF TOKEN
// ============================================================
function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? ''
}

// ============================================================
// ADD TO CART
// ============================================================
window.addToCart = async function(productId, quantity = 1, btn = null) {
    if (btn) {
        btn.disabled = true
        btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>'
    }

    try {
        const res = await fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': getCsrf(),
            },
            body: JSON.stringify({ product_id: productId, quantity }),
        })

        const data = await res.json()

        if (data.success) {
            // Update cart count badge via Alpine event
            window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.item_count } }))

            // Open the cart drawer
            window.dispatchEvent(new CustomEvent('open-cart'))

            showToast(data.message, 'success')
        } else {
            showToast(data.message, 'error')
        }
    } catch (err) {
        showToast('Could not add to cart. Please try again.', 'error')
    } finally {
        if (btn) {
            btn.disabled = false
            btn.innerHTML = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>'
        }
    }
}

// ============================================================
// UPDATE CART ITEM QUANTITY
// ============================================================
window.updateCartItem = async function(itemId, quantity) {
    try {
        const res = await fetch(`/cart/update/${itemId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': getCsrf(),
            },
            body: JSON.stringify({ quantity }),
        })

        const data = await res.json()

        if (data.success) {
            window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.item_count } }))
            // Reload page to reflect updated totals (simple approach)
            // For SPA-feel, you'd update DOM elements directly
            if (window.location.pathname === '/cart') window.location.reload()
        } else {
            showToast(data.message, 'error')
        }
    } catch (err) {
        showToast('Could not update cart.', 'error')
    }
}

// ============================================================
// REMOVE CART ITEM
// ============================================================
window.removeCartItem = async function(itemId) {
    try {
        const res = await fetch(`/cart/remove/${itemId}`, {
            method: 'DELETE',
            headers: {
                'Accept':       'application/json',
                'X-CSRF-TOKEN': getCsrf(),
            },
        })

        const data = await res.json()

        if (data.success) {
            // Remove item row from DOM immediately
            const el = document.getElementById(`drawer-item-${itemId}`)
            if (el) el.remove()

            window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.item_count } }))

            if (window.location.pathname === '/cart') window.location.reload()
        }
    } catch (err) {
        showToast('Could not remove item.', 'error')
    }
}

// ============================================================
// TOGGLE WISHLIST
// ============================================================
window.toggleWishlist = async function(productId, btn) {
    try {
        const res = await fetch(`/wishlist/${productId}`, {
            method: 'POST',
            headers: {
                'Accept':       'application/json',
                'X-CSRF-TOKEN': getCsrf(),
            },
        })

        const data = await res.json()

        // Update button appearance
        btn.dataset.wishlisted = data.wishlisted ? 'true' : 'false'
        btn.querySelector('svg').style.fill = data.wishlisted ? '#ef4444' : 'none'
        btn.querySelector('svg').style.stroke = data.wishlisted ? '#ef4444' : 'currentColor'

        showToast(data.message, 'success')
    } catch (err) {
        showToast('Please log in to use wishlist.', 'error')
    }
}

// ============================================================
// TOAST NOTIFICATION
// LESSON: A lightweight toast that doesn't require any library.
// Creates a DOM element, appends it, then removes it after 3s.
// ============================================================
window.showToast = function(message, type = 'success') {
    const toast = document.createElement('div')
    toast.className = `fixed bottom-6 right-4 z-[9999] px-5 py-3 rounded-xl shadow-2xl text-sm font-medium
        transform translate-y-2 opacity-0 transition-all duration-300
        ${type === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'}`
    toast.textContent = message
    document.body.appendChild(toast)

    requestAnimationFrame(() => {
        toast.style.transform = 'translateY(0)'
        toast.style.opacity = '1'
    })

    setTimeout(() => {
        toast.style.opacity = '0'
        toast.style.transform = 'translateY(8px)'
        setTimeout(() => toast.remove(), 300)
    }, 3000)
}