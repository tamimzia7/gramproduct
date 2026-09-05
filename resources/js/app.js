import 'bootstrap';

/*
 * কার্ট ও ইচ্ছেতালিকা — হালকা fetch ভিত্তিক ইন্টারঅ্যাকশন
 * সব অনুরোধে Laravel CSRF token ব্যবহৃত হয়।
 */
(function () {
    const csrfToken = () =>
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    // ---------- টোস্ট ফিডব্যাক ----------
    function toast(message, type) {
        const kind = type === 'error' ? 'danger' : 'success';
        let container = document.getElementById('app-toast-container');
        if (! container) {
            container = document.createElement('div');
            container.id = 'app-toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1090';
            document.body.appendChild(container);
        }

        const el = document.createElement('div');
        el.className = 'toast align-items-center text-bg-' + kind + ' border-0';
        el.setAttribute('role', 'alert');

        const inner = document.createElement('div');
        inner.className = 'd-flex';

        const body = document.createElement('div');
        body.className = 'toast-body';
        body.textContent = message;

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'btn-close btn-close-white me-2 m-auto';
        close.setAttribute('data-bs-dismiss', 'toast');
        close.setAttribute('aria-label', 'বন্ধ করুন');

        inner.appendChild(body);
        inner.appendChild(close);
        el.appendChild(inner);
        container.appendChild(el);

        const t = bootstrap.Toast.getOrCreateInstance(el, { delay: 3000 });
        t.show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    async function send(url, method, body) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        };

        if (method === 'POST' || method === 'PATCH' || method === 'PUT') {
            options.body = JSON.stringify(body || {});
        }

        const response = await fetch(url, options);
        let data = {};
        try {
            data = await response.json();
        } catch (e) {
            data = { success: false, message: null };
        }

        return { ok: response.ok, status: response.status, data: data };
    }

    function updateCartBadge(count) {
        const badge = document.querySelector('.cart-count-badge');
        if (! badge) return;

        if (count > 0) {
            badge.textContent = new Intl.NumberFormat('bn-BD').format(count);
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    function formatBn(value) {
        return new Intl.NumberFormat('bn-BD').format(value);
    }

    function updateHeaderCartTotal(subtotal) {
        const totalEl = document.querySelector('.site-cart-total');
        if (! totalEl) return;

        if (typeof subtotal === 'number' && subtotal > 0) {
            totalEl.textContent = '৳' + formatBn(subtotal);
        } else {
            totalEl.textContent = 'খালি';
        }
    }

    // ---------- কার্টে যোগ ----------
    document.addEventListener('click', async (event) => {
        const trigger = event.target.closest('[data-add-to-cart]');
        if (! trigger || trigger.disabled) return;

        const variantId = trigger.dataset.variantId;
        if (! variantId) return;

        trigger.disabled = true;

        const quantityInput = document.querySelector('#quantity');
        const quantity = quantityInput && Number(quantityInput.value) > 0 ? Number(quantityInput.value) : 1;

        const result = await send(trigger.dataset.addToCart, 'POST', {
            product_variant_id: variantId,
            quantity: quantity,
        });

        trigger.disabled = false;

        if (result.data.success) {
            toast(result.data.message, 'success');
            updateCartBadge(result.data.cart_count);
            updateHeaderCartTotal(result.data.subtotal);
        } else {
            toast(result.data.message || 'দুঃখিত, কিছু সমস্যা হয়েছে। আবার চেষ্টা করুন।', 'error');
        }
    });

    // ---------- ইচ্ছেতালিকা toggle ----------
    document.addEventListener('click', async (event) => {
        const trigger = event.target.closest('[data-wishlist-toggle]');
        if (! trigger || trigger.disabled) return;

        const productId = trigger.dataset.productId;
        const saved = trigger.dataset.saved === '1';

        trigger.disabled = true;

        const result = saved
            ? await send('/wishlist/products/' + productId, 'DELETE')
            : await send('/wishlist', 'POST', { product_id: productId });

        trigger.disabled = false;

        if (result.ok && result.data.success !== false) {
            const nowSaved = ! saved;
            trigger.dataset.saved = nowSaved ? '1' : '0';

            const label = nowSaved ? 'ইচ্ছেতালিকা থেকে সরান' : 'ইচ্ছেতালিকায় যোগ করুন';
            const icon = nowSaved ? 'bi-heart-fill' : 'bi-heart';

            if (trigger.querySelector('span')) {
                trigger.querySelector('.bi').className = 'bi ' + icon;
                trigger.querySelector('span').textContent = label;
            } else {
                trigger.innerHTML = '<i class="bi ' + icon + '"></i>';
                trigger.classList.toggle('btn-outline-danger', nowSaved);
                trigger.classList.toggle('btn-outline-secondary', ! nowSaved);
            }

            toast(result.data.message, 'success');
        } else {
            toast(result.data.message || 'দুঃখিত, কিছু সমস্যা হয়েছে। আবার চেষ্টা করুন।', 'error');
        }
    });

    // ---------- কার্ট পরিমাণ ও অপসারণ ----------
    function refreshTotals(data) {
        if (! data) return;

        updateCartBadge(data.cart_count);
        updateHeaderCartTotal(data.subtotal);

        const subtotalEl = document.querySelector('.subtotal-value');
        const grandEl = document.querySelector('.grand-total');
        if (typeof data.subtotal === 'number' && subtotalEl && grandEl) {
            subtotalEl.textContent = formatBn(data.subtotal);
            grandEl.textContent = formatBn(data.subtotal);
        }
    }

    async function pushQuantity(input, quantity) {
        quantity = Math.max(1, parseInt(quantity, 10) || 1);

        const max = input.dataset.max ? parseInt(input.dataset.max, 10) : null;
        if (max !== null && max >= 0 && quantity > max) {
            input.value = Math.max(1, max);
            toast('এই পরিমাণ পণ্য বর্তমানে স্টকে নেই। সর্বোচ্চ ' + formatBn(max) + 'টি যোগ করা যাবে।', 'error');
            return;
        }
        input.value = quantity;

        const result = await send(input.dataset.updateUrl, 'PATCH', { quantity: quantity });
        if (! result.data.success) {
            toast(result.data.message || 'সমস্যা হয়েছে', 'error');
            return;
        }

        refreshTotals(result.data);

        const lineEl = document.querySelector('[data-line-total-for="' + input.closest('.list-group-item')?.id.replace('cart-item-', '') + '"]');
        if (lineEl && typeof result.data.line_total === 'number') {
            lineEl.textContent = formatBn(result.data.line_total);
        }
    }

    document.querySelectorAll('.cart-qty-input').forEach((input) => {
        input.addEventListener('change', () => pushQuantity(input, input.value));
    });

    document.addEventListener('click', async (event) => {
        const stepButton = event.target.closest('[data-qty-step]');
        if (stepButton) {
            const input = stepButton.parentElement.querySelector('.cart-qty-input');
            if (input) {
                await pushQuantity(input, Number(input.value) + Number(stepButton.dataset.qtyStep));
            }
            return;
        }

        const removeTrigger = event.target.closest('[data-remove-cart-item]');
        if (removeTrigger && confirm('আপনি কি এই পণ্যটি কার্ট থেকে সরাতে চান?')) {
            const row = removeTrigger.closest('.list-group-item');
            const result = await send(removeTrigger.dataset.removeCartItem, 'DELETE');
            if (result.data.success) {
                if (row) row.remove();
                refreshTotals(result.data);
                toast(result.data.message, 'success');
                if (! document.querySelector('.list-group-item')) window.location.reload();
            } else {
                toast(result.data.message || 'সমস্যা হয়েছে', 'error');
            }
            return;
        }

        const clearTrigger = event.target.closest('[data-clear-cart]');
        if (clearTrigger && confirm('আপনি কি কার্টের সব পণ্য সরিয়ে ফেলতে চান?')) {
            const result = await send(clearTrigger.dataset.clearCart, 'DELETE');
            if (result.data.success) window.location.reload();
        }
    });
})();

// ---------- ক্রেতার মতামত slider ----------
// হালকা, লাইব্রেরি-মুক্ত scroll-snap slider। কিবোর্ড (ArrowLeft/Right),
// আগে/পরের বোতাম, hover/focus-এ pause, prefers-reduced-motion-এ auto-play বন্ধ।
(function () {
    const slider = document.querySelector('[data-testimonials-slider]');
    if (! slider) return;

    const track = slider.querySelector('[data-testimonials-track]');
    const prevButton = slider.querySelector('[data-testimonials-prev]');
    const nextButton = slider.querySelector('[data-testimonials-next]');
    if (! track || ! prevButton || ! nextButton) return;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const step = () => {
        const slide = track.querySelector('.testimonials-slider__slide');
        return slide ? slide.getBoundingClientRect().width : track.clientWidth;
    };

    const scrollByStep = (direction) => {
        track.scrollBy({ left: direction * step(), behavior: reducedMotion ? 'auto' : 'smooth' });
    };

    const hasOverflow = () => track.scrollWidth > track.clientWidth + 1;

    const updateNavState = () => {
        const maxScroll = track.scrollWidth - track.clientWidth;
        prevButton.hidden = ! hasOverflow() || track.scrollLeft <= 1;
        nextButton.hidden = ! hasOverflow() || track.scrollLeft >= maxScroll - 1;
    };

    prevButton.addEventListener('click', () => scrollByStep(-1));
    nextButton.addEventListener('click', () => scrollByStep(1));
    track.addEventListener('scroll', updateNavState, { passive: true });
    window.addEventListener('resize', updateNavState);
    updateNavState();

    // কিবোর্ড নেভিগেশন — focus track-এ থাকা অবস্থায়
    track.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            scrollByStep(-1);
        } else if (event.key === 'ArrowRight') {
            event.preventDefault();
            scrollByStep(1);
        }
    });

    // Auto-play — মৃদু (৫ সেকেন্ড), hover/focus-এ pause, reduced-motion বন্ধ
    if (reducedMotion || ! hasOverflow()) return;

    let timer = null;

    const stop = () => {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    };

    const start = () => {
        stop();
        timer = window.setInterval(() => {
            if (track.scrollLeft >= track.scrollWidth - track.clientWidth - 1) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                scrollByStep(1);
            }
        }, 5000);
    };

    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);
    slider.addEventListener('focusin', stop);
    slider.addEventListener('focusout', start);
    start();
})();
