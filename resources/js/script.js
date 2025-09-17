document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('section');
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

    function activateLink() {
        let currentSection = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 80; // adjust for navbar height
            if (window.scrollY >= sectionTop) {
                currentSection = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            link.style.color = ''; // reset color
            if (link.getAttribute('href') === '#' + currentSection) {
                link.classList.add('active');
                link.style.color = 'white'; // active color
            }
        });
    }

    window.addEventListener('scroll', activateLink);
    activateLink(); // initial call
});
