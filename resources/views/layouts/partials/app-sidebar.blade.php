@php
    use Illuminate\Support\Facades\Storage;

    $user = auth()->user();
    $isProvisionnel = $user?->isProvisionnel();
    $hasPortal = $user?->hasPensionPortalAccess();
    $demandeCompte = $user?->demandeCreationCompte;

    $corbeilleRoles = \Database\Seeders\RolesAndPermissionsSeeder::internalRoles();
    $showCorbeille = $user?->canSeePortalNavCorbeille() ?? false;
    $showPersonal = ($user?->canSeePortalNavMesDemandes() ?? false) && ! $isProvisionnel;
    $showCodesPension = $user
        && ! $isProvisionnel
        && ($user->hasCategory('pensionne') || $user->hasRole('pensionne'));
    $isInternalDpcUser = $user?->hasAnyRole($corbeilleRoles) ?? false;
    $showRencontreCreate = $user && ! $isInternalDpcUser;

    $hasUpperPortalNav = $showPersonal
        || $showCorbeille
        || $isProvisionnel
        || (bool) $demandeCompte
        || $showCodesPension
        || ($user?->hasAnyRole(['validateur_rdv', 'responsable_service_accueil_formalites', 'directeur', 'assistant_directeur']) ?? false)
        || ($user?->hasRole(\App\Models\User::ROLE_AGENT_RDV) ?? false)
        || ($user?->canSeePortalNavFormalitesComptes() ?? false)
        || ($user?->canSeePortalNavFormalitesSuivi() ?? false);

    $sidebarHome = $showPersonal
        ? route('personal.dashboard')
        : ($showCorbeille ? route('personal.cart') : route('home'));
@endphp

@php
    $portalNavLink = function (bool $active): string {
        $base = 'flex items-center gap-2.5 min-h-9 py-1.5 px-2.5 rounded-md text-[0.8rem] font-medium transition-colors group-data-[collapsed=true]/body:justify-center group-data-[collapsed=true]/body:px-2 [&>i]:w-5 [&>i]:shrink-0 [&>i]:text-center [&>i]:text-[0.85rem]';
        $state = $active ? 'bg-white/20 text-white' : 'text-slate-400 hover:bg-white/10 hover:text-slate-200';
        return trim($base.' '.$state);
    };
    $portalNavLabel = 'truncate group-data-[collapsed=true]/body:hidden';
@endphp

<div class="flex items-center gap-2 px-3 py-3 border-b border-white/10 group-data-[collapsed=true]/body:justify-center group-data-[collapsed=true]/body:px-2">
    <a href="{{ $sidebarHome }}" class="flex items-center gap-2 min-w-0 flex-1 group group-data-[collapsed=true]/body:hidden" title="Mon espace">
        <x-optimized-image src="images/setting-logo-1-M13oPLiYoM.png" alt="DPC" class="w-8 h-8 rounded flex-shrink-0" width="32" height="32" loading="eager"></x-optimized-image>
        <div class="leading-tight min-w-0 overflow-hidden truncate">
            <p class="text-white text-xs font-semibold leading-snug truncate">Pension Civile</p>
            <p class="text-slate-400 text-[10px]">Mon espace</p>
        </div>
    </a>
    <button type="button" id="portal-sidebar-collapse" @click="toggleCollapse()"
        class="hidden lg:flex flex-shrink-0 w-7 h-7 items-center justify-center rounded-md text-slate-400 hover:text-white hover:bg-white/10 transition-colors group-data-[collapsed=true]/body:mx-auto"
        title="Réduire le menu">
        <i class="fas fa-angles-left text-xs group-data-[collapsed=true]/body:hidden"></i>
        <i class="fas fa-angles-right text-xs hidden group-data-[collapsed=true]/body:inline"></i>
    </button>
</div>

<nav class="flex-1 overflow-y-auto overflow-x-hidden py-2 px-1.5 space-y-0.5">
    @if($showPersonal)
        <a href="{{ route('personal.dashboard') }}" title="Mes demandes"
           class="{{ $portalNavLink(request()->routeIs('personal.dashboard', 'personal.requests-dashboard', 'personal.index') ? 'active' : '') }}">
            <i class="fas fa-folder-open"></i>
            <span class="{{ $portalNavLabel }}">Mes demandes</span>
        </a>
    @endif

    @if($showCorbeille)
        <a href="{{ route('personal.cart') }}" title="Corbeille"
           class="{{ $portalNavLink(request()->routeIs('personal.cart*') ? 'active' : '') }}">
            <i class="fas fa-inbox"></i>
            <span class="{{ $portalNavLabel }}">Corbeille</span>
        </a>
    @endif

    @if($isProvisionnel)
        <a href="{{ route('compte.provisoire') }}" title="Espace provisoire"
           class="{{ $portalNavLink(request()->routeIs('compte.provisoire') ? 'active' : '') }}">
            <i class="fas fa-hourglass-half"></i>
            <span class="{{ $portalNavLabel }}">Espace provisoire</span>
        </a>
    @endif

    @if($demandeCompte)
        <a href="{{ route('compte.demande.show') }}" title="Demande de compte"
           class="{{ $portalNavLink(request()->routeIs('compte.demande.*') ? 'active' : '') }}">
            <i class="fas fa-user-plus"></i>
            <span class="{{ $portalNavLabel }}">Demande de compte</span>
        </a>
    @endif

    @if($showCodesPension)
        <a href="{{ route('compte.affiliations.index') }}" title="Mes codes pension"
           class="{{ $portalNavLink(request()->routeIs('compte.affiliations.*') ? 'active' : '') }}">
            <i class="fas fa-id-card"></i>
            <span class="{{ $portalNavLabel }}">Codes pension</span>
        </a>
    @endif

    @role(['validateur_rdv', 'responsable_service_accueil_formalites', 'directeur', 'assistant_directeur'])
        <a href="{{ route('rencontres.pilotage.index') }}" title="Pilotage des RDV"
           class="{{ $portalNavLink(request()->routeIs('rencontres.pilotage.index', 'rencontres.pilotage.show') ? 'active' : '') }}">
            <i class="fas fa-calendar-check"></i>
            <span class="{{ $portalNavLabel }}">Pilotage RDV</span>
        </a>
    @endrole

    @role(\App\Models\User::ROLE_AGENT_RDV)
        <a href="{{ route('rencontres.pilotage.disponibilites.edit') }}" title="Mes créneaux"
           class="{{ $portalNavLink(request()->routeIs('rencontres.pilotage.disponibilites.*') ? 'active' : '') }}">
            <i class="fas fa-clock"></i>
            <span class="{{ $portalNavLabel }}">Mes créneaux</span>
        </a>
    @endrole

    @if($user?->canSeePortalNavFormalitesComptes())
        <a href="{{ route('formalites.comptes-demandes.index') }}" title="Demandes de compte"
           class="{{ $portalNavLink(request()->routeIs('formalites.comptes-demandes.*') ? 'active' : '') }}">
            <i class="fas fa-user-check"></i>
            <span class="{{ $portalNavLabel }}">Comptes (form.)</span>
        </a>
    @endif

    @if($user?->canSeePortalNavFormalitesSuivi())
        <a href="{{ route('formalites.suivi.index') }}" title="Suivi formalités"
           class="{{ $portalNavLink(request()->routeIs('formalites.suivi.*') ? 'active' : '') }}">
            <i class="fas fa-clipboard-check"></i>
            <span class="{{ $portalNavLabel }}">Suivi formalités</span>
        </a>
    @endif

    <!-- @if(auth()->user()?->can('pension_mandats.view') || auth()->user()?->can('beneficiaire_relations.view'))
        <a href="{{ route('formalites.representations.index') }}" title="Mandats et relations"
           class="{{ $portalNavLink(request()->routeIs('formalites.representations.*', 'formalites.pension-mandats.*', 'formalites.beneficiaire-relations.*') ? 'active' : '') }}">
            <i class="fas fa-people-arrows"></i>
            <span class="{{ $portalNavLabel }}">Mandats &amp; rel.</span>
        </a>
    @endif -->

    @if($hasUpperPortalNav)
        <div class="my-2 border-t border-white/10 mx-1.5 group-data-[collapsed=true]/body:mx-2"></div>
    @endif

    <a href="{{ route('home') }}" title="Accueil public" class="{{ $portalNavLink(false) }}">
        <i class="fas fa-home"></i>
        <span class="{{ $portalNavLabel }}">Accueil</span>
    </a>
    <a href="{{ route('simulateur-calcul') }}" title="{{ __('messages.simulateur-calcul') }}"
       class="{{ $portalNavLink(request()->routeIs('simulateur-calcul') ? 'active' : '') }}">
        <i class="fas fa-calculator"></i>
        <span class="{{ $portalNavLabel }}">{{ __('messages.simulateur-calcul') }}</span>
    </a>
    @if($showRencontreCreate)
        <a href="{{ route('demandes.rencontre.create') }}" title="{{ __('messages.appointment_request') }}"
           class="{{ $portalNavLink(request()->routeIs('demandes.rencontre.create', 'demandes.rencontre.store') ? 'active' : '') }}">
            <i class="fas fa-video"></i>
            <span class="{{ $portalNavLabel }}">{{ __('messages.appointment_request') }}</span>
        </a>
    @endif
</nav>

<div class="border-t border-white/10 px-1.5 py-2 space-y-0.5">
    <a href="{{ route('notifications.index') }}" title="Notifications"
       class="{{ $portalNavLink(request()->routeIs('notifications.*') ? 'active' : '') }}">
        <i class="fas fa-bell"></i>
        <span class="{{ $portalNavLabel }}">Notifications</span>
    </a>

    @if(! $isProvisionnel)
        <a href="{{ route('profile.edit') }}" title="Mon profil"
           class="{{ $portalNavLink(request()->routeIs('profile.*') ? 'active' : '') }}">
            <i class="fas fa-user-circle"></i>
            <span class="{{ $portalNavLabel }}">Mon profil</span>
        </a>
    @endif

    @role('admin')
        <a href="{{ route('admin.dashboard.index') }}" title="Administration" class="{{ $portalNavLink(false) }}">
            <i class="fas fa-cog"></i>
            <span class="{{ $portalNavLabel }}">Administration</span>
        </a>
    @endrole

    @auth
        <div class="flex items-center gap-2 px-2 py-2 group-data-[collapsed=true]/body:hidden">
            @if($user->profile_photo)
                <img src="{{ Storage::url($user->profile_photo) }}"
                     class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
            @else
                <div class="w-7 h-7 rounded-full bg-slate-600 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-user text-slate-300 text-xs"></i>
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-white text-xs font-medium truncate">{{ $user->name }}</p>
                <p class="text-slate-500 text-[10px] truncate">{{ $user->accountStatusLabel() }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                @csrf
                <button type="submit" title="Déconnexion"
                    class="text-slate-500 hover:text-red-400 transition-colors p-1">
                    <i class="fas fa-sign-out-alt text-sm"></i>
                </button>
            </form>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="hidden group-data-[collapsed=true]/body:block">
            @csrf
            <button type="submit" title="Déconnexion" class="{{ $portalNavLink(false) }} w-full">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </form>
    @endauth
</div>
