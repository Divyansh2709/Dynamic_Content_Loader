const state = {
    offset: 0,
    limit: 6,
    search: '',
    category: '',
    sort: 'newest',
    mine: false,
    total: 0,
    hasMore: false,
    categoriesLoaded: false,
    user: null
};

const appBasePath = window.location.pathname.replace(/\/[^/]*$/, '');
const appUrl = (path) => `${appBasePath}/${path.replace(/^\//, '')}`;

let searchTimeout = null;
let currentScrollPos = 0; // For modal handling
const postMap = new Map();

document.addEventListener('DOMContentLoaded', async () => {
    console.log('DOMContentLoaded fired - initializing app');
    
    try {
        initTheme();
        readUrlParams();
        await hydrateAuth();
        
        // Set initial values in DOM - with null checks
        const searchInputEl = document.getElementById('searchInput');
        const sortFilterEl = document.getElementById('sortFilter');
        const categoryFilterEl = document.getElementById('categoryFilter');
        const mineOnlyEl = document.getElementById('mineOnly');
        
        if (searchInputEl) searchInputEl.value = state.search;
        if (sortFilterEl) sortFilterEl.value = state.sort;
        if (mineOnlyEl) mineOnlyEl.checked = state.mine;

        wireAuthActions();
        wirePostForm();

        console.log('Calling loadPosts()...');
        await loadPosts();
        console.log('loadPosts() completed');

        // Event Listeners with enhanced UX
        if (searchInputEl) {
            searchInputEl.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    state.search = e.target.value;
                    state.offset = 0;
                    updateUrl();
                    loadPosts();
                }, 400);
            });
        }

        if (categoryFilterEl) {
            categoryFilterEl.addEventListener('change', (e) => {
                state.category = e.target.value;
                state.offset = 0;
                updateUrl();
                loadPosts();
            });
        }
        
        if (sortFilterEl) {
            sortFilterEl.addEventListener('change', (e) => {
                state.sort = e.target.value;
                state.offset = 0;
                updateUrl();
                loadPosts();
            });
        }

        if (mineOnlyEl) {
            mineOnlyEl.addEventListener('change', (e) => {
                if (!state.user && e.target.checked) {
                    e.target.checked = false;
                    showFlash('Please log in to filter your own posts.', true);
                    return;
                }

                state.mine = e.target.checked;
                state.offset = 0;
                updateUrl();
                loadPosts();
            });
        }

        // Handle back/forward browser buttons flawlessly
        window.addEventListener('popstate', () => {
            readUrlParams();
            if (searchInputEl) searchInputEl.value = state.search || '';
            if (categoryFilterEl) categoryFilterEl.value = state.category || '';
            if (sortFilterEl) sortFilterEl.value = state.sort || 'newest';
            if (mineOnlyEl) mineOnlyEl.checked = state.mine;
            loadPosts();
        });
    
        // Dark mode toggle
        const themeBtn = document.getElementById('themeToggle');
        if (themeBtn) {
            themeBtn.addEventListener('click', toggleTheme);
        }
    } catch (error) {
        console.error('Error during app initialization:', error);
        showFlash('Error initializing app. Please refresh the page.', true);
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
    state.offset = 0;
    if (params.has('search')) state.search = params.get('search');
    if (params.has('category')) state.category = params.get('category');
    if (params.has('sort')) state.sort = params.get('sort');
    state.mine = params.get('mine') === '1';
}

function updateUrl() {
    const params = new URLSearchParams();
    if (state.search) params.set('search', state.search);
    if (state.category) params.set('category', state.category);
    if (state.sort && state.sort !== 'newest') params.set('sort', state.sort);
    if (state.mine) params.set('mine', '1');
    
    const newUrl = `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
    window.history.pushState({ path: newUrl }, '', newUrl);
}

/* --- Core Fetch Logic --- */

async function loadPosts(options = {}) {
    try {
        const { append = false } = options;
        
        // Get elements - be defensive
        const contentEl = document.getElementById('content');
        const loadMoreContainer = document.getElementById('loadMoreContainer') || document.getElementById('pagination');
        const statusEl = document.getElementById('resultCount');
        const loadMoreBtn = document.getElementById('loadMoreBtn');

        // Critical check: if content element doesn't exist, something is very wrong
        if (!contentEl) {
            console.error('FATAL: #content element not found in DOM');
            return;
        }

        if (append) {
            // Append mode - loading more posts
            if (loadMoreBtn) {
                loadMoreBtn.disabled = true;
                loadMoreBtn.textContent = 'Loading...';
            }
            if (statusEl) {
                statusEl.innerHTML = '<span style="opacity: 0.7; display: flex; align-items: center; gap: 8px;"><svg class="spinner" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="24 40" stroke-opacity="0.8"/></svg> Loading more posts...</span>';
            }
        } else {
            // Initial load or filter change - show skeletons
            const skeletons = renderSkeletons(state.limit);
            if (skeletons) {
                contentEl.innerHTML = skeletons;
            }
            
            if (loadMoreContainer) {
                loadMoreContainer.innerHTML = '';
            }
            
            if (statusEl) {
                statusEl.innerHTML = '<span style="opacity: 0.7; display: flex; align-items: center; gap: 8px;"><svg class="spinner" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="24 40" stroke-opacity="0.8"/></svg> Fetching latest data...</span>';
            }
        }

        // Add spinner keyframes
        if (!document.getElementById('spinnerStyles')) {
            const style = document.createElement('style');
            style.id = 'spinnerStyles';
            style.textContent = '@keyframes spin { 100% { transform: rotate(360deg); } }';
            document.head.appendChild(style);
        }

        // Fetch posts from API
        const params = new URLSearchParams({
            limit: state.limit,
            offset: state.offset,
            search: state.search,
            category: state.category,
            sort: state.sort
        });

        if (state.mine) {
            params.set('mine', '1');
        }

        // Wait for both API response and minimum skeleton display time
        const [response] = await Promise.all([
            fetch(`${appUrl('api/fetch_posts.php')}?${params}`),
            new Promise(res => setTimeout(res, 300))
        ]);

        // Handle response
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.error || `Server responded with ${response.status}`);
        }

        // Update state
        const normalizedTotal = Number(data.total ?? data?.pagination?.total ?? 0);
        const normalizedHasMore = data.hasMore !== undefined
            ? Boolean(data.hasMore)
            : state.offset + Number(state.limit) < normalizedTotal;

        state.total = Number.isFinite(normalizedTotal) ? normalizedTotal : 0;
        state.hasMore = normalizedHasMore;

        // Clear post map if not appending
        if (!append) {
            postMap.clear();
        }

        // Store posts for edit/delete
        (data.posts || []).forEach((post) => {
            postMap.set(Number(post.id), post);
        });

        // Load categories if not already done
        const categories = Array.isArray(data.categories) ? data.categories : [];
        if (!state.categoriesLoaded && categories.length > 0) {
            populateCategories(categories);
            const categoryFilter = document.getElementById('categoryFilter');
            if (categoryFilter) {
                categoryFilter.value = state.category;
            }
            state.categoriesLoaded = true;
        }

        // Render posts
        const posts = Array.isArray(data.posts) ? data.posts : [];
        if (append) {
            appendPosts(posts);
        } else {
            renderPosts(posts);
        }

        // Update UI
        renderLoadMoreButton();
        updateStatusBar();
        console.log('✓ Posts loaded successfully');

    } catch (error) {
        console.error('✗ Error in loadPosts:', error);
        
        const contentEl = document.getElementById('content');
        const statusEl = document.getElementById('resultCount');
        const loadMoreBtn = document.getElementById('loadMoreBtn');

        if (options.append) {
            // Load more failed - rollback and show error
            if (loadMoreBtn) {
                loadMoreBtn.disabled = false;
                loadMoreBtn.textContent = 'Load More Posts';
            }
            state.offset = Math.max(0, state.offset - state.limit);
            showFlash(error.message || 'Failed to load more posts.', true);
        } else {
            // Initial load failed - show error state
            if (contentEl) {
                contentEl.innerHTML = `
                    <div class="error-state">
                        <div class="icon">⚠️</div>
                        <h3>Connection Error</h3>
                        <p>${escapeHtml(error.message)}</p>
                        <button onclick="loadPosts()">Try Again</button>
                    </div>`;
            }
            if (statusEl) {
                statusEl.textContent = 'Error loading records';
            }
        }
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

    contentEl.innerHTML = posts.map((post, index) => createPostCardHTML(post, index * 0.08, Number(post.id))).join('');
}

function appendPosts(posts) {
    const contentEl = document.getElementById('content');

    if (!posts || posts.length === 0) {
        return;
    }

    const existingIds = new Set(Array.from(contentEl.querySelectorAll('.post')).map((postEl) => postEl.dataset.postId));
    const fragment = document.createDocumentFragment();

    posts.forEach((post, index) => {
        const postId = String(post.id);
        if (existingIds.has(postId)) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.innerHTML = createPostCardHTML(post, (index + 1) * 0.06, postId);
        fragment.appendChild(wrapper.firstElementChild);
    });

    contentEl.appendChild(fragment);
}

function createPostCardHTML(post, animationDelay, postId) {
    return `
        <div class="post post-cat-${sanitizeCategoryClass(post.category)}" data-post-id="${postId}" style="animation-delay: ${animationDelay}s" onclick='openPost(${JSON.stringify(post).replace(/'/g, "&#39;")})' role="button" tabindex="0" aria-label="Read more about ${escapeHtml(post.title)}" onkeydown="if(event.key === 'Enter') this.click();">

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
            ${post.can_manage ? `
            <div class="post-actions">
                <button type="button" onclick="event.stopPropagation(); openEditPostModal(${Number(post.id)});">Edit</button>
                <button type="button" class="danger" onclick="event.stopPropagation(); deletePost(${Number(post.id)});">Delete</button>
            </div>` : ''}
        </div>`;
}

function loadMorePosts() {
    if (!state.hasMore) {
        return;
    }

    state.offset += state.limit;
    updateUrl();
    loadPosts({ append: true });
}

function renderLoadMoreButton() {
    const el = document.getElementById('loadMoreContainer') || document.getElementById('pagination');

    if (!el) {
        return;
    }

    if (!state.hasMore) {
        el.innerHTML = '';
        return;
    }

    el.innerHTML = `
        <button type="button" id="loadMoreBtn" class="load-more-btn" onclick="loadMorePosts()">
            Load More Posts
        </button>
    `;
}

function openPost(post) {
    currentScrollPos = window.scrollY;
    const modal = document.getElementById('postModal');

    modal.querySelector('.modal').className = 'modal modal-cat-' + sanitizeCategoryClass(post.category);
    document.getElementById('modalTitle').textContent = post.title;
    document.getElementById('modalCategory').textContent = post.category;
    document.getElementById('modalCategory').className = 'post-category cat-' + post.category;
    document.getElementById('modalAuthor').textContent = 'Written by ' + post.author;
    document.getElementById('modalDate').textContent = formatDate(post.created_at);
    document.getElementById('modalContent').textContent = post.content;

    modal.classList.add('active');
    document.body.style.position = 'fixed';
    document.body.style.top = `-${currentScrollPos}px`;
    document.body.style.width = '100%';
    document.body.style.overflowY = 'scroll';
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

function openCreatePostModal() {
    if (!state.user) {
        showFlash('You must be logged in to create a post.', true);
        return;
    }

    document.getElementById('postFormTitle').textContent = 'Add Post';
    document.getElementById('postSubmitBtn').textContent = 'Create Post';
    document.getElementById('postId').value = '';
    document.getElementById('postTitle').value = '';
    document.getElementById('postCategory').value = '';
    document.getElementById('postContent').value = '';
    document.getElementById('postFormModal').classList.add('active');
}

function openEditPostModal(postId) {
    const post = postMap.get(Number(postId));
    if (!post) {
        showFlash('Post data not found. Please refresh.', true);
        return;
    }

    document.getElementById('postFormTitle').textContent = 'Edit Post';
    document.getElementById('postSubmitBtn').textContent = 'Update Post';
    document.getElementById('postId').value = String(post.id);
    document.getElementById('postTitle').value = decodeEntities(post.title);
    document.getElementById('postCategory').value = decodeEntities(post.category);
    document.getElementById('postContent').value = decodeEntities(post.content);
    document.getElementById('postFormModal').classList.add('active');
}

function closePostFormModal() {
    document.getElementById('postFormModal').classList.remove('active');
}

function wirePostForm() {
    const form = document.getElementById('postForm');
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const id = Number(document.getElementById('postId').value || 0);
        const payload = {
            title: document.getElementById('postTitle').value.trim(),
            content: document.getElementById('postContent').value.trim(),
            category: document.getElementById('postCategory').value.trim()
        };

        if (!payload.title || !payload.content || !payload.category) {
            showFlash('Please complete title, content, and category.', true);
            return;
        }

        try {
            const endpoint = id > 0 ? appUrl('api/update_post.php') : appUrl('api/create_post.php');
            if (id > 0) payload.id = id;

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || 'Unable to save post.');
            }

            closePostFormModal();
            showFlash(data.message || 'Post saved successfully.');
            state.offset = 0;
            updateUrl();
            loadPosts();
        } catch (error) {
            showFlash(error.message || 'Failed to save post.', true);
        }
    });
}

async function deletePost(postId) {
    if (!confirm('Delete this post permanently?')) {
        return;
    }

    try {
        const response = await fetch(appUrl('api/delete_post.php'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: Number(postId) })
        });

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.error || 'Unable to delete post.');
        }

        showFlash(data.message || 'Post deleted successfully.');
        loadPosts();
    } catch (error) {
        showFlash(error.message || 'Delete failed.', true);
    }
}

async function hydrateAuth() {
    try {
        const response = await fetch(appUrl('auth/me.php'));
        const data = await response.json();
        state.user = data && data.authenticated ? data.user : null;
    } catch (error) {
        state.user = null;
    }

    updateAuthUI();
}

function wireAuthActions() {
    const addPostBtn = document.getElementById('addPostBtn');
    const quickAddPostBtn = document.getElementById('quickAddPostBtn');
    const logoutBtn = document.getElementById('logoutBtn');

    if (addPostBtn) {
        addPostBtn.addEventListener('click', openCreatePostModal);
    }

    if (quickAddPostBtn) {
        quickAddPostBtn.addEventListener('click', openCreatePostModal);
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            try {
                const response = await fetch(appUrl('auth/logout.php'), { method: 'POST' });
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.error || 'Logout failed.');
                }

                state.user = null;
                state.mine = false;
                document.getElementById('mineOnly').checked = false;
                updateAuthUI();
                updateUrl();
                window.location.href = 'login.php';
            } catch (error) {
                showFlash(error.message || 'Logout failed.', true);
            }
        });
    }
}

function updateAuthUI() {
    const guestActions = document.getElementById('guestActions');
    const userActions = document.getElementById('userActions');
    const userName = document.getElementById('userName');
    const mineToggle = document.querySelector('.mine-toggle');

    if (state.user) {
        guestActions.classList.add('hidden');
        userActions.classList.remove('hidden');
        userName.textContent = `Hi, ${state.user.name}`;
        mineToggle.classList.remove('hidden');
    } else {
        guestActions.classList.remove('hidden');
        userActions.classList.add('hidden');
        userName.textContent = '';
        mineToggle.classList.add('hidden');
    }
}

function showFlash(message, isError = false) {
    const flash = document.getElementById('flashMessage');
    if (!flash) return;

    flash.className = `flash-message ${isError ? 'error' : 'success'}`;
    flash.textContent = message;
    setTimeout(() => {
        flash.className = 'flash-message';
        flash.textContent = '';
    }, 3000);
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
    if (e.key === 'Escape') closePostFormModal();
});

/* --- Utilities & Load More --- */

function truncate(str, len) {
    if (str.length <= len) return str;
    return str.substring(0, str.lastIndexOf(' ', len)) + '...'; // Break at whole word
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
    const loadedCount = document.querySelectorAll('#content .post').length;
    const countText = `Showing <strong>${loadedCount}</strong> of <strong>${state.total}</strong> posts`;
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

function decodeEntities(value) {
    const txt = document.createElement('textarea');
    txt.innerHTML = String(value || '');
    return txt.value;
}
