import re
import pathlib

path = pathlib.Path("resources/views/layouts/admin.blade.php")
text = path.read_text(encoding="utf-8")

text = text.replace(
    'class="nav-section mt-2 w-full',
    'class="{{ $adminNavSection }} mt-2 w-full',
)

def link_repl(m):
    cond = m.group(1)
    extra = (m.group(2) or "").strip()
    if extra:
        return f"class=\"{{{{ $adminNavLink({cond}, '{extra}') }}}}\""
    return f'class="{{{{ $adminNavLink({cond}) }}}}"'

text = re.sub(
    r'class="nav-link \{\{ ([^}]+) \}\}( text-xs)?"',
    link_repl,
    text,
)

old_php = """@php
                $adminNavSection = 'px-3 pt-2 pb-1 text-[0.65rem] font-bold tracking-wider text-slate-500 uppercase';
            @endphp"""

new_php = """@php
                $adminNavSection = 'px-3 pt-2 pb-1 text-[0.65rem] font-bold tracking-wider text-slate-500 uppercase';
                $adminNavLink = function (bool $active, string $extra = ''): string {
                    $size = str_contains($extra, 'text-xs') ? 'text-xs' : 'text-[0.825rem]';
                    $base = 'flex items-center gap-2.5 py-[0.45rem] px-3.5 rounded-md font-medium whitespace-nowrap transition-colors [&>i]:w-4 [&>i]:shrink-0 [&>i]:text-center [&>i]:text-[0.85rem] '.$size;
                    $state = $active ? 'bg-white/20 text-white' : 'text-slate-400 hover:bg-white/10 hover:text-slate-200';
                    return trim($base.' '.$state);
                };
            @endphp"""

text = text.replace(old_php, new_php)
path.write_text(text, encoding="utf-8")
print("converted", text.count("nav-link"))
