import './bootstrap';

import Alpine from 'alpinejs';
import noscroll from './plugins/noscroll';

Alpine.plugin(noscroll);

window.Alpine = Alpine;

Alpine.start();
