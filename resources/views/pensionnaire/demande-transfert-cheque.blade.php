@extends('layouts.main')
<style>
    @media print {
        body * {
            visibility: hidden;
        }

        #myForm,
        #myForm * {
            visibility: visible;
        }

        #myForm {
            position: absolute;
            left: 0;
            top: 0;
        }

        #myForm button {
            visibility: hidden;
        }
    }
</style>

@section('content')
    <div class="max-w-6xl mx-auto p-6 m-2 bg-white">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-600 mb-4">
            <span class="text-gray-800">Pensionnaire</span>
            <span class="mx-2">></span>
            <span class="text-gray-800">Demande de transfert de cheques</span>
        </nav>
        <h1 class="text-2xl font-semibold text-gray-800">Demande de transfert de cheques</h1>

        <ul class="mt-3 flex flex-wrap justify-center text-sm font-medium text-center text-gray-500 dark:text-gray-400">
            <li class="me-2">
                <a href="#" class="tab-link inline-block px-4 py-3 text-white bg-blue-600 rounded-lg active"
                    data-target="pdf">
                    PDF
                </a>
            </li>
            <li class="me-2">
                <a href="#" class="tab-link inline-block px-4 py-3 rounded-lg" data-target="html_form">
                    HTML
                </a>
            </li>
        </ul>

        <div id="pdf" class="tab-content max-w-7xl mx-auto p-6 bg-white rounded-lg shadow-md">
            <!-- Page Title & Download Button -->
            <div class="flex justify-between items-center mb-4">
                <a href="{{ asset('documents/virement_bancaire_forme.pdf') }}" download
                    class="ml-auto inline-block px-4 py-2 bg-blue-600 text-white font-medium text-sm rounded hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-300">
                    Télécharger
                </a>
            </div>
            <!-- PDF Viewer -->
            <div class="border border-gray-300 rounded-lg overflow-hidden">
                <embed src="{{ asset('documents/virement_bancaire_forme.pdf') }}" type="application/pdf" width="100%"
                    height="600px" class="block">
            </div>
        </div>

        <div id="html_form" class="hidden tab-content max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">
            <div class="flex justify-between items-center mb-6">
                <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Logo"
                    class="w-24 h-24 object-cover">
                <h1 class="text-2xl font-bold text-center flex-1">MINISTERE DE L’ECONOMIE ET DES FINANCES <br /> <span
                        class="underline">PENSION CIVILE</span><br /> <span>PAIEMENT PAR VIREMENT BANCAIRE</span>
                    <br /><span class="underline font-normal">Formulaire de souscription</span>
                </h1>
                <div class="flex flex-col items-center relative">
                    <label class="block font-semibold">Photo</label>
                    <input type="file" id="photoUpload" accept="image/*" class="hidden" onchange="previewPhoto(event)">
                    <label for="photoUpload"
                        class="cursor-pointer border rounded-lg w-24 h-24 flex items-center justify-center bg-gray-200 text-gray-500 text-sm">Choisir</label>
                    <img id="photoPreview" class="absolute top-0 left-0 w-24 h-24 object-cover rounded-lg hidden" />
                </div>
            </div>

            <form>
                <div class="grid grid-cols-2 gap-4 mb-4 items-center">
                    <div>
                        <label class="block font-semibold">Code du pensionné</label>
                        <input type="text" class="w-full border rounded p-2">
                    </div>
                    <div>
                        <div class="flex flex-col space-y-2 mt-2">
                            <label class="flex items-center">
                                <input type="radio" class="mr-2" name="pension_type" value="carriere"> Pension de
                                carrière
                            </label>
                            <label class="flex items-center">
                                <input type="radio" class="mr-2" name="pension_type" value="reversibilite"> Pension de
                                réversibilité (veuf(ve))
                            </label>
                        </div>
                    </div>
                </div>
                <fieldset class="shadow-md rounded-lg p-5">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block font-semibold">NIF</label>
                            <input type="text" class="w-full border rounded p-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block font-semibold">Nom et Prénom(s)</label>
                            <input type="text" class="w-full border rounded p-2">
                        </div>
                        <div>
                            <label class="block font-semibold">Adresse Ville</label>
                            <input type="text" class="w-full border rounded p-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block font-semibold">Date de naissance</label>
                            <input type="date" class="w-full border rounded p-2">
                        </div>
                        <div>
                            <label class="block font-semibold">État civil</label>
                            <select class="w-full border rounded p-2">
                                <option value="">Sélectionner</option>
                                <option value="célibataire">Célibataire</option>
                                <option value="marié(e)">Marié(e)</option>
                                <option value="divorcé(e)">Divorcé(e)</option>
                                <option value="veuf(ve)">Veuf(ve)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold">Sexe (M/F)</label>
                            <select class="w-full border rounded p-2">
                                <option value="">Sélectionner</option>
                                <option value="M">M</option>
                                <option value="F">F</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold">Montant allocation</label>
                            <input type="text" class="w-full border rounded p-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block font-semibold">Nom et Prénom(s) de la mère</label>
                            <input type="text" class="w-full border rounded p-2">
                        </div>
                        <div>
                            <label class="block font-semibold">Portable</label>
                            <input type="text" class="w-full border rounded p-2">
                        </div>
                    </div>
                </fieldset>
                <div class="flex justify-between p-2">
                    <label class="flex items-center">
                        <input type="radio" class="mr-2" name="categorie_pension" value="civile"> <span
                            class="text-sm">PENSION CIVILE</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" class="mr-2" name="categorie_pension" value="militaire"> <span
                            class="text-sm">PENSION MILITAIRE</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" class="mr-2" name="categorie_pension" value="bndai"> <span
                            class="text-sm">BNDAI</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" class="mr-2" name="categorie_pension" value="minoterie"> <span
                            class="text-sm">MINOTERIE</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" class="mr-2" name="categorie_pension" value="selection_nationale"> <span
                            class="text-sm">SELECTION
                            NATIONALE</span>
                    </label>
                </div>

                <fieldset class="shadow-md rounded-lg p-5">
                    <div class="mb-4">
                        <label class="block font-semibold">Banque du pensionné (Banque locale)</label>
                        <input type="text" class="w-full border rounded p-2">
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block font-semibold">Numéro de compte du pensionné</label>
                            <input type="text" class="w-full border rounded p-2">
                        </div>
                        <div>
                            <label class="block font-semibold">Nom du compte du pensionné</label>
                            <input type="text" class="w-full border rounded p-2">
                        </div>
                    </div>

                    <div class="mt-6">
                        <p class="text-sm text-gray-600">Seul le numéro de compte personnel du pensionné est autorisé pour
                            cette
                            opération.</p>
                    </div>
                </fieldset>

                <div class="mt-8 text-sm">
                    <p>Par la présente, je demande à la Direction du Trésor de virer sur mon compte personnel le montant net
                        de
                        mes prestations mensuelles et je l’autorise à déduire toutes valeurs indûment versées sur mon
                        compte, en
                        l’absence d’un règlement à l’amiable.</p>
                    <p class="mt-4 font-semibold">Le ...../...../20.....</p>
                    <div class="mt-6 flex justify-between">
                        <div>
                            <hr class="border-t-2 border-black w-64">
                            <p class="font-semibold">Chef de Service de la Comptabilité</p>
                        </div>
                        <div>
                            <hr class="border-t-2 border-black w-64">
                            <p class="font-semibold">Signature du Pensionné</p>
                        </div>
                    </div>
                    <p class="mt-6 text-xs text-center">dpc/sicp/jr</p>
                    <p class="mt-2 font-semibold text-center">5, 16, Ave Charles Sumner et 104, Rue Oswald Durand,
                        Port-au-Prince, Haiti
                    </p>
                </div>

                <div class="mt-6 text-right">
                    <button class="bg-blue-600 text-white px-6 py-2 rounded">Soumettre</button>
                    <button onclick="window.print()"
                        class="mt-6 bg-blue-600 text-white px-6 py-2 rounded">Imprimer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById("photoPreview");
                    img.src = e.target.result;
                    img.classList.remove("hidden");
                }
                reader.readAsDataURL(file);
            }
        }
    </script>

    <script>
        document.querySelectorAll('.tab-link').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent the default link behavior

                // Remove active class from all tabs
                document.querySelectorAll('.tab-link').forEach(t => {
                    t.classList.remove('text-white', 'bg-blue-600', 'active');
                });

                // Add active class to the clicked tab
                this.classList.add('text-white', 'bg-blue-600', 'active');

                // Hide all content divs
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });

                // Show the corresponding content
                const targetId = this.getAttribute('data-target');
                document.getElementById(targetId).classList.remove('hidden');
            });
        });
    </script>
@endsection
