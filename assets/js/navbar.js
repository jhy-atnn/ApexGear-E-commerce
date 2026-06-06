// ── Navbar Float
        const navbar = document.getElementById("mainNav");
        if (navbar) {
            window.addEventListener("scroll", () => {
                if (window.scrollY > 50) {
                    navbar.classList.add("nav-scrolled");
                } else {
                    navbar.classList.remove("nav-scrolled");
                }
            });
        }

        // ── Scroll animations
        const fadeEls = document.querySelectorAll('.fade-up');
        const io = new IntersectionObserver((entries) => {
            entries.forEach((e, i) => {
                if (e.isIntersecting) {
                    setTimeout(() => e.target.classList.add('visible'), i * 60);
                    io.unobserve(e.target);
                }
            });
        }, {
            threshold: 0.12
        });
        fadeEls.forEach(el => io.observe(el));

// ── Navbar search filtering
const navSearchWrap = document.querySelector('.nav-search-wrap');
const navSearchToggle = document.querySelector('.nav-search-toggle');
const navSearchInput = document.querySelector('.nav-product-search');
const navSearchResults = document.querySelectorAll('.nav-search-result');
const navSearchEmpty = document.querySelector('.nav-search-empty');

if (navSearchWrap && navSearchToggle && navSearchInput) {
    const updateSearchResults = (value) => {
        const query = (value || '').trim().toLowerCase();
        let found = false;

        navSearchResults.forEach(result => {
            const text = result.dataset.searchText || '';
            if (query === '' || text.includes(query)) {
                result.classList.remove('is-hidden');
                found = true;
            } else {
                result.classList.add('is-hidden');
            }
        });

        if (!found) {
            navSearchEmpty.classList.add('show');
        } else {
            navSearchEmpty.classList.remove('show');
        }
    };

    const openNavSearch = () => {
        navSearchWrap.classList.add('open');
        navSearchInput.focus();
        updateSearchResults(navSearchInput.value);
    };

    const closeNavSearch = () => {
        navSearchWrap.classList.remove('open');
    };

    navSearchToggle.addEventListener('click', (event) => {
        event.preventDefault();
        if (navSearchWrap.classList.contains('open')) {
            closeNavSearch();
        } else {
            openNavSearch();
        }
    });

    navSearchInput.addEventListener('input', (event) => {
        updateSearchResults(event.target.value);
    });

    navSearchInput.addEventListener('focus', () => {
        openNavSearch();
    });

    document.addEventListener('click', (event) => {
        if (!navSearchWrap.contains(event.target)) {
            closeNavSearch();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeNavSearch();
        }
    });
}

