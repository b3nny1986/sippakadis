import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

/**
 * Toast notification store (Alpine).
 */
document.addEventListener('alpine:init', () => {
    Alpine.store('toast', {
        items: [],

        show(message, type = 'success') {
            const id = Date.now() + Math.random();
            this.items.push({ id, message, type });

            setTimeout(() => {
                this.remove(id);
            }, 4000);
        },

        remove(id) {
            this.items = this.items.filter((item) => item.id !== id);
        },
    });
});

Alpine.start();
