// ── Back to Top
const btt = document.getElementById('btt');
if (btt) {
    window.addEventListener('scroll', () => {
        btt.classList.toggle('visible', window.scrollY > 300);
    });
    btt.addEventListener('click', e => {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
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

const cdH = document.getElementById('cd-h');
const cdM = document.getElementById('cd-m');
const cdS = document.getElementById('cd-s');

if (cdH && cdM && cdS) {
    let totalSeconds = 8 * 3600 + 34 * 60 + 22;
    setInterval(() => {
        if (totalSeconds <= 0) return;
        totalSeconds--;
        const h = Math.floor(totalSeconds / 3600);
        const m = Math.floor((totalSeconds % 3600) / 60);
        const s = totalSeconds % 60;
        cdH.textContent = String(h).padStart(2, '0');
        cdM.textContent = String(m).padStart(2, '0');
        cdS.textContent = String(s).padStart(2, '0');
    }, 1000);
}

function filterProducts(btn, cat) {
    document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.product-item').forEach(item => {
        if (cat === 'all' || item.dataset.cat === cat) {
            item.style.display = ''; 
        } else {
            item.style.display = 'none'; 
        }
    });
}

// ── Add to Cart feedback
document.querySelectorAll('.btn-shop').forEach(btn => {
    btn.addEventListener('click', function (e) {
        // Allow PHP link to execute if it exists
        if (this.getAttribute('href') !== '#') return;

        e.preventDefault();
        const orig = this.textContent;
        this.textContent = '✓ Added!';
        this.style.background = '#00c2ff';
        this.style.color = '#060f2e';
        setTimeout(() => {
            this.textContent = orig;
            this.style.background = '';
            this.style.color = '';
        }, 1400);
    });
});

function WhileLoop() {
    let counter = 0;
    while (counter < 5) {
        console.log('While loop iteration: ' + counter);
        counter++;
    }
}

function DoWhileLoop() {
    let counter = 0;
    do {
        console.log('Do-while loop iteration: ' + counter);
        counter++;
    } while (counter < 5);
}
