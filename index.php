<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Content Loading</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            padding: 30px 0;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 0 0 12px 12px;
        }

        header h1 { font-size: 1.8rem; margin-bottom: 6px; }
        header p { opacity: 0.85; font-size: 0.95rem; }

        /* Controls Bar */
        .controls {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .search-box {
            flex: 1;
            min-width: 200px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 16px 12px 42px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
            outline: none;
        }

        .search-box input:focus { border-color: #667eea; }

        .search-box::before {
            content: '🔍';
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
        }

        .category-filter select {
            padding: 12px 16px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            background: #fff;
            cursor: pointer;
            outline: none;
            transition: border-color 0.2s;
        }

        .category-filter select:focus { border-color: #667eea; }

        /* Status Bar */
        .status-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            font-size: 0.9rem;
            color: #666;
        }

        /* Posts Grid */
        #content {
            display: grid;
            gap: 20px;
        }

        .post {
            background: #fff;
            border-radius: 10px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            animation: fadeInUp 0.4s ease-out;
        }

        .post:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .post h3 {
            font-size: 1.15rem;
            color: #2d3748;
            margin-bottom: 4px;
        }

        .post-category {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .cat-PHP         { background: #e8daef; color: #6c3483; }
        .cat-JavaScript   { background: #fdebd0; color: #b9770e; }
        .cat-Database     { background: #d5f5e3; color: #1e8449; }
        .cat-CSS          { background: #d6eaf8; color: #2471a3; }
        .cat-General      { background: #eaecee; color: #566573; }

        .post { cursor: pointer; }

        .post-excerpt {
            color: #555;
            font-size: 0.95rem;
            margin-bottom: 8px;
        }

        .read-more {
            font-size: 0.85rem;
            color: #667eea;
            font-weight: 600;
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            background: #fff;
            border-radius: 14px;
            padding: 32px;
            max-width: 700px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
            transform: translateY(20px);
            transition: transform 0.3s;
        }

        .modal-overlay.active .modal {
            transform: translateY(0);
        }

        .modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
            line-height: 1;
        }

        .modal-close:hover { color: #333; }

        .modal h2 {
            font-size: 1.4rem;
            color: #2d3748;
            margin-bottom: 10px;
            padding-right: 30px;
        }

        .modal .post-category {
            margin-bottom: 18px;
            display: inline-block;
        }

        .modal-body {
            font-size: 1rem;
            color: #444;
            line-height: 1.8;
        }

        /* Loading Skeleton */
        .skeleton {
            background: #fff;
            border-radius: 10px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .skeleton-line {
            height: 14px;
            background: linear-gradient(90deg, #eee 25%, #ddd 50%, #eee 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .skeleton-line.title { width: 60%; height: 20px; margin-bottom: 16px; }
        .skeleton-line.text  { width: 100%; }
        .skeleton-line.short { width: 40%; }

        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .pagination button {
            padding: 10px 18px;
            border: 2px solid #ddd;
            background: #fff;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .pagination button:hover:not(:disabled) {
            border-color: #667eea;
            color: #667eea;
        }

        .pagination button.active {
            background: #667eea;
            color: #fff;
            border-color: #667eea;
        }

        .pagination button:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .page-info {
            font-size: 0.9rem;
            color: #666;
            padding: 0 8px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state .icon { font-size: 3rem; margin-bottom: 12px; }
        .empty-state p { font-size: 1.05rem; }

        /* Error State */
        .error-state {
            text-align: center;
            padding: 40px 20px;
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 10px;
            color: #c53030;
        }

        .error-state button {
            margin-top: 12px;
            padding: 8px 20px;
            background: #c53030;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        @media (max-width: 600px) {
            .controls { flex-direction: column; }
            header h1 { font-size: 1.4rem; }
        }
    </style>
</head>
<body>

<header>
    <div class="container">
        <h1>📝 Dynamic Content Loader</h1>
        <p>Content loaded asynchronously from a MySQL database via PHP</p>
    </div>
</header>

<div class="container">
    <div class="controls">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search posts by title or content...">
        </div>
        <div class="category-filter">
            <select id="categoryFilter">
                <option value="">All Categories</option>
            </select>
        </div>
    </div>

    <div class="status-bar">
        <span id="resultCount"></span>
    </div>

    <div id="content"></div>

    <div id="pagination" class="pagination"></div>
</div>

<!-- Post Detail Modal -->
<div class="modal-overlay" id="postModal" onclick="if(event.target===this)closeModal()">
    <div class="modal">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <h2 id="modalTitle"></h2>
        <span id="modalCategory" class="post-category"></span>
        <div class="modal-body" id="modalContent"></div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
