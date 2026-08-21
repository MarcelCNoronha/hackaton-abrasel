import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    // Every page is Vuetify now (the last plain-Tailwind Breeze primitives were removed) --
    // preflight's un-scoped resets (bare `button { background-color: transparent }` has
    // higher specificity than Vuetify's own :where()-scoped utilities) were winning over
    // Vuetify's button/field colors app-wide. Utilities stay on; only the reset layer goes.
    corePlugins: {
        preflight: false,
    },

    plugins: [forms],
};
