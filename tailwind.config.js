/** @type {import('tailwindcss').Config} */
// The Unique Workbench look, shared by every app (apps spread this into their own tailwind.config.js).
// bt_primary is the brand red: buttons, links, the active menu item, focus rings.
// The chrome (top bar, sidebar) is zinc-900 with white text; pages sit on zinc-100 in white bordered cards.
export default {
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
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
};
