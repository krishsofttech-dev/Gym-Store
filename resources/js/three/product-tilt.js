/**
 * LESSON: Product Card 3D Tilt Effect
 *
 * This adds a subtle 3D tilt to product cards when you hover over them.
 * It works by:
 *   1. Listening to mousemove events on each card
 *   2. Calculating where the mouse is relative to the card's centre
 *   3. Applying a CSS rotateX/rotateY transform proportional to that offset
 *
 * This is pure JS CSS manipulation — no Three.js needed here.
 * Three.js is overkill for a tilt effect; CSS transforms handle it perfectly.
 * We put it in the /three/ folder since it's part of the 3D feel of the site.
 */
export function initProductTilt() {
    const cards = document.querySelectorAll('.product-card')

    cards.forEach(card => {
        card.addEventListener('mousemove', handleTilt)
        card.addEventListener('mouseleave', resetTilt)
    })
}

function handleTilt(e) {
    const card   = e.currentTarget
    const rect   = card.getBoundingClientRect()
    const centerX = rect.left + rect.width  / 2
    const centerY = rect.top  + rect.height / 2

    // Normalise: -1 to +1
    const dx = (e.clientX - centerX) / (rect.width  / 2)
    const dy = (e.clientY - centerY) / (rect.height / 2)

    // Max tilt degrees
    const maxTilt = 8

    const rotateY =  dx * maxTilt
    const rotateX = -dy * maxTilt

    card.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-4px)`
    card.style.transition = 'transform 0.1s ease'
}

function resetTilt(e) {
    const card = e.currentTarget
    card.style.transform = 'perspective(800px) rotateX(0) rotateY(0) translateY(0)'
    card.style.transition = 'transform 0.4s ease'
}

// Auto-init when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProductTilt)
} else {
    initProductTilt()
}

// Re-init after Livewire/Turbo navigations (if added later)
document.addEventListener('turbo:render', initProductTilt)