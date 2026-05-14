import preset from '../../../../vendor/filament/support/tailwind.config.preset.js'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',

        './resources/views/filament/widgets/*.blade.php',
        './resources/views/filament/pages/*.blade.php',
    ],
}
