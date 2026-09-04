/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./src/**/*.{html,js}",
        './resources/css/**/*.css',
        './vendor/filament/**/*.blade.php',

        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {},
    },
    plugins: [],
}

