    document.addEventListener('DOMContentLoaded', function () {
        // Select elements
        const rentButton = document.getElementById('rentBtn');
        const closeSummaryButton = document.getElementById('closeSummaryBtn');
        const orderSummary = document.querySelector('.order-summary');

        // Show the order summary when "Rent" is clicked
        rentButton.addEventListener('click', function () {
            orderSummary.style.display = 'block'; // Show the order summary
        });

        // Hide the order summary when "X" is clicked
        closeSummaryButton.addEventListener('click', function () {
            orderSummary.style.display = 'none'; // Hide the order summary
        });
    });

    // Category filter functionality
    const categoryButtons = document.querySelectorAll('.menu-categories button');
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;  // Get selected category
            document.querySelectorAll('.item').forEach(item => {
                const itemCategory = item.dataset.categories;  // Get the category of each item
                if (category === 'all' || itemCategory === category) {
                    item.style.display = 'block'; // Show the item if it matches the selected category
                } else {
                    item.style.display = 'none'; // Hide the item if it doesn't match
                }
            });
        });
    });

    // Search functionality
    const searchBox = document.getElementById('search-box');
    searchBox.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.item').forEach(item => {
            const productName = item.dataset.name.toLowerCase();
            if (productName.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Get the sidebar and the toggle button
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');

    // Add event listener to toggle sidebar visibility
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });

    // Close sidebar if clicked outside of it
    document.addEventListener('click', (event) => {
        if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    });
    
    document.addEventListener('DOMContentLoaded', () => {
    const closeSummaryBtn = document.getElementById('closeSummaryBtn');
    const orderSummary = document.querySelector('.order-summary');

    // Close the order summary when the "X" button is clicked
    closeSummaryBtn.addEventListener('click', () => {
        orderSummary.style.display = 'none';
    });
    
});



