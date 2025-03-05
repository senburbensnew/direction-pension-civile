<nav class="bg-white relative z-[1000] shadow-sm border-b">
    <!-- Mobile menu button -->
    <label for="drop" class="md:hidden block px-5 py-4 text-black cursor-pointer hover:bg-gray-50 transition-colors">
        <svg class="w-8 h-8 transform transition-transform duration-300 peer-checked:rotate-90" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </label>
    <input type="checkbox" id="drop" class="hidden peer" />

    <!-- Main menu -->
    <ul
        class="hidden peer-checked:block md:flex md:justify-center md:items-center md:space-x-4 p-0 m-0 max-h-[80vh] overflow-y-auto md:max-h-none md:overflow-visible">

        <!-- Home -->
        <li class="group relative md:inline-block w-full md:w-auto border-b md:border-none">
            <a href="{{ route('home') }}"
                class="block px-5 py-4 text-black hover:text-blue-600 text-lg md:text-xl active:bg-gray-50">Accueil</a>
        </li>

        <!-- Qui sommes nous ? -->
        <li class="group relative md:inline-block w-full md:w-auto border-b md:border-none">
            <div class="flex items-center justify-between">
                <label for="drop-1" class="md:hidden block px-5 py-4 cursor-pointer grow">Qui sommes nous ?</label>
                <span
                    class="md:hidden pr-4 transform transition-transform duration-300 peer-checked-[drop-1]:rotate-180">▼</span>
            </div>
            <a href="#"
                class="hidden md:flex items-center px-5 py-4 text-black hover:text-blue-600 text-lg md:text-xl">
                Qui sommes nous ?
                <span class="ml-2 text-sm transform transition-transform duration-300 group-hover:rotate-180">▼</span>
            </a>
            <input type="checkbox" id="drop-1" class="hidden peer" />
            <ul
                class="md:absolute md:top-full md:left-0 md:hidden md:group-hover:block peer-checked:block bg-white md:shadow-lg md:min-w-[200px]">
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Mots
                        de la directrice</a></li>
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Mission
                        et Attributions</a></li>
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Historique</a>
                </li>
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Structure
                        Organique</a></li>
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Financement</a>
                </li>
            </ul>
        </li>

        <!-- Pensionaire -->
        <li class="group relative md:inline-block w-full md:w-auto border-b md:border-none">
            <div class="flex items-center justify-between">
                <label for="drop-2" class="md:hidden block px-5 py-4 cursor-pointer grow">Pensionaire</label>
                <span
                    class="md:hidden pr-4 transform transition-transform duration-300 peer-checked-[drop-2]:rotate-180">▼</span>
            </div>
            <a href="#"
                class="hidden md:flex items-center px-5 py-4 text-black hover:text-blue-600 text-lg md:text-xl">
                Pensionaire
                <span class="ml-2 text-sm transform transition-transform duration-300 group-hover:rotate-180">▼</span>
            </a>
            <input type="checkbox" id="drop-2" class="hidden peer" />
            <ul
                class="md:absolute md:top-full md:left-0 md:hidden md:group-hover:block peer-checked:block bg-white md:shadow-lg md:min-w-[250px]">
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Demande
                        de virement</a></li>
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Demande
                        d’attestation</a></li>
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Demande
                        de transfert de chèque</a></li>
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Demande
                        d’arret de paiement</a></li>
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Demande
                        de réinsertion</a></li>
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Demande
                        d’arret de virement</a></li>
            </ul>
        </li>

        <!-- Fonctionnaire -->
        <li class="group relative md:inline-block w-full md:w-auto border-b md:border-none">
            <div class="flex items-center justify-between">
                <label for="drop-3" class="md:hidden block px-5 py-4 cursor-pointer grow">Fonctionnaire</label>
                <span
                    class="md:hidden pr-4 transform transition-transform duration-300 peer-checked-[drop-3]:rotate-180">▼</span>
            </div>
            <a href="#"
                class="hidden md:flex items-center px-5 py-4 text-black hover:text-blue-600 text-lg md:text-xl">
                Fonctionnaire
                <span class="ml-2 text-sm transform transition-transform duration-300 group-hover:rotate-180">▼</span>
            </a>
            <input type="checkbox" id="drop-3" class="hidden peer" />
            <ul
                class="md:absolute md:top-full md:left-0 md:hidden md:group-hover:block peer-checked:block bg-white md:shadow-lg md:min-w-[220px]">
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Demande
                        d’état de carrière</a></li>
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Simulation
                        de retraite</a></li>
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Demande
                        de Pension</a></li>
            </ul>
        </li>

        <!-- Institutions -->
        <li class="group relative md:inline-block w-full md:w-auto border-b md:border-none">
            <div class="flex items-center justify-between">
                <label for="drop-4" class="md:hidden block px-5 py-4 cursor-pointer grow">Institutions</label>
                <span
                    class="md:hidden pr-4 transform transition-transform duration-300 peer-checked-[drop-4]:rotate-180">▼</span>
            </div>
            <a href="#"
                class="hidden md:flex items-center px-5 py-4 text-black hover:text-blue-600 text-lg md:text-xl">
                Institutions
                <span class="ml-2 text-sm transform transition-transform duration-300 group-hover:rotate-180">▼</span>
            </a>
            <input type="checkbox" id="drop-4" class="hidden peer" />
            <ul
                class="md:absolute md:top-full md:left-0 md:hidden md:group-hover:block peer-checked:block bg-white md:shadow-lg md:min-w-[240px]">
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Transmission
                        des demandes de pension</a></li>
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Demande
                        d’adhésion</a></li>
            </ul>
        </li>

        <!-- Communications -->
        <li class="group relative md:inline-block w-full md:w-auto border-b md:border-none">
            <div class="flex items-center justify-between">
                <label for="drop-5" class="md:hidden block px-5 py-4 cursor-pointer grow">Communications</label>
                <span
                    class="md:hidden pr-4 transform transition-transform duration-300 peer-checked-[drop-5]:rotate-180">▼</span>
            </div>
            <a href="#"
                class="hidden md:flex items-center px-5 py-4 text-black hover:text-blue-600 text-lg md:text-xl">
                Communications
                <span class="ml-2 text-sm transform transition-transform duration-300 group-hover:rotate-180">▼</span>
            </a>
            <input type="checkbox" id="drop-5" class="hidden peer" />
            <ul
                class="md:absolute md:top-full md:left-0 md:hidden md:group-hover:block peer-checked:block bg-white md:shadow-lg md:min-w-[220px]">
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Textes
                        et publications</a></li>
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Médiathèques</a>
                </li>
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Success
                        stories</a></li>
            </ul>
        </li>

        <!-- Portail -->
        <li class="group relative md:inline-block w-full md:w-auto border-b md:border-none">
            <div class="flex items-center justify-between">
                <label for="drop-6" class="md:hidden block px-5 py-4 cursor-pointer grow">Portail</label>
                <span
                    class="md:hidden pr-4 transform transition-transform duration-300 peer-checked-[drop-6]:rotate-180">▼</span>
            </div>
            <a href="#"
                class="hidden md:flex items-center px-5 py-4 text-black hover:text-blue-600 text-lg md:text-xl">
                Portail
                <span class="ml-2 text-sm transform transition-transform duration-300 group-hover:rotate-180">▼</span>
            </a>
            <input type="checkbox" id="drop-6" class="hidden peer" />
            <ul
                class="md:absolute md:top-full md:left-0 md:hidden md:group-hover:block peer-checked:block bg-white md:shadow-lg md:min-w-[200px]">
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Fonctionnaire</a>
                </li>
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Employé</a>
                </li>
                <li><a href="#"
                        class="block px-5 py-3 hover:bg-gray-100 active:bg-gray-200 transition-colors text-base md:text-lg">Institutions</a>
                </li>
            </ul>
        </li>
    </ul>
</nav>
