# 🍽️ Restaurant Ordering System

A full-stack restaurant management system built with vanilla PHP, JavaScript, and Tailwind CSS. Customers can browse the menu and place orders online, kitchen staff see live order updates in real time, and admins manage everything from a central dashboard.

---

## 🔴 Live Demo

👉 [View Live](https://restaurant-ordering-system-lstg.onrender.com)

| Role | URL | Credentials |
|------|-----|-------------|
| 🧑‍🍳 Kitchen / Staff | `/staff/` | PIN: `4321` |
| 🛠️ Admin | `/admin/` | Username: `admin` / Password: `admin123` |

---

## ✨ Features

### 🧾 Public Menu
- Browse menu items by category
- Add items to cart and place orders online
- Fake payment flow to simulate real checkout experience

### 👨‍🍳 Kitchen / Staff Page
- Live order feed — updates in real time without page refresh
- See all incoming orders from both online customers and staff
- Mark orders as they are being processed

### 🛠️ Admin Dashboard
- Full order history with order details
- Add, edit, and delete menu items
- Create and manage categories
- All data persisted and stored

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| Frontend | HTML, Tailwind CSS, Vanilla JavaScript |
| Backend | Vanilla PHP |
| Database | SQLite |
| Hosting | Render |

---

## 📸 Screenshots

**Public Menu**
<img width="1912" height="957" alt="Screenshot 2026-04-13 110334" src="https://github.com/user-attachments/assets/c3aa0448-432d-4f88-b1f1-3e7aaa4af78b" />

**Kitchen Board**
<img width="1919" height="959" alt="Screenshot 2026-04-13 110252" src="https://github.com/user-attachments/assets/39853c59-e5e7-4c86-bb81-faf59a9d8a1f" />

**Admin Dashboard**
<img width="1915" height="962" alt="Screenshot 2026-04-13 112114" src="https://github.com/user-attachments/assets/25ac6a02-bd5b-49e0-a92b-963ba0a85651" />


---

## 🚀 Running Locally

```bash
# Clone the repo
git clone https://github.com/minty-9/restaurant-ordering-system.git

# Navigate into the project
cd restaurant-ordering-system

# Serve with PHP
php -S localhost:8000
```

Then open `http://localhost:8000` in your browser.

> Make sure you have PHP installed. SQLite comes bundled with PHP by default.

---

## 📁 Project Structure

```
restaurant-ordering-system/
├── index.php          # Public menu
├── cart.php           # Cart & checkout
├── staff/             # Kitchen/staff live order view
├── admin/             # Admin dashboard
├── db/                # SQLite database
└── assets/            # Images, styles
```

---

## 💡 What I Learned

- Building role-based systems with different access levels
- Real-time data updates using vanilla JavaScript
- Structuring a full-stack PHP application without frameworks
- Handling cart logic, order flow, and database relationships

---

## 👤 Author

**Simeon (Minty)**
[Portfolio](https://minty-9.github.io/simeon) • [GitHub](https://github.com/minty-9)

---

> Built from scratch with no frameworks — just clean PHP, JavaScript, and a lot of problem solving. 💪
