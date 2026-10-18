import re
import pathlib

path = pathlib.Path("resources/views/layouts/partials/app-sidebar.blade.php")
text = path.read_text(encoding="utf-8")

text = text.replace('onclick="togglePortalSidebarCollapse()"', '@click="toggleCollapse()"')

text = text.replace(
    'class="portal-sidebar-brand flex items-center gap-2 px-3 py-3 border-b border-white/10"',
    'class="portal-sidebar-brand flex items-center gap-2 px-3 py-3 border-b border-white/10 group-data-[collapsed=true]/body:justify-center group-data-[collapsed=true]/body:px-2"',
)

text = text.replace(
    '<div class="portal-sidebar-label leading-tight min-w-0 overflow-hidden">',
    '<div class="leading-tight min-w-0 overflow-hidden truncate group-data-[collapsed=true]/body:hidden">',
)

text = text.replace(
    'class="hidden lg:flex flex-shrink-0 w-7 h-7 items-center justify-center rounded-md text-slate-400 hover:text-white hover:bg-white/10 transition-colors"',
    'class="hidden lg:flex flex-shrink-0 w-7 h-7 items-center justify-center rounded-md text-slate-400 hover:text-white hover:bg-white/10 transition-colors group-data-[collapsed=true]/body:mx-auto"',
)

text = text.replace(
    'class="fas fa-angles-left text-xs portal-collapse-icon-expanded"',
    'class="fas fa-angles-left text-xs group-data-[collapsed=true]/body:hidden"',
)
text = text.replace(
    'class="fas fa-angles-right text-xs portal-collapse-icon-collapsed hidden"',
    'class="fas fa-angles-right text-xs hidden group-data-[collapsed=true]/body:inline"',
)

text = text.replace(
    'class="portal-sidebar-brand flex items-center gap-2 px-3 py-3 border-b border-white/10 group-data-[collapsed=true]/body:justify-center group-data-[collapsed=true]/body:px-2"',
    'class="flex items-center gap-2 px-3 py-3 border-b border-white/10 group-data-[collapsed=true]/body:justify-center group-data-[collapsed=true]/body:px-2"',
)

text = text.replace(
    '<a href="{{ $sidebarHome }}" class="flex items-center gap-2 min-w-0 flex-1 group" title="Mon espace">',
    '<a href="{{ $sidebarHome }}" class="flex items-center gap-2 min-w-0 flex-1 group group-data-[collapsed=true]/body:flex-none" title="Mon espace">',
)

text = text.replace(
    '<div class="portal-sidebar-label portal-nav-divider my-2 border-t border-white/10"></div>',
    '<div class="my-2 border-t border-white/10 mx-1.5 group-data-[collapsed=true]/body:mx-2"></div>',
)

text = text.replace(
    '<div class="portal-sidebar-user flex items-center gap-2 px-2 py-2">',
    '<div class="flex items-center gap-2 px-2 py-2 group-data-[collapsed=true]/body:hidden">',
)

text = text.replace(
    '<div class="portal-sidebar-label flex-1 min-w-0">',
    '<div class="flex-1 min-w-0">',
)

text = text.replace(
    '<form method="POST" action="{{ route(\'logout\') }}" class="portal-sidebar-label flex-shrink-0">',
    '<form method="POST" action="{{ route(\'logout\') }}" class="flex-shrink-0">',
)

text = text.replace(
    'class="hidden portal-sidebar-collapsed-only"',
    'class="hidden group-data-[collapsed=true]/body:block"',
)

# portal nav links -> helper
php = """@php
    $portalNavLink = function (bool $active): string {
        $base = 'flex items-center gap-2.5 min-h-9 py-1.5 px-2.5 rounded-md text-[0.8rem] font-medium transition-colors group-data-[collapsed=true]/body:justify-center group-data-[collapsed=true]/body:px-2 [&>i]:w-5 [&>i]:shrink-0 [&>i]:text-center [&>i]:text-[0.85rem]';
        $state = $active ? 'bg-white/20 text-white' : 'text-slate-400 hover:bg-white/10 hover:text-slate-200';
        return trim($base.' '.$state);
    };
    $portalNavLabel = 'truncate group-data-[collapsed=true]/body:hidden';
@endphp

"""

if '$portalNavLink' not in text:
    text = text.replace('@endphp\n\n<div class="flex', '@endphp\n\n' + php + '<div class="flex')

text = re.sub(
    r'class="portal-nav-link \{\{ ([^}]+) \}\}"',
    lambda m: f'class="{{{{ $portalNavLink({m.group(1)}) }}}}"',
    text,
)
text = text.replace('class="portal-nav-link"', 'class="{{ $portalNavLink(false) }}"')
text = text.replace('class="portal-nav-link w-full"', 'class="{{ $portalNavLink(false) }} w-full"')

text = text.replace(
    '<span class="portal-sidebar-label truncate">',
    '<span class="{{ $portalNavLabel }}">',
)

path.write_text(text, encoding="utf-8")
print("portal-nav-link count", text.count("portal-nav-link"))
