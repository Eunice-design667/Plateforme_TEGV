import Alpine from 'alpinejs';
import fanCarousel from './fan';
import setupNavigation from './navigation';
import setupOfflineSupport from './offline';
import setupPasswordToggles from './password-toggle';
import setupImageResizing from './resize-images';
import storyViewer from './stories';

window.Alpine = Alpine;
Alpine.data('storyViewer', storyViewer);
Alpine.data('fanCarousel', fanCarousel);
Alpine.start();

setupNavigation();
setupOfflineSupport();
setupPasswordToggles();
setupImageResizing();

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
