document.addEventListener('DOMContentLoaded', () => {
    const maxItems = 3;
    let selectedItems = [];

    const notify = (msg, type = 'error') => {
        if (typeof showToast === 'function') {
            showToast(msg, type);
        } else {
            console[type === 'error' ? 'error' : 'log'](msg);
        }
    };

    const grid = document.querySelector('.supplements-grid');
    const summaryList = document.getElementById('selected-items');
    const subtotalEl = document.getElementById('pack-subtotal');
    const discountEl = document.getElementById('pack-discount');
    const totalEl = document.getElementById('pack-total');
    const benefitsBox = document.getElementById('auto-benefits');
    const benefitsList = document.getElementById('auto-benefits-list');
    const benefitsInput = document.getElementById('benefits-input');
    const addToCartBtn = document.getElementById('add-pack-to-cart');

    if (!grid) return;

    grid.addEventListener('click', (e) => {
        if (e.target.classList.contains('add-to-pack-btn')) {
            const card = e.target.closest('.product-card');
            const id = card.dataset.id;
            const name = card.dataset.name;
            const price = parseFloat(card.dataset.price);
            const image = card.dataset.image;
            const benefits = parseBenefits(card.dataset.benefits);

            toggleItem(id, name, price, image, e.target, benefits);
        }
    });

    summaryList.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-item')) {
            const id = e.target.dataset.id;
            // Find the button in the grid to toggle it back
            const btn = grid.querySelector(`.product-card[data-id="${id}"] .add-to-pack-btn`);
            if (btn) {
                toggleItem(id, null, 0, null, btn);
            } else {
                // If not found (e.g. filtered out), just remove from list
                removeItem(id);
                updateUI();
            }
        }
    });

    addToCartBtn.addEventListener('click', () => {
        addPackToCart();
    });

    // El guardado ahora se maneja por el formulario HTML

    function toggleItem(id, name, price, image, btn, benefits = []) {
        const index = selectedItems.findIndex(item => item.id === id);

        if (index > -1) {
            selectedItems.splice(index, 1);
            btn.classList.remove('selected');
            btn.textContent = 'Añadir';
        } else {
            selectedItems.push({ id, name, price, qty: 1, image, benefits });
            btn.classList.add('selected');
            btn.textContent = 'Añadido';
        }

        updateUI();
    }

    function removeItem(id) {
        const index = selectedItems.findIndex(item => item.id === id);
        if (index > -1) {
            selectedItems.splice(index, 1);
            // Also update button state if visible
            const btn = grid.querySelector(`.product-card[data-id="${id}"] .add-to-pack-btn`);
            if (btn) {
                btn.classList.remove('selected');
                btn.textContent = 'Añadir';
            }
        }
    }

    function updateUI() {
        // Update Summary List
        summaryList.innerHTML = '';
        if (selectedItems.length === 0) {
            summaryList.innerHTML = '<li class="empty-msg">Selecciona productos...</li>';
        } else {
            selectedItems.forEach(item => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <span>${item.name}</span>
                    <span class="remove-item" data-id="${item.id}">✖</span>
                `;
                summaryList.appendChild(li);
            });
        }

        // Calculate Totals
        const subtotal = selectedItems.reduce((sum, item) => sum + item.price * (item.qty || 1), 0);
        const discountRate = getDiscountRate(selectedItems.length);
        const discount = subtotal * discountRate;
        const total = subtotal - discount;

        // Beneficios automáticos
        const autoBenefits = buildBenefits(selectedItems);
        if (benefitsBox && benefitsList) {
            benefitsList.innerHTML = autoBenefits.map(b => `<li>${b}</li>`).join('');
            benefitsBox.style.display = autoBenefits.length ? 'block' : 'none';
        }
        if (benefitsInput) {
            benefitsInput.value = JSON.stringify(autoBenefits);
        }

        subtotalEl.textContent = subtotal.toFixed(2);
        discountEl.textContent = discount.toFixed(2);
        totalEl.textContent = total.toFixed(2);

        // Enable/Disable Button
        if (selectedItems.length > 0) {
            addToCartBtn.removeAttribute('disabled');
            addToCartBtn.textContent = `Añadir Pack por ${total.toFixed(2)} €`;
        } else {
            addToCartBtn.setAttribute('disabled', 'true');
            addToCartBtn.textContent = `Selecciona al menos 1 producto`;
        }
    }

    function addPackToCart() {
        const subtotal = selectedItems.reduce((sum, item) => sum + item.price * (item.qty || 1), 0);
        const discountRate = getDiscountRate(selectedItems.length);
        const discount = subtotal * discountRate;
        const total = subtotal - discount;

        // Get custom pack name from input
        const packNameInput = document.getElementById('pack-name');
        const customName = packNameInput ? packNameInput.value.trim() : '';
        const packName = customName || 'Pack Personalizado';

        const packId = 'custom_pack_' + Date.now();
        const packItem = {
            id: packId, // Important for cart key
            name: packName, // Use custom name
            type: 'custom_pack',
            items: selectedItems,
            price: total,
            qty: 1,
            image: selectedItems[0].image,
            icon: selectedItems[0].image // Fallback for standard render if needed
        };

        const cart = getCart();
        cart[packId] = packItem;
        saveCart(cart);
        updateCartCount();

        // Redirect to cart
        window.location.href = '../views/cart.php';
    }

    // Función eliminada - ahora el formulario maneja el guardado

    // ============================================
    // FORMULARIO GUARDAR PACK
    // ============================================
    const packForm = document.getElementById("pack-form");
    if (packForm) {
        packForm.addEventListener("submit", function(e) {
            // Verificar que se hayan seleccionado productos
            if (selectedItems.length === 0) {
                e.preventDefault();
                notify('Debes seleccionar al menos un producto para guardar el pack');
                return;
            }

            // Actualizar el campo hidden con los items seleccionados
            const itemsInput = document.getElementById("selected-items-input");
            itemsInput.value = JSON.stringify(selectedItems);

            const btn = document.getElementById("save-pack-btn");
            btn.classList.add("saving");
            btn.textContent = "Guardando...";
            btn.disabled = true;

            // El formulario se enviará normalmente
            // La animación se mantendrá hasta que se recargue la página
        });
    }

    function parseBenefits(raw = '') {
        if (!raw) return [];
        try {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) {
                return parsed.filter(b => typeof b === 'string' && b.trim() !== '');
            }
        } catch (e) {
            return [];
        }
        return [];
    }

    function deriveBenefitFromName(name = '') {
        const n = (name || '').toLowerCase();
        if (n.includes('prote')) return 'Construcción muscular';
        if (n.includes('whey')) return 'Recuperación rápida post-entreno';
        if (n.includes('creat')) return 'Mejora de fuerza y potencia';
        if (n.includes('bcaa') || n.includes('amino')) return 'Menos fatiga y mejor recuperación';
        if (n.includes('pre') || n.includes('cafe') || n.includes('cafei')) return 'Energía y enfoque pre-entreno';
        if (n.includes('beta')) return 'Rendimiento anaeróbico sostenido';
        if (n.includes('omega') || n.includes('epa') || n.includes('dha')) return 'Salud articular y cardiovascular';
        if (n.includes('vit') || n.includes('multi')) return 'Soporte inmunitario y micronutrientes';
        if (n.includes('glut')) return 'Recuperación y salud intestinal';
        if (n.includes('zma') || n.includes('magnes')) return 'Sueño y recuperación muscular';
        if (n.includes('carb') || n.includes('gainer')) return 'Energía sostenida y soporte calórico';
        return 'Beneficio complementario para tu objetivo';
    }

    function buildBenefits(items = []) {
        const list = [];
        items.forEach(item => {
            const benefits = Array.isArray(item.benefits) && item.benefits.length
                ? item.benefits
                : [deriveBenefitFromName(item.name)];
            benefits.forEach(b => list.push(b));
        });
        return Array.from(new Set(list.map(b => b.trim()).filter(Boolean)));
    }

    function getDiscountRate(count) {
        if (count >= 4) return 0.10;
        if (count === 3) return 0.07;
        if (count === 2) return 0.05;
        return 0;
    }
});
