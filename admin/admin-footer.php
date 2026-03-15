    </main>
    </div>
    </div>

    <!-- Bootstrap Toast for notifications -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer" style="z-index: 1099">
    </div>

    <script>
// Global admin scripts
document.addEventListener('DOMContentLoaded', function() {
  // Active menu highlight
  const currentPath = window.location.pathname.split('/').pop();
  document.querySelectorAll('.nav-link').forEach(link => {
    if (link.getAttribute('href') === currentPath || link.getAttribute('href') === currentPath.split('.')[0] +
      '.php') {
      link.classList.add('active', 'bg-primary-subtle', 'text-primary');
    }
  });

  // Global search (if present)
  const globalSearch = document.getElementById('globalSearch');
  if (globalSearch) {
    globalSearch.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        const query = this.value.trim();
        if (query) {
          window.location.search += (window.location.search ? '&' : '?') + 'q=' + encodeURIComponent(query);
        }
      }
    });
  }
});

// Toast notification function
function showAdminToast(message, type = 'success') {
  const container = document.getElementById('toastContainer') || document.querySelector('.toast-container') || document
    .body;
  const toastHtml = `
    <div class="toast align-items-center text-bg-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'times-circle'} me-2"></i>
          ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  `;
  container.insertAdjacentHTML('beforeend', toastHtml);
  const toastElement = container.lastElementChild;
  const toast = new bootstrap.Toast(toastElement);
  toast.show();
  toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
}
    </script>
    </body>

    </html>