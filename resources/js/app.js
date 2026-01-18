import './bootstrap';

// Add to Cart functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle all add-to-cart forms
    document.querySelectorAll('form[data-add-to-cart]').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const button = form.querySelector('button[type="submit"]');
            const originalText = button.textContent;
            
            // Disable button and show loading state
            button.disabled = true;
            button.textContent = '...';
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        product_id: form.querySelector('input[name="product_id"]').value,
                        quantity: form.querySelector('select[name="quantity"]')?.value || 1,
                        size: form.querySelector('select[name="size"]')?.value || null
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update cart counter
                    updateCartCounter(data.cart_count);
                    
                    // Show success toast with cart link
                    const link = data.cart_url ? { url: data.cart_url, text: data.cart_link_text } : null;
                    showToast(data.message, 'success', link);
                } else {
                    showToast(data.message || 'Something went wrong', 'error');
                }
            } catch (error) {
                showToast('Something went wrong', 'error');
            } finally {
                // Restore button
                button.disabled = false;
                button.textContent = originalText;
            }
        });
    });
});

function updateCartCounter(count) {
    const cartLink = document.querySelector('[data-cart-counter]');
    if (!cartLink) return;
    
    let badge = cartLink.querySelector('.cart-badge');
    
    if (count > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'cart-badge absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-volt-yellow text-xs font-bold text-volt-purple';
            cartLink.appendChild(badge);
        }
        badge.textContent = count;
    } else if (badge) {
        badge.remove();
    }
}

function showToast(message, type = 'success', link = null) {
    // Remove existing toasts
    document.querySelectorAll('.toast-notification').forEach(t => t.remove());
    
    const toast = document.createElement('div');
    toast.className = 'fixed top-20 right-4 z-50 toast-notification';
    
    const bgColor = type === 'success' ? 'bg-green-600' : 'bg-red-600';
    const icon = type === 'success' 
        ? '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />'
        : '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />';
    
    const linkHtml = link ? `<a href="${link.url}" class="text-sm font-medium underline hover:no-underline ml-2">${link.text}</a>` : '';
    
    toast.innerHTML = `
        <div class="rounded-lg ${bgColor} text-white px-4 py-3 shadow-lg flex items-center space-x-3">
            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                ${icon}
            </svg>
            <p class="text-sm font-medium">${message}${linkHtml}</p>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Remove after animation
    setTimeout(() => toast.remove(), 4000);
}
