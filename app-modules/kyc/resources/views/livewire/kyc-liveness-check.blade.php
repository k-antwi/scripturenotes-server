<div class="w-full max-w-lg">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">

        {{-- Step 4: Success --}}
        @if($step === 4)
        <div class="p-8">
            <div class="w-full px-4 py-5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="font-semibold text-green-800 dark:text-green-300 text-sm">
                    Your liveness verification video has been recorded successfully. Click Continue to
                    proceed with your verification.
                </p>
            </div>
            <div class="mt-6 flex justify-end">
                <a href="/dashboard" wire:navigate
                    class="px-6 py-2.5 text-sm font-medium text-white bg-blue-900 rounded-lg hover:bg-blue-800 transition-colors">
                    Continue
                </a>
            </div>
        </div>

        {{-- Step 3: Review & Confirm --}}
        @elseif($step === 3)
        <div class="p-8 space-y-6" x-data="{ blobUrl: $store.livenessVideo?.url }">
            <div>
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Review your video</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Please review the recording below. If you're happy with it, confirm to upload.
                </p>
            </div>

            {{-- Video playback --}}
            <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden bg-zinc-900">
                <video x-bind:src="blobUrl" controls playsinline
                    class="w-full max-h-72 object-contain"></video>
            </div>

            {{-- Upload progress --}}
            <div wire:loading wire:target="saveVideo" class="flex items-center gap-2 text-sm text-zinc-500">
                <svg class="w-4 h-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Uploading video...
            </div>

            @error('video')
            <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <button wire:click="retake" type="button"
                    class="px-5 py-2.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                    Retake
                </button>
                <button wire:click="saveVideo" type="button"
                    class="px-5 py-2.5 text-sm font-medium text-white bg-blue-900 rounded-lg hover:bg-blue-800 transition-colors">
                    Confirm &amp; Upload
                </button>
            </div>
        </div>

        {{-- Step 2: Recording --}}
        @elseif($step === 2)
        <div class="p-8 space-y-6" x-data="livenessRecorder()" x-init="startCamera()">
            <div>
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Liveness verification</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Follow the prompts on screen. The recording will last about 10 seconds.
                </p>
            </div>

            {{-- Camera feed --}}
            <div class="relative border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden bg-zinc-900">
                <video x-ref="preview" autoplay playsinline muted
                    class="w-full max-h-72 object-cover" style="transform: scaleX(-1);"></video>

                {{-- Prompt overlay --}}
                <div x-show="recording" x-transition
                    class="absolute inset-0 flex flex-col items-center justify-end pb-6 pointer-events-none">
                    {{-- Progress bar --}}
                    <div class="absolute top-0 left-0 right-0 h-1 bg-zinc-700/50">
                        <div class="h-full bg-blue-500 transition-all duration-300"
                            :style="'width: ' + progress + '%'"></div>
                    </div>
                    {{-- Prompt text --}}
                    <div class="bg-black/60 backdrop-blur-sm px-6 py-3 rounded-lg">
                        <p class="text-white text-sm font-semibold text-center" x-text="currentPrompt"></p>
                    </div>
                </div>

                {{-- Recording indicator --}}
                <div x-show="recording" class="absolute top-3 right-3 flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse"></span>
                    <span class="text-xs font-medium text-white bg-black/50 px-2 py-0.5 rounded">REC</span>
                </div>

                {{-- Camera error --}}
                <div x-show="cameraError" x-cloak
                    class="absolute inset-0 flex flex-col items-center justify-center bg-zinc-900 text-center px-6">
                    <x-phosphor-camera-slash class="w-10 h-10 text-zinc-500 mb-3" />
                    <p class="text-sm text-zinc-400 font-medium" x-text="cameraError"></p>
                    <button @click="startCamera()" type="button"
                        class="mt-3 text-sm font-medium text-blue-400 hover:text-blue-300">
                        Try again
                    </button>
                </div>
            </div>

            {{-- Unsupported browser warning --}}
            <div x-show="!supported" x-cloak
                class="px-4 py-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                <div class="flex items-center gap-2">
                    <x-phosphor-warning class="w-4 h-4 text-amber-500 flex-shrink-0" />
                    <span class="text-sm text-amber-700 dark:text-amber-300">
                        Your browser does not support video recording. Please use a modern browser like Chrome, Firefox, or Edge.
                    </span>
                </div>
            </div>

            {{-- Controls --}}
            <div class="flex justify-end" x-show="!recording && !cameraError && supported">
                <button @click="startRecording()" type="button"
                    x-show="!recorded"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-blue-900 rounded-lg hover:bg-blue-800 transition-colors">
                    Start Recording
                </button>
            </div>

            {{-- Hidden file input for Livewire upload --}}
            <input type="file" x-ref="videoInput" wire:model="video" class="hidden" accept="video/*" />
        </div>

        {{-- Step 1: Instructions --}}
        @else
        <div class="p-8 space-y-6">
            <div>
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Liveness verification</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    To confirm your identity, we need to record a short video of you. You will be asked to
                    follow simple head movement prompts while your camera records.
                </p>
            </div>

            {{-- Tips --}}
            <div x-data="{ open: false }" class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                <button @click="open = !open" type="button"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                    <span>Tips</span>
                    <x-phosphor-caret-down class="w-4 h-4 transition-transform" ::class="open && 'rotate-180'" />
                </button>
                <div x-show="open" x-collapse class="px-4 pb-4 space-y-3">
                    <div>
                        <p class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1">What will happen:</p>
                        <ul class="text-xs text-zinc-500 dark:text-zinc-400 space-y-0.5 list-disc list-inside">
                            <li>Your camera will record a short 10-second video</li>
                            <li>You will be asked to look straight, then turn left, then right</li>
                            <li>The recording will stop automatically when complete</li>
                            <li>You can review and retake before submitting</li>
                        </ul>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Keep in mind:</p>
                        <ul class="text-xs text-zinc-500 dark:text-zinc-400 space-y-0.5 list-disc list-inside">
                            <li>Ensure your face is well-lit and clearly visible</li>
                            <li>Remove hats, sunglasses, or anything covering your face</li>
                            <li>Use a plain background if possible</li>
                            <li>Keep the camera at eye level</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Info box --}}
            <div class="px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-center gap-2">
                    <x-phosphor-info class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0" />
                    <span class="text-sm text-blue-700 dark:text-blue-300">
                        You will need to allow camera access when prompted by your browser.
                    </span>
                </div>
            </div>

            {{-- Begin button --}}
            <div class="flex justify-end">
                <button wire:click="$set('step', 2)" type="button"
                    class="px-6 py-2.5 text-sm font-medium text-white bg-blue-900 rounded-lg hover:bg-blue-800 transition-colors">
                    Begin
                </button>
            </div>
        </div>
        @endif

    </div>
</div>

@script
<script>
Alpine.store('livenessVideo', { url: null, file: null });

Alpine.data('livenessRecorder', () => ({
    stream: null,
    recorder: null,
    chunks: [],
    recording: false,
    recorded: false,
    currentPrompt: '',
    progress: 0,
    cameraError: null,
    supported: !!(navigator.mediaDevices?.getUserMedia && typeof MediaRecorder !== 'undefined'),

    prompts: [
        { text: 'Look straight at the camera', duration: 2000 },
        { text: 'Slowly turn your head to the left', duration: 3000 },
        { text: 'Slowly turn your head to the right', duration: 3000 },
        { text: 'Look straight at the camera', duration: 2000 },
    ],

    async startCamera() {
        if (!this.supported) return;

        this.cameraError = null;

        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false,
            });
            this.$refs.preview.srcObject = this.stream;
        } catch (err) {
            if (err.name === 'NotAllowedError') {
                this.cameraError = 'Camera access was denied. Please allow camera access and try again.';
            } else {
                this.cameraError = 'Could not access your camera. Please check your device settings.';
            }
        }
    },

    startRecording() {
        if (!this.stream) return;

        this.chunks = [];
        this.recording = true;
        this.recorded = false;
        this.progress = 0;

        const mimeType = MediaRecorder.isTypeSupported('video/webm;codecs=vp9')
            ? 'video/webm;codecs=vp9'
            : MediaRecorder.isTypeSupported('video/webm')
                ? 'video/webm'
                : 'video/mp4';

        this.recorder = new MediaRecorder(this.stream, {
            mimeType,
            videoBitsPerSecond: 500000,
        });

        this.recorder.ondataavailable = (e) => {
            if (e.data.size > 0) this.chunks.push(e.data);
        };

        this.recorder.onstop = () => {
            const ext = mimeType.includes('webm') ? 'webm' : 'mp4';
            const blob = new Blob(this.chunks, { type: mimeType });
            const file = new File([blob], `liveness.${ext}`, { type: mimeType });

            // Store for review playback
            Alpine.store('livenessVideo', {
                url: URL.createObjectURL(blob),
                file: file,
            });

            // Set on the hidden file input for Livewire upload
            const dt = new DataTransfer();
            dt.items.add(file);
            this.$refs.videoInput.files = dt.files;
            this.$refs.videoInput.dispatchEvent(new Event('change', { bubbles: true }));

            this.stopCamera();
            this.recording = false;
            this.recorded = true;

            // Move to review step
            $wire.set('step', 3);
        };

        this.recorder.start(500);
        this.runPrompts();
    },

    runPrompts() {
        const totalDuration = this.prompts.reduce((sum, p) => sum + p.duration, 0);
        let elapsed = 0;
        let promptIndex = 0;

        const showPrompt = () => {
            if (promptIndex >= this.prompts.length) {
                this.recorder.stop();
                return;
            }

            const prompt = this.prompts[promptIndex];
            this.currentPrompt = prompt.text;

            // Animate progress over this prompt's duration
            const startElapsed = elapsed;
            const interval = setInterval(() => {
                elapsed += 100;
                this.progress = Math.min(100, (elapsed / totalDuration) * 100);

                if (elapsed >= startElapsed + prompt.duration) {
                    clearInterval(interval);
                    promptIndex++;
                    showPrompt();
                }
            }, 100);
        };

        showPrompt();
    },

    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
    },

    destroy() {
        this.stopCamera();
    },
}));
</script>
@endscript
