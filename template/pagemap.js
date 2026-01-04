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
 * Lightbox
 */
(function() {
    /**
     * Status: just creates DIV wrapper(s) and <img>
     * Todo: css for lightbox, close button
     */

    // document.querySelectorAll('a[href$=".gif"],a[href$=".webp"],a[href$=".jpg"],a[href$=".jpeg"]').forEach(a => {
    //     a.onclick = event => {
    //         event.preventDefault();
            
    //         const outerWrapper = document.createElement('div');
    //         const innerWrapper = document.createElement('div');
            
    //         const image = document.createElement('img');
    //         image.src = a.href.replace('/pagemap.php', '');
            
    //         outerWrapper.appendChild(innerWrapper).appendChild(image);
    //         document.body.appendChild(outerWrapper);
    //     }
    // });
})();