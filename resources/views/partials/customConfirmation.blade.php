<script>
function customConfirmation(message, options = {}) {
  const {
    title = 'Confirm Action',
    confirmText = 'Confirm',
    cancelText = 'Cancel',
    type = 'default', // 'default', 'danger', 'warning', 'success'
    onConfirm = () => {},
    onCancel = () => {}
  } = options;

  // Overlay
  const overlay = document.createElement('div');
  overlay.className = 'fixed inset-0 bg-black bg-opacity-50 dark:bg-opacity-70 z-50 flex items-center justify-center p-4';

  // Modal container
  const modal = document.createElement('div');
  modal.className = `bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full overflow-hidden border-l-4 ${getBorderColor(type)} transform scale-95 opacity-0 transition-all duration-300`;

  setTimeout(() => {
    modal.classList.remove('scale-95', 'opacity-0');
    modal.classList.add('scale-100', 'opacity-100');
  }, 10);

  // Header
  const header = document.createElement('div');
  header.className = 'flex items-center p-4 border-b border-gray-200 dark:border-gray-700';

  const icon = document.createElement('div');
  icon.className = `flex items-center justify-center w-10 h-10 rounded-full ${getIconColor(type)} mr-3`;
  icon.innerHTML = getIcon(type);

  const titleEl = document.createElement('h3');
  titleEl.className = 'text-lg font-semibold text-gray-900 dark:text-white';
  titleEl.textContent = title;

  header.appendChild(icon);
  header.appendChild(titleEl);

  // Body
  const body = document.createElement('div');
  body.className = 'p-4';
  const messageEl = document.createElement('p');
  messageEl.className = 'text-gray-600 dark:text-gray-300';
  messageEl.textContent = message;
  body.appendChild(messageEl);

  // Footer (dark mode fixed)
  const footer = document.createElement('div');
  footer.className = 'flex gap-3 p-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700';

  const cancelBtn = document.createElement('button');
  cancelBtn.className = 'flex-1 py-2 px-4 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200';
  cancelBtn.textContent = cancelText;
  cancelBtn.addEventListener('click', () => {
    closeModal();
    onCancel();
  });

  const confirmBtn = document.createElement('button');
  confirmBtn.className = `flex-1 py-2 px-4 rounded-lg text-white ${getButtonColor(type)} hover:opacity-90 transition-opacity duration-200`;
  confirmBtn.textContent = confirmText;
  confirmBtn.addEventListener('click', () => {
    closeModal();
    onConfirm();
  });

  footer.appendChild(cancelBtn);
  footer.appendChild(confirmBtn);

  // Assemble modal
  modal.appendChild(header);
  modal.appendChild(body);
  modal.appendChild(footer);
  overlay.appendChild(modal);
  document.body.appendChild(overlay);
  document.body.style.overflow = 'hidden';

  // Close modal
  const handleEscape = (e) => {
    if (e.key === 'Escape') {
      closeModal();
      onCancel();
    }
  };
  document.addEventListener('keydown', handleEscape);

  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) {
      closeModal();
      onCancel();
    }
  });

  function closeModal() {
    modal.classList.add('scale-95', 'opacity-0');
    overlay.classList.add('opacity-0');
    setTimeout(() => {
      document.body.removeChild(overlay);
      document.body.style.overflow = '';
      document.removeEventListener('keydown', handleEscape);
    }, 200);
  }

  // Helpers
  function getBorderColor(type) {
    const colors = {
      default: 'border-blue-500',
      danger: 'border-red-500',
      warning: 'border-amber-500',
      success: 'border-green-500'
    };
    return colors[type] || colors.default;
  }

  function getIcon(type) {
    const icons = {
      default: '<i class="fas fa-question-circle text-xl"></i>',
      danger: '<i class="fas fa-exclamation-circle text-xl"></i>',
      warning: '<i class="fas fa-exclamation-triangle text-xl"></i>',
      success: '<i class="fas fa-check-circle text-xl"></i>'
    };
    return icons[type] || icons.default;
  }

  function getIconColor(type) {
    const colors = {
      default: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
      danger: 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
      warning: 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
      success: 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400'
    };
    return colors[type] || colors.default;
  }

  function getButtonColor(type) {
    const colors = {
      default: 'bg-blue-600',
      danger: 'bg-red-600',
      warning: 'bg-amber-600',
      success: 'bg-green-600'
    };
    return colors[type] || colors.default;
  }
}



</script>
