    {{-- @push('styles') --}}
    <style>
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
    {{-- @endpush --}}

    <nav id="menu" class="bg-white relative z-[1000] shadow-sm border-b nav-container"
        aria-label="Navigation Principale">
        <!-- Mobile Menu Toggle -->
        <div class="flex items-center justify-between md:hidden">
            <button id="mobile-menu-toggle" aria-expanded="false" aria-controls="mobile-menu"
                class="px-4 py-3 text-slate-600 cursor-pointer hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 ">
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
                    overflow-y-auto md:max-h-none md:overflow-visible 
                   transition-all duration-300 w-full"
            aria-label="Menu de Navigation">
            <!-- Home -->
            <li class="w-full md:w-auto border-b md:border-none">
                <a href="{{ route('home') }}"
                    class="block px-4 py-3 text-slate-600 hover:text-blue-600 
                           text-base md:text-sm truncate-text
                           focus:outline-none focus:ring-2 ">
                    {{ __('messages.home') }}
                </a>
            </li>

            <!-- Qui sommes nous? Dropdown -->
            <li class="relative w-full md:w-auto border-b md:border-none group">
                <button
                    class="dropdown-toggle flex w-full items-center justify-between px-4 py-3 
                           text-slate-600 hover:text-blue-600 text-base md:text-sm 
                           focus:outline-none focus:ring-2 ">
                    <span class="truncate-text">{{ __('messages.who_are_we') }}</span>
                    <svg class="dropdown-icon ml-2 w-4 h-4 transform transition-transform duration-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                        </path>
                    </svg>
                </button>

                <ul class="dropdown-content bg-white md:shadow-lg md:border md:rounded-md"
                    aria-label="Sous-menu Qui sommes nous">
                    <li>
                        <a href="{{ route('quisommesnous.mots') }}"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 ">
                            {{ __('messages.words_from_the_director') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('quisommesnous.missions') }}"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 ">
                            {{ __('messages.mission_and_responsibilities') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('quisommesnous.historique') }}"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 ">
                            {{ __('messages.history') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('quisommesnous.structure-organique') }}"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 ">
                            {{ __('messages.organizational_structure') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('quisommesnous.financement') }}"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 ">
                            {{ __('messages.funding') }}
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Pensionaire Dropdown -->
            @if (auth()->guest() || auth()->user()?->can('viewPensionnaireMenu'))
                <li class="relative w-full md:w-auto border-b md:border-none group">
                    <button
                        class="dropdown-toggle flex w-full items-center justify-between px-4 py-3 
                           text-slate-600 hover:text-blue-600 text-base md:text-sm 
                           focus:outline-none focus:ring-2 ">
                        <span class="truncate-text">{{ __('messages.pensioner') }}</span>
                        <svg class="dropdown-icon ml-2 w-4 h-4 transform transition-transform duration-300"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>

                    <ul class="dropdown-content bg-white md:shadow-lg md:border md:rounded-md"
                        aria-label="Sous-menu Pensionaire">
                        <li>
                            <a href="{{ route('pensionnaire.virement-request-form') }}"
                                class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 ">
                                {{ __('messages.transfer_request') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pensionnaire.attestation-request-form') }}"
                                class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 ">
                                {{ __('messages.certificate_request') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pensionnaire.check-transfer-request-form') }}"
                                class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 ">
                                {{ __('messages.check_transfer_request') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pensionnaire.payment-stop-request-form') }}"
                                class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 ">
                                {{ __('messages.payment_stop_request') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pensionnaire.reinstatement-request-form') }}"
                                class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 ">
                                {{ __('messages.reinstatement_request') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pensionnaire.transfer-stop-request-form') }}"
                                class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 ">
                                {{ __('messages.transfer_stop_request') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pensionnaire.preuve-existence') }}"
                                class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 ">
                                {{ __('messages.proof_of_existence') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pensionnaire.pension-request-form') }}"
                                class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 ">
                                Demande de pension de reversion
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            <!-- Fonctionnaire Dropdown -->
            @if (auth()->guest() || auth()->user()?->can('viewFonctionnaireMenu'))
                <li class="relative w-full md:w-auto border-b md:border-none group">
                    <button
                        class="dropdown-toggle flex w-full items-center justify-between px-4 py-3 
                                       text-slate-600 hover:text-blue-600 text-base md:text-sm 
                                       focus:outline-none focus:ring-2 ">
                        <span class="truncate-text">{{ __('messages.civil_servant') }}</span>
                        <svg class="dropdown-icon ml-2 w-4 h-4 transform transition-transform duration-300"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>

                    <ul class="dropdown-content bg-white md:shadow-lg md:border md:rounded-md"
                        aria-label="Sous-menu Pensionaire">
                        <li>
                            <a href="{{ route('fonctionnaire.career-state-form') }}"
                                class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                               transition-colors text-sm truncate-text
                                               focus:outline-none focus:ring-2 ">
                                {{ __('messages.career_status_request') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('fonctionnaire.retirement-simulation-form') }}"
                                class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                               transition-colors text-sm truncate-text
                                               focus:outline-none focus:ring-2 ">
                                {{ __('messages.retirement_simulation') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('fonctionnaire.pension-request-form') }}"
                                class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                               transition-colors text-sm truncate-text
                                               focus:outline-none focus:ring-2 ">
                                {{ __('messages.pension_request') }}
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            <!-- Institution Dropdown -->
            @if (auth()->guest() || auth()->user()?->can('viewInstitutionMenu'))
                <li class="relative w-full md:w-auto border-b md:border-none group">
                    <button
                        class="dropdown-toggle flex w-full items-center justify-between px-4 py-3 
                           text-slate-600 hover:text-blue-600 text-base md:text-sm 
                           focus:outline-none focus:ring-2 ">
                        <span class="truncate-text">{{ __('messages.institutions') }}</span>
                        <svg class="dropdown-icon ml-2 w-4 h-4 transform transition-transform duration-300"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                   focus:outline-none focus:ring-2 ">
                                {{ __('messages.pension_request_transmission') }}
                            </a>
                        </li>
                        <li>
                            <a href="#"
                                class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                   transition-colors text-sm truncate-text
                                   focus:outline-none focus:ring-2 ">
                                {{ __('messages.membership_request') }}
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            <li class="relative w-full md:w-auto border-b md:border-none group">
                <button
                    class="dropdown-toggle flex w-full items-center justify-between px-4 py-3 
                                       text-slate-600 hover:text-blue-600 text-base md:text-sm 
                                       focus:outline-none focus:ring-2 ">
                    <span class="truncate-text">{{ __('messages.communications') }}</span>
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
                                               focus:outline-none focus:ring-2 ">
                            {{ __('messages.texts_and_publications') }}
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                               transition-colors text-sm truncate-text
                                               focus:outline-none focus:ring-2 ">
                            {{ __('messages.media_libraries') }}
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="text-slate-600 block px-4 py-3 hover:bg-gray-100 
                                               transition-colors text-sm truncate-text
                                               focus:outline-none focus:ring-2 ">
                            {{ __('messages.success_stories') }}
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    {{-- @push('scripts') --}}
    <script defer>
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
    {{-- @endpush --}}
