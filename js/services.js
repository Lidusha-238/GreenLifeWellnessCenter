// Mobile Menu Toggle
const menuOpenButton = document.getElementById('menu-open-button');
const menuCloseButton = document.getElementById('menu-close-button');
const navigation = document.querySelector('.navigation');

menuOpenButton.addEventListener('click', () => {
    navigation.classList.add('active');
    menuOpenButton.style.display = 'none';
    menuCloseButton.style.display = 'block';
});

menuCloseButton.addEventListener('click', () => {
    navigation.classList.remove('active');
    menuOpenButton.style.display = 'block';
    menuCloseButton.style.display = 'none';
});

// Login/Signup Form Toggle
const userBtn = document.getElementById('user-btn');
const loginFormContainer = document.getElementById('login-form-container');
const formClose = document.getElementById('form-close');
const switchToSignup = document.getElementById('switch-to-signup');
const switchToLogin = document.getElementById('switch-to-login');
const loginForm = document.getElementById('login-form');
const signupForm = document.getElementById('signup-form');
const formTabs = document.querySelectorAll('.form-tab');

userBtn.addEventListener('click', () => {
    loginFormContainer.classList.add('active');
});

formClose.addEventListener('click', () => {
    loginFormContainer.classList.remove('active');
});

// Switch between login and signup forms
switchToSignup.addEventListener('click', (e) => {
    e.preventDefault();
    loginForm.classList.remove('active');
    signupForm.classList.add('active');
    document.querySelector('.form-tab[data-tab="login"]').classList.remove('active');
    document.querySelector('.form-tab[data-tab="signup"]').classList.add('active');
});

switchToLogin.addEventListener('click', (e) => {
    e.preventDefault();
    signupForm.classList.remove('active');
    loginForm.classList.add('active');
    document.querySelector('.form-tab[data-tab="signup"]').classList.remove('active');
    document.querySelector('.form-tab[data-tab="login"]').classList.add('active');
});

// Tab switching functionality
formTabs.forEach(tab => {
    tab.addEventListener('click', () => {
        const tabName = tab.getAttribute('data-tab');
        formTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        if (tabName === 'login') {
            signupForm.classList.remove('active');
            loginForm.classList.add('active');
        } else {
            loginForm.classList.remove('active');
            signupForm.classList.add('active');
        }
    });
});

// Form Validation for Signup
const signupErrors = document.getElementById('signup-errors');
if (signupForm) {
    signupForm.addEventListener('submit', function (e) {
        const password = this.querySelector('input[name="password"]').value;
        const phone = this.querySelector('input[name="phone_number"]').value;
        let errors = [];

        // Clear previous errors
        signupErrors.innerHTML = '';

        // Password validation
        if (password.length < 8) {
            errors.push('Password must be at least 8 characters long.');
        }

        // Phone number validation (simple check)
        if (!/^\d{10,15}$/.test(phone)) {
            errors.push('Please enter a valid phone number (10-15 digits).');
        }

        // If there are errors, prevent form submission
        if (errors.length > 0) {
            e.preventDefault();
            errors.forEach(error => {
                const errorElement = document.createElement('p');
                errorElement.textContent = error;
                signupErrors.appendChild(errorElement);
            });
        }
    });
}

// Initialize AOS (Animate On Scroll)
document.addEventListener('DOMContentLoaded', function () {
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        offset: 100
    });
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();

        const targetId = this.getAttribute('href');
        if (targetId === '#') return;

        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - 80,
                behavior: 'smooth'
            });

            // Close mobile menu if open
            if (navigation.classList.contains('active')) {
                navigation.classList.remove('active');
                menuOpenButton.style.display = 'block';
                menuCloseButton.style.display = 'none';
            }
        }
    });
});

// Responsive adjustments
function handleResponsiveChanges() {
    if (window.innerWidth > 768) {
        // Desktop - ensure menu is visible and buttons are hidden
        navigation.classList.remove('active');
        menuOpenButton.style.display = 'none';
        menuCloseButton.style.display = 'none';
    } else {
        // Mobile - show menu open button if menu is closed
        if (!navigation.classList.contains('active')) {
            menuOpenButton.style.display = 'block';
            menuCloseButton.style.display = 'none';
        }
    }
}

// Initial check and event listener for resize
handleResponsiveChanges();
window.addEventListener('resize', handleResponsiveChanges);

// Initialize Testimonials Swiper
const testimonialsSwiper = new Swiper('.testimonials-swiper', {
    slidesPerView: 1,
    spaceBetween: 30,
    loop: true,
    autoplay: {
        delay: 5000,
        disableOnInteraction: false,
    },
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
    },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
    breakpoints: {
        768: {
            slidesPerView: 2,
        },
        1024: {
            slidesPerView: 3,
        }
    }
});

window.addEventListener('scroll', function () {
    const header = document.querySelector('header');
    const scrollPosition = window.scrollY;

    if (scrollPosition > 100) { // Change 100 to whatever pixel value works for you
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

document.addEventListener('DOMContentLoaded', function () {
    // Mobile menu toggle
    const menuOpen = document.getElementById('menu-open-button');
    const menuClose = document.getElementById('menu-close-button');
    const nav = document.querySelector('.navigation');

    menuOpen.addEventListener('click', function () {
        nav.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent scrolling when menu is open
    });

    menuClose.addEventListener('click', function () {
        nav.classList.remove('active');
        document.body.style.overflow = ''; // Re-enable scrolling
    });

    // Dropdown functionality
    document.querySelectorAll('.dropbtn').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const dropdown = this.parentElement;
            dropdown.classList.toggle('active');

            // Close other open dropdowns
            document.querySelectorAll('.dropdown').forEach(otherDropdown => {
                if (otherDropdown !== dropdown && otherDropdown.classList.contains('active')) {
                    otherDropdown.classList.remove('active');
                }
            });
        });
    });

    // Close menu when clicking on a link (optional)
    document.querySelectorAll('.navigation a:not(.dropbtn)').forEach(link => {
        link.addEventListener('click', function () {
            nav.classList.remove('active');
            document.body.style.overflow = '';
        });
    });
});
