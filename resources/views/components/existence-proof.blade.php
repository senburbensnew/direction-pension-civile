<div class="max-w-6xl mx-auto p-6 m-2 bg-white">
    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-600 mb-4">
        <span class="text-gray-800">Pensionnaire</span>
        <span class="mx-2">></span>
        <span class="text-gray-800">Preuve d'existence</span>
    </nav>

    {{-- <form method="POST" action="{{ route('pensionnaire.process-identification') }}" --}}
    <form method="POST" action="{{ route('pensionnaire.process-existence-proof-request') }}"
        class="p-5 bg-white shadow-md rounded-lg border">
        @csrf
        <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-8">
            <div class="text-center mb-6 flex flex-col md:flex-row items-center justify-center w-full gap-4 md:gap-8">
                <!-- ID Number Input - Mobile first -->
                <div class="relative w-40 md:w-48"> <!-- Fixed width value -->
                    <input type="text" name="id_number" id="id_number" value="{{ old('id_number') }}"
                        class="peer w-full h-12 py-2 text-lg border-b-2 border-gray-500 focus:outline-none placeholder-transparent"
                        placeholder="NO D’IDENTITE" aria-label="Numéro d'identité" />
                    <label for="id_number"
                        class="absolute left-0 -top-4 text-gray-500 text-sm transition-all peer-placeholder-shown:top-2 peer-placeholder-shown:text-base peer-focus:-top-4 peer-focus:text-sm">
                        NO D’IDENTITE
                    </label>
                </div>

                <!-- Organization Info -->
                <div class="px-4 order-2 md:order-none flex-shrink-0">
                    <h1 class="text-xl md:text-2xl font-bold leading-tight">
                        MINISTERE DE L’ECONOMIE ET DES FINANCES
                    </h1>
                    <h2 class="text-lg md:text-xl font-bold mb-1">
                        Direction de la Pension Civile (DPC)
                    </h2>
                    <h3 class="text-md font-semibold mb-4 md:mb-6">
                        FICHE D’IDENTIFICATION DU PENSIONNE
                    </h3>
                </div>

                <!-- Profile Picture -->
                <x-profile-picture class="order-3 md:order-none w-24 h-24 md:w-32 md:h-32" />
            </div>
        </div>
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
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

        <div class="w-1/2 mt-5">
            <label for="annee_fiscale" class="block text-sm font-medium text-gray-700">ANNEE FISCALE *</label>
            <input value="{{ old('fiscal_year') }}" placeholder="20../20.." type="text" name="fiscal_year"
                id="annee_fiscale" min="1900" max="2100"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <fieldset class="mt-2 mb-2 shadow-md rounded-lg p-5 border">
            <div class="grid grid-cols-2 gap-4 mb-4 items-center">
                <div>
                    <label for="nif" class="block text-sm font-medium text-gray-700">NIF *</label>
                    <input type="text" name="nif" id="nif" value="{{ auth()->user()->nif }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-100"
                        readonly>
                </div>
                <div>
                    <label for="nom" class="block text-sm font-medium text-gray-700">NOM *</label>
                    <input value="{{ old('lastname') }}" type="text" name="lastname" id="nom"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="prenom" class="block text-sm font-medium text-gray-700">PRENOM *</label>
                    <input value="{{ old('firstname') }}" type="text" name="firstname" id="prenom"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="adresse" class="block text-sm font-medium text-gray-700">ADRESSE *</label>
                    <input value="{{ old('address') }}" type="text" name="address" id="adresse"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700">LOCALISATION *</label>
                    <input value="{{ old('location') }}" type="text" name="location" id="location"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="date_naissance" class="block text-sm font-medium text-gray-700">DATE DE NAISSANCE
                        *</label>
                    <input value="{{ old('birth_date') }}" type="date" name="birth_date" id="date_naissance"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
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
                </div>
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
                </div>
                <div>
                    <label for="boite_postale" class="block text-sm font-medium text-gray-700">BOITE POSTALE *</label>
                    <input value="{{ old('postal_address') }}" type="text" name="postal_address"
                        id="boite_postale"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="telephone" class="block text-sm font-medium text-gray-700">TELEPHONE *</label>
                    <input value="{{ old('phone') }}" type="tel" name="phone" id="telephone"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="montant_pension" class="block text-sm font-medium text-gray-700">MONTANT PENSION
                        *</label>
                    <input value="{{ old('pension_amount') }}" type="number" name="pension_amount"
                        id="montant_pension"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        min="0">
                </div>
                <div>
                    <label for="no_moniteur" class="block text-sm font-medium text-gray-700">NO MONITEUR *</label>
                    <input value="{{ old('monitor_number') }}" type="text" name="monitor_number"
                        id="no_moniteur"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="date_moniteur" class="block text-sm font-medium text-gray-700">DATE MONITEUR *</label>
                    <input value="{{ old('monitor_date') }}" type="date" name="monitor_date" id="date_moniteur"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="debut_pension" class="block text-sm font-medium text-gray-700">DEBUT PENSION *</label>
                    <input value="{{ old('pension_start_date') }}" type="date" name="pension_start_date"
                        id="debut_pension"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="fin_pension" class="block text-sm font-medium text-gray-700">FIN PENSION *</label>
                    <input value="{{ old('pension_end_date') }}" type="date" name="pension_end_date"
                        id="fin_pension"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="nature_pension" class="block text-sm font-medium text-gray-700">NATURE PENSION
                        *</label>
                    @foreach ($pensionCategories as $category)
                        <div class="flex items-center">
                            <input type="radio" id="pension_{{ $category->slug }}" name="pension_category_id"
                                value="{{ $category->id }}"
                                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                @checked(old('pension_category_id') == $category->id)>
                            <label for="pension_{{ $category->slug }}" class="ml-2 text-sm text-gray-700">
                                {{ $category->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </fieldset>

        <fieldset class="mt-2 mb-2 shadow-md rounded-lg p-5 border">
            <legend>LISTE DES DEPENDANTS</legend>
            <div>
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">
                                NOM ET PRENOMS</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">
                                RELATION</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">
                                DATE DE NAISSANCE</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 uppercase tracking-wider">
                                SEXE</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                1
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="text" name="nom_1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="text" name="relation_1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="date" name="date_naissance_1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <select name="sexe" id="sexe"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Sélectionner</option>
                                    @foreach ($genders as $gender)
                                        <option value="{{ $gender['id'] }}" @selected(old('gender_id') == $gender['id'])>
                                            {{ $gender['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                2
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="text" name="nom_2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="text" name="relation_2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="date" name="date_naissance_2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <select name="sexe" id="sexe"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Sélectionner</option>
                                    @foreach ($genders as $gender)
                                        <option value="{{ $gender['id'] }}" @selected(old('gender_id') == $gender['id'])>
                                            {{ $gender['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                3
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="text" name="nom_3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="text" name="relation_3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="date" name="date_naissance_3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <select name="sexe" id="sexe"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Sélectionner</option>
                                    @foreach ($genders as $gender)
                                        <option value="{{ $gender['id'] }}" @selected(old('gender_id') == $gender['id'])>
                                            {{ $gender['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                4
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="text" name="nom_3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="text" name="relation_3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="date" name="date_naissance_3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <select name="sexe" id="sexe"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Sélectionner</option>
                                    @foreach ($genders as $gender)
                                        <option value="{{ $gender['id'] }}" @selected(old('gender_id') == $gender['id'])>
                                            {{ $gender['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                5
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="text" name="nom_3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="text" name="relation_3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <input type="date" name="date_naissance_3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <select name="sexe" id="sexe"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Sélectionner</option>
                                    @foreach ($genders as $gender)
                                        <option value="{{ $gender['id'] }}" @selected(old('gender_id') == $gender['id'])>
                                            {{ $gender['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <!-- Additional rows can be added here in the same format -->
                    </tbody>
                </table>

            </div>
        </fieldset>

        <fieldset class="mt-2 mb-2 shadow-md rounded-lg p-5 border flex justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                    SIGNATURE EMPLOYE PENSION CIVILE
                </p>
                <div class="h-2 border-t-2 border-gray-700"></div>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                    Signature du Pensionné
                </p>
                <div class="h-2 border-t-2 border-gray-700"></div>
                <x-signature-pad />
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                    SIGNATURE MANDATAIRE
                </p>
                <div class="h-2 border-t-2 border-gray-700"></div>
                <br /> <br /> <br /> <br />
                MERE, PERE, TUTEUR, CURATEUR <br />
                NOM__________________________________<br />
                NIF OU CIN__________________________________
            </div>
        </fieldset>

        <fieldset class="mt-2 mb-2 shadow-md rounded-lg p-5 border flex justify-between">
        </fieldset>

        <div class="mt-8 text-sm text-gray-700 text-right">
            <button type="submit"
                class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors">Soumettre</button>
        </div>
    </form>
</div>
