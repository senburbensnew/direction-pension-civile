<div class="max-w-6xl mx-auto p-4 md:p-6 m-2 bg-white">
    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-600 mb-4">
        <span class="text-gray-800">Pensionnaire</span>
        <span class="mx-2">></span>
        <span class="text-gray-800">Preuve d'existence</span>
    </nav>

    <form method="POST" action="{{ route('pensionnaire.process-existence-proof-request') }}"
        class="p-4 md:p-5 bg-white shadow-md rounded-lg border" enctype="multipart/form-data">
        @csrf
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <!-- ID Input -->
            <div class="w-full md:w-40 order-1">
                <div class="relative">
                    <input type="text" name="id_number" id="id_number" value="{{ old('id_number') }}"
                        class="peer w-full h-12 py-2 text-lg border-b-2 border-gray-500 focus:outline-none placeholder-transparent @error('id_number') border-red-500 @else border-gray-300 @enderror"
                        placeholder="NO D’IDENTITE" aria-label="Numéro d'identité" />
                    <label for="id_number"
                        class="absolute left-0 -top-4 text-gray-500 text-sm transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-base peer-focus:-top-4 peer-focus:text-sm">
                        NO D’IDENTITE
                    </label>
                    @error('id_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Organization Info -->
            <div class="text-center order-3 md:order-2 flex-grow mx-4">
                <h1 class="text-lg md:text-xl font-bold leading-tight">
                    MINISTERE DE L’ECONOMIE ET DES FINANCES
                </h1>
                <h2 class="text-md md:text-lg font-bold mb-1">
                    Direction de la Pension Civile (DPC)
                </h2>
                <h3 class="text-sm md:text-md font-semibold">
                    FICHE D’IDENTIFICATION DU PENSIONNE
                </h3>
            </div>

            <!-- Profile Picture -->
            <x-profile-picture class="order-2 md:order-3 w-16 h-16 md:w-24 md:h-24" />
        </div>

        <!-- Alerts Section -->
        @if (session('success'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- <input type="file" id="photoUpload" accept="image/*" class="hidden" onchange="previewPhoto(event)"
            name="profile_photo"> --}}
        <!-- Main Form Content -->
        <div class="space-y-6 mt-5">
            <!-- Fiscal Year -->
            <div class="w-full md:w-1/2">
                <label for="annee_fiscale" class="block text-sm font-medium text-gray-700">ANNEE FISCALE *</label>
                <input value="{{ old('fiscal_year') }}" placeholder="20../20.." type="text" name="fiscal_year"
                    id="annee_fiscale" min="1900" max="2100"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('fiscal_year')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Personal Information -->
            <fieldset class="shadow-md rounded-lg p-4 md:p-5 border">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- NIF -->
                    <div>
                        <label for="nif" class="block text-sm font-medium text-gray-700">NIF *</label>
                        <input type="text" name="nif" id="nif" value="{{ auth()->user()->nif }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-100"
                            readonly>
                        @error('nif')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Name Fields -->
                    <div>
                        <label for="nom" class="block text-sm font-medium text-gray-700">NOM *</label>
                        <input value="{{ old('lastname') }}" type="text" name="lastname" id="nom"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('lastname')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="prenom" class="block text-sm font-medium text-gray-700">PRENOM *</label>
                        <input value="{{ old('firstname') }}" type="text" name="firstname" id="prenom"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('firstname')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address Fields -->
                    <div class="md:col-span-2">
                        <label for="adresse" class="block text-sm font-medium text-gray-700">ADRESSE *</label>
                        <input value="{{ old('address') }}" type="text" name="address" id="adresse"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700">LOCALISATION *</label>
                        <input value="{{ old('location') }}" type="text" name="location" id="location"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Birth Date -->
                    <div>
                        <label for="date_naissance" class="block text-sm font-medium text-gray-700">DATE DE NAISSANCE
                            *</label>
                        <input value="{{ old('birth_date') }}" type="date" name="birth_date" id="date_naissance"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('birth_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Civil Status -->
                    <div>
                        <label for="etat_civil" class="block text-sm font-medium text-gray-700">ETAT CIVIL *</label>
                        <select name="civil_status_id" id="etat_civil"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Sélectionner</option>
                            @foreach ($civilStatuses as $status)
                                <option value="{{ $status['id'] }}" @selected(old('civil_status_id') == $status['id'])>
                                    {{ ucfirst($status['name']) }}
                                </option>
                            @endforeach
                        </select>
                        @error('civil_status_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="sexe" class="block text-sm font-medium text-gray-700">SEXE *</label>
                        <select name="gender_id" id="sexe"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Sélectionner</option>
                            @foreach ($genders as $gender)
                                <option value="{{ $gender['id'] }}" @selected(old('gender_id') == $gender['id'])>
                                    {{ $gender['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('gender_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact Info -->
                    <div>
                        <label for="boite_postale" class="block text-sm font-medium text-gray-700">BOITE POSTALE
                            *</label>
                        <input value="{{ old('postal_address') }}" type="text" name="postal_address"
                            id="boite_postale"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('postal_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="telephone" class="block text-sm font-medium text-gray-700">TELEPHONE *</label>
                        <input value="{{ old('phone') }}" type="tel" name="phone" id="telephone"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pension Info -->
                    <div>
                        <label for="montant_pension" class="block text-sm font-medium text-gray-700">MONTANT PENSION
                            *</label>
                        <input value="{{ old('pension_amount') }}" type="number" name="pension_amount"
                            id="montant_pension"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            min="0">
                        @error('pension_amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="no_moniteur" class="block text-sm font-medium text-gray-700">NO MONITEUR *</label>
                        <input value="{{ old('monitor_number') }}" type="text" name="monitor_number"
                            id="no_moniteur"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('monitor_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Dates -->
                    <div>
                        <label for="date_moniteur" class="block text-sm font-medium text-gray-700">DATE MONITEUR
                            *</label>
                        <input value="{{ old('monitor_date') }}" type="date" name="monitor_date"
                            id="date_moniteur"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('monitor_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="debut_pension" class="block text-sm font-medium text-gray-700">DEBUT PENSION
                            *</label>
                        <input value="{{ old('pension_start_date') }}" type="date" name="pension_start_date"
                            id="debut_pension"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('pension_start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="fin_pension" class="block text-sm font-medium text-gray-700">FIN PENSION *</label>
                        <input value="{{ old('pension_end_date') }}" type="date" name="pension_end_date"
                            id="fin_pension"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('pension_end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pension Type -->
                    <div class="md:col-span-2 border rounded-lg p-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">NATURE PENSION *</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            @foreach ($pensionCategories as $category)
                                <div class="flex items-center">
                                    <input type="radio" id="pension_{{ $category->slug }}"
                                        name="pension_category_id" value="{{ $category->id }}"
                                        class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                        @checked(old('pension_category_id') == $category->id)>
                                    <label for="pension_{{ $category->slug }}" class="ml-2 text-sm text-gray-700">
                                        {{ $category->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('pension_category_id')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </fieldset>

            <!-- Dependants Section -->
            <fieldset class="shadow-md rounded-lg p-4 md:p-5 border">
                <legend class="text-lg font-medium mb-4">LISTE DES DEPENDANTS</legend>
                <div>
                    <div id="no-dependants-message" class="text-center text-sm text-gray-500 py-4">
                        Aucun dépendant ajouté
                    </div>

                    <div id="dependants-table" class="hidden overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">#</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">NOM ET PRENOMS
                                    </th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">RELATION</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">DATE DE NAISSANCE
                                    </th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">SEXE</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <button type="button" onclick="addDependantRow()"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                            + Ajouter un dépendant
                        </button>
                    </div>
                </div>
            </fieldset>

            <!-- Signature Section -->
            <fieldset class="shadow-md rounded-lg p-4 md:p-5 border">
                <div class="flex flex-col md:flex-row justify-between gap-4">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-gray-600 uppercase">SIGNATURE EMPLOYE PENSION CIVILE</p>
                        <div class="h-0.5 bg-gray-300 my-2"></div>
                    </div>

                    <div class="flex-1">
                        <p class="text-xs font-semibold text-gray-600 uppercase">Signature du Pensionné</p>
                        <div class="h-0.5 bg-gray-300 my-2"></div>
                        <x-signature-pad class="h-20 md:h-32" />
                    </div>

                    <div class="flex-1">
                        <p class="text-xs font-semibold text-gray-600 uppercase">SIGNATURE MANDATAIRE</p>
                        <div class="h-0.5 bg-gray-300 my-2"></div>
                        <div class="space-y-2 text-sm">
                            <div class="h-20 md:h-32"></div>
                            <p>MERE, PERE, TUTEUR, CURATEUR</p>
                            <p>NOM__________________________</p>
                            <p>NIF OU CIN____________________</p>
                        </div>
                    </div>
                </div>
            </fieldset>

            <!-- Submit Button -->
            <div class="mt-6 text-right">
                <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors text-sm md:text-base">
                    Soumettre
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Hidden Template -->
<div class="hidden">
    <select id="genderOptionsTemplate">
        <option value="">Sélectionner</option>
        @foreach ($genders as $gender)
            <option value="{{ $gender['id'] }}">{{ $gender['name'] }}</option>
        @endforeach
    </select>
</div>

<script>
    let rowCount = 0;

    function addDependantRow() {
        const tableContainer = document.getElementById('dependants-table');
        const messageDiv = document.getElementById('no-dependants-message');
        const tbody = document.querySelector('#dependants-table tbody');

        if (rowCount === 0) {
            tableContainer.classList.remove('hidden');
            messageDiv.classList.add('hidden');
        }

        rowCount++;

        const newRow = document.createElement('tr');
        newRow.className = 'even:bg-gray-50';
        newRow.innerHTML = `
        <td class="px-4 py-2 text-sm font-medium text-gray-900">${rowCount}</td>
        <td class="px-4 py-2">
            <input type="text" name="nom_${rowCount}" 
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                   >
        </td>
        <td class="px-4 py-2">
            <input type="text" name="relation_${rowCount}" 
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                   >
        </td>
        <td class="px-4 py-2">
            <input type="date" name="date_naissance_${rowCount}" 
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                   >
        </td>
        <td class="px-4 py-2">
            <select name="sexe_${rowCount}" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                    >
                ${document.getElementById('genderOptionsTemplate').innerHTML}
            </select>
        </td>
        <td class="px-4 py-2">
            <button type="button" onclick="deleteRow(this)" 
                    class="text-red-600 hover:text-red-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </td>
    `;

        tbody.appendChild(newRow);
    }

    function deleteRow(button) {
        const row = button.closest('tr');
        row.remove();
        rowCount--;
        reindexRows();

        if (rowCount === 0) {
            document.getElementById('dependants-table').classList.add('hidden');
            document.getElementById('no-dependants-message').classList.remove('hidden');
        }
    }

    function reindexRows() {
        const rows = document.querySelectorAll('#dependants-table tbody tr');
        rows.forEach((row, index) => {
            row.cells[0].textContent = index + 1;
            const newIndex = index + 1;
            row.querySelectorAll('input, select').forEach(input => {
                input.name = input.name.replace(/_(\d+)$/, `_${newIndex}`);
            });
        });
        rowCount = rows.length;
    }
</script>
