import * as lucide from 'lucide';

document.addEventListener('DOMContentLoaded', () => {
    // Replace all icons in the page using Lucide's createIcons method
    if (lucide.createIcons) {
        lucide.createIcons({
            icons: lucide.icons // Pass all available icons
        });
    }
});