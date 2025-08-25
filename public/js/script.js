
// Theme Toggle
const themeToggle = document.getElementById('theme-toggle');
const html = document.documentElement;

// Check saved theme or OS preference
const savedTheme = localStorage.getItem('theme') || 
    (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
html.classList.toggle('dark', savedTheme === 'dark');

themeToggle.addEventListener('click', () => {
    html.classList.toggle('dark');
    localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
});

// Mobile Menu Toggle
const mobileMenuBtn = document.getElementById('mobile-menu-btn');
const sidebar = document.getElementById('sidebar');
const mobileBackdrop = document.getElementById('mobile-backdrop');

mobileMenuBtn.addEventListener('click', () => {
    sidebar.classList.toggle('hidden');
    mobileBackdrop.classList.toggle('hidden');
});

mobileBackdrop.addEventListener('click', () => {
    sidebar.classList.add('hidden');
    mobileBackdrop.classList.add('hidden');
});

// Highlight.js init
document.addEventListener('DOMContentLoaded', function () {
    if (typeof hljs !== 'undefined') {
        hljs.highlightAll();
    }

    // Add smooth animations to elements
    const animatedElements = document.querySelectorAll('.animate-on-load');
    animatedElements.forEach((el, index) => {
        el.style.animationDelay = `${index * 0.1}s`;
        el.classList.add('animate-fade-in');
    });
});

// Responsive sidebar handling
function handleResize() {
    if (window.innerWidth >= 768) {
        sidebar.classList.remove('hidden');
        mobileBackdrop.classList.add('hidden');
    } else {
        sidebar.classList.add('hidden');
    }
}
window.addEventListener('resize', handleResize);
handleResize(); // Initial call

// Card hover effects
const cards = document.querySelectorAll('.card-hover');
cards.forEach(card => {
    card.addEventListener('mouseenter', () => {
        card.classList.add('shadow-lg', 'transform', 'scale-105');
    });

    card.addEventListener('mouseleave', () => {
        card.classList.remove('shadow-lg', 'transform', 'scale-105');
    });
});

// Toast notification system
window.showToast = function (message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${
        type === 'success' ? 'bg-green-600 text-white' :
        type === 'error' ? 'bg-red-600 text-white' :
        'bg-blue-600 text-white'
    }`;
    toast.innerHTML = `
        <div class="flex items-center space-x-3">
            <i class="fas ${
                type === 'success' ? 'fa-check-circle' : 
                type === 'error' ? 'fa-exclamation-circle' : 
                'fa-info-circle'
            }"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.remove('translate-x-full');
        toast.classList.add('translate-x-0');
    }, 10);

    setTimeout(() => {
        toast.classList.remove('translate-x-0');
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};
