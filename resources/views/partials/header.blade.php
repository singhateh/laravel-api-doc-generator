<header class="bg-white dark:bg-dark-900 border-b border-gray-200 dark:border-dark-700 shadow-sm">
    <div class="flex flex-wrap items-center justify-between p-4 gap-2">
        <!-- Title -->
        <div class="flex-1 min-w-0">
            <h1 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white truncate">
                @yield('title', 'API Documentation')
            </h1>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center flex-wrap gap-2">
            <!-- Raw JSON button -->
            <a href="{{ route('api-docs.json') }}" target="_blank"
               class="flex items-center space-x-2 px-3 sm:px-4 py-2 rounded-lg bg-gray-100 dark:bg-dark-800 
                      text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors max-w-[140px] sm:max-w-none truncate">
                <i class="fas fa-code shrink-0"></i>
                <span class="truncate">Raw JSON</span>
            </a>

            <!-- Regenerate button -->
            <form id="generate-docs-form" action="{{ route('api-docs.generate') }}" method="POST" class="hidden">
                @csrf
            </form>
            <button id="regenerate-btn"
                onclick="handleRegenerate(event)"
                class="flex items-center space-x-2 px-3 sm:px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors max-w-[160px] sm:max-w-none truncate">
                <i class="fas fa-sync-alt shrink-0" id="regenerate-icon"></i>
                <span id="regenerate-text" class="truncate">Regenerate</span>
            </button>

            <!-- Share button -->
            <button id="share-btn"
                class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 dark:bg-dark-800 
                       text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">
                <i class="fas fa-share-alt"></i>
            </button>

            <!-- Dark Mode Toggle -->
            <button id="theme-toggle"
                class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 dark:bg-dark-800 
                       text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-dark-700 transition-colors">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block"></i>
            </button>
        </div>
    </div>
</header>


<script>
    async function handleRegenerate(event) {
        event.preventDefault();
        const btn = document.getElementById('regenerate-btn');
        const text = document.getElementById('regenerate-text');

        // Show loading state
        btn.disabled = true;
        text.textContent = "Generating...";
        btn.classList.add("opacity-70", "cursor-not-allowed");

        try {
            const response = await fetch("{{ route('api-docs.generate') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            });

            if (response.ok) {
                showToast("Documentation regenerated successfully ✅");
            } else {
                showToast("Failed to regenerate docs ❌");
            }
        } catch (err) {
            showToast("Error: " + err.message);
        } finally {
            btn.disabled = false;
            text.textContent = "Regenerate";
            btn.classList.remove("opacity-70", "cursor-not-allowed");
        }
    }

 // Share Button
document.getElementById("share-btn").addEventListener("click", async () => {
    const url = window.location.href;
    const title = document.title;
    const text = "Check out this API documentation 📚";

    if (navigator.share) {
        // Modern Web Share API
        try {
            await navigator.share({
                title: title,
                text: text,
                url: url
            });
        } catch (err) {
            showToast("Share canceled ❌");
        }
    } else {
        // Fallback: Custom share menu
        const shareMenu = document.createElement("div");
        shareMenu.className =
            "fixed bottom-16 right-4 bg-white dark:bg-gray-800 border rounded-lg shadow-lg p-4 space-y-2 z-50";

        shareMenu.innerHTML = `
            <p class="font-semibold mb-2">Share via:</p>
            <a href="https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}"
               target="_blank" class="block text-blue-500 hover:underline">🐦 Twitter</a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}"
               target="_blank" class="block text-blue-700 hover:underline">💼 LinkedIn</a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}"
               target="_blank" class="block text-blue-600 hover:underline">📘 Facebook</a>
            <a href="https://api.whatsapp.com/send?text=${encodeURIComponent(text + " " + url)}"
               target="_blank" class="block text-green-500 hover:underline">💬 WhatsApp</a>
            <button id="copy-link" class="block w-full text-left text-gray-700 hover:underline">📋 Copy Link</button>
            <button id="invite-users" class="block w-full text-left text-purple-600 hover:underline">👥 Invite Users</button>
        `;

        // Remove old menu if exists
        document.querySelectorAll(".share-menu").forEach(el => el.remove());
        shareMenu.classList.add("share-menu");
        document.body.appendChild(shareMenu);

        // Copy link
        document.getElementById("copy-link").addEventListener("click", () => {
            navigator.clipboard.writeText(url);
            showToast("Link copied to clipboard 📋");
            shareMenu.remove();
        });

        // Invite users (your custom logic)
        document.getElementById("invite-users").addEventListener("click", () => {
            showToast("Invite modal coming soon 🚀");
            // e.g., open a modal where user enters email → send invite
            shareMenu.remove();
        });

        // Close menu when clicking outside
        document.addEventListener("click", (e) => {
            if (!shareMenu.contains(e.target) && e.target.id !== "share-btn") {
                shareMenu.remove();
            }
        }, { once: true });
    }
});


    // Toast notification helper
    function showToast(message) {
        if (!document.getElementById('toast')) {
            const toast = document.createElement('div');
            toast.id = 'toast';
            toast.className = 'fixed bottom-4 right-4 px-4 py-2 bg-green-600 text-white rounded-lg shadow-lg z-50 transition-opacity duration-300 opacity-0';
            document.body.appendChild(toast);
        }

        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.classList.remove('opacity-0');

        setTimeout(() => {
            toast.classList.add('opacity-0');
        }, 2500);
    }
</script>
