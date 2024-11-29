import preset from "./vendor/filament/support/tailwind.config.preset";

export default {
    presets: [preset],
    content: [
        "./app/Filament/**/*.php",
        "./resources/views/filament/**/*.blade.php",
        "./resources/views/filament/pages/**/*.blade.php",
        "./resources/views/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.blade.php",
        "./resources/js/**/*.vue",
        "./resources/**/*.vue",
        // './resources/views/filament/**/*.blade.php',
        "./vendor/filament/**/*.blade.php",
    ],
};
