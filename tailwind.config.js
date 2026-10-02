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
                bt_primary: {
                    DEFAULT: '#D03A3A',
                    50: '#FDF4F4',
                    100: '#FBE6E6',
                    200: '#F5CCCC',
                    300: '#EDA5A5',
                    400: '#E07272',
                    500: '#D64C4C',
                    600: '#D03A3A',
                    700: '#AE2E2E',
                    800: '#8F2929',
                    900: '#772727',
                },
            },
        },
    },
    plugins: [
        // Text with no size class follows text-base
        ({ addBase }) => addBase({ body: { fontSize: '0.9375rem', lineHeight: '1.5rem' } }),
    ],
};
