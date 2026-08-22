import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import path from 'path';
import tailwindcss from "@tailwindcss/vite";

const themesDir = path.resolve(__dirname, 'resources/themes');
const themes = fs.readdirSync(themesDir).filter(d => fs.statSync(path.join(themesDir, d)).isDirectory());
console.log(`Building themes: ${themes.join(', ')}`);

const themeInputs = themes.flatMap(theme => [
    `resources/themes/${theme}/assets/css/app.css`,
    `resources/themes/${theme}/assets/js/app.js`,
]);

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                ...themeInputs,
                'resources/css/filament/admin/theme.css',
                'resources/css/filament/financial-advisor/theme.css',
            ],
            refresh: [
                'resources/themes/**/*',
            ],
        }),
    ],
});
