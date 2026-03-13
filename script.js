const state = {
    page: 1,
    limit: 6,
    search: '',
    category: '',
    totalPages: 0,
    total: 0,
    categoriesLoaded: false
};

let searchTimeout = null;

document.addEventListener('DOMContentLoaded', () => {
    loadPosts();

    document.getElementById('searchInput').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            state.search = e.target.value.trim();
            state.page = 1;
            loadPosts();
        }, 350);
    });

    document.getElementById('categoryFilter').addEventListener('change', (e) => {
        state.category = e.target.value;
        state.page = 1;
        loadPosts();
    });
});

async function loadPosts() {
    const contentEl = document.getElementById('content');
    const paginationEl = document.getElementById('pagination');

    contentEl.innerHTML = renderSkeletons(state.limit);
    paginationEl.innerHTML = '';

    try {
        const params = new URLSearchParams({
            page: state.page,
            limit: state.limit,
            search: state.search,
            category: state.category
        });

        const response = await fetch(`fetch_posts.php?${params}`);

        if (!response.ok) {
            throw new Error(`Server responded with ${response.status}`);
        }

        const data = await response.json();

        state.totalPages = data.pagination.totalPages;
        state.total = data.pagination.total;

        if (!state.categoriesLoaded && data.categories.length > 0) {
            populateCategories(data.categories);
            state.categoriesLoaded = true;
        }

        renderPosts(data.posts);
        renderPagination();
        updateStatusBar();

    } catch (error) {
        console.error('Failed to load posts:', error);
        contentEl.innerHTML = `
            <div class="error-state">
                <p><strong>Failed to load content</strong></p>
                <p>${escapeHtml(error.message)}</p>
                <button onclick="loadPosts()">Try Again</button>
            </div>`;
        document.getElementById('resultCount').textContent = '';
    }
}

function renderPosts(posts) {
    const contentEl = document.getElementById('content');

    if (posts.length === 0) {
        contentEl.innerHTML = `
            <div class="empty-state">
                <div class="icon">📭</div>
                <p>No posts found${state.search ? ' for "' + escapeHtml(state.search) + '"' : ''}.</p>
            </div>`;
        return;
    }

    contentEl.innerHTML = posts.map((post, i) => `
        <div class="post" style="animation-delay: ${i * 0.06}s" onclick='openPost(${JSON.stringify(post).replace(/'/g, "&#39;")})'>
            <div class="post-header">
                <h3>${escapeHtml(post.title)}</h3>
                <span class="post-category cat-${escapeHtml(post.category)}">${escapeHtml(post.category)}</span>
            </div>
            <p class="post-excerpt">${truncate(escapeHtml(post.content), 120)}</p>
            <span class="read-more">Click to read more →</span>
        </div>
    `).join('');
}

function openPost(post) {
    const modal = document.getElementById('postModal');
    document.getElementById('modalTitle').textContent = post.title;
    document.getElementById('modalCategory').textContent = post.category;
    document.getElementById('modalCategory').className = 'post-category cat-' + post.category;
    document.getElementById('modalContent').textContent = post.content;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('postModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

function truncate(str, len) {
    return str.length > len ? str.substring(0, len) + '…' : str;
}

function renderPagination() {
    const el = document.getElementById('pagination');

    if (state.totalPages <= 1) {
        el.innerHTML = '';
        return;
    }

    let html = '';

    html += `<button onclick="goToPage(${state.page - 1})" ${state.page === 1 ? 'disabled' : ''}>&laquo; Prev</button>`;

    const pages = getPageRange(state.page, state.totalPages);
    pages.forEach(p => {
        if (p === '...') {
            html += `<span class="page-info">…</span>`;
        } else {
            html += `<button onclick="goToPage(${p})" class="${p === state.page ? 'active' : ''}">${p}</button>`;
        }
    });

    html += `<button onclick="goToPage(${state.page + 1})" ${state.page === state.totalPages ? 'disabled' : ''}>Next &raquo;</button>`;

    el.innerHTML = html;
}

function goToPage(page) {
    if (page < 1 || page > state.totalPages) return;
    state.page = page;
    loadPosts();
    window.scrollTo({ top: 0, behavior: 'smooth' });
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
    categories.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat;
        option.textContent = cat;
        select.appendChild(option);
    });
}

function updateStatusBar() {
    document.getElementById('resultCount').textContent = `${state.total} post${state.total !== 1 ? 's' : ''} found`;
}

function renderSkeletons(count) {
    return Array.from({ length: count }, () => `
        <div class="skeleton">
            <div class="skeleton-line title"></div>
            <div class="skeleton-line text"></div>
            <div class="skeleton-line text"></div>
            <div class="skeleton-line short"></div>
        </div>
    `).join('');
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}
