# 🚀 Dynamic Content Loading Web App

> A modern full-stack web application demonstrating dynamic content loading using PHP, MySQL, and JavaScript with a premium UI/UX.

---

## ✨ Features

### 🔍 Smart Search
- Live search with debouncing
- Efficient API usage
- Instant UI updates

### 📂 Advanced Filtering
- Filter posts by category
- Dynamic category loading from database

### ⏱ Sorting System
- Sort posts by:
  - Newest
  - Oldest

### 📄 Pagination
- Server-side pagination
- Smooth navigation between pages

### 🌙 Dark Mode
- Toggle between light and dark themes
- Persistent theme using localStorage

### ⚡ Dynamic Loading
- No page reloads
- Powered by Fetch API
- Seamless UX

### 🧠 URL State Management
- Shareable URLs
- Maintains filters, search, and pagination

### 💎 Premium UI/UX
- Glassmorphism design
- Gradient accents
- Skeleton loaders
- Smooth animations

---

## 🛠 Tech Stack

| Layer        | Technology |
|-------------|-----------|
| Frontend    | HTML, CSS (Modern UI), JavaScript |
| Backend     | PHP |
| Database    | MySQL |
| API Format  | JSON |

---

## 📁 Project Structure

project-root/

│

├── index.php            # Main frontend UI

├── fetch_posts.php      # Backend API endpoint

├── db.php               # Database connection

├── setup.php            # DB setup & seeding

├── script.js            # Frontend logic

│

└── assets/              # (Optional) images, styles, etc.


---

## ⚙️ Setup Instructions

### 1️⃣ Clone the Repository


git clone https://github.com/your-username/dynamic-content-app.git

cd dynamic-content-app

---

## 2️⃣ Start Local Server

Use any of the following:

- XAMPP  
- WAMP  
- MAMP  

Place project inside:
htdocs/

---

## 3️⃣ Setup Database

Run this in your browser:
http://localhost/your-project/setup.php


This will:

- Create database: `demo_db`  
- Create table: `posts`  
- Insert sample data  

---

## 4️⃣ Run the Application
http://localhost/your-project/index.php


---

## 🔌 API Documentation

### Endpoint

GET /fetch_posts.php

---

### Query Parameters

| Parameter | Type   | Description          |
|----------|--------|----------------------|
| page     | int    | Page number          |
| limit    | int    | Posts per page       |
| search   | string | Search keyword       |
| category | string | Filter category      |
| sort     | string | newest / oldest      |

---

### Example Request

/fetch_posts.php?page=1&limit=6&search=php&category=PHP&sort=newest

---

### Example Response

```json
{
  "posts": [
    {
      "id": 1,
      "title": "Getting Started with PHP",
      "author": "Alice Johnson",
      "category": "PHP"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 6,
    "total": 50,
    "totalPages": 9
  },
  "categories": ["PHP", "JavaScript", "CSS"]
}
```

## 🧠 How It Works

### Backend (PHP)
- Handles API requests  
- Builds dynamic SQL queries  
- Uses prepared statements for security  
- Returns JSON responses  

### Frontend (JavaScript)
- Fetches data using Fetch API  
- Updates UI dynamically  
- Handles:
  - Search  
  - Filters  
  - Pagination  
  - Theme toggle  

### Database (MySQL)
- Stores posts  
- Supports:
  - Filtering  
  - Searching  
  - Sorting  
  - Pagination  

---

## 🔐 Security Practices

- ✅ Prepared statements (SQL injection prevention)  
- ✅ Input validation  
- ✅ Output sanitization  
- ✅ Controlled error handling  

---

## 🎨 UI Highlights

- Responsive design  
- Smooth animations  
- Skeleton loading states  
- Modal-based post view  
- Category-based styling  

---

## 🚀 Future Enhancements

- 🔑 User authentication system  
- ✍️ CRUD operations (Create/Edit/Delete)  
- 📦 REST API expansion  
- 🌐 Deployment (Vercel / AWS / Docker)  
- 🔍 Full-text search optimization  

---

## 🤝 Contributing

Contributions are welcome.

1. Fork the repo  
2. Create a new branch  
3. Make your changes  
4. Submit a pull request  

---

## 📜 License

This project is licensed under the **MIT License**.

---

## 👨‍💻 Author

**Divyansh Gupta**

**Harsh Agrahari**

---

## ⭐ Support

If you like this project, consider giving it a ⭐ on GitHub.
