/**
 * Show active menu element
 */
(function() {
    const path = window.location.pathname.replace('/pagemap.php', '');
    
    document.querySelectorAll('header nav ul li').forEach(li => {
        const href = li.querySelector('a').getAttribute('href');

        if (path == href || (href != '/' && path.startsWith(href))) {
            li.classList.add('active');
        }
    });
})();

/**
 * Redirect links to preview
 */
(function() {
    if (window.location.pathname.startsWith('/pagemap.php')) {
        document.querySelectorAll('a').forEach(a => {
            const href = a.getAttribute('href');

            if (href.startsWith('/')) {
                a.setAttribute('href', '/pagemap.php' + href);
            }
        });
    }
})();

/**
 * Typewriter text effect
 */
(function() {
    document.querySelectorAll('.typewriter').forEach(element => {
        const text = element.innerHTML;
        element.innerHTML = '';
        element.title = 'I don’t know what to write here …';

        for (let i = 0; i < text.length; i++) {
            window.setTimeout(() => {
                element.innerHTML += text[i];
            }, 20 * i);
        }

        console.log(text)
    });
})();