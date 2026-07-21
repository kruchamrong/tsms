import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

/*
// Global converter for Khmer numerals to Arabic numerals (DISABLED - CAUSES VUE HYDRATION ISSUES)
function convertKhmerNumbers(node) {
    if (node.nodeType === 3) { 
        if (/[០-៩]/.test(node.nodeValue)) {
            const khmerNumbers = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];
            node.nodeValue = node.nodeValue.replace(/[០-៩]/g, (match) => khmerNumbers.indexOf(match));
        }
    } else if (node.nodeType === 1 && node.nodeName !== 'SCRIPT' && node.nodeName !== 'STYLE') {
        for (let i = 0; i < node.childNodes.length; i++) {
            convertKhmerNumbers(node.childNodes[i]);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    convertKhmerNumbers(document.body);
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(convertKhmerNumbers);
            } else if (mutation.type === 'characterData') {
                convertKhmerNumbers(mutation.target);
            }
        });
    });
    observer.observe(document.body, { childList: true, subtree: true, characterData: true });
});
*/
