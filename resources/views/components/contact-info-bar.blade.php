<div class="container mx-auto relative overflow-hidden bg-[#173052] bg-motif-dots text-white text-sm
    @if($borderType === 'top') border-t border-white/20 @endif
    @if($borderType === 'bottom') border-b border-white/20 @endif
">
    <div class="relative z-10 px-4 py-2 md:py-0 md:h-10 flex items-center justify-center md:justify-start">
            <!-- Contact Info + Hours -->
            <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-4 text-center md:text-left">
                <!-- Hours -->
                <div class="flex items-center justify-center md:justify-start gap-1">
                    <i class="fas fa-clock text-xs"></i>
                    <span>Horaires d'ouverture : Lun - Ven 8.00 am - 4.00 pm</span>
                </div>

                <span class="hidden md:inline opacity-50">|</span>

                <!-- Email -->
                <a href="mailto:dpc.info@mef.gouv.ht" class="flex items-center justify-center md:justify-start gap-1 hover:text-orange-400 transition-colors">
                    <i class="fas fa-envelope text-xs"></i>
                    <span>dpc.info@mef.gouv.ht</span>
                </a>

                <span class="hidden md:inline opacity-50">|</span>

                <!-- Phone -->
                <a href="tel:+50929921007" class="flex items-center justify-center md:justify-start gap-1 hover:text-orange-400 transition-colors">
                    <i class="fas fa-phone text-xs"></i>
                    <span>Appeler sur : +509 29 92 1007</span>
                </a>
            </div>
    </div>
</div>
