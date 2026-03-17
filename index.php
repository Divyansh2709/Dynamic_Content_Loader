<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Content Loading</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            /* Light Theme Custom Properties */
            --bg-color: #f7fafc;
            --text-color: #1a202c;
            --text-heading: #2d3748;
            --text-muted: #718096;
            
            --primary-accent: #667eea;
            --secondary-accent: #764ba2;
            --primary-gradient: linear-gradient(135deg, var(--primary-accent) 0%, var(--secondary-accent) 100%);
            
            --card-bg: rgba(255, 255, 255, 0.85);
            --card-border: rgba(226, 232, 240, 0.8);
            --card-shadow: 0 4px 15px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --card-shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            
            --input-bg: #fff;
            --input-border: #e2e8f0;
            --input-focus: rgba(102, 126, 234, 0.5);
            
            --modal-overlay: rgba(10, 15, 30, 0.6);
            --modal-bg: rgba(255, 255, 255, 0.95);
            
            --transition-speed: 0.3s;
            --font-main: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            
            --glass-blur: blur(12px);
        }

        body.dark-mode {
            /* Dark Theme Custom Properties */
            --bg-color: #0f111a;
            --text-color: #e2e8f0;
            --text-heading: #f7fafc;
            --text-muted: #a0aec0;
            
            --primary-accent: #8e9ef2;
            --secondary-accent: #b392cf;
            --primary-gradient: linear-gradient(135deg, var(--primary-accent) 0%, var(--secondary-accent) 100%);
            
            --card-bg: rgba(20, 24, 38, 0.75);
            --card-border: rgba(255, 255, 255, 0.08);
            --card-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            --card-shadow-hover: 0 20px 25px rgba(0, 0, 0, 0.5), 0 0 15px rgba(142, 158, 242, 0.15);
            
            --input-bg: rgba(15, 17, 26, 0.6);
            --input-border: rgba(255, 255, 255, 0.1);
            --input-focus: rgba(142, 158, 242, 0.4);
            
            --modal-overlay: rgba(0, 0, 0, 0.85);
            --modal-bg: rgba(20, 24, 38, 0.95);
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(102, 126, 234, 0.05) 0%, transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(118, 75, 162, 0.05) 0%, transparent 25%);
            background-attachment: fixed;
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            transition: background-color var(--transition-speed) ease, color var(--transition-speed) ease;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 24px 40px;
        }

        header {
            padding: 40px 0 30px;
            margin-bottom: 40px;
            border-bottom: 1px solid var(--card-border);
            background: linear-gradient(to bottom, 
                        color-mix(in srgb, var(--card-bg) 80%, transparent), 
                        transparent);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .header-text {
            text-align: left;
        }

        header h1 { 
            font-size: 2.4rem; 
            font-weight: 700;
            margin-bottom: 4px; 
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.5px;
        }
        
        header p { 
            color: var(--text-muted);
            font-size: 1.05rem; 
            font-weight: 400;
        }

        .theme-btn {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            box-shadow: var(--card-shadow);
            padding: 10px 18px;
            border-radius: 30px;
            color: var(--text-heading);
            cursor: pointer;
            font-family: var(--font-main);
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 10px;
            backdrop-filter: var(--glass-blur);
        }
        
        .theme-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--card-shadow-hover);
        }
        
        .theme-btn:active {
            transform: translateY(1px);
        }
        
        .theme-icon { font-size: 1.1rem; }

        /* Controls Area */
        .controls-wrapper {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            transition: var(--transition-speed);
        }

        .controls {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 3;
            min-width: 250px;
            position: relative;
        }

        .search-box input, .category-filter select, .sort-filter select {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid var(--input-border);
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: var(--font-main);
            background: var(--input-bg);
            color: var(--text-heading);
            transition: all 0.25s ease;
            outline: none;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
            appearance: none;
        }

        .search-box input {
            padding-left: 48px;
        }

        .search-box input:focus, .category-filter select:focus, .sort-filter select:focus { 
            border-color: var(--primary-accent); 
            box-shadow: 0 0 0 3px var(--input-focus);
            background: var(--card-bg);
        }

        .search-box::before {
            content: '🔍';
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.1rem;
            color: var(--text-muted);
            opacity: 0.7;
            pointer-events: none;
        }

        .category-filter, .sort-filter {
            flex: 1;
            min-width: 160px;
            position: relative;
        }
        
        .category-filter::after, .sort-filter::after {
            content: "▼";
            font-size: 0.8rem;
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        .category-filter select, .sort-filter select {
            cursor: pointer;
            padding-right: 40px;
        }

        /* Status Bar */
        .status-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 0 10px;
            font-size: 0.95rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* Posts Grid */
        #content {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        .post {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 28px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--card-border);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            cursor: pointer;
            position: relative;
            z-index: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            animation: fadeInScale 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
        }

        /* Subtle top border accent */
        .post::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .post:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow-hover);
            border-color: transparent;
        }
        
        .post:hover::before {
            opacity: 1;
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            gap: 16px;
        }

        .post-title-wrapper {
            flex: 1;
        }

        .post h3 {
            font-size: 1.25rem;
            color: var(--text-heading);
            margin-bottom: 8px;
            line-height: 1.4;
            font-weight: 600;
        }

        .post-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .post-author, .post-date {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .post-category {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            white-space: nowrap;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        /* Beautiful pill category colors */
        .cat-PHP         { background: rgba(108, 52, 131, 0.1); color: #6c3483; border: 1px solid rgba(108, 52, 131, 0.2); }
        .cat-JavaScript  { background: rgba(185, 119, 14, 0.1); color: #b9770e; border: 1px solid rgba(185, 119, 14, 0.2); }
        .cat-Database    { background: rgba(30, 132, 73, 0.1);  color: #1e8449; border: 1px solid rgba(30, 132, 73, 0.2); }
        .cat-CSS         { background: rgba(36, 113, 163, 0.1); color: #2471a3; border: 1px solid rgba(36, 113, 163, 0.2); }
        .cat-General     { background: rgba(86, 101, 115, 0.1); color: #566573; border: 1px solid rgba(86, 101, 115, 0.2); }

        body.dark-mode .cat-PHP        { background: rgba(215, 189, 226, 0.15); color: #d7bde2; border-color: rgba(215, 189, 226, 0.3); }
        body.dark-mode .cat-JavaScript { background: rgba(250, 215, 161, 0.15); color: #fad7a1; border-color: rgba(250, 215, 161, 0.3); }
        body.dark-mode .cat-Database   { background: rgba(171, 235, 198, 0.15); color: #abebc6; border-color: rgba(171, 235, 198, 0.3); }
        body.dark-mode .cat-CSS        { background: rgba(169, 204, 227, 0.15); color: #a9cce3; border-color: rgba(169, 204, 227, 0.3); }
        body.dark-mode .cat-General    { background: rgba(213, 216, 220, 0.15); color: #d5d8dc; border-color: rgba(213, 216, 220, 0.3); }

        .post-excerpt {
            color: var(--text-color);
            opacity: 0.85;
            font-size: 1rem;
            margin-bottom: 20px;
            line-height: 1.6;
            flex-grow: 1; /* Pushes footer down */
        }

        .post-footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid var(--card-border);
        }

        .read-more {
            font-size: 0.9rem;
            color: var(--primary-accent);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s, transform 0.2s;
        }

        .post:hover .read-more {
            transform: translateX(4px);
            color: var(--secondary-accent);
        }

        /* Modal Enhancements */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: var(--modal-overlay);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            padding: 20px;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            background: var(--modal-bg);
            color: var(--text-color);
            border-radius: 20px;
            padding: 40px;
            max-width: 750px;
            width: 100%;
            max-height: 85vh;
            overflow-y: auto;
            position: relative;
            transform: translateY(30px) scale(0.95);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid var(--card-border);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
        }

        /* Custom Scrollbar for Modal */
        .modal::-webkit-scrollbar { width: 8px; }
        .modal::-webkit-scrollbar-track { background: transparent; }
        .modal::-webkit-scrollbar-thumb { 
            background: var(--card-border); 
            border-radius: 10px; 
        }
        .modal::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

        .modal-overlay.active .modal {
            transform: translateY(0) scale(1);
        }

        .modal-close {
            position: absolute;
            top: 24px;
            right: 24px;
            background: var(--input-bg);
            border: 1px solid var(--card-border);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 10;
        }

        .modal-close:hover { 
            color: var(--text-heading);
            transform: rotate(90deg) scale(1.1);
            background: var(--card-bg);
            box-shadow: var(--card-shadow);
        }

        .modal h2 {
            font-size: 2.2rem;
            color: var(--text-heading);
            margin-bottom: 16px;
            padding-right: 50px;
            line-height: 1.2;
            font-weight: 700;
        }

        .modal-meta-container {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--card-border);
            flex-wrap: wrap;
        }

        .modal .post-category { margin-bottom: 0; font-size: 0.85rem; padding: 6px 16px; }

        .modal-author-info {
            display: flex;
            flex-direction: column;
        }

        .modal-author {
            font-weight: 600;
            font-size: 1.05rem;
            color: var(--text-heading);
        }

        .modal-date {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .modal-body {
            font-size: 1.1rem;
            color: var(--text-color);
            line-height: 1.8;
            white-space: pre-line;
            opacity: 0.9;
        }

        /* Premium Skeleton Loading */
        .skeleton {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 28px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--card-border);
            backdrop-filter: var(--glass-blur);
        }

        .skeleton-line {
            height: 16px;
            background: color-mix(in srgb, var(--card-border) 60%, transparent);
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .skeleton-line::after {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            transform: translateX(-100%);
            background-image: linear-gradient(
                90deg,
                rgba(255, 255, 255, 0) 0,
                color-mix(in srgb, var(--text-muted) 15%, transparent) 40%,
                color-mix(in srgb, var(--text-muted) 15%, transparent) 60%,
                rgba(255, 255, 255, 0) 100%
            );
            animation: shimmer 1.5s infinite ease-in-out;
        }

        .skeleton-line.title { width: 70%; height: 28px; margin-bottom: 24px; border-radius: 12px; }
        .skeleton-line.meta  { width: 40%; height: 12px; margin-bottom: 24px; }
        .skeleton-line.text  { width: 100%; }
        .skeleton-line.short { width: 60%; }

        @keyframes shimmer { 100% { transform: translateX(100%); } }
        
        @keyframes fadeInScale {
            0% { opacity: 0; transform: scale(0.95) translateY(10px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* Pagination Styling - Modern */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .pagination button {
            padding: 10px 18px;
            border: 2px solid transparent;
            background: var(--card-bg);
            color: var(--text-heading);
            border-radius: 12px;
            cursor: pointer;
            font-size: 0.95rem;
            font-family: var(--font-main);
            font-weight: 600;
            transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .pagination button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: var(--card-shadow);
            color: var(--primary-accent);
        }

        .pagination button.active {
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            box-shadow: none;
            background: var(--input-bg);
            border-color: var(--card-border);
        }

        .page-info {
            font-weight: 700;
            color: var(--text-muted);
            padding: 0 4px;
        }

        /* Empty/Error States */
        .empty-state, .error-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 80px 20px;
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px dashed var(--text-muted);
            box-shadow: var(--card-shadow);
            backdrop-filter: var(--glass-blur);
        }

        .empty-state .icon { font-size: 4rem; margin-bottom: 20px; animation: float 3s ease-in-out infinite; }
        .empty-state h3 { font-size: 1.5rem; color: var(--text-heading); margin-bottom: 8px; }
        .empty-state p { color: var(--text-muted); font-size: 1.1rem; }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .error-state {
            border-color: #fc8181;
            background: rgba(252, 129, 129, 0.05);
        }

        .error-state .icon { font-size: 4rem; margin-bottom: 16px; color: #e53e3e; }

        .error-state button {
            margin-top: 24px;
            padding: 12px 28px;
            background: #e53e3e;
            color: #fff;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            font-family: var(--font-main);
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(229, 62, 62, 0.3);
        }

        .error-state button:hover {
            background: #c53030;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(229, 62, 62, 0.4);
        }

        /* Custom Highlight/Selection */
        ::selection { background: rgba(102, 126, 234, 0.3); color: inherit; }

        @media (max-width: 768px) {
            .header-content { flex-direction: column; text-align: center; gap: 20px; }
            .header-text { text-align: center; }
            .controls-wrapper { padding: 16px; }
            .controls { flex-direction: column; gap: 12px; }
            .search-box, .category-filter, .sort-filter { width: 100%; }
            #content { grid-template-columns: 1fr; }
            .modal { padding: 24px; }
            .modal h2 { font-size: 1.8rem; }
            .modal-meta-container { flex-direction: column; align-items: flex-start; gap: 12px; }
        }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <div class="header-text">
            <h1>Dynamic Content</h1>
            <p>High-performance asynchronous data loading experience</p>
        </div>
        <button id="themeToggle" class="theme-btn" aria-label="Toggle Dark Mode">
            <span class="theme-icon">🌙</span> 
            <span class="theme-text">Dark</span>
        </button>
    </div>
</header>

<div class="container">
    <div class="controls-wrapper">
        <div class="controls">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search by title, content or author..." autocomplete="off">
            </div>
            <div class="category-filter">
                <select id="categoryFilter" aria-label="Filter by Category">
                    <option value="">All Categories</option>
                </select>
            </div>
            <div class="sort-filter">
                <select id="sortFilter" aria-label="Sort Posts">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                </select>
            </div>
        </div>
    </div>

    <div class="status-bar">
        <span id="resultCount">Loading database records...</span>
    </div>

    <div id="content"></div>

    <div id="pagination" class="pagination"></div>
</div>

<!-- Premium Detail Modal -->
<div class="modal-overlay" id="postModal" onclick="if(event.target===this)closeModal()">
    <div class="modal">
        <button class="modal-close" onclick="closeModal()" aria-label="Close modal">&times;</button>
        <h2 id="modalTitle"></h2>
        
        <div class="modal-meta-container">
            <span id="modalCategory" class="post-category"></span>
            <div class="modal-author-info">
                <span id="modalAuthor" class="modal-author"></span>
                <span id="modalDate" class="modal-date"></span>
            </div>
        </div>
        
        <div class="modal-body" id="modalContent"></div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
