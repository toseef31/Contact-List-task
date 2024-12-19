import { createApp, h } from 'vue'
import { InertiaApp } from '@inertiajs/inertia-vue3'
import { createInertiaApp } from '@inertiajs/inertia-vue3'

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel'

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => import(`./Pages/${name}.vue`),
    setup({ el, App, props }) {
        createApp({ render: () => h(App, props) })
            .use(InertiaApp)
            .mount(el)
    },
})
