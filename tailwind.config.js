/** @type {import('tailwindcss').Config} */
// The Unique Workbench look, shared by every app (apps spread this into their own tailwind.config.js,
// and put sharedConfig.plugins before their own).
// bt_primary is the brand red: buttons, links, the active menu item, focus rings.
// The chrome (top bar, sidebar) is zinc-900 with white text; pages sit on zinc-100 in white bordered cards.
// Type is a step smaller than Tailwind's defaults: text-base is 15px, and so is text without a size class.
export default {
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            fontSize: {
                xs: ['0.6875rem', { lineHeight: '1rem' }],
                sm: ['0.8125rem', { lineHeight: '1.25rem' }],
                base: ['0.9375rem', { lineHeight: '1.5rem' }],
                lg: ['1.0625rem', { lineHeight: '1.625rem' }],
                xl: ['1.1875rem', { lineHeight: '1.75rem' }],
                '2xl': ['1.375rem', { lineHeight: '1.875rem' }],
                '3xl': ['1.75rem', { lineHeight: '2.125rem' }],
                '4xl': ['2.125rem', { lineHeight: '2.5rem' }],
            },
            colors: {
                // CSS variables ("r g b") so a portal can recolour them (workbench/theme.blade.php); these are the defaults
                bt_primary: {
                    DEFAULT: 'rgb(var(--bt-primary-600, 208 58 58) / <alpha-value>)',
                    50: 'rgb(var(--bt-primary-50, 253 244 244) / <alpha-value>)',
                    100: 'rgb(var(--bt-primary-100, 251 230 230) / <alpha-value>)',
                    200: 'rgb(var(--bt-primary-200, 245 204 204) / <alpha-value>)',
                    300: 'rgb(var(--bt-primary-300, 237 165 165) / <alpha-value>)',
                    400: 'rgb(var(--bt-primary-400, 224 114 114) / <alpha-value>)',
                    500: 'rgb(var(--bt-primary-500, 214 76 76) / <alpha-value>)',
                    600: 'rgb(var(--bt-primary-600, 208 58 58) / <alpha-value>)',
                    700: 'rgb(var(--bt-primary-700, 174 46 46) / <alpha-value>)',
                    800: 'rgb(var(--bt-primary-800, 143 41 41) / <alpha-value>)',
                    900: 'rgb(var(--bt-primary-900, 119 39 39) / <alpha-value>)',
                },
            },
        },
    },
    plugins: [
        // Text with no size class follows text-base
        ({ addBase }) => addBase({ body: { fontSize: '0.9375rem', lineHeight: '1.5rem' } }),
    ],
};
