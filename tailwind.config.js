import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                forest: '#2f4f4f',
                'forest-bark': '#5c4438',
                'forest-canopy': '#4f7942',
                'forest-dark': '#3d4e17',
                'forest-fern': '#6b8e23',
                'forest-leaf': '#6b8e23',
                'forest-mist': '#dce3dc',
                'forest-moss': '#8a9a5b',
                'forest-soil': '#836953',
                'garden-soil': '#4b5563',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
