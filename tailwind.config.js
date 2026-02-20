const colors = require('tailwindcss/colors')
const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
    content: [
        './packages/**/*.blade.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/views/**/*.vue',
        './vendor/habinho/ucp/resources/views/components/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                transparent: "transparent",
                current: "currentColor",
                gray: colors.slate,
                orange: colors.amber,
                primary: colors.blue,
                secondary: colors.slate,
                blue: colors.blue,
                green: colors.emerald,
                pink: colors.pink,
                red: colors.red,
                danger: colors.rose,
                error: colors.red,
                primary: colors.blue,
                success: colors.green,
                warning: colors.amber,
                neutral: colors.slate,
                info: colors.blue,
                header: 'var(--color-header-bg)',
            },
            fontFamily: {
                sans: ['coresans', ...defaultTheme.fontFamily.sans],
            },
            minHeight: (theme) => ({
                ...theme('spacing'),
            }),
            fontSize: {
                xm: ["0.785rem", { lineHeight: "1rem" }],
            },
        },
    },

    plugins: [
        require('@tailwindcss/aspect-ratio'),
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
