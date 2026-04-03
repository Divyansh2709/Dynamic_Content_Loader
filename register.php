<?php
declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Register | Dynamic Content Loader';
include __DIR__ . '/partials/header.php';
?>
<div class="auth-shell">
    <div class="auth-card">
        <h1>Create account</h1>
        <p>Join now to publish and manage your posts.</p>
        <div id="notice" class="notice"></div>
        <form id="registerForm" novalidate>
            <div class="field">
                <label for="name">Name</label>
                <input id="name" type="text" required autocomplete="name">
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" required autocomplete="email">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" type="password" required minlength="6" autocomplete="new-password">
            </div>
            <button class="btn" type="submit">Create Account</button>
        </form>
        <p class="meta-link">Already have an account? <a href="login.php">Log in</a></p>
    </div>
</div>
<script>
const form = document.getElementById('registerForm');
const notice = document.getElementById('notice');
const appBasePath = window.location.pathname.replace(/\/[^/]*$/, '');

function showNotice(message, type) {
    notice.className = 'notice ' + type;
    notice.textContent = message;
}

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const payload = {
        name: document.getElementById('name').value.trim(),
        email: document.getElementById('email').value.trim(),
        password: document.getElementById('password').value
    };

    if (!payload.name || !payload.email || !payload.password) {
        showNotice('Please fill in all fields.', 'error');
        return;
    }

    try {
        const response = await fetch(`${appBasePath}/auth/register.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        let data;
        try {
            data = await response.json();
        } catch (parseError) {
            console.error('Failed to parse response:', parseError);
            showNotice('Server error: Invalid response format.', 'error');
            return;
        }

        if (!response.ok) {
            showNotice(data.error || `Registration failed (${response.status}).`, 'error');
            return;
        }

        showNotice('Registration successful. Redirecting...', 'success');
        setTimeout(() => { window.location.href = 'index.php'; }, 500);
    } catch (error) {
        console.error('Fetch error:', error);
        showNotice('Network error: ' + (error.message || 'Please try again.'), 'error');
    }
});
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>
