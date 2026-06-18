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

// ── Navbar search with AJAX live results
const navSearchWrap = document.querySelector('.nav-search-wrap');
const navSearchToggle = document.querySelector('.nav-search-toggle');
const navSearchClear = document.querySelector('.nav-search-clear');
const navSearchInput = document.querySelector('.nav-product-search');
const navSearchPanel = document.querySelector('.nav-search-panel');

let searchTimeout;

if (navSearchWrap && navSearchToggle && navSearchInput) {
    const setSearchPanel = (html = '') => {
        if (!navSearchPanel) return;
        navSearchPanel.innerHTML = html;
        navSearchWrap.classList.toggle('has-results', html.trim().length > 0);
    };

    const updateClearButton = () => {
        navSearchWrap.classList.toggle('has-query', navSearchInput.value.trim().length > 0);
    };

    const fetchSearchResults = async (query) => {
        query = (query || '').trim();
        
        // Clear if query is too short
        if (query.length < 2) {
            setSearchPanel('');
            return;
        }

        try {
            setSearchPanel('<div class="nav-search-loading"><i class="fas fa-spinner fa-spin"></i> Searching...</div>');
            
            const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (!data.success || data.count === 0) {
                setSearchPanel(`<div class="nav-search-empty show">No products found for "${query}"</div>`);
                return;
            }
            
            let resultsHTML = '';
            data.results.forEach(product => {
                const image = product.image ? (product.image.includes('/') ? product.image : `assets/images/products/${product.image}`) : 'assets/images/products/placeholder.png';
                resultsHTML += `
                    <a href="product.php?id=${product.id}" class="nav-search-result">
                        <img src="${image}" alt="${product.name}" class="nav-search-img">
                        <div class="nav-search-info">
                            <div class="nav-search-name">${product.name}</div>
                            <div class="nav-search-meta">${product.brand}${product.category ? ' • ' + product.category : ''}</div>
                            <div class="nav-search-price">₱${parseFloat(product.price).toLocaleString('en-PH', {minimumFractionDigits: 2})}</div>
                        </div>
                    </a>
                `;
            });
            
            resultsHTML += `<a href="store.php?q=${encodeURIComponent(query)}" class="nav-search-viewall">View all results →</a>`;
            setSearchPanel(resultsHTML);
            
        } catch (error) {
            console.error('Search error:', error);
            setSearchPanel('<div class="nav-search-empty show">Error loading results</div>');
        }
    };

    const openNavSearch = () => {
        navSearchWrap.classList.add('open');
        updateClearButton();
        navSearchInput.focus();
    };

    const closeNavSearch = () => {
        navSearchWrap.classList.remove('open');
        setSearchPanel('');
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
        const query = event.target.value;
        clearTimeout(searchTimeout);
        updateClearButton();
        
        // Debounce search
        searchTimeout = setTimeout(() => {
            if (query.trim().length >= 2) {
                fetchSearchResults(query);
            } else {
                setSearchPanel('');
            }
        }, 300);
    });

    if (navSearchClear) {
        navSearchClear.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            navSearchInput.value = '';
            updateClearButton();
            setSearchPanel('');
            navSearchInput.focus();
        });
    }

    navSearchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            const query = navSearchInput.value.trim();
            if (query) {
                window.location.href = `store.php?q=${encodeURIComponent(query)}`;
            }
        }
    });

    navSearchInput.addEventListener('focus', () => {
        openNavSearch();
    });

    updateClearButton();

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

// Store hero search with the same live results as the navbar search
const storeHeroSearch = document.querySelector('.store-hero-search');
const storeHeroSearchInput = document.querySelector('.store-product-search');
const storeHeroSearchPanel = document.querySelector('.store-search-panel');
let storeSearchTimeout;

if (storeHeroSearch && storeHeroSearchInput && storeHeroSearchPanel) {
    const setStoreSearchPanel = (html = '') => {
        storeHeroSearchPanel.innerHTML = html;
        storeHeroSearch.classList.toggle('has-results', html.trim().length > 0);
    };

    const fetchStoreSearchResults = async (query) => {
        query = (query || '').trim();
        if (query.length < 2) {
            setStoreSearchPanel('');
            return;
        }

        try {
            setStoreSearchPanel('<div class="nav-search-loading"><i class="fas fa-spinner fa-spin"></i> Searching...</div>');

            const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (!data.success || data.count === 0) {
                setStoreSearchPanel(`<div class="nav-search-empty show">No products found for "${query}"</div>`);
                return;
            }

            let resultsHTML = '';
            data.results.forEach(product => {
                const image = product.image ? (product.image.includes('/') ? product.image : `assets/images/products/${product.image}`) : 'assets/images/products/placeholder.png';
                resultsHTML += `
                    <a href="product.php?id=${product.id}" class="nav-search-result">
                        <img src="${image}" alt="${product.name}" class="nav-search-img">
                        <div class="nav-search-info">
                            <div class="nav-search-name">${product.name}</div>
                            <div class="nav-search-meta">${product.brand}${product.category ? ' â€¢ ' + product.category : ''}</div>
                            <div class="nav-search-price">â‚±${parseFloat(product.price).toLocaleString('en-PH', {minimumFractionDigits: 2})}</div>
                        </div>
                    </a>
                `;
            });

            resultsHTML += `<a href="store.php?q=${encodeURIComponent(query)}" class="nav-search-viewall">View all results â†’</a>`;
            setStoreSearchPanel(resultsHTML);
        } catch (error) {
            console.error('Search error:', error);
            setStoreSearchPanel('<div class="nav-search-empty show">Error loading results</div>');
        }
    };

    storeHeroSearchInput.addEventListener('input', (event) => {
        const query = event.target.value;
        clearTimeout(storeSearchTimeout);

        storeSearchTimeout = setTimeout(() => {
            if (query.trim().length >= 2) {
                fetchStoreSearchResults(query);
            } else {
                setStoreSearchPanel('');
            }
        }, 300);
    });

    storeHeroSearchInput.addEventListener('focus', () => {
        const query = storeHeroSearchInput.value.trim();
        if (query.length >= 2) {
            fetchStoreSearchResults(query);
        }
    });

    storeHeroSearchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setStoreSearchPanel('');
            storeHeroSearchInput.blur();
        }
    });

    document.addEventListener('click', (event) => {
        if (!storeHeroSearch.contains(event.target)) {
            setStoreSearchPanel('');
        }
    });
}

document.querySelectorAll('[data-scroll-top]').forEach(link => {
    if (link.dataset.scrollBound === 'true') return;
    link.dataset.scrollBound = 'true';
    link.addEventListener('click', event => {
        event.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
