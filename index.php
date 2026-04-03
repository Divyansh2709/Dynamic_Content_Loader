<?php
declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Content Loading</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            --font-main: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            
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
            margin-bottom: 56px;
            border-bottom: 1px solid var(--card-border);
            background: linear-gradient(135deg, #e8e0ff 0%, #f0e8ff 100%);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            box-shadow: 0 14px 30px -22px rgba(90, 95, 207, 0.45);
        }

        body.dark-mode header {
            background: linear-gradient(135deg, rgba(52, 57, 95, 0.55) 0%, rgba(26, 30, 52, 0.35) 100%);
            box-shadow: 0 16px 34px -24px rgba(0, 0, 0, 0.65);
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
            font-weight: 800;
            margin-bottom: 2px; 
            line-height: 1.08;
            color: #5a5fcf;
            letter-spacing: -0.5px;
            text-shadow: 0 0 24px rgba(90, 95, 207, 0.25);
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        header h1 .title-bold {
            font-weight: 800;
            background: linear-gradient(90deg, #5a5fcf 0%, #7b7fff 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            display: inline-block;
        }

        header h1 .title-light {
            font-weight: 300;
            letter-spacing: 0.3px;
            background: linear-gradient(90deg, #667eea 0%, #8b9fff 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            display: inline-block;
        }

        body.dark-mode header h1 .title-bold {
            color: transparent;
            background: linear-gradient(90deg, #9ba8ff 0%, #b3baff 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: none;
        }

        body.dark-mode header h1 .title-light {
            color: transparent;
            background: linear-gradient(90deg, #8b9bff 0%, #a8b8ff 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
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

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .auth-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 999px;
            padding: 8px 12px;
            box-shadow: var(--card-shadow);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
        }

        .auth-pill a,
        .auth-pill button {
            border: none;
            background: transparent;
            color: var(--text-heading);
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            padding: 0;
        }

        .auth-pill .btn-add-post {
            background: var(--primary-gradient);
            color: #fff;
            border-radius: 999px;
            padding: 8px 14px;
        }

        .auth-pill .user-name {
            font-size: 0.9rem;
            color: var(--text-muted);
            max-width: 180px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .hidden {
            display: none !important;
        }

        .mine-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 2px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text-heading);
            min-width: 170px;
            font-size: 0.92rem;
            font-weight: 500;
        }

        .mine-toggle input {
            accent-color: var(--primary-accent);
        }

        .flash-message {
            margin: 14px 0 20px;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 500;
            display: none;
        }

        .flash-message.error {
            display: block;
            background: rgba(229, 62, 62, 0.12);
            border: 1px solid rgba(229, 62, 62, 0.35);
            color: #c53030;
        }

        .flash-message.success {
            display: block;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #047857;
        }

        .post-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .post-actions button {
            border: 1px solid var(--card-border);
            background: var(--card-bg);
            color: var(--text-heading);
            border-radius: 8px;
            padding: 6px 10px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .post-actions .danger {
            color: #b91c1c;
            border-color: rgba(185, 28, 28, 0.25);
        }

        .post-form .field {
            margin-bottom: 14px;
        }

        .post-form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--text-heading);
        }

        .post-form input,
        .post-form textarea,
        .post-form select {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 2px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text-heading);
            font-family: var(--font-main);
        }

        .post-form textarea {
            min-height: 140px;
            resize: vertical;
        }

        .post-form button {
            border: none;
            background: var(--primary-gradient);
            color: #fff;
            font-weight: 700;
            padding: 12px 16px;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
        }

        .quick-add-btn {
            border: none;
            border-radius: 12px;
            background: var(--primary-gradient);
            color: #fff;
            font-weight: 700;
            padding: 12px 16px;
            cursor: pointer;
            min-width: 170px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 10px 20px -14px rgba(90, 95, 207, 0.75);
        }

        .quick-add-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 24px -16px rgba(90, 95, 207, 0.85);
        }

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
            color: #3B82F6;
            opacity: 0.85;
            pointer-events: none;
            filter: brightness(0) saturate(100%) invert(63%) sepia(89%) saturate(1288%) hue-rotate(184deg);
            font-weight: 600;
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
            padding-top: 190px;
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
            height: 8px;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.45s cubic-bezier(0.22, 1, 0.36, 1);
            z-index: 3;
        }

        /* Category-specific images positioned at top as header image */
        .post::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 160px;
            background-image: var(--post-card-image);
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            opacity: 0.85;
            pointer-events: none;
            z-index: 0;
            border-radius: 16px 16px 0 0;
            --post-card-image: none;
        }

        .post.post-cat-PHP::before {
            background: linear-gradient(135deg, #e0b0ff 0%, #d78fff 100%);
        }

        .post.post-cat-PHP::after {
            --post-card-image: url('https://st2.depositphotos.com/4021139/7394/i/450/depositphotos_73943277-stock-photo-php-concept.jpg');
        }

        .post.post-cat-JavaScript::before {
            background: linear-gradient(135deg, #ffc107 0%, #ffda47 100%);
        }

        .post.post-cat-JavaScript::after {
            --post-card-image: url('https://www.shutterstock.com/image-vector/javascript-programming-language-script-code-260nw-1062509657.jpg');
            background-size: contain;
        }

        .post.post-cat-Database::before {
            background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
        }

        .post.post-cat-Database::after {
            --post-card-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS8G14B0Ocwj2r1uisQl5ql66QmVKULQfflkA&s');
        }

        .post.post-cat-CSS::before {
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
        }

        .post.post-cat-CSS::after {
            --post-card-image: url('https://cdn.mos.cms.futurecdn.net/Vp9WvV7YKdH4k8sKRePcE8.jpg');
        }

        .post:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow-hover);
            border-color: transparent;
        }
        
        .post:hover::before { opacity: 1; }

        /* Ensure content stays above images */
        .post-header, .post-excerpt, .post-footer {
            position: relative;
            z-index: 2;
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
            font-size: 0.9rem;
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
            max-width: 750px;
            width: 100%;
            max-height: 85vh;
            position: relative;
            transform: translateY(30px) scale(0.95);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid var(--card-border);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            isolation: isolate;
            overflow: hidden;
            --modal-watermark-image: none;
        }

        .modal::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: var(--modal-watermark-image);
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            opacity: 0.13;
            pointer-events: none;
            z-index: 0;
            border-radius: inherit;
        }

        .modal-inner {
            overflow-y: auto;
            max-height: 85vh;
            padding: 40px;
            position: relative;
            z-index: 1;
        }

        .modal.modal-cat-PHP {
            --modal-watermark-image: url('https://images.unsplash.com/photo-1599507593499-a3f7d7d97667?fm=jpg&q=60&w=3000&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8cGhwfGVufDB8fDB8fHww');
        }

        .modal.modal-cat-JavaScript {
            --modal-watermark-image: url('https://www.squash.io/wp-content/uploads/2023/11/javascript-series.jpg');
        }

        .modal.modal-cat-Database {
            --modal-watermark-image: url('https://cdn.corporatefinanceinstitute.com/assets/database-1024x703.jpeg');
        }

        .modal.modal-cat-CSS {
            --modal-watermark-image: url('https://web.dev/static/css/image/hero-css.png');
        }

        /* Custom Scrollbar for Modal */
        .modal-inner::-webkit-scrollbar { width: 8px; }
        .modal-inner::-webkit-scrollbar-track { background: transparent; }
        .modal-inner::-webkit-scrollbar-thumb { 
            background: var(--card-border); 
            border-radius: 10px; 
        }
        .modal-inner::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

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

        /* Load More Styling */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .load-more-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: min(100%, 320px);
            margin: 8px auto 0;
            padding: 14px 22px;
            border: 0;
            border-radius: 999px;
            background: var(--primary-gradient);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 12px 24px -14px rgba(90, 95, 207, 0.9);
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }

        .load-more-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 16px 28px -16px rgba(90, 95, 207, 0.95);
        }

        .load-more-btn:disabled {
            cursor: wait;
            opacity: 0.7;
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
            .modal-inner { padding: 24px; }
            .modal h2 { font-size: 1.8rem; }
            .modal-meta-container { flex-direction: column; align-items: flex-start; gap: 12px; }
        }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <div class="header-text">
            <h1>
                <span class="title-bold">Dynamic</span>
                <span class="title-light">Content Loader</span>
            </h1>
            <p>High-performance asynchronous data loading experience</p>
        </div>
        <div class="header-actions">
            <div id="guestActions" class="auth-pill">
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            </div>
            <div id="userActions" class="auth-pill hidden">
                <span id="userName" class="user-name"></span>
                <button type="button" id="logoutBtn">Logout</button>
            </div>
            <button id="themeToggle" class="theme-btn" aria-label="Toggle Dark Mode">
                <span class="theme-icon">🌙</span>
                <span class="theme-text">Dark</span>
            </button>
        </div>
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
            <button type="button" id="quickAddPostBtn" class="quick-add-btn">+ Add New Post</button>
            <label class="mine-toggle hidden" for="mineOnly">
                <input type="checkbox" id="mineOnly">
                My posts only
            </label>
        </div>
    </div>

    <div class="status-bar">
        <span id="resultCount">Loading database records...</span>
    </div>

    <div id="flashMessage" class="flash-message"></div>

    <div id="content"></div>

    <div id="loadMoreContainer" class="pagination"></div>
</div>

<!-- Premium Detail Modal -->
<div class="modal-overlay" id="postModal" onclick="if(event.target===this)closeModal()">
    <div class="modal">
        <div class="modal-inner">
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
</div>

<div class="modal-overlay" id="postFormModal" onclick="if(event.target===this)closePostFormModal()">
    <div class="modal">
        <div class="modal-inner">
            <button class="modal-close" onclick="closePostFormModal()" aria-label="Close form">&times;</button>
            <h2 id="postFormTitle">Add Post</h2>
            <form id="postForm" class="post-form">
                <input type="hidden" id="postId">
                <div class="field">
                    <label for="postTitle">Title</label>
                    <input type="text" id="postTitle" maxlength="255" required>
                </div>
                <div class="field">
                    <label for="postCategory">Category</label>
                    <input type="text" id="postCategory" required>
                </div>
                <div class="field">
                    <label for="postContent">Content</label>
                    <textarea id="postContent" required></textarea>
                </div>
                <button type="submit" id="postSubmitBtn">Create Post</button>
            </form>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
