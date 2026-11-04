const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    // The legacy Bootstrap 3 theme (public/assets) provides the base styles;
    // Tailwind's global reset would override it.
    corePlugins: {
        preflight: false,
    },

    // The site has no dark theme; keep Jetstream's dark: variants from following the OS setting.
    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
                // New UI: one neutral grotesk for everything, an italic serif only as an accent.
                body: ['"Inter Tight"', ...defaultTheme.fontFamily.sans],
                serif: ['"Instrument Serif"', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                // "Valle de noche": deep night green, warm off-white, a single gold accent.
                noche: { 950: '#040b08', 900: '#06110d', 800: '#0b1a14', 700: '#12261d', 600: '#1c3529', 500: '#2b4a3a' },
                humo: { 50: '#fafaf7', 100: '#f3f2ee', 200: '#e7e5df', 300: '#d3d0c7' },
                grafito: { DEFAULT: '#14181a', 600: '#3d4447', 500: '#5d6569', 400: '#8a9195' },
                oro: { 300: '#f2d58a', 400: '#ecc663', 500: '#e2ad3b' },
                menta: { 300: '#9fdcc0', 400: '#6cc49d' },
            },
        },
    },

    plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
};
