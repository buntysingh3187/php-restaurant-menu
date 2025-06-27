# Restaurant Menu PHP Project
---
**Project By:**
Gourav Sharma (23BCS10857), Bunty Singh (23BCS10683)
---

## About
This is my project for restaurant menu and order thing. I made it using PHP and MySQL (xampp). It lets you pick tables, order food, and chef can see orders. I tried to make it work on my laptop.

## Features
- Pick table and see if it's busy or not
- Order food for each table
- Chef page to see all orders and mark done
- Customers can check order status (live)
- PHP backend, some JS for updates
- Has some sample data so you can test

## How to Run/Test

1. **Setup Database**
   - Open `setup.php` in browser (I used XAMPP, so http://localhost/yourfolder/setup.php). It will make the tables and add some sample data.

2. **Table Selection**
   - Go to `index.php`. Click a table to start. Table color/status changes when you order.

3. **Place an Order**
   - Choose food and quantity, then submit. It will save order for that table.

4. **Order Status**
   - After ordering, click "View Order Status" to see if chef finished your food.

5. **Chef Dashboard**
   - Open `chef_dashboard.php` to see all orders. Chef can mark items as done.

## Notes for Teacher/Evaluator
- I tried to make all flows simple.
- Progress bars and badges show order status.
- All main code is PHP, JS is only for updating stuff without reload.
- Dummy data is there, so you don't need to add anything.
- If something doesn't work, maybe XAMPP/MySQL is not running.

## Files in Project

- `index.php` — Table selection
- `menu.php` — Menu and ordering
- `order_status.php` — Order status
- `chef_dashboard.php` — Chef page
- `setup.php` — Setup DB and sample data
- `styles.css` — Styles
- `scripts.js` — JS for updates

---

If you want to check, just follow above steps. I tested most things, but if any bug, please let me know.

