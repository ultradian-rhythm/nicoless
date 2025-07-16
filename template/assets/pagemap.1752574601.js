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