export function initMenubar() {
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    if (!mobileMenuToggle || !mobileMenu) {
        return;
    }

    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

    mobileMenuToggle.addEventListener('click', () => {
        const isExpanded = mobileMenuToggle.getAttribute('aria-expanded') === 'true';
        mobileMenuToggle.setAttribute('aria-expanded', String(!isExpanded));
        mobileMenu.classList.toggle('hidden');
    });

    dropdownToggles.forEach((toggle) => {
        toggle.addEventListener('click', (event) => {
            dropdownToggles.forEach((otherToggle) => {
                if (otherToggle !== toggle) {
                    otherToggle.nextElementSibling.style.display = 'none';
                    otherToggle.querySelector('.dropdown-icon')?.classList.remove('rotate-180');
                }
            });

            const dropdownContent = toggle.nextElementSibling;
            const dropdownIcon = toggle.querySelector('.dropdown-icon');

            if (dropdownContent.style.display === 'block') {
                dropdownContent.style.display = 'none';
                dropdownIcon?.classList.remove('rotate-180');
            } else {
                dropdownContent.style.display = 'block';
                dropdownIcon?.classList.add('rotate-180');
            }
        });
    });

    document.addEventListener('click', (event) => {
        dropdownToggles.forEach((toggle) => {
            const dropdownContent = toggle.nextElementSibling;
            const dropdownIcon = toggle.querySelector('.dropdown-icon');

            if (!toggle.contains(event.target) && !dropdownContent?.contains(event.target)) {
                if (dropdownContent) {
                    dropdownContent.style.display = 'none';
                }
                dropdownIcon?.classList.remove('rotate-180');
            }
        });

        if (!mobileMenu.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
            mobileMenu.classList.add('hidden');
            mobileMenuToggle.setAttribute('aria-expanded', 'false');
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            mobileMenu.classList.remove('hidden');
            dropdownToggles.forEach((toggle) => {
                toggle.nextElementSibling.style.display = '';
                toggle.querySelector('.dropdown-icon')?.classList.remove('rotate-180');
            });
        } else {
            mobileMenu.classList.add('hidden');
            dropdownToggles.forEach((toggle) => {
                toggle.nextElementSibling.style.display = 'none';
                toggle.querySelector('.dropdown-icon')?.classList.remove('rotate-180');
            });
        }
    });
}
