/**
 * POS Offline-First Module
 * Handles IndexedDB storage for offline POS functionality
 */

// Guard against re-declaration
if (typeof window.PosOfflineDB !== 'undefined') {
    console.log('POS Offline module already loaded, skipping re-initialization');
} else {

const PosOfflineDB = {
    dbName: 'pos_offline_db',
    dbVersion: 1,
    db: null,

    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                resolve(this.db);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // Store for menu items with variations
                if (!db.objectStoreNames.contains('menuItems')) {
                    const menuStore = db.createObjectStore('menuItems', { keyPath: 'id' });
                    menuStore.createIndex('menu_id', 'menu_id', { unique: false });
                    menuStore.createIndex('category_id', 'item_category_id', { unique: false });
                }

                // Store for categories
                if (!db.objectStoreNames.contains('categories')) {
                    db.createObjectStore('categories', { keyPath: 'id' });
                }

                // Store for menus
                if (!db.objectStoreNames.contains('menus')) {
                    db.createObjectStore('menus', { keyPath: 'id' });
                }

                // Store for taxes
                if (!db.objectStoreNames.contains('taxes')) {
                    db.createObjectStore('taxes', { keyPath: 'id' });
                }

                // Store for order types
                if (!db.objectStoreNames.contains('orderTypes')) {
                    db.createObjectStore('orderTypes', { keyPath: 'id' });
                }

                // Store for tables
                if (!db.objectStoreNames.contains('tables')) {
                    db.createObjectStore('tables', { keyPath: 'id' });
                }

                // Store for waiters
                if (!db.objectStoreNames.contains('waiters')) {
                    db.createObjectStore('waiters', { keyPath: 'id' });
                }

                // Store for pending orders (offline cart)
                if (!db.objectStoreNames.contains('pendingCart')) {
                    db.createObjectStore('pendingCart', { keyPath: 'sessionId' });
                }

                // Store for sync metadata
                if (!db.objectStoreNames.contains('syncMeta')) {
                    db.createObjectStore('syncMeta', { keyPath: 'key' });
                }
            };
        });
    },

    async put(storeName, data) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.put(data);
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    },

    async putAll(storeName, dataArray) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);

            // Clear existing data first
            store.clear();

            dataArray.forEach(item => {
                store.put(item);
            });

            transaction.oncomplete = () => resolve();
            transaction.onerror = () => reject(transaction.error);
        });
    },

    async getAll(storeName) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.getAll();
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    },

    async get(storeName, key) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.get(key);
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    },

    async delete(storeName, key) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.delete(key);
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    },

    async clear(storeName) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.clear();
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    },

    async getLastSync() {
        try {
            const meta = await this.get('syncMeta', 'lastSync');
            return meta ? meta.timestamp : null;
        } catch {
            return null;
        }
    },

    async setLastSync() {
        await this.put('syncMeta', { key: 'lastSync', timestamp: Date.now() });
    },

    async needsSync(maxAge = 3600000) { // 1 hour default
        const lastSync = await this.getLastSync();
        if (!lastSync) return true;
        return (Date.now() - lastSync) > maxAge;
    }
};

/**
 * POS Cart Manager - Handles local cart state
 */
const PosCartManager = {
    sessionId: null,
    cart: [],
    orderInfo: {},

    init(sessionId) {
        this.sessionId = sessionId || `pos_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        this.loadFromStorage();
    },

    loadFromStorage() {
        const stored = localStorage.getItem(`pos_cart_${this.sessionId}`);
        if (stored) {
            const data = JSON.parse(stored);
            this.cart = data.cart || [];
            this.orderInfo = data.orderInfo || {};
        }
    },

    saveToStorage() {
        localStorage.setItem(`pos_cart_${this.sessionId}`, JSON.stringify({
            cart: this.cart,
            orderInfo: this.orderInfo
        }));
    },

    addItem(item, variation = null, modifiers = [], qty = 1) {
        const key = this.generateItemKey(item.id, variation?.id, modifiers);
        const existingIndex = this.cart.findIndex(c => c.key === key);

        if (existingIndex >= 0) {
            this.cart[existingIndex].qty += qty;
        } else {
            const basePrice = variation?.price || item.price;
            const modifiersPrice = modifiers.reduce((sum, m) => sum + (m.price || 0), 0);

            this.cart.push({
                key,
                item_id: item.id,
                item_name: item.item_name,
                variation_id: variation?.id || null,
                variation_name: variation?.variation_name || null,
                modifier_ids: modifiers.map(m => m.id),
                modifiers: modifiers,
                price: basePrice,
                modifiers_price: modifiersPrice,
                qty: qty,
                note: '',
                type: item.type,
                image: item.image
            });
        }

        this.saveToStorage();
        return this.cart;
    },

    generateItemKey(itemId, variationId, modifiers) {
        const modifierIds = (modifiers || []).map(m => m.id).sort().join('_');
        return `${itemId}_${variationId || 0}_${modifierIds}`;
    },

    updateQty(index, qty) {
        if (qty < 1) {
            this.removeItem(index);
        } else {
            this.cart[index].qty = qty;
            this.saveToStorage();
        }
        return this.cart;
    },

    removeItem(index) {
        this.cart.splice(index, 1);
        this.saveToStorage();
        return this.cart;
    },

    updateNote(index, note) {
        this.cart[index].note = note;
        this.saveToStorage();
    },

    setOrderInfo(info) {
        this.orderInfo = { ...this.orderInfo, ...info };
        this.saveToStorage();
    },

    getSubTotal() {
        return this.cart.reduce((sum, item) => {
            return sum + (item.price + item.modifiers_price) * item.qty;
        }, 0);
    },

    getTotal(taxes = []) {
        const subTotal = this.getSubTotal();
        let taxAmount = 0;
        taxes.forEach(tax => {
            taxAmount += (tax.tax_percent / 100) * subTotal;
        });
        return subTotal + taxAmount;
    },

    clearCart() {
        this.cart = [];
        this.saveToStorage();
        return this.cart;
    },

    getCartForSubmission() {
        return {
            items: this.cart.map(item => ({
                id: item.item_id,
                variant_id: item.variation_id || 0,
                quantity: item.qty,
                price: item.price,
                modifiers_price: item.modifiers_price,
                note: item.note || null,
                modifier_ids: item.modifier_ids || []
            })),
            orderInfo: this.orderInfo
        };
    },

    getCart() {
        return this.cart;
    },

    getOrderInfo() {
        return this.orderInfo;
    }
};

// Export for use
window.PosOfflineDB = PosOfflineDB;
window.PosCartManager = PosCartManager;

} // End of guard block
