<div x-data="{ open: false }" x-show="open" @keydown.escape="open = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <!-- Overlay -->
    <div class="fixed inset-0 bg-black opacity-50"></div>

    <!-- Modal -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold">Visioconférence Jitsi</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Jitsi Container -->
            <div id="jitsi-container" class="p-4" style="height: 600px;"></div>
        </div>
    </div>
</div>

<script>
    function openJitsiModal(roomName) {
        const modal = document.querySelector('[x-data]').__x.$data;
        modal.open = true;

        // Initialiser Jitsi une fois le modal ouvert
        if (typeof JitsiMeetExternalAPI === 'function') {
            const domain = "meet.jit.si";
            const options = {
                roomName: roomName,
                parentNode: document.querySelector('#jitsi-container')
            };
            const api = new JitsiMeetExternalAPI(domain, options);
        } else {
            console.error('Jitsi Meet External API not loaded.');
        }
    }
</script>
