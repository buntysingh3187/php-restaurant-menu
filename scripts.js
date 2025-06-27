// scripts for restaurant project


// Table click: handled by ⚓anchor links in index.php

// Poll order status on order_status.php
if (window.location.pathname.endsWith('order_status.php')) {
    // order status polling
    function fetchOrderStatus() {
        const params = new URLSearchParams(window.location.search);
        fetch('order_status.php?' + params.toString() + '&ajax=1')
            .then(res => res.text())
            .then(html => {
                // Only update the orders section, not the whole progress/status area
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const newOrders = tempDiv.querySelectorAll('.order');
                const ordersDiv = document.getElementById('orders');
                ordersDiv.innerHTML = '';
                newOrders.forEach(order => ordersDiv.appendChild(order));
            });
    }
    setInterval(fetchOrderStatus, 3000);
}

// Poll chef dashboard on chef_dashboard.php
if (window.location.pathname.endsWith('chef_dashboard.php')) {
    // chef dashboard polling
    function fetchChefOrders() {
        fetch('chef_dashboard.php?ajax=1')
            .then(res => res.text())
            .then(html => {
                document.getElementById('orders').innerHTML = html;
            });
    }
    setInterval(fetchChefOrders, 3000);
}
