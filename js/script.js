// main.js

document.addEventListener("DOMContentLoaded", function () {
    // ========== Menu Toggle ==========
    const openBtn = document.getElementById('menu-open-button');
    const closeBtn = document.getElementById('menu-close-button');
    const nav = document.querySelector('.navigation');
    const body = document.body;

    if (openBtn && closeBtn && nav) {
        openBtn.addEventListener('click', () => {
            nav.classList.add('active');
            openBtn.style.display = 'none';
            closeBtn.style.display = 'inline-block';
            body.classList.add('menu-open');
        });

        closeBtn.addEventListener('click', () => {
            nav.classList.remove('active');
            openBtn.style.display = 'inline-block';
            closeBtn.style.display = 'none';
            body.classList.remove('menu-open');
        });

        document.querySelectorAll('.navigation a:not(.dropbtn)').forEach(link => {
            link.addEventListener('click', () => {
                nav.classList.remove('active');
                openBtn.style.display = 'inline-block';
                closeBtn.style.display = 'none';
                body.classList.remove('menu-open');
            });
        });
    }

    // ========== Sticky Header ==========
    window.addEventListener('scroll', () => {
        const header = document.querySelector('header');
        if (header) header.classList.toggle('scrolled', window.scrollY > 50);
    });

    // ========== Mobile Dropdown ==========
    document.querySelectorAll('.dropbtn').forEach(button => {
        button.addEventListener('click', function (e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                const dropdown = this.parentElement;
                dropdown.classList.toggle('active');

                document.querySelectorAll('.dropdown').forEach(otherDropdown => {
                    if (otherDropdown !== dropdown) otherDropdown.classList.remove('active');
                });
            }
        });
    });

    // ========== Login Modal Setup ==========
    const loginContainer = document.getElementById('login-form-container');
    const formClose = document.getElementById('form-close');
    const switchToSignup = document.getElementById('switch-to-signup');
    const switchToLogin = document.getElementById('switch-to-login');
    const userBtn = document.getElementById('user-btn');

    if (userBtn) {
        userBtn.addEventListener('click', async function (e) {
            e.preventDefault();
            try {
                const response = await fetch('php/check_session.php', { credentials: 'include' });
                const data = await response.json();
                if (data.loggedIn) {
                    window.location.href = getDashboardUrl(data.role);
                } else {
                    loginContainer.classList.add('active');
                    switchToTab('login');
                }
            } catch (error) {
                console.error(error);
                loginContainer.classList.add('active');
                switchToTab('login');
            }
        });
    }

    formClose?.addEventListener('click', () => loginContainer.classList.remove('active'));
    loginContainer?.addEventListener('click', e => { if (e.target === loginContainer) loginContainer.classList.remove('active'); });
    switchToSignup?.addEventListener('click', e => { e.preventDefault(); switchToTab('signup'); });
    switchToLogin?.addEventListener('click', e => { e.preventDefault(); switchToTab('login'); });

    // ========== Login Form Submit ==========
    document.getElementById('login-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const form = e.target;
        const errorElement = document.getElementById('form-errors');
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;

        errorElement.textContent = '';
        errorElement.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Logging in...';

        try {
            const formData = new FormData(form);
            const response = await fetch('php/login.php', { method: 'POST', body: formData, credentials: 'include' });
            const data = await response.json();

            if (data.success) {
                sessionStorage.setItem('isLoggedIn', 'true');
                sessionStorage.setItem('role', data.role || 'user');
                window.location.href = data.redirect;
            } else {
                throw new Error(data.message || 'Login failed');
            }
        } catch (error) {
            console.error('Login error:', error);
            errorElement.textContent = error.message || 'An error occurred during login';
            errorElement.style.display = 'block';
            form.querySelector('[name="password"]').value = '';
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    });

    // ========== Signup Form Submit ==========
    document.getElementById('signup-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const form = e.target;
        const errorElement = document.getElementById('form-errors');
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;

        errorElement.textContent = '';
        errorElement.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Signing up...';

        try {
            const formData = new FormData(form);
            const response = await fetch('php/register.php', { method: 'POST', body: formData, credentials: 'include' });
            const data = await response.json();

            if (data.success) {
                errorElement.textContent = 'Registration successful! Please login.';
                errorElement.style.color = '#28a745';
                errorElement.style.display = 'block';
                setTimeout(() => { switchToTab('login'); form.reset(); }, 2000);
            } else {
                throw new Error(data.message || 'Registration failed');
            }
        } catch (error) {
            errorElement.textContent = error.message || 'An error occurred during registration';
            errorElement.style.display = 'block';
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    });

    // ========== Logout ==========
    document.getElementById('logout-btn')?.addEventListener('click', async function (e) {
        e.preventDefault();
        try {
            const response = await fetch('php/logout.php', { method: 'POST', credentials: 'include' });
            sessionStorage.clear();
            if (response.ok) {
                window.location.href = 'home.html?notification=success&message=Logged+out+successfully';
            } else {
                throw new Error('Logout failed');
            }
        } catch (error) {
            console.error('Logout error:', error);
            window.location.href = 'home.html?notification=error&message=Logout+failed';
        }
    });

    // ========== Notification Handling ==========
    document.querySelector('.popup-close')?.addEventListener('click', () => {
        document.getElementById('notification-popup')?.classList.remove('show');
    });

    const urlParams = new URLSearchParams(window.location.search);
    const notificationType = urlParams.get('notification');
    const notificationMessage = urlParams.get('message');
    if (notificationType && notificationMessage) {
        const popup = document.getElementById('notification-popup');
        if (popup) {
            popup.querySelector('.popup-message').textContent = notificationMessage;
            popup.style.borderLeft = notificationType === 'success' ? '4px solid #4CAF50' : '4px solid #F44336';
            popup.querySelector('.success-icon').style.display = notificationType === 'success' ? 'inline-block' : 'none';
            popup.querySelector('.error-icon').style.display = notificationType !== 'success' ? 'inline-block' : 'none';
            popup.classList.add('show');
            setTimeout(() => popup.classList.remove('show'), 5000);
        }
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    const swiper = new Swiper('.swiper', {
        slidesPerView: 3,          // Number of slides visible at once
        spaceBetween: 30,          // Space between slides in px
        loop: true,                // Loop back to first slide
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        // optional breakpoints for responsiveness
        breakpoints: {
            640: {
                slidesPerView: 1,
                spaceBetween: 10,
            },
            768: {
                slidesPerView: 2,
                spaceBetween: 20,
            },
            1024: {
                slidesPerView: 3,
                spaceBetween: 30,
            },
        }
    });


    // Helper: Switch tabs
    function switchToTab(tabName) {
        const loginTab = document.querySelector('[data-tab="login"]');
        const signupTab = document.querySelector('[data-tab="signup"]');
        const loginForm = document.getElementById('login-form');
        const signupForm = document.getElementById('signup-form');

        if (tabName === 'login') {
            loginTab?.classList.add('active');
            signupTab?.classList.remove('active');
            loginForm?.classList.add('active');
            signupForm?.classList.remove('active');
        } else {
            loginTab?.classList.remove('active');
            signupTab?.classList.add('active');
            loginForm?.classList.remove('active');
            signupForm?.classList.add('active');
        }
    }

    // Helper: Redirect by role
    function getDashboardUrl(role) {
        switch (role) {
            case 'admin': return 'admin_dashboard.php';
            case 'therapists': return 'therapist_dashboard.php';
            case 'user': return 'user_dashboard.php';
            default: return 'home.html';
        }
    }
});