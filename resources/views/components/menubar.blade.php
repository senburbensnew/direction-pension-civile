    <style>
        /* Prevent body overflow */
        body {
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Dropdown styles */
        .dropdown-content {
            display: none;
            max-height: 80vh;
            overflow-y: auto;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .dropdown-content {
                position: static;
                width: 100%;
                max-width: 100%;
                box-shadow: none;
                border: none;
                background-color: #f9fafb;
            }

            .nav-container {
                max-width: 100%;
                width: 100%;
            }
        }

        /* Desktop dropdown positioning */
        @media (min-width: 768px) {
            .dropdown-content {
                position: absolute;
                left: 0;
                right: 0;
                width: max-content;
                min-width: 250px;
                max-width: 300px;
            }

            .group:hover .dropdown-content {
                display: block;
            }
        }

        /* Ensure text doesn't overflow */
        .truncate-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>

    <nav class="bg-white relative z-[1000] shadow-sm border-b nav-container" aria-label="Navigation Principale">
        <!-- Mobile Menu Toggle -->
        <div class="flex items-center justify-between md:hidden">
            <button id="mobile-menu-toggle" aria-expanded="false" aria-controls="mobile-menu"
                class="px-4 py-3 text-slate-600 cursor-pointer hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span class="sr-only">Toggle mobile menu</span>
                <svg class="w-6 h-6 transform transition-transform duration-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Main Navigation Menu -->
        <ul id="mobile-menu"
            class="hidden md:flex md:justify-center md:items-center md:space-x-2 p-0 m-0 
                   max-h-[80vh] overflow-y-auto md:max-h-none md:overflow-visible 
                   transition-all duration-300 w-full"
            aria-label="Menu de Navigation">
            <!-- Home -->
            <li class="w-full md:w-auto border-b md:border-none">
                <a href="{{ route('home') }}"
                    class="block px-4 py-3 text-slate-600 hover:text-blue-600 
                           text-base md:text-sm truncate-text
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Accueil
                </a>
            </li>

            <!-- Qui sommes nous? Dropdown -->
            <li class="relative w-full md:w-auto border-b md:border-none group">
                <button
                    class="dropdown-toggle flex w-full items-center justify-between px-4 py-3 
                           text-slate-600 hover:text-blue-600 text-base md:text-sm 
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="truncate-text">Qui sommes nous ?</span>
                    <svg class="dropdown-icon ml-2 w-4 h-4 transform transition-transform duration-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <ul class="dropdown-content bg-white md:shadow-lg md:border md:rounded-md"
                    aria-label="Sous-menu Qui sommes nous">
                    <li>
                        <a href="#"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Mots de la directrice
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Mission et Attributions
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Pensionaire Dropdown -->
            <li class="relative w-full md:w-auto border-b md:border-none group">
                <button
                    class="dropdown-toggle flex w-full items-center justify-between px-4 py-3 
                           text-slate-600 hover:text-blue-600 text-base md:text-sm 
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="truncate-text">Pensionaire</span>
                    <svg class="dropdown-icon ml-2 w-4 h-4 transform transition-transform duration-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <ul class="dropdown-content bg-white md:shadow-lg md:border md:rounded-md"
                    aria-label="Sous-menu Pensionaire">
                    <li>
                        <a href="#"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Demande de virement
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Demande d'attestation
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Pensionaire Dropdown -->
            <li class="relative w-full md:w-auto border-b md:border-none group">
                <button
                    class="dropdown-toggle flex w-full items-center justify-between px-4 py-3 
                                       text-slate-600 hover:text-blue-600 text-base md:text-sm 
                                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="truncate-text">Pensionaire</span>
                    <svg class="dropdown-icon ml-2 w-4 h-4 transform transition-transform duration-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <ul class="dropdown-content bg-white md:shadow-lg md:border md:rounded-md"
                    aria-label="Sous-menu Pensionaire">
                    <li>
                        <a href="#"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                               transition-colors text-sm truncate-text
                                               focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Demande de virement
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                               transition-colors text-sm truncate-text
                                               focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Demande d'attestation
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Pensionaire Dropdown -->
            <li class="relative w-full md:w-auto border-b md:border-none group">
                <button
                    class="dropdown-toggle flex w-full items-center justify-between px-4 py-3 
                           text-slate-600 hover:text-blue-600 text-base md:text-sm 
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="truncate-text">Pensionaire</span>
                    <svg class="dropdown-icon ml-2 w-4 h-4 transform transition-transform duration-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <ul class="dropdown-content bg-white md:shadow-lg md:border md:rounded-md"
                    aria-label="Sous-menu Pensionaire">
                    <li>
                        <a href="#"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Demande de virement
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Demande d'attestation
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Pensionaire Dropdown -->
            <li class="relative w-full md:w-auto border-b md:border-none group">
                <button
                    class="dropdown-toggle flex w-full items-center justify-between px-4 py-3 
                                       text-slate-600 hover:text-blue-600 text-base md:text-sm 
                                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="truncate-text">Pensionaire</span>
                    <svg class="dropdown-icon ml-2 w-4 h-4 transform transition-transform duration-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <ul class="dropdown-content bg-white md:shadow-lg md:border md:rounded-md"
                    aria-label="Sous-menu Pensionaire">
                    <li>
                        <a href="#"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                               transition-colors text-sm truncate-text
                                               focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Demande de virement
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                               transition-colors text-sm truncate-text
                                               focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Demande d'attestation
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Pensionaire Dropdown -->
            <li class="relative w-full md:w-auto border-b md:border-none group">
                <button
                    class="dropdown-toggle flex w-full items-center justify-between px-4 py-3 
                           text-slate-600 hover:text-blue-600 text-base md:text-sm 
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="truncate-text">Pensionaire</span>
                    <svg class="dropdown-icon ml-2 w-4 h-4 transform transition-transform duration-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                        </path>
                    </svg>
                </button>

                <ul class="dropdown-content bg-white md:shadow-lg md:border md:rounded-md"
                    aria-label="Sous-menu Pensionaire">
                    <li>
                        <a href="#"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Demande de virement
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Demande d'attestation
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Pensionaire Dropdown -->
            <li class="relative w-full md:w-auto border-b md:border-none group">
                <button
                    class="dropdown-toggle flex w-full items-center justify-between px-4 py-3 
                                       text-slate-600 hover:text-blue-600 text-base md:text-sm 
                                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="truncate-text">Pensionaire</span>
                    <svg class="dropdown-icon ml-2 w-4 h-4 transform transition-transform duration-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                        </path>
                    </svg>
                </button>

                <ul class="dropdown-content bg-white md:shadow-lg md:border md:rounded-md"
                    aria-label="Sous-menu Pensionaire">
                    <li>
                        <a href="#"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                               transition-colors text-sm truncate-text
                                               focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Demande de virement
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                               transition-colors text-sm truncate-text
                                               focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Demande d'attestation
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

            // Mobile menu toggle
            mobileMenuToggle.addEventListener('click', () => {
                const isExpanded = mobileMenuToggle.getAttribute('aria-expanded') === 'true';
                mobileMenuToggle.setAttribute('aria-expanded', !isExpanded);
                mobileMenu.classList.toggle('hidden');
            });

            // Dropdown toggle for mobile
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', (event) => {
                    // Close other open dropdowns
                    dropdownToggles.forEach(otherToggle => {
                        if (otherToggle !== toggle) {
                            otherToggle.nextElementSibling.style.display = 'none';
                            otherToggle.querySelector('.dropdown-icon').classList.remove(
                                'rotate-180');
                        }
                    });

                    // Toggle current dropdown
                    const dropdownContent = toggle.nextElementSibling;
                    const dropdownIcon = toggle.querySelector('.dropdown-icon');

                    if (dropdownContent.style.display === 'block') {
                        dropdownContent.style.display = 'none';
                        dropdownIcon.classList.remove('rotate-180');
                    } else {
                        dropdownContent.style.display = 'block';
                        dropdownIcon.classList.add('rotate-180');
                    }
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', (event) => {
                dropdownToggles.forEach(toggle => {
                    const dropdownContent = toggle.nextElementSibling;
                    const dropdownIcon = toggle.querySelector('.dropdown-icon');

                    if (!toggle.contains(event.target) && !dropdownContent.contains(event.target)) {
                        dropdownContent.style.display = 'none';
                        dropdownIcon.classList.remove('rotate-180');
                    }
                });

                // Close mobile menu when clicking outside
                if (!mobileMenu.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                    mobileMenu.classList.add('hidden');
                    mobileMenuToggle.setAttribute('aria-expanded', 'false');
                }
            });

            // Handle window resize
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 768) {
                    // Desktop view
                    mobileMenu.classList.remove('hidden');
                    dropdownToggles.forEach(toggle => {
                        toggle.nextElementSibling.style.display = '';
                        toggle.querySelector('.dropdown-icon').classList.remove('rotate-180');
                    });
                } else {
                    // Mobile view
                    mobileMenu.classList.add('hidden');
                    dropdownToggles.forEach(toggle => {
                        toggle.nextElementSibling.style.display = 'none';
                        toggle.querySelector('.dropdown-icon').classList.remove('rotate-180');
                    });
                }
            });
        });
    </script>
