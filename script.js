const state = {
    page: 1,
    limit: 6,
    search: '',
    category: '',
    sort: 'newest',
    totalPages: 0,
    total: 0,
    categoriesLoaded: false
};

let searchTimeout = null;
let currentScrollPos = 0; // For modal handling

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    readUrlParams();
    
    // Set initial values in DOM
    document.getElementById('searchInput').value = state.search;
    document.getElementById('sortFilter').value = state.sort;

    loadPosts();

    // Event Listeners with enhanced UX
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        // Add subtle loading indicator on input wrapper if desired
        searchTimeout = setTimeout(() => {
            state.search = e.target.value;
            state.page = 1;
            updateUrl();
            loadPosts();
        }, 400); // Slightly longer debounce for better API usage
    });

    document.getElementById('categoryFilter').addEventListener('change', (e) => {
        state.category = e.target.value;
        state.page = 1;
        updateUrl();
        loadPosts();
    });
    
    document.getElementById('sortFilter').addEventListener('change', (e) => {
        state.sort = e.target.value;
        state.page = 1;
        updateUrl();
        loadPosts();
    });

    // Handle back/forward browser buttons flawlessly
    window.addEventListener('popstate', () => {
        readUrlParams();
        document.getElementById('searchInput').value = state.search || '';
        document.getElementById('categoryFilter').value = state.category || '';
        document.getElementById('sortFilter').value = state.sort || 'newest';
        loadPosts();
    });
    
    // Dark mode toggle
    const themeBtn = document.getElementById('themeToggle');
    if (themeBtn) {
        themeBtn.addEventListener('click', toggleTheme);
    }
});

/* --- Theme Management --- */

function initTheme() {
    const isDark = localStorage.getItem('theme') === 'dark' || 
                  (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
    
    if (isDark) {
        document.body.classList.add('dark-mode');
    }
    updateThemeBtn(isDark);
}

function toggleTheme() {
    const isDark = document.body.classList.toggle('dark-mode');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    updateThemeBtn(isDark);
    
    // Add brief animation class to body for smooth transition effect
    document.body.style.opacity = '0.9';
    setTimeout(() => { document.body.style.opacity = '1'; }, 150);
}

function updateThemeBtn(isDark) {
    const icon = document.querySelector('.theme-icon');
    const text = document.querySelector('.theme-text');
    if(icon) icon.textContent = isDark ? '☀️' : '🌙';
    if(text) text.textContent = isDark ? 'Light' : 'Dark';
}

/* --- URL State Management --- */

function readUrlParams() {
    const params = new URLSearchParams(window.location.search);
    if (params.has('page')) state.page = parseInt(params.get('page')) || 1;
    if (params.has('search')) state.search = params.get('search');
    if (params.has('category')) state.category = params.get('category');
    if (params.has('sort')) state.sort = params.get('sort');
}

function updateUrl() {
    const params = new URLSearchParams();
    if (state.page > 1) params.set('page', state.page);
    if (state.search) params.set('search', state.search);
    if (state.category) params.set('category', state.category);
    if (state.sort && state.sort !== 'newest') params.set('sort', state.sort);
    
    const newUrl = `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
    window.history.pushState({ path: newUrl }, '', newUrl);
}

/* --- Core Fetch Logic --- */

async function loadPosts() {
    const contentEl = document.getElementById('content');
    const paginationEl = document.getElementById('pagination');
    const statusEl = document.getElementById('resultCount');

    // Show smooth skeletons
    contentEl.innerHTML = renderSkeletons(state.limit);
    paginationEl.innerHTML = '';
    
    // Subtle loading state in status bar
    statusEl.innerHTML = '<span style="opacity: 0.7; display: flex; align-items: center; gap: 8px;"><svg class="spinner" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="24 40" stroke-opacity="0.8"/></svg> Fetching latest data...</span>';

    // Add spinner keyframes dynamically if not present
    if (!document.getElementById('spinnerStyles')) {
        const style = document.createElement('style');
        style.id = 'spinnerStyles';
        style.textContent = '@keyframes spin { 100% { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    }

    try {
        const params = new URLSearchParams({
            page: state.page,
            limit: state.limit,
            search: state.search,
            category: state.category,
            sort: state.sort
        });

        // Simulate slight network delay for visual smoothness of skeletons if response is too fast
        const [response] = await Promise.all([
            fetch(`fetch_posts.php?${params}`),
            new Promise(res => setTimeout(res, 300)) // Min 300ms showing skeleton
        ]);

        if (!response.ok) throw new Error(`Server responded with ${response.status}`);

        const data = await response.json();

        state.totalPages = data.pagination.totalPages;
        state.total = data.pagination.total;

        if (!state.categoriesLoaded && data.categories.length > 0) {
            populateCategories(data.categories);
            document.getElementById('categoryFilter').value = state.category;
            state.categoriesLoaded = true;
        }

        renderPosts(data.posts);
        renderPagination();
        updateStatusBar();

    } catch (error) {
        console.error('Failed to load posts:', error);
        contentEl.innerHTML = `
            <div class="error-state">
                <div class="icon">⚠️</div>
                <h3>Connection Error</h3>
                <p>${escapeHtml(error.message)}</p>
                <button onclick="loadPosts()">Try Again</button>
            </div>`;
        statusEl.textContent = 'Error loading records';
    }
}

/* --- Rendering --- */

function renderPosts(posts) {
    const contentEl = document.getElementById('content');

    if (posts.length === 0) {
        contentEl.innerHTML = `
            <div class="empty-state">
                <div class="icon">🔍</div>
                <h3>No results found</h3>
                <p>We couldn't find any posts matching your criteria.${state.search ? '<br>Try adjusting your search terms.' : ''}</p>
            </div>`;
        return;
    }

    contentEl.innerHTML = posts.map((post, i) => `
        <div class="post post-cat-${sanitizeCategoryClass(post.category)}" style="animation-delay: ${i * 0.08}s" onclick='openPost(${JSON.stringify(post).replace(/'/g, "&#39;")})' role="button" tabindex="0" aria-label="Read more about ${escapeHtml(post.title)}" onkeydown="if(event.key === 'Enter') this.click();">

            <div class="post-header">
                <div class="post-title-wrapper">
                    <h3>${escapeHtml(post.title)}</h3>
                    <div class="post-meta">
                        <span class="post-author">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            ${escapeHtml(post.author)}
                        </span>
                        <span class="post-date">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            ${formatDate(post.created_at)}
                        </span>
                    </div>
                </div>
            </div>
            <p class="post-excerpt">${truncate(escapeHtml(post.content), 130)}</p>
            <div class="post-footer">
                <span class="post-category cat-${escapeHtml(post.category)}">${escapeHtml(post.category)}</span>
                <span class="read-more">Read Article 
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </span>
            </div>
        </div>
    `).join('');
}

/* --- Modal Actions --- */

function openPost(post) {
    currentScrollPos = window.scrollY; // Save position
    
    const modal = document.getElementById('postModal');
    const modalPanel = modal.querySelector('.modal');
    modalPanel.className = 'modal modal-cat-' + sanitizeCategoryClass(post.category);
    document.getElementById('modalTitle').textContent = post.title;
    
    document.getElementById('modalCategory').textContent = post.category;
    document.getElementById('modalCategory').className = 'post-category cat-' + post.category;
    
    document.getElementById('modalAuthor').textContent = 'Written by ' + post.author;
    document.getElementById('modalDate').textContent = formatDate(post.created_at);
    
    document.getElementById('modalContent').textContent = post.content;
    
    modal.classList.add('active');
    
    // Prevent background scrolling while showing modal gracefully
    document.body.style.position = 'fixed';
    document.body.style.top = `-${currentScrollPos}px`;
    document.body.style.width = '100%';
    document.body.style.overflowY = 'scroll'; // Prevent layout shift from scrollbar disappearing
}

function closeModal() {
    const modal = document.getElementById('postModal');
    if (!modal.classList.contains('active')) return;
    
    modal.classList.remove('active');
    
    // Restore scrolling
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';
    document.body.style.overflowY = '';
    
    window.scrollTo(0, currentScrollPos);
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});

/* --- Utilities & Pagination --- */

function truncate(str, len) {
    if (str.length <= len) return str;
    return str.substring(0, str.lastIndexOf(' ', len)) + '...'; // Break at whole word
}

function renderPagination() {
    const el = document.getElementById('pagination');

    if (state.totalPages <= 1) {
        el.innerHTML = '';
        return;
    }

    let html = '';

    html += `<button onclick="goToPage(${state.page - 1})" ${state.page === 1 ? 'disabled' : ''} aria-label="Previous Page">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:-4px"><polyline points="15 18 9 12 15 6"></polyline></svg> 
             </button>`;

    const pages = getPageRange(state.page, state.totalPages);
    pages.forEach(p => {
        if (p === '...') {
            html += `<span class="page-info">…</span>`;
        } else {
            html += `<button onclick="goToPage(${p})" class="${p === state.page ? 'active' : ''}" aria-label="Page ${p}" ${p === state.page ? 'aria-current="page"' : ''}>${p}</button>`;
        }
    });

    html += `<button onclick="goToPage(${state.page + 1})" ${state.page === state.totalPages ? 'disabled' : ''} aria-label="Next Page">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:-4px"><polyline points="9 18 15 12 9 6"></polyline></svg>
             </button>`;

    el.innerHTML = html;
}

function goToPage(page) {
    if (page < 1 || page > state.totalPages || page === state.page) return;
    state.page = page;
    updateUrl();
    
    // Smooth scroll to top before loading
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Load posts
    loadPosts();
}

function getPageRange(current, total) {
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);

    const pages = [];
    pages.push(1);

    if (current > 3) pages.push('...');

    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
        pages.push(i);
    }

    if (current < total - 2) pages.push('...');

    pages.push(total);
    return pages;
}

function populateCategories(categories) {
    const select = document.getElementById('categoryFilter');
    select.innerHTML = '<option value="">All Categories</option>';
    categories.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat;
        option.textContent = cat;
        select.appendChild(option);
    });
}

function updateStatusBar() {
    const countText = `Displaying <strong>${state.total}</strong> active logic record${state.total !== 1 ? 's' : ''}`;
    document.getElementById('resultCount').innerHTML = countText;
}

function renderSkeletons(count) {
    return Array.from({ length: count }, () => `
        <div class="skeleton">
            <div class="skeleton-line title"></div>
            <div class="skeleton-line meta"></div>
            <div style="margin-top:20px;">
                <div class="skeleton-line text"></div>
                <div class="skeleton-line text"></div>
                <div class="skeleton-line short"></div>
            </div>
        </div>
    `).join('');
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function sanitizeCategoryClass(category) {
    return String(category || 'General')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^a-zA-Z0-9_-]/g, '');
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}
