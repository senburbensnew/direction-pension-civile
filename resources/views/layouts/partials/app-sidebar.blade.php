@php
    use Illuminate\Support\Facades\Storage;

    $user = auth()->user();
    $isProvisionnel = $user?->isProvisionnel();
    $hasPortal = $user?->hasPensionPortalAccess();
    $demandeCompte = $user?->demandeCreationCompte;

    $corbeilleRoles = \Database\Seeders\RolesAndPermissionsSeeder::internalRoles();
    $showCorbeille = $user && $user->hasAnyRole($corbeilleRoles);
    $showPersonal = $hasPortal || $user?->hasAnyRole(['fonctionnaire', 'institution']);
    $rencontreMotifs = $user
        ? app(\App\Services\RencontreMotifService::class)->motifsForUser($user)
        : [];
    $showRencontreCreate = $rencontreMotifs !== [];

    $sidebarHome = ($showPersonal && ! $isProvisionnel)
        ? route('personal.dashboard')
        : ($showCorbeille ? route('personal.cart') : route('home'));
@endphp

<div class="portal-sidebar-brand flex items-center gap-2 px-3 py-3 border-b border-white/10">
    <a href="{{ $sidebarHome }}" class="flex items-center gap-2 min-w-0 flex-1 group" title="Mon espace">
        <x-optimized-image src="images/setting-logo-1-M13oPLiYoM.png" alt="DPC" class="w-8 h-8 rounded flex-shrink-0" width="32" height="32" loading="eager"></x-optimized-image>
        <div class="portal-sidebar-label leading-tight min-w-0 overflow-hidden">
            <p class="text-white text-xs font-semibold leading-snug truncate">Pension Civile</p>
            <p class="text-slate-400 text-[10px]">Mon espace</p>
        </div>
    </a>
    <button type="button" id="portal-sidebar-collapse" onclick="togglePortalSidebarCollapse()"
        class="hidden lg:flex flex-shrink-0 w-7 h-7 items-center justify-center rounded-md text-slate-400 hover:text-white hover:bg-white/10 transition-colors"
        title="Réduire le menu">
        <i class="fas fa-angles-left text-xs portal-collapse-icon-expanded"></i>
        <i class="fas fa-angles-right text-xs portal-collapse-icon-collapsed hidden"></i>
    </button>
</div>

<nav class="flex-1 overflow-y-auto overflow-x-hidden py-2 px-1.5 space-y-0.5">
    @if($showPersonal && ! $isProvisionnel)
        <a href="{{ route('personal.dashboard') }}" title="Mes demandes"
           class="portal-nav-link {{ request()->routeIs('personal.dashboard', 'personal.requests-dashboard', 'personal.index') ? 'active' : '' }}">
            <i class="fas fa-folder-open"></i>
            <span class="portal-sidebar-label truncate">Mes demandes</span>
        </a>
    @endif

    @if($showCorbeille)
        <a href="{{ route('personal.cart') }}" title="Corbeille"
           class="portal-nav-link {{ request()->routeIs('personal.cart*') ? 'active' : '' }}">
            <i class="fas fa-inbox"></i>
            <span class="portal-sidebar-label truncate">Corbeille</span>
        </a>
    @endif

    @if($isProvisionnel)
        <a href="{{ route('compte.provisoire') }}" title="Espace provisoire"
           class="portal-nav-link {{ request()->routeIs('compte.provisoire') ? 'active' : '' }}">
            <i class="fas fa-hourglass-half"></i>
            <span class="portal-sidebar-label truncate">Espace provisoire</span>
        </a>
    @endif

    @if($demandeCompte)
        <a href="{{ route('compte.demande.show') }}" title="Demande de compte"
           class="portal-nav-link {{ request()->routeIs('compte.demande.*') ? 'active' : '' }}">
            <i class="fas fa-user-plus"></i>
            <span class="portal-sidebar-label truncate">Demande de compte</span>
        </a>
    @endif

    @if($hasPortal && ! $isProvisionnel)
        <a href="{{ route('compte.affiliations.index') }}" title="Mes codes pension"
           class="portal-nav-link {{ request()->routeIs('compte.affiliations.*') ? 'active' : '' }}">
            <i class="fas fa-id-card"></i>
            <span class="portal-sidebar-label truncate">Codes pension</span>
        </a>
        <!-- <a href="{{ route('compte.beneficiaires.index') }}" title="Mes bénéficiaires"
           class="portal-nav-link {{ request()->routeIs('compte.beneficiaires.*', 'compte.relations.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span class="portal-sidebar-label truncate">Bénéficiaires</span>
        </a>
        <a href="{{ route('compte.mandats.index') }}" title="Mes mandats"
           class="portal-nav-link {{ request()->routeIs('compte.mandats.*') ? 'active' : '' }}">
            <i class="fas fa-file-signature"></i>
            <span class="portal-sidebar-label truncate">Mandats</span>
        </a> -->
    @endif

    @role(['validateur_rdv', 'responsable_service_accueil_formalites', 'directeur', 'assistant_directeur'])
        <a href="{{ route('rencontres.pilotage.index') }}" title="Pilotage des RDV"
           class="portal-nav-link {{ request()->routeIs('rencontres.pilotage.index', 'rencontres.pilotage.show') ? 'active' : '' }}">
            <i class="fas fa-calendar-check"></i>
            <span class="portal-sidebar-label truncate">Pilotage RDV</span>
        </a>
    @endrole

    @role(\App\Models\User::ROLE_AGENT_RDV)
        <a href="{{ route('rencontres.pilotage.disponibilites.edit') }}" title="Mes créneaux"
           class="portal-nav-link {{ request()->routeIs('rencontres.pilotage.disponibilites.*') ? 'active' : '' }}">
            <i class="fas fa-clock"></i>
            <span class="portal-sidebar-label truncate">Mes créneaux</span>
        </a>
    @endrole

    @role(\App\Models\User::ROLE_AGENT_FORMALITES)
        <a href="{{ route('formalites.comptes-demandes.index') }}" title="Demandes de compte"
           class="portal-nav-link {{ request()->routeIs('formalites.comptes-demandes.*') ? 'active' : '' }}">
            <i class="fas fa-user-check"></i>
            <span class="portal-sidebar-label truncate">Comptes (form.)</span>
        </a>
    @endrole

    @role(['responsable_service_accueil_formalites', \App\Models\User::ROLE_VALIDATEUR_RDV, \App\Models\User::ROLE_AGENT_FORMALITES, 'admin', \App\Models\User::ROLE_DIRECTEUR, \App\Models\User::ROLE_ASSISTANT_DIRECTEUR])
        <a href="{{ route('formalites.suivi.index') }}" title="Suivi formalités"
           class="portal-nav-link {{ request()->routeIs('formalites.suivi.*') ? 'active' : '' }}">
            <i class="fas fa-clipboard-check"></i>
            <span class="portal-sidebar-label truncate">Suivi formalités</span>
        </a>
    @endrole

    <!-- @if(auth()->user()?->can('pension_mandats.view') || auth()->user()?->can('beneficiaire_relations.view'))
        <a href="{{ route('formalites.representations.index') }}" title="Mandats et relations"
           class="portal-nav-link {{ request()->routeIs('formalites.representations.*', 'formalites.pension-mandats.*', 'formalites.beneficiaire-relations.*') ? 'active' : '' }}">
            <i class="fas fa-people-arrows"></i>
            <span class="portal-sidebar-label truncate">Mandats &amp; rel.</span>
        </a>
    @endif -->

    <div class="portal-sidebar-label portal-nav-divider my-2 border-t border-white/10"></div>

    <a href="{{ route('home') }}" title="Accueil public" class="portal-nav-link">
        <i class="fas fa-home"></i>
        <span class="portal-sidebar-label truncate">Accueil</span>
    </a>
    @if($showRencontreCreate)
        <a href="{{ route('demandes.rencontre.create') }}" title="Demande de rendez-vous"
           class="portal-nav-link {{ request()->routeIs('demandes.rencontre.create', 'demandes.rencontre.store') ? 'active' : '' }}">
            <i class="fas fa-video"></i>
            <span class="portal-sidebar-label truncate">Rendez-vous</span>
        </a>
    @endif
</nav>

<div class="border-t border-white/10 px-1.5 py-2 space-y-0.5">
    <a href="{{ route('notifications.index') }}" title="Notifications"
       class="portal-nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
        <i class="fas fa-bell"></i>
        <span class="portal-sidebar-label truncate">Notifications</span>
    </a>

    @if(! $isProvisionnel)
        <a href="{{ route('profile.edit') }}" title="Mon profil"
           class="portal-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="fas fa-user-circle"></i>
            <span class="portal-sidebar-label truncate">Mon profil</span>
        </a>
    @endif

    @role('admin')
        <a href="{{ route('admin.dashboard.index') }}" title="Administration" class="portal-nav-link">
            <i class="fas fa-cog"></i>
            <span class="portal-sidebar-label truncate">Administration</span>
        </a>
    @endrole

    @auth
        <div class="portal-sidebar-user flex items-center gap-2 px-2 py-2">
            @if($user->profile_photo)
                <img src="{{ Storage::url($user->profile_photo) }}"
                     class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
            @else
                <div class="w-7 h-7 rounded-full bg-slate-600 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-user text-slate-300 text-xs"></i>
                </div>
            @endif
            <div class="portal-sidebar-label flex-1 min-w-0">
                <p class="text-white text-xs font-medium truncate">{{ $user->name }}</p>
                <p class="text-slate-500 text-[10px] truncate">{{ $user->accountStatusLabel() }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="portal-sidebar-label flex-shrink-0">
                @csrf
                <button type="submit" title="Déconnexion"
                    class="text-slate-500 hover:text-red-400 transition-colors p-1">
                    <i class="fas fa-sign-out-alt text-sm"></i>
                </button>
            </form>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="hidden portal-sidebar-collapsed-only">
            @csrf
            <button type="submit" title="Déconnexion" class="portal-nav-link w-full">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </form>
    @endauth
</div>
