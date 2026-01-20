<?php
/**
 * Admin Footer Component
 * Closes layout containers and includes common scripts
 */
?>
        </main>
    </div>
    
    <!-- Common Admin Scripts -->
    <script>
        // Auto-hide success/error messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.5s';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
        
        // Confirm delete actions
        function confirmDelete(itemName) {
            return confirm(`Are you sure you want to delete "${itemName}"? This action cannot be undone.`);
        }
        
        // Mobile sidebar toggle (if needed)
        function toggleSidebar() {
            const sidebar = document.querySelector('aside');
            sidebar.classList.toggle('hidden');
        }
    </script>
    
    <?php if (isset($additional_scripts)): ?>
        <?php echo $additional_scripts; ?>
    <?php endif; ?>
</body>
</html>
