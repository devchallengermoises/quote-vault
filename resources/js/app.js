import './bootstrap';

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';

// Only initialize Alpine if it hasn't been initialized yet
if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.plugin(focus);
    Alpine.start();
}
