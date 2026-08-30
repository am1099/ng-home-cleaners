import './bootstrap';
import './analytics';
import { initMobileNav } from './mobile-nav';
import { initImageLightbox } from './lightbox';
import intersect from '@alpinejs/intersect';
import Alpine from 'alpinejs';
import 'flowbite';

window.Alpine = Alpine;
Alpine.plugin(intersect);
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    initMobileNav();
    initImageLightbox();
});
