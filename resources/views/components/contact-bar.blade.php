<div class="bg-[#064991] text-white text-sm
    @if($borderType === 'top') border-t-4 border-yellow-600 @endif
    @if($borderType === 'bottom') border-b-4 border-yellow-600 @endif
">
    <div class="container mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center px-4 py-2 md:py-0 md:h-10">
            <!-- Contact Info -->
            <div class="flex flex-wrap justify-center md:justify-start gap-4 mb-2 md:mb-0">
                <a href="mailto:contact@example.com" class="flex items-center gap-1 hover:text-blue-200 transition-colors">
                    <i class="fas fa-envelope text-xs"></i>
                    <span>dpc.info@mef.gouv.ht</span>
                </a>
                <a href="tel:1234567890" class="flex items-center gap-1 hover:text-blue-200 transition-colors">
                    <i class="fas fa-phone text-xs"></i>
                    <span>+(509) 29 92 1007</span>
                </a>
            </div>

            <!-- Social Media -->
            <div class="flex items-center gap-3">
                <a href="#" class="hover:text-blue-200 transition-colors" aria-label="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="hover:text-blue-200 transition-colors" aria-label="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="hover:text-blue-200 transition-colors" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="hover:text-blue-200 transition-colors" aria-label="LinkedIn">
                    <i class="fab fa-linkedin-in"></i>
                </a>
                <a href="#" class="hover:text-blue-200 transition-colors" aria-label="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>
        </div>
    </div>
</div>
