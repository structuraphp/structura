import {defineConfig} from 'vitepress'

export default defineConfig({
    title: 'StructuraPHP',
    description: 'Architectural testing tool for PHP',
    base: '/structura/',

    head: [
        ['link', {rel: 'icon', type: 'image/svg+xml', href: '/logo.svg'}],
    ],

    themeConfig: {
        nav: [
            {text: 'Guide', link: '/guide/getting-started'},
            {text: 'Assertions', link: '/assertions/types'},
            {text: 'Customization', link: '/customization/custom-assert'},
        ],

        sidebar: {
            '/guide/': [
                {
                    text: 'Introduction',
                    items: [
                        {text: 'Getting Started', link: '/guide/getting-started'},
                        {text: 'Configuration', link: '/guide/configuration'},
                        {text: 'Usage', link: '/guide/usage'},
                        {text: 'First Run & CLI', link: '/guide/first-run'},
                        {text: 'PHPUnit Integration', link: '/guide/phpunit'},
                    ],
                },
            ],
            '/assertions/': [
                {
                    text: 'Assertions',
                    items: [
                        {text: '🧬 Types', link: '/assertions/types'},
                        {text: '🔗 Dependencies', link: '/assertions/dependencies'},
                        {text: '🧲 Relations', link: '/assertions/relations'},
                        {text: '🔌 Methods', link: '/assertions/methods'},
                        {text: '🔒 Constants', link: '/assertions/constants'},
                        {text: '🕶️ Naming', link: '/assertions/naming'},
                        {text: '🕹️ Other', link: '/assertions/other'},
                        {
                            text: '🗜️ Operators', link: '/assertions/operators',
                            items: [
                                {text: 'and()', link: '/assertions/operators#and'},
                                {text: 'or()', link: '/assertions/operators#or'},
                            ],
                        },
                    ],
                },
            ],
            '/customization/': [
                {
                    text: 'Customization',
                    items: [
                        {text: 'Custom Assert', link: '/customization/custom-assert'},
                        {text: 'Custom Progress Bar', link: '/customization/custom-progress'},
                        {text: 'Custom Error Format', link: '/customization/custom-error'},
                    ],
                },
            ],
        },

        socialLinks: [
            {icon: 'github', link: 'https://github.com/structuraphp/structura'},
        ],

        search: {
            provider: 'local',
        },

        footer: {
            message: 'Released under the MIT License.',
            copyright: 'Copyright © 2024-present StructuraPHP',
        },
    },
})



